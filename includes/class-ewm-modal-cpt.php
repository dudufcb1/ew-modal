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
		   'name'                  => _x( 'Modals', 'Post type general name', 'ewm-modal-cta' ),
		   'singular_name'         => _x( 'Modal', 'Post type singular name', 'ewm-modal-cta' ),
		   'menu_name'             => _x( 'EWM Modals', 'Admin Menu text', 'ewm-modal-cta' ),
		   'name_admin_bar'        => _x( 'Modal', 'Add New on Toolbar', 'ewm-modal-cta' ),
		   'add_new'               => __( 'Add New', 'ewm-modal-cta' ),
		   'add_new_item'          => __( 'Add New Modal', 'ewm-modal-cta' ),
		   'new_item'              => __( 'New Modal', 'ewm-modal-cta' ),
		   'edit_item'             => __( 'Edit Modal', 'ewm-modal-cta' ),
		   'view_item'             => __( 'View Modal', 'ewm-modal-cta' ),
		   'all_items'             => __( 'All Modals', 'ewm-modal-cta' ),
		   'search_items'          => __( 'Search Modals', 'ewm-modal-cta' ),
		   'parent_item_colon'     => __( 'Parent Modal:', 'ewm-modal-cta' ),
		   'not_found'             => __( 'No modals found.', 'ewm-modal-cta' ),
		   'not_found_in_trash'    => __( 'No modals found in Trash.', 'ewm-modal-cta' ),
		   'featured_image'        => _x( 'Modal Image', 'Overrides the "Featured Image" phrase', 'ewm-modal-cta' ),
		   'set_featured_image'    => _x( 'Set modal image', 'Overrides the "Set featured image" phrase', 'ewm-modal-cta' ),
		   'remove_featured_image' => _x( 'Remove modal image', 'Overrides the "Remove featured image" phrase', 'ewm-modal-cta' ),
		   'use_featured_image'    => _x( 'Use as modal image', 'Overrides the "Use as featured image" phrase', 'ewm-modal-cta' ),
		   'archives'              => _x( 'Modal Archives', 'The post type archive label used in nav menus', 'ewm-modal-cta' ),
		   'insert_into_item'      => _x( 'Insert into modal', 'Overrides the "Insert into post" phrase', 'ewm-modal-cta' ),
		   'uploaded_to_this_item' => _x( 'Uploaded to this modal', 'Overrides the "Uploaded to this post" phrase', 'ewm-modal-cta' ),
		   'filter_items_list'     => _x( 'Filter modals list', 'Screen reader text for the filter links', 'ewm-modal-cta' ),
		   'items_list_navigation' => _x( 'Modals list navigation', 'Screen reader text for the pagination', 'ewm-modal-cta' ),
		   'items_list'            => _x( 'Modals list', 'Screen reader text for the items list', 'ewm-modal-cta' ),
	   );

		$args = array(
			'labels'                => $labels,
			'public'                => false,
			'publicly_queryable'    => true, // Permitir consultas públicas
			'show_ui'               => true,
			'show_in_menu'          => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'ew-modal' ),
			'capability_type'       => 'post',
			'has_archive'           => false,
			'hierarchical'          => false,
			'menu_position'         => 20,
			'menu_icon'             => 'dashicons-admin-page',
			'supports'              => array( 'title' ),
			'show_in_rest'          => true,
			'rest_base'             => 'ew-modals',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		);

		register_post_type( self::POST_TYPE, $args );


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


	}

	/**
	 * Agregar meta boxes
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'ewm-modal-config',
			__( 'Modal Configuration', 'ewm-modal-cta' ),
			array( $this, 'render_config_meta_box' ),
			self::POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'ewm-modal-shortcode',
			__( 'Generated Shortcode', 'ewm-modal-cta' ),
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

   // Opción de almacenamiento serializado eliminada del DOM (solo backend la mantiene)
   // Si se requiere reactivar, restaurar el bloque HTML correspondiente aquí.

   ?>
   <div id="ewm-modal-builder">
	   <p><?php esc_html_e( 'Advanced configuration will be done through the Modal Builder.', 'ewm-modal-cta' ); ?></p>
	   <p>
		   <a href="<?php echo esc_url( admin_url( 'admin.php?page=ewm-modal-builder&modal_id=' . $post->ID ) ); ?>"
			   class="button button-secondary">
			   <?php esc_html_e( 'Open Modal Builder', 'ewm-modal-cta' ); ?>
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
			<p><?php esc_html_e( 'Usa este shortcode para mostrar el modal:', 'ewm-modal-cta' ); ?></p>
			<input type="text" value="<?php echo esc_attr( $shortcode ); ?>" readonly 
					style="width: 100%;" onclick="this.select();">
			<p class="description">
				<?php esc_html_e( 'Copy and paste this shortcode where you want to display the modal.', 'ewm-modal-cta' ); ?>
			</p>
			<?php
		} else {
			?>
			<p><?php esc_html_e( 'The shortcode will be available after publishing the modal.', 'ewm-modal-cta' ); ?></p>
			<?php
		}
	}

	/**
	 * Guardar meta fields
	 */
	public function save_meta_fields( $post_id ) {
		// Verificar nonce
		if ( ! isset( $_POST['ewm_modal_meta_box_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ewm_modal_meta_box_nonce'] ) ), 'ewm_modal_meta_box' ) ) {
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
		$use_serialized = isset( $_POST['ewm_use_serialized'] ) ? '1' : '0';
		update_post_meta( $post_id, 'ewm_use_serialized', $use_serialized );

	}

	/**
	 * Agregar columnas personalizadas
	 */
	public function add_custom_columns( $columns ) {
		$new_columns               = array();
		$new_columns['cb']         = $columns['cb'];
		$new_columns['title']      = $columns['title'];
		$new_columns['shortcode']  = __( 'Shortcode', 'ewm-modal-cta' );
		$new_columns['date']       = $columns['date'];

		return $new_columns;
	}

	/**
	 * Contenido de columnas personalizadas
	 */
	public function custom_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'shortcode':
				if ( get_post_status( $post_id ) === 'publish' ) {
					echo '<code>[ew_modal id="' . esc_attr( $post_id ) . '"]</code>';
				} else {
					echo '<em>' . esc_html__( 'Disponible al publicar', 'ewm-modal-cta' ) . '</em>';
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

	}
}
