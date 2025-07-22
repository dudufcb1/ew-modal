<?php
/**
 * EWM REST API - Endpoints REST con logging integrado
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clase para manejar los endpoints REST API del plugin
 */
class EWM_REST_API {

	/**
	 * Namespace de la API
	 */
	const NAMESPACE = 'ewm/v1';

	/**
	 * Instancia singleton
	 *
	 * @var EWM_REST_API|null
	 */
	private static $instance = null;

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
		// NO registramos el hook aquí porque se llama directamente desde ewm_init_rest_api.
	}

	/**
	 * Registrar todas las rutas REST
	 */
	public function register_routes() {

		// Endpoint de prueba simple.
		$test_route_registered = register_rest_route(
			self::NAMESPACE,
			'/test',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => function () {
					return new WP_REST_Response( array( 'message' => 'EWM REST API is working!' ), 200 );
				},
				'permission_callback' => '__return_true',
			)
		);

	

		// Endpoint para gestión de modales (simplificado para debugging)
		$modals_route_registered = register_rest_route(
			self::NAMESPACE,
			'/modals',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_modals' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_modal' ),
					'permission_callback' => array( $this, 'check_permissions' ),
					// Temporalmente sin schema para debugging
				),
			)
		);



		// Endpoint para modal específico (simplificado para debugging)
		$modal_id_route_registered = register_rest_route(
			self::NAMESPACE,
			'/modals/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_modal' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_modal' ),
					'permission_callback' => array( $this, 'check_permissions' ),
					// Temporalmente sin schema para debugging
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_modal' ),
					'permission_callback' => array( $this, 'check_permissions' ),
				),
			)
		);


		// Endpoint para envío de formularios (simplificado para debugging)
		$submit_form_route_registered = register_rest_route(
			self::NAMESPACE,
			'/submit-form',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'submit_form' ),
				'permission_callback' => '__return_true', // Público
			// Temporalmente sin schema para debugging
			)
		);



		// Endpoint para vista previa de modales
		$preview_route_registered = register_rest_route(
			self::NAMESPACE,
			'/preview',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'preview_modal' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

	

		// Endpoint para cupones de WooCommerce
		register_rest_route(
			self::NAMESPACE,
			'/wc-coupons',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_wc_coupons' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		// Verificar que las rutas se registraron correctamente
		$registered_routes = rest_get_server()->get_routes();
		$our_routes        = array_filter(
			array_keys( $registered_routes ),
			function ( $route ) {
				return strpos( $route, '/' . self::NAMESPACE . '/' ) === 0;
			}
		);

	
	}

	/**
	 * Obtener lista de modales
	 */
	public function get_modals( $request ) {
		// Limpiar cualquier salida previa para evitar contaminar el JSON
		if ( ob_get_level() ) {
			ob_clean();
		}

		$start_time = microtime( true );

	

		try {
			$args = array(
				'post_type'      => 'ew_modal',
				'post_status'    => 'publish',
				'posts_per_page' => $request->get_param( 'per_page' ) ?: 10,
				'paged'          => $request->get_param( 'page' ) ?: 1,
			);

			$query  = new WP_Query( $args );
			$modals = array();

			foreach ( $query->posts as $post ) {
				$modals[] = $this->prepare_modal_for_response( $post );
			}

			$response = array(
				'modals' => $modals,
				'total'  => $query->found_posts,
				'pages'  => $query->max_num_pages,
			);

			$execution_time = microtime( true ) - $start_time;

		

			return rest_ensure_response( $response );

		} catch ( Exception $e ) {
		

			return new WP_Error(
				'ewm_get_modals_error',
				'Failed to retrieve modals',
				array( 'status' => 500 )
			);
		}
	}

	/**
	 * Obtener modal específico
	 */
	public function get_modal( $request ) {
		// Suprimir notices para evitar contaminar el JSON
		$old_error_reporting = error_reporting();
		error_reporting( E_ERROR | E_WARNING | E_PARSE );

		// Limpiar cualquier salida previa para evitar contaminar el JSON
		if ( ob_get_level() ) {
			ob_clean();
		}

		$start_time = microtime( true );
		$modal_id   = intval( $request['id'] );

		error_log( '🔍 EWM LOAD DEBUG: ===== get_modal method called =====' );
		error_log( '🔍 EWM LOAD DEBUG: Modal ID: ' . $modal_id );
		error_log( '🔍 EWM LOAD DEBUG: User ID: ' . get_current_user_id() );

	

		try {
			// Verificar que el modal existe
			$modal_post = get_post( $modal_id );
			if ( ! $modal_post || $modal_post->post_type !== 'ew_modal' ) {
				return new WP_Error( 'modal_not_found', __( 'Modal no encontrado.', 'ewm-modal-cta' ), array( 'status' => 404 ) );
			}

			// REFACTORIZACIÓN: SOLO leer de ewm_modal_config (API-Only)
			$config_json = get_post_meta( $modal_id, 'ewm_modal_config', true );
			error_log( '🔍 EWM LOAD DEBUG: ewm_modal_config contenido: ' . $config_json );

			if ( empty( $config_json ) ) {
				error_log( '🔍 EWM LOAD DEBUG: ewm_modal_config vacío, devolviendo configuración por defecto' );
				$config = $this->get_default_config();
			} else {
				$config = json_decode( $config_json, true );
				if ( json_last_error() !== JSON_ERROR_NONE ) {
					error_log( '🔍 EWM LOAD DEBUG: Error al decodificar JSON: ' . json_last_error_msg() );
					$config = $this->get_default_config();
				}
			}

			$modal_data = array(
				'id'     => $modal_id,
				'title'  => $modal_post->post_title,
				'config' => $config,
			);

			error_log( '🔍 EWM LOAD DEBUG: Modal data preparado (REFACTORIZADO): ' . wp_json_encode( $modal_data ) );

			$execution_time = microtime( true ) - $start_time;

	

			// Restaurar error reporting
			error_reporting( $old_error_reporting );

			return new WP_REST_Response( $modal_data, 200 );

		} catch ( Exception $e ) {
			// Restaurar error reporting en caso de error también
			error_reporting( $old_error_reporting );


			return new WP_Error(
				'ewm_get_modal_error',
				'Failed to retrieve modal',
				array( 'status' => 500 )
			);
		}
	}

	/**
	 * Crear nuevo modal
	 */
	public function create_modal( $request ) {
		$start_time = microtime( true );


		try {
			$title      = sanitize_text_field( $request->get_param( 'title' ) );
			$config     = $request->get_param( 'config' );
			$all_params = $request->get_params();

			// LOGGING DETALLADO: Datos recibidos

			// Validar datos
			if ( empty( $title ) ) {

			}



			// Crear post
			$post_id = wp_insert_post(
				array(
					'post_title'  => $title,
					'post_type'   => 'ew_modal',
					'post_status' => 'publish',
					'meta_input'  => array(
						'ewm_modal_config' => wp_json_encode( $config ),
					),
				)
			);


			if ( is_wp_error( $post_id ) ) {
				return new WP_Error(
					'ewm_create_failed',
					'Failed to create modal',
					array( 'status' => 500 )
				);
			}

			$modal    = get_post( $post_id );
			$response = $this->prepare_modal_for_response( $modal );

			$execution_time = microtime( true ) - $start_time;


			return rest_ensure_response( $response );

		} catch ( Exception $e ) {
			return new WP_Error(
				'ewm_create_modal_error',
				'Failed to create modal',
				array( 'status' => 500 )
			);
		}
	}

	/**
	 * Enviar formulario
	 */
	public function submit_form( $request ) {
		$start_time = microtime( true );



		try {
			$modal_id  = (int) $request->get_param( 'modal_id' );
			$form_data = $request->get_param( 'form_data' );
			$step_data = $request->get_param( 'step_data' );

			// Debug logging detallado
			error_log( 'EWM Debug: === FORM SUBMISSION RECEIVED IN BACKEND ===' );
			error_log( 'EWM Debug: Modal ID: ' . $modal_id );
			error_log( 'EWM Debug: Form Data: ' . print_r( $form_data, true ) );
			error_log( 'EWM Debug: Step Data: ' . print_r( $step_data, true ) );
			error_log( 'EWM Debug: Raw Request Body: ' . $request->get_body() );

			// Validar modal ID
			if ( ! $modal_id || ! get_post( $modal_id ) ) {
			

				return new WP_Error(
					'ewm_invalid_modal',
					'Invalid modal ID',
					array( 'status' => 400 )
				);
			}

			// Validar datos del formulario
			if ( empty( $form_data ) ) {

				return new WP_Error(
					'ewm_empty_form_data',
					'Form data is required',
					array( 'status' => 400 )
				);
			}

			// Procesar envío del formulario
			$submission_id = $this->process_form_submission( $modal_id, $form_data, $step_data );

			if ( is_wp_error( $submission_id ) ) {

				return $submission_id;
			}

			$execution_time = microtime( true ) - $start_time;


			// Trigger action hook para integraciones
			do_action( 'ewm_form_submitted', $submission_id, $modal_id, $form_data );

			return rest_ensure_response(
				array(
					'success'       => true,
					'submission_id' => $submission_id,
					'message'       => 'Form submitted successfully',
				)
			);

		} catch ( Exception $e ) {

			return new WP_Error(
				'ewm_submit_form_error',
				'Failed to submit form',
				array( 'status' => 500 )
			);
		}
	}

	/**
	 * Actualizar modal existente
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_modal( $request ) {
		// LOGGING DETALLADO: Inicio del método
		error_log( '🔍 EWM SAVE DEBUG: ===== update_modal method called =====' );
		error_log( '🔍 EWM SAVE DEBUG: Timestamp: ' . date('Y-m-d H:i:s') );

		$start_time = microtime( true );
		$modal_id   = intval( $request['id'] );


		try {
			// Verificar que el modal existe
			$modal_post = get_post( $modal_id );
			if ( ! $modal_post || $modal_post->post_type !== 'ew_modal' ) {
				return new WP_Error(
					'ewm_modal_not_found',
					__( 'Modal no encontrado.', 'ewm-modal-cta' ),
					array( 'status' => 404 )
				);
			}

			// Obtener datos del request
			$title      = sanitize_text_field( $request->get_param( 'title' ) );
			$config     = $request->get_param( 'config' );
			$all_params = $request->get_params();

		

			// Actualizar post si hay título
			if ( ! empty( $title ) ) {
				$update_result = wp_update_post(
					array(
						'ID'         => $modal_id,
						'post_title' => $title,
					)
				);

			}


			// Verificar que hay configuración válida
			if ( empty( $config ) ) {
				error_log( '🔧 EWM DEBUG: Config vacío, no hay datos para procesar' );
				return new WP_Error( 'empty_config', 'No configuration data provided', array( 'status' => 400 ) );
			}

			// Actualizar configuración si hay config
			error_log( 'EWM DEBUG: update_modal - checking config: ' . ( ! empty( $config ) ? 'NOT EMPTY' : 'EMPTY' ) );

			if ( ! empty( $config ) ) {
				error_log( '🔍 EWM SAVE DEBUG: REFACTORIZACIÓN - API-Only guardando solo en ewm_modal_config' );
				error_log( '🔍 EWM SAVE DEBUG: Config a guardar: ' . wp_json_encode( $config ) );
				error_log( '🔍 EWM SAVE DEBUG: Config size: ' . strlen( wp_json_encode( $config ) ) . ' bytes' );

				// Asegurar schema version
				if ( ! isset( $config['schema_version'] ) ) {
					$config['schema_version'] = '2.0.0';
				}

				// VERIFICAR ESTADO ANTES DEL GUARDADO
				$before_save = get_post_meta( $modal_id, 'ewm_modal_config', true );
				error_log( '🔍 EWM SAVE DEBUG: Estado ANTES del guardado: ' . $before_save );

				// REFACTORIZACIÓN: SOLO guardar en ewm_modal_config (API-Only)
				$meta_result = update_post_meta( $modal_id, 'ewm_modal_config', wp_json_encode( $config ) );
				error_log( '🔍 EWM SAVE DEBUG: update_post_meta result: ' . var_export( $meta_result, true ) );

				// VERIFICAR ESTADO DESPUÉS DEL GUARDADO
				$after_save = get_post_meta( $modal_id, 'ewm_modal_config', true );
				error_log( '🔍 EWM SAVE DEBUG: Estado DESPUÉS del guardado: ' . $after_save );
				error_log( '🔍 EWM SAVE DEBUG: Datos guardados correctamente: ' . ( $after_save === wp_json_encode( $config ) ? 'SÍ' : 'NO' ) );

				$saved_config = get_post_meta( $modal_id, 'ewm_modal_config', true );
			
			}

			$execution_time = microtime( true ) - $start_time;

		
			return new WP_REST_Response(
				array(
					'id'             => $modal_id,
					'title'          => get_the_title( $modal_id ),
					'updated'        => true,
					'execution_time' => $execution_time,
				),
				200
			);

		} catch ( Exception $e ) {
			return new WP_Error(
				'ewm_update_modal_error',
				__( 'Error al actualizar el modal.', 'ewm-modal-cta' ),
				array( 'status' => 500 )
			);
		}
	}

	/**
	 * Generar vista previa del modal
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function preview_modal( $request ) {
		$start_time = microtime( true );

	
		try {
			// Obtener datos del modal desde el request
			$modal_data = $request->get_json_params();

			if ( empty( $modal_data ) ) {
				// Fallback: intentar obtener desde form data
				$modal_data = $request->get_params();
			}

			if ( empty( $modal_data ) ) {
				return new WP_Error(
					'ewm_preview_no_data',
					__( 'No se proporcionaron datos para la vista previa.', 'ewm-modal-cta' ),
					array( 'status' => 400 )
				);
			}

			// Generar HTML de vista previa
			$preview_html = $this->generate_preview_html( $modal_data );

			$execution_time = microtime( true ) - $start_time;



			return new WP_REST_Response(
				array(
					'html'           => $preview_html,
					'execution_time' => $execution_time,
				),
				200
			);

		} catch ( Exception $e ) {
			return new WP_Error(
				'ewm_preview_error',
				__( 'Error al generar la vista previa.', 'ewm-modal-cta' ),
				array( 'status' => 500 )
			);
		}
	}

	/**
	 * Generar HTML de vista previa del modal
	 *
	 * @param array $modal_data Datos del modal.
	 * @return string HTML de la vista previa.
	 */
	private function generate_preview_html( $modal_data ) {
		// Configuración con valores por defecto para vista previa
		$default_design = array(
			'colors'     => array(
				'primary'    => '#ff6b35',
				'secondary'  => '#333333',
				'background' => '#ffffff',
			),
			'modal_size' => 'medium',
		);

		$default_steps = array(
			'progressBar' => array(
				'enabled' => true,
				'style'   => 'line',
			),
		);

		// Combinar datos reales con defaults inteligentemente
		$design = $modal_data['design'] ?? array();
		if ( empty( $design ) || empty( $design['colors'] ) ) {
			$design = $default_design;
		} else {
			// Completar colores faltantes con defaults
			$design['colors']     = array_merge( $default_design['colors'], $design['colors'] ?? array() );
			$design['modal_size'] = $design['modal_size'] ?? $default_design['modal_size'];
		}

		$steps = $modal_data['steps'] ?? array();
		if ( empty( $steps ) || ! isset( $steps['progressBar'] ) ) {
			$steps = $default_steps;
		}

		$config = array(
			'modal_id' => 'preview',
			'title'    => $modal_data['title'] ?? __( 'Vista Previa del Modal', 'ewm-modal-cta' ),
			'mode'     => $modal_data['mode'] ?? 'formulario',
			'steps'    => $steps,
			'design'   => $design,
			'triggers' => $modal_data['triggers'] ?? array(),
		);

		// Usar el motor de renderizado para generar el HTML.
		ob_start();
		?>
		<div class="ewm-preview-modal" style="
			--ewm-primary-color: <?php echo esc_attr( $config['design']['colors']['primary'] ?? '#ff6b35' ); ?>;
			--ewm-secondary-color: <?php echo esc_attr( $config['design']['colors']['secondary'] ?? '#333333' ); ?>;
			--ewm-background-color: <?php echo esc_attr( $config['design']['colors']['background'] ?? '#ffffff' ); ?>;
		">
			<div class="ewm-modal-content ewm-size-<?php echo esc_attr( $config['design']['modal_size'] ?? 'medium' ); ?>">
				<div class="ewm-modal-header">
					<span class="ewm-modal-close">×</span>
				</div>
				<div class="ewm-modal-body">
					<?php if ( $config['mode'] === 'formulario' ) : ?>
						<h3><?php echo esc_html( $config['title'] ); ?></h3>
						<p><strong><?php esc_html_e( 'Vista previa del formulario multi-paso', 'ewm-modal-cta' ); ?></strong></p>
						<p><em><?php esc_html_e( 'Modo:', 'ewm-modal-cta' ); ?> <?php echo esc_html( ucfirst( $config['mode'] ) ); ?></em></p>

						<?php if ( ! empty( $config['steps']['progressBar']['enabled'] ) ) : ?>
							<div class="ewm-progress-bar" data-style="<?php echo esc_attr( $config['steps']['progressBar']['style'] ?? 'line' ); ?>">
								<div class="ewm-progress-fill" style="width: 33%;"></div>
								<span class="ewm-progress-text"><?php esc_html_e( 'Paso 1 de 3', 'ewm-modal-cta' ); ?></span>
							</div>
						<?php endif; ?>

						<div class="ewm-preview-form">
							<div class="ewm-field">
								<label><?php esc_html_e( 'Nombre completo', 'ewm-modal-cta' ); ?> <span style="color: red;">*</span></label>
								<input type="text" placeholder="<?php esc_attr_e( 'Introduce tu nombre...', 'ewm-modal-cta' ); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
							</div>
							<div class="ewm-field" style="margin-top: 15px;">
								<label><?php esc_html_e( 'Email', 'ewm-modal-cta' ); ?> <span style="color: red;">*</span></label>
								<input type="email" placeholder="<?php esc_attr_e( 'tu@email.com', 'ewm-modal-cta' ); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
							</div>
							<div style="margin-top: 20px; text-align: center;">
								<button class="ewm-btn ewm-btn-primary" style="background: var(--ewm-primary-color); color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer;">
									<?php esc_html_e( 'Siguiente Paso →', 'ewm-modal-cta' ); ?>
								</button>
							</div>
						</div>
					<?php else : ?>
						<h3><?php echo esc_html( $config['title'] ); ?></h3>
						<p><strong><?php esc_html_e( 'Vista previa del anuncio', 'ewm-modal-cta' ); ?></strong></p>
						<p><em><?php esc_html_e( 'Modo:', 'ewm-modal-cta' ); ?> <?php echo esc_html( ucfirst( $config['mode'] ) ); ?></em></p>
						<p><?php esc_html_e( 'Este es un ejemplo de cómo se verá tu anuncio modal. Puedes personalizar el contenido, colores y diseño desde las opciones de configuración.', 'ewm-modal-cta' ); ?></p>
						<div style="margin-top: 20px; text-align: center;">
							<button class="ewm-btn ewm-btn-primary" style="background: var(--ewm-primary-color); color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer;">
								<?php esc_html_e( 'Llamada a la Acción', 'ewm-modal-cta' ); ?>
							</button>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Obtener cupones de WooCommerce
	 */
	public function get_wc_coupons( $request ) {
		$start_time = microtime( true );


		try {
			if ( ! class_exists( 'WooCommerce' ) ) {
				return new WP_Error(
					'ewm_wc_not_active',
					'WooCommerce is not active',
					array( 'status' => 400 )
				);
			}

			$args = array(
				'post_type'      => 'shop_coupon',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			);

			$coupons     = get_posts( $args );
			$coupon_data = array();

			foreach ( $coupons as $coupon ) {
				$coupon_obj    = new WC_Coupon( $coupon->ID );
				$coupon_data[] = array(
					'id'            => $coupon->ID,
					'code'          => $coupon->post_title,
					'description'   => $coupon->post_excerpt,
					'discount_type' => $coupon_obj->get_discount_type(),
					'amount'        => $coupon_obj->get_amount(),
					'usage_count'   => $coupon_obj->get_usage_count(),
					'usage_limit'   => $coupon_obj->get_usage_limit(),
				);
			}

			$execution_time = microtime( true ) - $start_time;

			return rest_ensure_response( $coupon_data );

		} catch ( Exception $e ) {
		
			return new WP_Error(
				'ewm_wc_coupons_error',
				'Failed to retrieve coupons',
				array( 'status' => 500 )
			);
		}
	}

	// GUTENBERG ELIMINADO: Método de transformación removido

	/**
	 * VALIDACIÓN ESTRUCTURAL: Validar configuración de WooCommerce Integration
	 *
	 * Valida que la estructura sea correcta, no que los valores sean "truthy"
	 */
	private function is_valid_wc_integration_config( $data ) {
		// Debe ser un array
		if ( ! is_array( $data ) ) {
			return false;
		}

		// La clave 'enabled' debe existir y ser un booleano
		if ( ! isset( $data['enabled'] ) || ! is_bool( $data['enabled'] ) ) {
			return false;
		}

		// Si está habilitado, validar estructura completa
		if ( $data['enabled'] ) {
			// cart_abandonment debe existir y ser un array
			if ( ! isset( $data['cart_abandonment'] ) || ! is_array( $data['cart_abandonment'] ) ) {
				return false;
			}

			// product_recommendations debe existir y ser un array
			if ( ! isset( $data['product_recommendations'] ) || ! is_array( $data['product_recommendations'] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * VALIDACIÓN ESTRUCTURAL: Validar configuración de Display Rules
	 *
	 * Valida que la estructura sea correcta, arrays vacíos son válidos
	 */
	private function is_valid_display_rules_config( $data ) {
		// Debe ser un array
		if ( ! is_array( $data ) ) {
			return false;
		}

		// userRoles debe existir y ser un array (aunque esté vacío)
		if ( ! isset( $data['userRoles'] ) || ! is_array( $data['userRoles'] ) ) {
			return false;
		}

		// pages debe existir y ser un array
		if ( ! isset( $data['pages'] ) || ! is_array( $data['pages'] ) ) {
			return false;
		}

		// include dentro de pages debe existir y ser un array
		if ( ! isset( $data['pages']['include'] ) || ! is_array( $data['pages']['include'] ) ) {
			return false;
		}

		// exclude dentro de pages debe existir y ser un array
		if ( ! isset( $data['pages']['exclude'] ) || ! is_array( $data['pages']['exclude'] ) ) {
			return false;
		}

		// devices debe existir y ser un array
		if ( ! isset( $data['devices'] ) || ! is_array( $data['devices'] ) ) {
			return false;
		}

		// frequency debe existir y ser un array
		if ( ! isset( $data['frequency'] ) || ! is_array( $data['frequency'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * VALIDACIÓN ESTRUCTURAL: Validar configuración de Design
	 *
	 * Valida que la estructura sea correcta para configuración de diseño
	 */
	private function is_valid_design_config( $data ) {
		// Debe ser un array
		if ( ! is_array( $data ) ) {
			return false;
		}

		// colors debe existir y ser un array
		if ( isset( $data['colors'] ) && ! is_array( $data['colors'] ) ) {
			return false;
		}

		// typography debe existir y ser un array
		if ( isset( $data['typography'] ) && ! is_array( $data['typography'] ) ) {
			return false;
		}

		// modal_size debe ser string si existe
		if ( isset( $data['modal_size'] ) && ! is_string( $data['modal_size'] ) ) {
			return false;
		}

		// animation debe ser string si existe
		if ( isset( $data['animation'] ) && ! is_string( $data['animation'] ) ) {
			return false;
		}

		// theme debe ser string si existe
		if ( isset( $data['theme'] ) && ! is_string( $data['theme'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * VALIDACIÓN ESTRUCTURAL: Validar configuración de Triggers
	 *
	 * Valida que la estructura sea correcta para configuración de triggers
	 */
	private function is_valid_triggers_config( $data ) {
		// Debe ser un array
		if ( ! is_array( $data ) ) {
			return false;
		}

		// exit_intent debe ser un array si existe
		if ( isset( $data['exit_intent'] ) && ! is_array( $data['exit_intent'] ) ) {
			return false;
		}

		// time_delay debe ser un array si existe
		if ( isset( $data['time_delay'] ) && ! is_array( $data['time_delay'] ) ) {
			return false;
		}

		// scroll_percentage debe ser un array si existe
		if ( isset( $data['scroll_percentage'] ) && ! is_array( $data['scroll_percentage'] ) ) {
			return false;
		}

		// page_views debe ser un array si existe
		if ( isset( $data['page_views'] ) && ! is_array( $data['page_views'] ) ) {
			return false;
		}

		// Validar estructura de exit_intent si existe
		if ( isset( $data['exit_intent'] ) ) {
			$exit_intent = $data['exit_intent'];
			if ( isset( $exit_intent['enabled'] ) && ! is_bool( $exit_intent['enabled'] ) ) {
				return false;
			}
		}

		// Validar estructura de time_delay si existe
		if ( isset( $data['time_delay'] ) ) {
			$time_delay = $data['time_delay'];
			if ( isset( $time_delay['enabled'] ) && ! is_bool( $time_delay['enabled'] ) ) {
				return false;
			}
		}

		// Validar estructura de scroll_percentage si existe
		if ( isset( $data['scroll_percentage'] ) ) {
			$scroll_percentage = $data['scroll_percentage'];
			if ( isset( $scroll_percentage['enabled'] ) && ! is_bool( $scroll_percentage['enabled'] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Verificar permisos para administración
	 */
	public function check_admin_permissions( $request ) {
		// Para lectura de modales, permitir acceso público (solo lectura es segura)
		if ( $request->get_method() === 'GET' ) {
			return true;
		}

		// Para operaciones de escritura, verificar permisos normales
		if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
			return true;
		}

		// Fallback a verificación estándar
		return $this->check_permissions( $request );
	}

	/**
	 * Verificar permisos
	 */
	public function check_permissions( $request ) {
		$user_id = get_current_user_id();
		$user    = wp_get_current_user();
		$route   = $request->get_route();
		$method  = $request->get_method();

		// Para usuarios logueados en admin, usar verificación más permisiva
		$is_admin_context = is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$has_permission   = false;

		if ( $is_admin_context && is_user_logged_in() ) {
			// En contexto admin, verificar capacidades básicas
			$has_permission = current_user_can( 'edit_posts' ) || current_user_can( 'manage_options' );
		} else {
			// Para REST API público, verificar nonce
			$nonce = $request->get_header( 'X-WP-Nonce' );
			if ( $nonce && wp_verify_nonce( $nonce, 'wp_rest' ) ) {
				$has_permission = current_user_can( 'edit_posts' );
			}
		}


		if ( ! $has_permission ) {

		}

		return $has_permission;
	}

	/**
	 * Preparar modal para respuesta
	 */
	private function prepare_modal_for_response( $post ) {
		$config = get_post_meta( $post->ID, 'ewm_modal_config', true );

		return array(
			'id'       => $post->ID,
			'title'    => $post->post_title,
			'config'   => $config ? json_decode( $config, true ) : array(),
			'created'  => $post->post_date,
			'modified' => $post->post_modified,
		);
	}

	/**
	 * Procesar envío de formulario
	 */
	private function process_form_submission( $modal_id, $form_data, $step_data ) {
	
		$submission_id = EWM_Submission_CPT::create_submission( $modal_id, $form_data, $step_data );

		if ( is_wp_error( $submission_id ) ) {
			return $submission_id;
		}
			return $submission_id;
	}

	/**
	 * Configuración por defecto para modales (Schema 2.0.0)
	 */
	private function get_default_config() {
		return array(
			'schema_version' => '2.0.0',
			'mode'           => 'formulario',
			'steps'          => array(
				'steps'        => array(),
				'final_step'   => array(
					'title'   => 'Gracias',
					'message' => 'Gracias por tu interés.',
				),
				'progress_bar' => array(
					'enabled' => false,
					'color'   => '#ff6b35',
					'style'   => 'line',
				),
			),
			'design'         => array(
				'primary_color'    => '#2b64ce',
				'background_color' => '#ffffff',
				'font_family'      => 'Arial',
				'border_radius'    => '8px',
			),
			'triggers'       => array(
				'frequency_type'     => 'always',
				'delay_seconds'      => 3,
				'exit_intent'        => false,
				'scroll_percentage'  => 50,
			),
			'woocommerce'    => array(
				'enabled'       => false,
				'product_ids'   => array(),
				'discount_code' => '',
			),
			'display_rules'  => array(
				'pages'      => array( 'all' ),
				'user_roles' => array( 'all' ),
				'devices'    => array( 'desktop', 'mobile' ),
			),
			'custom_css'     => '',
		);
	}

	/**
	 * Schema para modal
	 */
	private function get_modal_schema() {
		return array(
			'title'  => array(
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'config' => array(
				'required' => false,
				'type'     => 'object',
			),
		);
	}

	/**
	 * Schema para envío de formulario
	 */
	private function get_form_submission_schema() {
		return array(
			'modal_id'  => array(
				'required' => true,
				'type'     => 'integer',
			),
			'form_data' => array(
				'required' => true,
				'type'     => 'object',
			),
			'step_data' => array(
				'required' => false,
				'type'     => 'object',
			),
		);
	}
}

// Inicializar la clase
EWM_REST_API::get_instance();
