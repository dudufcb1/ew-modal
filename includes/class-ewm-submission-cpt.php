<?php
/**
 * EWM Leads Custom Post Type
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clase para manejar el Custom Post Type de leads generados por formularios
 */
class EWM_Submission_CPT {

	/**
	 * Post type name
	 */
	const POST_TYPE = 'ewm_submission';

	/**
	 * Instancia singleton
	 */
	private static $instance = null;

	/**
	 * Meta fields del lead
	 */
	private $meta_fields = array(
		'modal_id',             // ID del modal origen
		'form_data',            // JSON con datos del formulario
		'step_data',            // JSON con datos de pasos
		'submission_time',      // Timestamp del envío
		'ip_address',           // IP del usuario
		'user_agent',           // User agent del navegador
		'referer_url',          // URL de referencia
		'user_id',              // ID del usuario (si está logueado)
		'session_id',           // ID de sesión
		'conversion_value',     // Valor de conversión (para analytics)
		'status',               // Estado del envío (new, processed, archived)
		'notes',                 // Notas adicionales
	);

	/**
	 * Constructor privado para singleton
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Obtener instancia singleton
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Inicializar la clase
	 */
	private function init() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_meta_fields' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta_fields' ) );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'add_custom_columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'custom_column_content' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'modify_row_actions' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_trigger_title_update' ) );
		add_filter( 'bulk_actions-edit-' . self::POST_TYPE, array( $this, 'add_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-' . self::POST_TYPE, array( $this, 'handle_bulk_actions' ), 10, 3 );
		add_action( 'admin_notices', array( $this, 'show_bulk_action_notices' ) );
	}

	/**
	 * Registrar el Custom Post Type
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Leads', 'Post type general name', 'ewm-modal-cta' ),
			'singular_name'      => _x( 'Lead', 'Post type singular name', 'ewm-modal-cta' ),
			'menu_name'          => _x( 'Leads', 'Admin Menu text', 'ewm-modal-cta' ),
			'name_admin_bar'     => _x( 'Lead', 'Add New on Toolbar', 'ewm-modal-cta' ),
			'add_new'            => __( 'Agregar Nuevo', 'ewm-modal-cta' ),
			'add_new_item'       => __( 'Agregar Nuevo Lead', 'ewm-modal-cta' ),
			'new_item'           => __( 'Nuevo Lead', 'ewm-modal-cta' ),
			'edit_item'          => __( 'Ver Lead', 'ewm-modal-cta' ),
			'view_item'          => __( 'Ver Lead', 'ewm-modal-cta' ),
			'all_items'          => __( 'Todos los Envíos', 'ewm-modal-cta' ),
			'search_items'       => __( 'Buscar Envíos', 'ewm-modal-cta' ),
			'not_found'          => __( 'No se encontraron envíos.', 'ewm-modal-cta' ),
			'not_found_in_trash' => __( 'No se encontraron envíos en la papelera.', 'ewm-modal-cta' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => 'edit.php?post_type=ew_modal',
			'query_var'          => false,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'capabilities'       => array(
				'create_posts' => 'do_not_allow', // Evitar creación manual
			),
			'map_meta_cap'       => true,
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title' ),
			'show_in_rest'       => false, // No exponer en REST API por privacidad
		);

		register_post_type( self::POST_TYPE, $args );

		ewm_log_info(
			'Submission CPT registered successfully',
			array(
				'post_type'    => self::POST_TYPE,
				'show_in_rest' => false,
			)
		);
	}

	/**
	 * Registrar meta fields
	 */
	public function register_meta_fields() {
		foreach ( $this->meta_fields as $meta_key ) {
			register_post_meta(
				self::POST_TYPE,
				$meta_key,
				array(
					'show_in_rest'  => false, // Privacidad
					'single'        => true,
					'type'          => 'string',
					'auth_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}

		ewm_log_debug(
			'Submission meta fields registered',
			array(
				'fields_count' => count( $this->meta_fields ),
				'fields'       => $this->meta_fields,
			)
		);
	}

	/**
	 * Agregar meta boxes
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'ewm-submission-details',
			__( 'Detalles del Lead', 'ewm-modal-cta' ),
			array( $this, 'render_details_meta_box' ),
			self::POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'ewm-submission-data',
			__( 'Datos del Formulario', 'ewm-modal-cta' ),
			array( $this, 'render_data_meta_box' ),
			self::POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'ewm-submission-meta',
			__( 'Información Técnica', 'ewm-modal-cta' ),
			array( $this, 'render_meta_box' ),
			self::POST_TYPE,
			'side',
			'high'
		);
	}

	/**
	 * Renderizar meta box de detalles
	 */
	public function render_details_meta_box( $post ) {
		$modal_id         = get_post_meta( $post->ID, 'modal_id', true );
		$status           = get_post_meta( $post->ID, 'status', true ) ?: 'new';
		$submission_time  = get_post_meta( $post->ID, 'submission_time', true );
		$conversion_value = get_post_meta( $post->ID, 'conversion_value', true );
		$notes            = get_post_meta( $post->ID, 'notes', true );

		$modal_title = $modal_id ? get_the_title( $modal_id ) : __( 'Modal eliminado', 'ewm-modal-cta' );

		?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Modal Origen', 'ewm-modal-cta' ); ?></th>
				<td>
					<?php if ( $modal_id && get_post( $modal_id ) ) : ?>
						<a href="<?php echo get_edit_post_link( $modal_id ); ?>">
							<?php echo esc_html( $modal_title ); ?> (ID: <?php echo $modal_id; ?>)
						</a>
					<?php else : ?>
						<em><?php echo esc_html( $modal_title ); ?></em>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Fecha del Lead', 'ewm-modal-cta' ); ?></th>
				<td>
					<?php
					if ( $submission_time ) {
						echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $submission_time ) );
					} else {
						echo __( 'No disponible', 'ewm-modal-cta' );
					}
					?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="submission_status"><?php _e( 'Estado', 'ewm-modal-cta' ); ?></label>
				</th>
				<td>
					<select name="submission_status" id="submission_status">
						<option value="new" <?php selected( $status, 'new' ); ?>>
							<?php _e( 'Nuevo', 'ewm-modal-cta' ); ?>
						</option>
						<option value="processed" <?php selected( $status, 'processed' ); ?>>
							<?php _e( 'Procesado', 'ewm-modal-cta' ); ?>
						</option>
						<option value="archived" <?php selected( $status, 'archived' ); ?>>
							<?php _e( 'Archivado', 'ewm-modal-cta' ); ?>
						</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="conversion_value"><?php _e( 'Valor de Conversión', 'ewm-modal-cta' ); ?></label>
				</th>
				<td>
					<input type="number" name="conversion_value" id="conversion_value" 
							value="<?php echo esc_attr( $conversion_value ); ?>" step="0.01" min="0">
					<p class="description">
						<?php _e( 'Valor monetario asociado a esta conversión (opcional).', 'ewm-modal-cta' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="submission_notes"><?php _e( 'Notas', 'ewm-modal-cta' ); ?></label>
				</th>
				<td>
					<textarea name="submission_notes" id="submission_notes" rows="3" class="large-text"><?php echo esc_textarea( $notes ); ?></textarea>
				</td>
			</tr>
		</table>
		<?php
		wp_nonce_field( 'ewm_submission_meta_box', 'ewm_submission_meta_box_nonce' );
	}

	/**
	 * Renderizar meta box de datos del formulario
	 */
	public function render_data_meta_box( $post ) {
		$form_data = get_post_meta( $post->ID, 'form_data', true );
		$step_data = get_post_meta( $post->ID, 'step_data', true );

		$form_data_decoded = $form_data ? json_decode( $form_data, true ) : array();
		$step_data_decoded = $step_data ? json_decode( $step_data, true ) : array();

		?>
		<div class="ewm-submission-data">
			<h4><?php _e( 'Datos del Formulario', 'ewm-modal-cta' ); ?></h4>
			<?php if ( ! empty( $form_data_decoded ) ) : ?>
				<table class="widefat">
					<thead>
						<tr>
							<th><?php _e( 'Campo', 'ewm-modal-cta' ); ?></th>
							<th><?php _e( 'Valor', 'ewm-modal-cta' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $form_data_decoded as $field => $value ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $field ); ?></strong></td>
								<td><?php echo esc_html( is_array( $value ) ? implode( ', ', $value ) : $value ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><em><?php _e( 'No hay datos de formulario disponibles.', 'ewm-modal-cta' ); ?></em></p>
			<?php endif; ?>
			
			<?php if ( ! empty( $step_data_decoded ) ) : ?>
				<h4 style="margin-top: 20px;"><?php _e( 'Datos de Pasos', 'ewm-modal-cta' ); ?></h4>
				<pre style="background: #f1f1f1; padding: 10px; overflow: auto; max-height: 200px;"><?php echo esc_html( wp_json_encode( $step_data_decoded, JSON_PRETTY_PRINT ) ); ?></pre>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Renderizar meta box de información técnica
	 */
	public function render_meta_box( $post ) {
		$ip_address  = get_post_meta( $post->ID, 'ip_address', true );
		$user_agent  = get_post_meta( $post->ID, 'user_agent', true );
		$referer_url = get_post_meta( $post->ID, 'referer_url', true );
		$user_id     = get_post_meta( $post->ID, 'user_id', true );
		$session_id  = get_post_meta( $post->ID, 'session_id', true );

		?>
		<table class="form-table">
			<tr>
				<th><?php _e( 'IP Address', 'ewm-modal-cta' ); ?></th>
				<td><?php echo esc_html( $ip_address ?: __( 'No disponible', 'ewm-modal-cta' ) ); ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Usuario', 'ewm-modal-cta' ); ?></th>
				<td>
					<?php
					if ( $user_id ) {
						$user = get_user_by( 'id', $user_id );
						if ( $user ) {
							echo '<a href="' . get_edit_user_link( $user_id ) . '">' . esc_html( $user->display_name ) . '</a>';
						} else {
							echo __( 'Usuario eliminado', 'ewm-modal-cta' );
						}
					} else {
						echo __( 'Usuario anónimo', 'ewm-modal-cta' );
					}
					?>
				</td>
			</tr>
			<tr>
				<th><?php _e( 'URL de Referencia', 'ewm-modal-cta' ); ?></th>
				<td>
					<?php if ( $referer_url ) : ?>
						<a href="<?php echo esc_url( $referer_url ); ?>" target="_blank">
							<?php echo esc_html( wp_trim_words( $referer_url, 8, '...' ) ); ?>
						</a>
					<?php else : ?>
						<?php _e( 'No disponible', 'ewm-modal-cta' ); ?>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th><?php _e( 'User Agent', 'ewm-modal-cta' ); ?></th>
				<td>
					<small><?php echo esc_html( wp_trim_words( $user_agent ?: __( 'No disponible', 'ewm-modal-cta' ), 10, '...' ) ); ?></small>
				</td>
			</tr>
			<?php if ( $session_id ) : ?>
			<tr>
				<th><?php _e( 'Session ID', 'ewm-modal-cta' ); ?></th>
				<td><code><?php echo esc_html( $session_id ); ?></code></td>
			</tr>
			<?php endif; ?>
		</table>
		<?php
	}

	/**
	 * Guardar meta fields
	 */
	public function save_meta_fields( $post_id ) {
		// Verificar nonce
		if ( ! isset( $_POST['ewm_submission_meta_box_nonce'] ) ||
			! wp_verify_nonce( $_POST['ewm_submission_meta_box_nonce'], 'ewm_submission_meta_box' ) ) {
			return;
		}

		// Verificar autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Verificar permisos
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Verificar post type
		if ( get_post_type( $post_id ) !== self::POST_TYPE ) {
			return;
		}

		// Guardar campos editables
		if ( isset( $_POST['submission_status'] ) ) {
			update_post_meta( $post_id, 'status', sanitize_text_field( $_POST['submission_status'] ) );
		}

		if ( isset( $_POST['conversion_value'] ) ) {
			$value = floatval( $_POST['conversion_value'] );
			update_post_meta( $post_id, 'conversion_value', $value );
		}

		if ( isset( $_POST['submission_notes'] ) ) {
			update_post_meta( $post_id, 'notes', sanitize_textarea_field( $_POST['submission_notes'] ) );
		}

		ewm_log_info(
			'Submission meta fields updated',
			array(
				'post_id'          => $post_id,
				'status'           => $_POST['submission_status'] ?? '',
				'conversion_value' => $_POST['conversion_value'] ?? '',
			)
		);
	}

	/**
	 * Agregar columnas personalizadas
	 */
	public function add_custom_columns( $columns ) {
		$new_columns                    = array();
		$new_columns['cb']              = $columns['cb'];
		$new_columns['title']           = $columns['title'];
		$new_columns['page_origin']     = __( 'Página de Origen', 'ewm-modal-cta' );
		$new_columns['modal']           = __( 'Modal', 'ewm-modal-cta' );
		$new_columns['status']          = __( 'Estado', 'ewm-modal-cta' );
		$new_columns['submission_date'] = __( 'Fecha del Lead', 'ewm-modal-cta' );
		$new_columns['user_info']       = __( 'Usuario', 'ewm-modal-cta' );

		return $new_columns;
	}

	/**
	 * Contenido de columnas personalizadas
	 */
	public function custom_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'page_origin':
				$referer_url = get_post_meta( $post_id, 'referer_url', true );
				if ( $referer_url ) {
					$page_name = self::detect_page_name_from_url( $referer_url );
					echo '<a href="' . esc_url( $referer_url ) . '" target="_blank" title="' . esc_attr( $referer_url ) . '">';
					echo '<span class="ewm-page-origin">' . esc_html( $page_name ) . '</span>';
					echo '<span class="dashicons dashicons-external" style="font-size: 12px; margin-left: 4px;"></span>';
					echo '</a>';
				} else {
					echo '<em style="color: #666;">' . __( 'No disponible', 'ewm-modal-cta' ) . '</em>';
				}
				break;

			case 'modal':
				$modal_id = get_post_meta( $post_id, 'modal_id', true );
				if ( $modal_id && get_post( $modal_id ) ) {
					echo '<a href="' . get_edit_post_link( $modal_id ) . '">' . get_the_title( $modal_id ) . '</a>';
				} else {
					echo '<em>' . __( 'Modal eliminado', 'ewm-modal-cta' ) . '</em>';
				}
				break;

			case 'status':
				$status        = get_post_meta( $post_id, 'status', true ) ?: 'new';
				$status_labels = array(
					'new'       => __( 'Nuevo', 'ewm-modal-cta' ),
					'processed' => __( 'Procesado', 'ewm-modal-cta' ),
					'archived'  => __( 'Archivado', 'ewm-modal-cta' ),
				);
				echo '<span class="ewm-status ewm-status-' . $status . '">' .
					( $status_labels[ $status ] ?? $status ) . '</span>';
				break;

			case 'submission_date':
				$submission_time = get_post_meta( $post_id, 'submission_time', true );
				if ( $submission_time ) {
					echo date_i18n( get_option( 'date_format' ), strtotime( $submission_time ) );
				} else {
					echo get_the_date( '', $post_id );
				}
				break;

			case 'user_info':
				$user_id    = get_post_meta( $post_id, 'user_id', true );
				$ip_address = get_post_meta( $post_id, 'ip_address', true );

				if ( $user_id ) {
					$user = get_user_by( 'id', $user_id );
					if ( $user ) {
						echo esc_html( $user->display_name );
					} else {
						echo __( 'Usuario eliminado', 'ewm-modal-cta' );
					}
				} else {
					echo __( 'Anónimo', 'ewm-modal-cta' );
				}

				if ( $ip_address ) {
					echo '<br><small>' . esc_html( $ip_address ) . '</small>';
				}
				break;
		}
	}

	/**
	 * Modificar acciones de fila
	 */
	public function modify_row_actions( $actions, $post ) {
		if ( $post->post_type === self::POST_TYPE ) {
			// Remover "Quick Edit" ya que no es necesario
			unset( $actions['inline hide-if-no-js'] );

			// Cambiar "Edit" por "View"
			if ( isset( $actions['edit'] ) ) {
				$actions['edit'] = str_replace( 'Editar', 'Ver', $actions['edit'] );
			}
		}

		return $actions;
	}

	/**
	 * Encolar estilos de administración
	 */
	public function enqueue_admin_styles( $hook ) {
		$screen = get_current_screen();
		
		// Solo cargar en páginas de envíos
		if ( ! $screen || $screen->post_type !== self::POST_TYPE ) {
			return;
		}

		wp_enqueue_style(
			'ewm-submission-admin',
			EWM_PLUGIN_URL . 'assets/css/modal-admin.css',
			array(),
			EWM_VERSION
		);

		ewm_log_debug( 'Submission admin styles enqueued', array( 'hook' => $hook, 'screen' => $screen->id ) );
	}

	/**
	 * Agregar acciones masivas personalizadas
	 */
	public function add_bulk_actions( $bulk_actions ) {
		$bulk_actions['ewm_update_titles'] = __( 'Actualizar títulos', 'ewm-modal-cta' );
		return $bulk_actions;
	}

	/**
	 * Manejar acciones masivas personalizadas
	 */
	public function handle_bulk_actions( $redirect_to, $doaction, $post_ids ) {
		if ( $doaction === 'ewm_update_titles' ) {
			$updated_count = 0;

			foreach ( $post_ids as $post_id ) {
				$referer_url = get_post_meta( $post_id, 'referer_url', true );
				$submission_time = get_post_meta( $post_id, 'submission_time', true );

				if ( $referer_url ) {
					$page_name = self::detect_page_name_from_url( $referer_url );
					
					if ( $submission_time ) {
						$formatted_date = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $submission_time ) );
					} else {
						$post_date = get_post_field( 'post_date', $post_id );
						$formatted_date = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $post_date ) );
					}

					$new_title = sprintf(
						__( 'Lead obtenido de: %1$s %2$s', 'ewm-modal-cta' ),
						$page_name,
						$formatted_date
					);

					$result = wp_update_post(
						array(
							'ID'         => $post_id,
							'post_title' => $new_title,
						),
						true
					);

					if ( ! is_wp_error( $result ) ) {
						$updated_count++;
					}
				}
			}

			$redirect_to = add_query_arg( 'ewm_updated_titles', $updated_count, $redirect_to );
		}

		return $redirect_to;
	}

	/**
	 * Mostrar mensajes de acciones masivas
	 */
	public function show_bulk_action_notices() {
		if ( ! empty( $_REQUEST['ewm_updated_titles'] ) ) {
			$count = intval( $_REQUEST['ewm_updated_titles'] );
			echo '<div class="notice notice-success is-dismissible">';
			echo '<p>' . sprintf( 
				_n( 
					'Se actualizó el título de %s envío.', 
					'Se actualizaron los títulos de %s envíos.', 
					$count, 
					'ewm-modal-cta' 
				), 
				number_format_i18n( $count ) 
			) . '</p>';
			echo '</div>';
		}
	}

	/**
	 * Crear nuevo lead de formulario
	 */
	public static function create_submission( $modal_id, $form_data, $step_data = array() ) {
		// Obtener información de la página de origen
		$referer_url = $_SERVER['HTTP_REFERER'] ?? '';
		$page_name   = __( 'Página desconocida', 'ewm-modal-cta' );

		if ( $referer_url ) {
			$page_name = self::detect_page_name_from_url( $referer_url );
		}

		// Crear título en formato: "Lead obtenido de: nombre_de_la_pagina fecha"
		$current_date = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
		$title        = sprintf(
			__( 'Lead obtenido de: %1$s %2$s', 'ewm-modal-cta' ),
			$page_name,
			$current_date
		);

		$submission_id = wp_insert_post(
			array(
				'post_type'   => self::POST_TYPE,
				'post_status' => 'private',
				'post_title'  => $title,
				'meta_input'  => array(
					'modal_id'        => $modal_id,
					'form_data'       => wp_json_encode( $form_data ),
					'step_data'       => wp_json_encode( $step_data ),
					'submission_time' => current_time( 'mysql' ),
					'ip_address'      => $_SERVER['REMOTE_ADDR'] ?? '',
					'user_agent'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
					'referer_url'     => $_SERVER['HTTP_REFERER'] ?? '',
					'user_id'         => get_current_user_id() ?: '',
					'session_id'      => session_id() ?: '',
					'status'          => 'new',
				),
			)
		);

		if ( ! is_wp_error( $submission_id ) ) {
			ewm_log_info(
				'New submission created',
				array(
					'submission_id' => $submission_id,
					'modal_id'      => $modal_id,
					'fields_count'  => count( $form_data ),
					'user_id'       => get_current_user_id(),
				)
			);
		}

		return $submission_id;
	}

	/**
	 * Detectar nombre de página desde URL con soporte avanzado
	 * 
	 * @param string $url URL de referencia
	 * @return string Nombre de la página detectado
	 */
	private static function detect_page_name_from_url( $url ) {
		$page_name = __( 'Página desconocida', 'ewm-modal-cta' );

		// Parsear URL
		$parsed_url = parse_url( $url );
		if ( ! $parsed_url || ! isset( $parsed_url['path'] ) ) {
			return $page_name;
		}

		$path = trim( $parsed_url['path'], '/' );
		$query = isset( $parsed_url['query'] ) ? $parsed_url['query'] : '';

		// Página de inicio
		if ( empty( $path ) ) {
			return __( 'Página de inicio', 'ewm-modal-cta' );
		}

		// Detectar WooCommerce si está activo
		if ( class_exists( 'WooCommerce' ) ) {
			$wc_page = self::detect_woocommerce_page( $path, $query );
			if ( $wc_page ) {
				return $wc_page;
			}
		}

		// Buscar página por slug
		$page = get_page_by_path( $path );
		if ( $page ) {
			return get_the_title( $page->ID );
		}

		// Buscar custom post type por slug
		$post = self::find_post_by_path( $path );
		if ( $post ) {
			return get_the_title( $post->ID );
		}

		// Detectar archivos o categorías
		$archive_name = self::detect_archive_page( $path );
		if ( $archive_name ) {
			return $archive_name;
		}

		// Fallback: usar path limpio
		return ucwords( str_replace( array( '-', '_', '/' ), ' ', $path ) );
	}

	/**
	 * Detectar páginas de WooCommerce
	 * 
	 * @param string $path Path de la URL
	 * @param string $query Query string
	 * @return string|false Nombre de página WC o false
	 */
	private static function detect_woocommerce_page( $path, $query ) {
		// Shop page
		if ( $path === 'shop' || $path === get_option( 'woocommerce_shop_page_id' ) ) {
			return __( 'Tienda', 'ewm-modal-cta' );
		}

		// Cart page
		if ( $path === 'cart' || $path === 'carrito' ) {
			return __( 'Carrito', 'ewm-modal-cta' );
		}

		// Checkout page
		if ( $path === 'checkout' || $path === 'finalizar-compra' ) {
			return __( 'Finalizar Compra', 'ewm-modal-cta' );
		}

		// My Account
		if ( $path === 'my-account' || $path === 'mi-cuenta' ) {
			return __( 'Mi Cuenta', 'ewm-modal-cta' );
		}

		// Product page
		$path_parts = explode( '/', $path );
		if ( isset( $path_parts[0] ) && ( $path_parts[0] === 'product' || $path_parts[0] === 'producto' ) ) {
			if ( isset( $path_parts[1] ) ) {
				$product = get_page_by_path( $path_parts[1], OBJECT, 'product' );
				if ( $product ) {
					return sprintf( __( 'Producto: %s', 'ewm-modal-cta' ), get_the_title( $product->ID ) );
				}
			}
			return __( 'Producto', 'ewm-modal-cta' );
		}

		// Product category
		if ( isset( $path_parts[0] ) && ( $path_parts[0] === 'product-category' || $path_parts[0] === 'categoria-producto' ) ) {
			if ( isset( $path_parts[1] ) ) {
				$term = get_term_by( 'slug', $path_parts[1], 'product_cat' );
				if ( $term ) {
					return sprintf( __( 'Categoría: %s', 'ewm-modal-cta' ), $term->name );
				}
			}
			return __( 'Categoría de Productos', 'ewm-modal-cta' );
		}

		return false;
	}

	/**
	 * Buscar post por path en cualquier post type
	 * 
	 * @param string $path Path a buscar
	 * @return WP_Post|false Post encontrado o false
	 */
	private static function find_post_by_path( $path ) {
		global $wpdb;

		// Obtener todos los post types públicos
		$post_types = get_post_types( array( 'public' => true ), 'names' );
		$post_types = array_diff( $post_types, array( 'page', 'attachment' ) ); // Excluir páginas y attachments

		if ( empty( $post_types ) ) {
			return false;
		}

		$post_types_in = "'" . implode( "', '", array_map( 'esc_sql', $post_types ) ) . "'";

		$sql = $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} 
			 WHERE post_name = %s 
			 AND post_type IN ({$post_types_in}) 
			 AND post_status = 'publish' 
			 LIMIT 1",
			$path
		);

		$post_id = $wpdb->get_var( $sql );

		return $post_id ? get_post( $post_id ) : false;
	}

	/**
	 * Detectar páginas de archivo (categorías, tags, etc.)
	 * 
	 * @param string $path Path de la URL
	 * @return string|false Nombre de archivo o false
	 */
	private static function detect_archive_page( $path ) {
		$path_parts = explode( '/', $path );

		// Category archive
		if ( isset( $path_parts[0] ) && ( $path_parts[0] === 'category' || $path_parts[0] === 'categoria' ) ) {
			if ( isset( $path_parts[1] ) ) {
				$term = get_term_by( 'slug', $path_parts[1], 'category' );
				if ( $term ) {
					return sprintf( __( 'Categoría: %s', 'ewm-modal-cta' ), $term->name );
				}
			}
			return __( 'Archivo de Categorías', 'ewm-modal-cta' );
		}

		// Tag archive
		if ( isset( $path_parts[0] ) && ( $path_parts[0] === 'tag' || $path_parts[0] === 'etiqueta' ) ) {
			if ( isset( $path_parts[1] ) ) {
				$term = get_term_by( 'slug', $path_parts[1], 'post_tag' );
				if ( $term ) {
					return sprintf( __( 'Etiqueta: %s', 'ewm-modal-cta' ), $term->name );
				}
			}
			return __( 'Archivo de Etiquetas', 'ewm-modal-cta' );
		}

		// Author archive
		if ( isset( $path_parts[0] ) && ( $path_parts[0] === 'author' || $path_parts[0] === 'autor' ) ) {
			if ( isset( $path_parts[1] ) ) {
				$user = get_user_by( 'slug', $path_parts[1] );
				if ( $user ) {
					return sprintf( __( 'Autor: %s', 'ewm-modal-cta' ), $user->display_name );
				}
			}
			return __( 'Archivo de Autor', 'ewm-modal-cta' );
		}

		// Date archive
		if ( is_numeric( $path_parts[0] ) && strlen( $path_parts[0] ) === 4 ) {
			$year = $path_parts[0];
			if ( isset( $path_parts[1] ) && is_numeric( $path_parts[1] ) ) {
				$month = $path_parts[1];
				$month_name = date_i18n( 'F', mktime( 0, 0, 0, $month, 1 ) );
				return sprintf( __( 'Archivo: %s %s', 'ewm-modal-cta' ), $month_name, $year );
			}
			return sprintf( __( 'Archivo: %s', 'ewm-modal-cta' ), $year );
		}

		return false;
	}

	/**
	 * Actualizar títulos de envíos existentes que están sin título
	 * Función utilitaria para migrar envíos antiguos al nuevo formato
	 */
	public static function update_existing_submission_titles() {
		global $wpdb;

		ewm_log_info( 'Starting bulk update of existing submission titles' );

		// Buscar envíos sin título o con título genérico
		$submissions = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'private',
				'numberposts'    => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => 'referer_url',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		$updated_count = 0;
		$total_count   = count( $submissions );

		ewm_log_info( "Found {$total_count} submissions to potentially update" );

		foreach ( $submissions as $submission_id ) {
			$current_title = get_the_title( $submission_id );
			
			// Actualizar solo si no tiene título o el título está vacío/genérico
			if ( empty( $current_title ) || $current_title === 'Auto Draft' || strpos( $current_title, '(no title)' ) !== false ) {
				$referer_url = get_post_meta( $submission_id, 'referer_url', true );
				$submission_time = get_post_meta( $submission_id, 'submission_time', true );

				if ( $referer_url ) {
					$page_name = self::detect_page_name_from_url( $referer_url );
					
					// Usar submission_time si existe, sino usar post_date
					if ( $submission_time ) {
						$formatted_date = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $submission_time ) );
					} else {
						$post_date = get_post_field( 'post_date', $submission_id );
						$formatted_date = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $post_date ) );
					}

					$new_title = sprintf(
						__( 'Lead obtenido de: %1$s %2$s', 'ewm-modal-cta' ),
						$page_name,
						$formatted_date
					);

					// Actualizar título
					$result = wp_update_post(
						array(
							'ID'         => $submission_id,
							'post_title' => $new_title,
						),
						true
					);

					if ( ! is_wp_error( $result ) ) {
						$updated_count++;
						ewm_log_debug( "Updated submission {$submission_id} title to: {$new_title}" );
					} else {
						ewm_log_warning( "Failed to update submission {$submission_id}: " . $result->get_error_message() );
					}
				}
			}
		}

		ewm_log_info( "Bulk title update completed: {$updated_count}/{$total_count} submissions updated" );

		return array(
			'total'   => $total_count,
			'updated' => $updated_count,
		);
	}

	/**
	 * Hook para ejecutar actualización de títulos en admin
	 * Se puede llamar desde una página de admin o vía wp-cli
	 */
	public static function maybe_trigger_title_update() {
		// Solo en admin y con permisos
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Verificar si se solicitó la actualización
		if ( isset( $_GET['ewm_update_titles'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'ewm_update_titles' ) ) {
			$result = self::update_existing_submission_titles();
			
			// Mostrar mensaje de admin
			add_action( 'admin_notices', function() use ( $result ) {
				echo '<div class="notice notice-success is-dismissible">';
				echo '<p><strong>EWM:</strong> ' . sprintf( 
					__( 'Títulos actualizados: %d de %d envíos procesados.', 'ewm-modal-cta' ),
					$result['updated'], 
					$result['total'] 
				) . '</p>';
				echo '</div>';
			} );

			// Redireccionar para evitar reenvío
			wp_redirect( remove_query_arg( array( 'ewm_update_titles', '_wpnonce' ) ) );
			exit;
		}
	}
}
