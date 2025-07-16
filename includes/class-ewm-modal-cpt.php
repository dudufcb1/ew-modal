<?php
/**
 * EWM Modal Custom Post Type
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clase para manejar el Custom Post Type de modales
 */
class EWM_Modal_CPT {

	/**
	 * Post type name
	 */
	const POST_TYPE = 'ew_modal';

	/**
	 * Instancia singleton
	 */
	private static $instance = null;

	/**
	 * Meta fields del modal
	 */
	private $meta_fields = array(
		'ewm_modal_mode',           // 'formulario' | 'anuncio'
		'ewm_steps_config',         // JSON con configuración de pasos (Opción A)
		'ewm_steps_serialized',     // String serializado para casos complejos (Opción B)
		'ewm_use_serialized',       // Boolean: true = usar serialized, false = usar JSON
		'ewm_design_config',        // JSON con estilos
		'ewm_trigger_config',       // JSON con triggers
		'ewm_wc_integration',       // JSON con datos de WooCommerce
		'ewm_display_rules',        // JSON con reglas de visualización
		'ewm_field_mapping',         // JSON para mapeo de campos personalizados
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
	}

	/**
	 * Registrar el Custom Post Type
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Modales', 'Post type general name', 'ewm-modal-cta' ),
			'singular_name'         => _x( 'Modal', 'Post type singular name', 'ewm-modal-cta' ),
			'menu_name'             => _x( 'EWM Modales', 'Admin Menu text', 'ewm-modal-cta' ),
			'name_admin_bar'        => _x( 'Modal', 'Add New on Toolbar', 'ewm-modal-cta' ),
			'add_new'               => __( 'Agregar Nuevo', 'ewm-modal-cta' ),
			'add_new_item'          => __( 'Agregar Nuevo Modal', 'ewm-modal-cta' ),
			'new_item'              => __( 'Nuevo Modal', 'ewm-modal-cta' ),
			'edit_item'             => __( 'Editar Modal', 'ewm-modal-cta' ),
			'view_item'             => __( 'Ver Modal', 'ewm-modal-cta' ),
			'all_items'             => __( 'Todos los Modales', 'ewm-modal-cta' ),
			'search_items'          => __( 'Buscar Modales', 'ewm-modal-cta' ),
			'parent_item_colon'     => __( 'Modal Padre:', 'ewm-modal-cta' ),
			'not_found'             => __( 'No se encontraron modales.', 'ewm-modal-cta' ),
			'not_found_in_trash'    => __( 'No se encontraron modales en la papelera.', 'ewm-modal-cta' ),
			'featured_image'        => _x( 'Imagen del Modal', 'Overrides the "Featured Image" phrase', 'ewm-modal-cta' ),
			'set_featured_image'    => _x( 'Establecer imagen del modal', 'Overrides the "Set featured image" phrase', 'ewm-modal-cta' ),
			'remove_featured_image' => _x( 'Remover imagen del modal', 'Overrides the "Remove featured image" phrase', 'ewm-modal-cta' ),
			'use_featured_image'    => _x( 'Usar como imagen del modal', 'Overrides the "Use as featured image" phrase', 'ewm-modal-cta' ),
			'archives'              => _x( 'Archivos de Modales', 'The post type archive label used in nav menus', 'ewm-modal-cta' ),
			'insert_into_item'      => _x( 'Insertar en modal', 'Overrides the "Insert into post" phrase', 'ewm-modal-cta' ),
			'uploaded_to_this_item' => _x( 'Subido a este modal', 'Overrides the "Uploaded to this post" phrase', 'ewm-modal-cta' ),
			'filter_items_list'     => _x( 'Filtrar lista de modales', 'Screen reader text for the filter links', 'ewm-modal-cta' ),
			'items_list_navigation' => _x( 'Navegación de lista de modales', 'Screen reader text for the pagination', 'ewm-modal-cta' ),
			'items_list'            => _x( 'Lista de modales', 'Screen reader text for the items list', 'ewm-modal-cta' ),
		);

		$args = array(
			'labels'                => $labels,
			'public'                => false,
			'publicly_queryable'    => true, // CAMBIO: Permitir consultas públicas para bloques dinámicos
			'show_ui'               => true,
			'show_in_menu'          => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'ew-modal' ),
			'capability_type'       => 'post',
			'has_archive'           => false,
			'hierarchical'          => false,
			'menu_position'         => null,
			'menu_icon'             => 'dashicons-admin-page',
			'supports'              => array( 'title' ),
			'show_in_rest'          => true,
			'rest_base'             => 'ew-modals',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		);

		register_post_type( self::POST_TYPE, $args );

		ewm_log_info(
			'Modal CPT registered successfully',
			array(
				'post_type'     => self::POST_TYPE,
				'supports_rest' => true,
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
					'show_in_rest'  => true,
					'single'        => true,
					'type'          => 'string',
					'auth_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}

		ewm_log_debug(
			'Modal meta fields registered',
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
			'ewm-modal-config',
			__( 'Configuración del Modal', 'ewm-modal-cta' ),
			array( $this, 'render_config_meta_box' ),
			self::POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'ewm-modal-shortcode',
			__( 'Shortcode Generado', 'ewm-modal-cta' ),
			array( $this, 'render_shortcode_meta_box' ),
			self::POST_TYPE,
			'side',
			'high'
		);
	}

	/**
	 * Renderizar meta box de configuración
	 */
	public function render_config_meta_box( $post ) {
		wp_nonce_field( 'ewm_modal_meta_box', 'ewm_modal_meta_box_nonce' );

		$modal_mode     = get_post_meta( $post->ID, 'ewm_modal_mode', true ) ?: 'formulario';
		$use_serialized = get_post_meta( $post->ID, 'ewm_use_serialized', true );

		?>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="ewm_modal_mode"><?php _e( 'Modo del Modal', 'ewm-modal-cta' ); ?></label>
				</th>
				<td>
					<select name="ewm_modal_mode" id="ewm_modal_mode">
						<option value="formulario" <?php selected( $modal_mode, 'formulario' ); ?>>
							<?php _e( 'Formulario Multi-Paso', 'ewm-modal-cta' ); ?>
						</option>
						<option value="anuncio" <?php selected( $modal_mode, 'anuncio' ); ?>>
							<?php _e( 'Anuncio/Notificación', 'ewm-modal-cta' ); ?>
						</option>
					</select>
					<p class="description">
						<?php _e( 'Selecciona el tipo de modal que deseas crear.', 'ewm-modal-cta' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="ewm_use_serialized"><?php _e( 'Tipo de Almacenamiento', 'ewm-modal-cta' ); ?></label>
				</th>
				<td>
					<label>
						<input type="checkbox" name="ewm_use_serialized" id="ewm_use_serialized" value="1" 
								<?php checked( $use_serialized, '1' ); ?>>
						<?php _e( 'Usar almacenamiento serializado (para configuraciones complejas)', 'ewm-modal-cta' ); ?>
					</label>
					<p class="description">
						<?php _e( 'Activa esta opción solo para configuraciones muy complejas. Por defecto se usa JSON.', 'ewm-modal-cta' ); ?>
					</p>
				</td>
			</tr>
		</table>
		
		<div id="ewm-modal-builder">
			<p><?php _e( 'La configuración avanzada se realizará a través del Modal Builder o el bloque de Gutenberg.', 'ewm-modal-cta' ); ?></p>
			<p>
				<a href="<?php echo admin_url( 'admin.php?page=ewm-modal-builder&modal_id=' . $post->ID ); ?>" 
					class="button button-secondary">
					<?php _e( 'Abrir Modal Builder', 'ewm-modal-cta' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Renderizar meta box de shortcode
	 */
	public function render_shortcode_meta_box( $post ) {
		if ( $post->post_status === 'publish' ) {
			$shortcode = '[ew_modal id="' . $post->ID . '"]';
			?>
			<p><?php _e( 'Usa este shortcode para mostrar el modal:', 'ewm-modal-cta' ); ?></p>
			<input type="text" value="<?php echo esc_attr( $shortcode ); ?>" readonly 
					style="width: 100%;" onclick="this.select();">
			<p class="description">
				<?php _e( 'Copia y pega este shortcode donde quieras mostrar el modal.', 'ewm-modal-cta' ); ?>
			</p>
			<?php
		} else {
			?>
			<p><?php _e( 'El shortcode estará disponible después de publicar el modal.', 'ewm-modal-cta' ); ?></p>
			<?php
		}
	}

	/**
	 * Guardar meta fields
	 */
	public function save_meta_fields( $post_id ) {
		// Verificar nonce
		if ( ! isset( $_POST['ewm_modal_meta_box_nonce'] ) ||
			! wp_verify_nonce( $_POST['ewm_modal_meta_box_nonce'], 'ewm_modal_meta_box' ) ) {
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

		// Guardar campos
		$modal_mode = sanitize_text_field( $_POST['ewm_modal_mode'] ?? 'formulario' );
		update_post_meta( $post_id, 'ewm_modal_mode', $modal_mode );

		$use_serialized = isset( $_POST['ewm_use_serialized'] ) ? '1' : '0';
		update_post_meta( $post_id, 'ewm_use_serialized', $use_serialized );

		ewm_log_info(
			'Modal meta fields saved',
			array(
				'post_id'        => $post_id,
				'modal_mode'     => $modal_mode,
				'use_serialized' => $use_serialized,
			)
		);
	}

	/**
	 * Agregar columnas personalizadas
	 */
	public function add_custom_columns( $columns ) {
		$new_columns               = array();
		$new_columns['cb']         = $columns['cb'];
		$new_columns['title']      = $columns['title'];
		$new_columns['modal_mode'] = __( 'Modo', 'ewm-modal-cta' );
		$new_columns['shortcode']  = __( 'Shortcode', 'ewm-modal-cta' );
		$new_columns['date']       = $columns['date'];

		return $new_columns;
	}

	/**
	 * Contenido de columnas personalizadas
	 */
	public function custom_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'modal_mode':
				$mode = get_post_meta( $post_id, 'ewm_modal_mode', true ) ?: 'formulario';
				echo $mode === 'formulario' ?
					__( 'Formulario', 'ewm-modal-cta' ) :
					__( 'Anuncio', 'ewm-modal-cta' );
				break;

			case 'shortcode':
				if ( get_post_status( $post_id ) === 'publish' ) {
					echo '<code>[ew_modal id="' . $post_id . '"]</code>';
				} else {
					echo '<em>' . __( 'Disponible al publicar', 'ewm-modal-cta' ) . '</em>';
				}
				break;
		}
	}

	/**
	 * Obtener configuración de modal con flexibilidad de almacenamiento
	 */
	public static function get_modal_config( $modal_id ) {
		$use_serialized = get_post_meta( $modal_id, 'ewm_use_serialized', true );

		if ( $use_serialized ) {
			$steps_data = get_post_meta( $modal_id, 'ewm_steps_serialized', true );
			return $steps_data ? unserialize( $steps_data ) : array();
		} else {
			$steps_data = get_post_meta( $modal_id, 'ewm_steps_config', true );
			return $steps_data ? json_decode( $steps_data, true ) : array();
		}
	}

	/**
	 * Guardar configuración de modal con flexibilidad de almacenamiento
	 */
	public static function save_modal_config( $modal_id, $config ) {
		$use_serialized = get_post_meta( $modal_id, 'ewm_use_serialized', true );

		if ( $use_serialized ) {
			update_post_meta( $modal_id, 'ewm_steps_serialized', serialize( $config ) );
		} else {
			update_post_meta( $modal_id, 'ewm_steps_config', wp_json_encode( $config ) );
		}

		ewm_log_info(
			'Modal config saved',
			array(
				'modal_id'     => $modal_id,
				'storage_type' => $use_serialized ? 'serialized' : 'json',
				'config_size'  => strlen( wp_json_encode( $config ) ),
			)
		);
	}
}
