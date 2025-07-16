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
		ewm_log_debug( 'EWM_REST_API constructor called' );
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
		ewm_log_debug( 'EWM_REST_API init called - routes will be registered directly' );
		// NO registramos el hook aquí porque se llama directamente desde ewm_init_rest_api.
	}

	/**
	 * Registrar todas las rutas REST
	 */
	public function register_routes() {
		ewm_log_debug( 'Registering REST API routes' );

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

		ewm_log_debug(
			'Test route registration result',
			array(
				'success' => $test_route_registered,
				'route'   => '/test',
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
					'permission_callback' => array( $this, 'check_gutenberg_permissions' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_modal' ),
					'permission_callback' => array( $this, 'check_permissions' ),
					// Temporalmente sin schema para debugging
				),
			)
		);

		ewm_log_debug(
			'Modals route registration result',
			array(
				'success' => $modals_route_registered,
				'route'   => '/modals',
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
					'permission_callback' => array( $this, 'check_gutenberg_permissions' ),
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

		ewm_log_debug(
			'Modal ID route registration result',
			array(
				'success' => $modal_id_route_registered,
				'route'   => '/modals/(?P<id>\d+)',
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

		ewm_log_debug(
			'Submit form route registration result',
			array(
				'success' => $submit_form_route_registered,
				'route'   => '/submit-form',
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

		ewm_log_debug(
			'Preview route registration result',
			array(
				'success' => $preview_route_registered,
				'route'   => '/preview',
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

		ewm_log_info(
			'REST API routes registered',
			array(
				'namespace'         => self::NAMESPACE,
				'expected_routes'   => array( 'test', 'modals', 'modals/(?P<id>\d+)', 'submit-form', 'preview', 'wc-coupons' ),
				'registered_routes' => $our_routes,
				'total_wp_routes'   => count( $registered_routes ),
			)
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

		ewm_log_info(
			'GET /modals endpoint called',
			array(
				'user_id'    => get_current_user_id(),
				'ip'         => sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ),
				'user_agent' => sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) ),
			)
		);

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

			ewm_log_info(
				'GET /modals completed successfully',
				array(
					'total_modals'   => count( $modals ),
					'execution_time' => round( $execution_time * 1000, 2 ) . 'ms',
				)
			);

			return rest_ensure_response( $response );

		} catch ( Exception $e ) {
			ewm_log_error(
				'Error in GET /modals',
				array(
					'error' => $e->getMessage(),
					'file'  => $e->getFile(),
					'line'  => $e->getLine(),
				)
			);

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

		ewm_log_info(
			'GET /modals/{id} endpoint called',
			array(
				'modal_id' => $modal_id,
				'user_id'  => get_current_user_id(),
				'ip'       => sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ),
			)
		);

		try {
			// Verificar que el modal existe
			$modal_post = get_post( $modal_id );
			if ( ! $modal_post || $modal_post->post_type !== 'ew_modal' ) {
				ewm_log_warning( 'Modal not found', array( 'modal_id' => $modal_id ) );
				return new WP_Error( 'modal_not_found', __( 'Modal no encontrado.', 'ewm-modal-cta' ), array( 'status' => 404 ) );
			}

			// Preparar datos del modal - CORREGIR: Leer directamente desde post_meta
			$steps_json    = get_post_meta( $modal_id, 'ewm_steps_config', true );
			$design_json   = get_post_meta( $modal_id, 'ewm_design_config', true );
			$triggers_json = get_post_meta( $modal_id, 'ewm_trigger_config', true );
			$wc_json       = get_post_meta( $modal_id, 'ewm_wc_integration', true );
			$rules_json    = get_post_meta( $modal_id, 'ewm_display_rules', true );

			$modal_data = array(
				'id'             => $modal_id,
				'title'          => $modal_post->post_title,
				'mode'           => get_post_meta( $modal_id, 'ewm_modal_mode', true ) ?: 'formulario',
				'steps'          => $steps_json ? json_decode( $steps_json, true ) : array(),
				'design'         => $design_json ? json_decode( $design_json, true ) : array(),
				'triggers'       => $triggers_json ? json_decode( $triggers_json, true ) : array(),
				'wc_integration' => $wc_json ? json_decode( $wc_json, true ) : array(),
				'display_rules'  => $rules_json ? json_decode( $rules_json, true ) : array(),
				'custom_css'     => get_post_meta( $modal_id, 'ewm_custom_css', true ) ?: '',
			);

			$execution_time = microtime( true ) - $start_time;

			ewm_log_info(
				'Modal retrieved successfully',
				array(
					'modal_id'       => $modal_id,
					'execution_time' => round( $execution_time * 1000, 2 ) . 'ms',
				)
			);

			// Restaurar error reporting
			error_reporting( $old_error_reporting );

			return new WP_REST_Response( $modal_data, 200 );

		} catch ( Exception $e ) {
			// Restaurar error reporting en caso de error también
			error_reporting( $old_error_reporting );

			ewm_log_error(
				'Error in GET /modals/{id}',
				array(
					'modal_id' => $modal_id,
					'error'    => $e->getMessage(),
					'file'     => $e->getFile(),
					'line'     => $e->getLine(),
				)
			);

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

		ewm_log_info(
			'POST /modals endpoint called',
			array(
				'user_id'   => get_current_user_id(),
				'data_size' => strlen( wp_json_encode( $request->get_params() ) ),
			)
		);

		try {
			$title      = sanitize_text_field( $request->get_param( 'title' ) );
			$config     = $request->get_param( 'config' );
			$all_params = $request->get_params();

			// LOGGING DETALLADO: Datos recibidos
			ewm_log_info(
				'CREATE MODAL - Datos recibidos',
				array(
					'title'           => $title,
					'config_received' => ! empty( $config ),
					'config_size'     => strlen( wp_json_encode( $config ) ),
					'all_params_keys' => array_keys( $all_params ),
					'total_params'    => count( $all_params ),
					'raw_config'      => $config, // Para debug completo
				)
			);

			// Validar datos
			if ( empty( $title ) ) {
				ewm_log_warning( 'Modal creation failed: missing title' );
				return new WP_Error(
					'ewm_missing_title',
					'Modal title is required',
					array( 'status' => 400 )
				);
			}

			// LOGGING: Antes de crear post
			ewm_log_info(
				'CREATE MODAL - Creando post',
				array(
					'title'       => $title,
					'config_json' => wp_json_encode( $config ),
					'config_size' => strlen( wp_json_encode( $config ) ),
				)
			);

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

			// LOGGING: Resultado de creación
			ewm_log_info(
				'CREATE MODAL - Post creado',
				array(
					'post_id'    => $post_id,
					'is_error'   => is_wp_error( $post_id ),
					'meta_saved' => ! is_wp_error( $post_id ) ? get_post_meta( $post_id, 'ewm_modal_config', true ) : null,
				)
			);

			if ( is_wp_error( $post_id ) ) {
				ewm_log_error(
					'Failed to create modal post',
					array(
						'error' => $post_id->get_error_message(),
					)
				);

				return new WP_Error(
					'ewm_create_failed',
					'Failed to create modal',
					array( 'status' => 500 )
				);
			}

			$modal    = get_post( $post_id );
			$response = $this->prepare_modal_for_response( $modal );

			$execution_time = microtime( true ) - $start_time;

			ewm_log_info(
				'Modal created successfully',
				array(
					'modal_id'       => $post_id,
					'title'          => $title,
					'execution_time' => round( $execution_time * 1000, 2 ) . 'ms',
				)
			);

			return rest_ensure_response( $response );

		} catch ( Exception $e ) {
			ewm_log_error(
				'Error in POST /modals',
				array(
					'error' => $e->getMessage(),
					'file'  => $e->getFile(),
					'line'  => $e->getLine(),
				)
			);

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

		ewm_log_info(
			'POST /submit-form endpoint called',
			array(
				'ip'         => sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ),
				'user_agent' => sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) ),
				'referer'    => sanitize_url( wp_unslash( $_SERVER['HTTP_REFERER'] ?? '' ) ),
			)
		);

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
				ewm_log_warning(
					'Form submission failed: invalid modal ID',
					array(
						'modal_id' => $modal_id,
					)
				);

				return new WP_Error(
					'ewm_invalid_modal',
					'Invalid modal ID',
					array( 'status' => 400 )
				);
			}

			// Validar datos del formulario
			if ( empty( $form_data ) ) {
				ewm_log_warning( 'Form submission failed: empty form data' );

				return new WP_Error(
					'ewm_empty_form_data',
					'Form data is required',
					array( 'status' => 400 )
				);
			}

			// Procesar envío del formulario
			$submission_id = $this->process_form_submission( $modal_id, $form_data, $step_data );

			if ( is_wp_error( $submission_id ) ) {
				ewm_log_error(
					'Form processing failed',
					array(
						'modal_id' => $modal_id,
						'error'    => $submission_id->get_error_message(),
					)
				);

				return $submission_id;
			}

			$execution_time = microtime( true ) - $start_time;

			ewm_log_info(
				'Form submitted successfully',
				array(
					'modal_id'       => $modal_id,
					'submission_id'  => $submission_id,
					'fields_count'   => count( $form_data ),
					'execution_time' => round( $execution_time * 1000, 2 ) . 'ms',
				)
			);

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
			ewm_log_error(
				'Error in POST /submit-form',
				array(
					'error'    => $e->getMessage(),
					'file'     => $e->getFile(),
					'line'     => $e->getLine(),
					'modal_id' => $modal_id ?? null,
				)
			);

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
		// LOGGING BÁSICO para verificar ejecución
		error_log( 'EWM DEBUG: update_modal method called' );

		$start_time = microtime( true );
		$modal_id   = intval( $request['id'] );

		error_log( 'EWM DEBUG: update_modal - modal_id: ' . $modal_id );

		ewm_log_info(
			'PUT /modals/{id} endpoint called',
			array(
				'modal_id'  => $modal_id,
				'user_id'   => get_current_user_id(),
				'data_size' => strlen( wp_json_encode( $request->get_params() ) ),
			)
		);

		try {
			// Verificar que el modal existe
			$modal_post = get_post( $modal_id );
			if ( ! $modal_post || $modal_post->post_type !== 'ew_modal' ) {
				ewm_log_warning( 'UPDATE MODAL - Modal not found', array( 'modal_id' => $modal_id ) );
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

			// LOGGING DETALLADO: Datos recibidos para actualización
			error_log( 'EWM DEBUG: update_modal - title: ' . $title );
			error_log( 'EWM DEBUG: update_modal - config: ' . wp_json_encode( $config ) );
			error_log( 'EWM DEBUG: update_modal - all_params: ' . wp_json_encode( $all_params ) );

			// 🔍 NUEVO LOG: Verificar si los datos vienen en la raíz en lugar de en 'config'
			error_log( '🔍 EWM DEBUG: steps en raíz: ' . wp_json_encode( $request->get_param( 'steps' ) ) );
			error_log( '🔍 EWM DEBUG: mode en raíz: ' . $request->get_param( 'mode' ) );
			error_log( '🔍 EWM DEBUG: design en raíz: ' . wp_json_encode( $request->get_param( 'design' ) ) );

			ewm_log_info(
				'UPDATE MODAL - Datos recibidos',
				array(
					'modal_id'        => $modal_id,
					'title'           => $title,
					'config_received' => ! empty( $config ),
					'config_size'     => strlen( wp_json_encode( $config ) ),
					'all_params_keys' => array_keys( $all_params ),
					'total_params'    => count( $all_params ),
					'raw_config'      => $config, // Para debug completo
				)
			);

			// Actualizar post si hay título
			if ( ! empty( $title ) ) {
				$update_result = wp_update_post(
					array(
						'ID'         => $modal_id,
						'post_title' => $title,
					)
				);

				ewm_log_info(
					'UPDATE MODAL - Post title updated',
					array(
						'modal_id'      => $modal_id,
						'update_result' => $update_result,
						'is_error'      => is_wp_error( $update_result ),
					)
				);
			}

			// 📊 LOG DETALLADO: Datos recibidos de Gutenberg
			error_log( '🚀 GUTENBERG BACKEND: Raw request data: ' . wp_json_encode( $request->get_params() ) );
			error_log( '🚀 GUTENBERG BACKEND: Title received: ' . $title );
			error_log( '🚀 GUTENBERG BACKEND: Config received: ' . wp_json_encode( $config ) );
			error_log( '🚀 GUTENBERG BACKEND: Config is empty: ' . ( empty( $config ) ? 'YES' : 'NO' ) );

			// 🔧 IMPLEMENTAR PATRÓN ADAPTADOR: Transformar datos de Gutenberg al formato que espera el backend
			if ( empty( $config ) ) {
				error_log( '🔧 EWM DEBUG: Config vacío, aplicando patrón adaptador para Gutenberg' );
				$config = $this->transform_gutenberg_data_to_legacy_format( $request );
				error_log( '🔧 EWM DEBUG: Config transformado: ' . wp_json_encode( $config ) );
			}

			// Actualizar configuración si hay config
			error_log( 'EWM DEBUG: update_modal - checking config: ' . ( ! empty( $config ) ? 'NOT EMPTY' : 'EMPTY' ) );

			if ( ! empty( $config ) ) {
				error_log( 'EWM DEBUG: update_modal - NUEVO CÓDIGO EJECUTÁNDOSE - usando update_post_meta directo' );

				// Guardar en el campo unificado (para compatibilidad)
				$meta_result = update_post_meta( $modal_id, 'ewm_modal_config', wp_json_encode( $config ) );
				error_log( 'EWM DEBUG: update_modal - ewm_modal_config result: ' . ( $meta_result ? 'SUCCESS' : 'FAILED' ) );

				// CORREGIR: Usar update_post_meta directamente para evitar problemas con EWM_Meta_Fields
				if ( isset( $config['mode'] ) ) {
					error_log( 'EWM DEBUG: update_modal - saving mode: ' . $config['mode'] );
					$mode_result = update_post_meta( $modal_id, 'ewm_modal_mode', $config['mode'] );
					// update_post_meta devuelve false si el valor no cambió, no significa error
					$mode_saved = ( $mode_result !== false || get_post_meta( $modal_id, 'ewm_modal_mode', true ) === $config['mode'] );
					error_log( 'EWM DEBUG: update_modal - mode saved: ' . ( $mode_saved ? 'SUCCESS' : 'FAILED' ) );
				}

				if ( isset( $config['steps'] ) ) {
					error_log( 'EWM DEBUG: update_modal - steps RAW: ' . var_export( $config['steps'], true ) );

					$steps_json = wp_json_encode( $config['steps'] );
					error_log( 'EWM DEBUG: update_modal - steps JSON: ' . $steps_json );

					$steps_result = update_post_meta( $modal_id, 'ewm_steps_config', $steps_json );
					error_log( 'EWM DEBUG: update_modal - update_post_meta result: ' . var_export( $steps_result, true ) );

					// Verificar inmediatamente
					$saved_value = get_post_meta( $modal_id, 'ewm_steps_config', true );
					error_log( 'EWM DEBUG: update_modal - SAVED VALUE: ' . var_export( $saved_value, true ) );

					// 🔧 CORREGIR: No comparar strings exactos, sino verificar que se guardó algo válido
					$steps_saved = ( $steps_result !== false && !empty( $saved_value ) );
					error_log( 'EWM DEBUG: update_modal - steps saved: ' . ( $steps_saved ? 'SUCCESS' : 'FAILED' ) );

					// LOG adicional: Verificar que los pasos se guardaron correctamente
					$saved_decoded = json_decode( $saved_value, true );
					$steps_count = isset( $saved_decoded['steps'] ) ? count( $saved_decoded['steps'] ) : 0;
					error_log( 'EWM DEBUG: update_modal - steps count in saved data: ' . $steps_count );
				}

				if ( isset( $config['design'] ) ) {
					$design_json   = wp_json_encode( $config['design'] );
					$design_result = update_post_meta( $modal_id, 'ewm_design_config', $design_json );
					// --> LÓGICA DE VERIFICACIÓN MEJORADA <--
					$design_saved  = ( $design_result !== false && !empty(get_post_meta($modal_id, 'ewm_design_config', true)) );
					error_log( 'EWM DEBUG: update_modal - design saved: ' . ( $design_saved ? 'SUCCESS' : 'FAILED' ) );
				}

				if ( isset( $config['triggers'] ) ) {
					$triggers_json   = wp_json_encode( $config['triggers'] );
					$triggers_result = update_post_meta( $modal_id, 'ewm_trigger_config', $triggers_json );
					// --> LÓGICA DE VERIFICACIÓN MEJORADA <--
					$triggers_saved  = ( $triggers_result !== false && !empty(get_post_meta($modal_id, 'ewm_trigger_config', true)) );
					error_log( 'EWM DEBUG: update_modal - triggers saved: ' . ( $triggers_saved ? 'SUCCESS' : 'FAILED' ) );
				}

				if ( isset( $config['wc_integration'] ) ) {
					$wc_json   = wp_json_encode( $config['wc_integration'] );
					$wc_result = update_post_meta( $modal_id, 'ewm_wc_integration', $wc_json );
					// --> LÓGICA DE VERIFICACIÓN MEJORADA <--
					$wc_saved  = ( $wc_result !== false && !empty(get_post_meta($modal_id, 'ewm_wc_integration', true)) );
					error_log( 'EWM DEBUG: update_modal - wc_integration saved: ' . ( $wc_saved ? 'SUCCESS' : 'FAILED' ) );
				}

				if ( isset( $config['display_rules'] ) ) {
					$rules_json   = wp_json_encode( $config['display_rules'] );
					$rules_result = update_post_meta( $modal_id, 'ewm_display_rules', $rules_json );
					// --> LÓGICA DE VERIFICACIÓN MEJORADA <--
					$rules_saved  = ( $rules_result !== false && !empty(get_post_meta($modal_id, 'ewm_display_rules', true)) );
					error_log( 'EWM DEBUG: update_modal - display_rules saved: ' . ( $rules_saved ? 'SUCCESS' : 'FAILED' ) );
				}

				if ( isset( $config['custom_css'] ) ) {
					$css_result = update_post_meta( $modal_id, 'ewm_custom_css', $config['custom_css'] );
					$css_saved  = ( $css_result !== false || get_post_meta( $modal_id, 'ewm_custom_css', true ) === $config['custom_css'] );
					error_log( 'EWM DEBUG: update_modal - custom_css saved: ' . ( $css_saved ? 'SUCCESS' : 'FAILED' ) );
				}

				ewm_log_info(
					'UPDATE MODAL - Config updated',
					array(
						'modal_id'    => $modal_id,
						'meta_result' => $meta_result,
						'config_json' => wp_json_encode( $config ),
						'config_size' => strlen( wp_json_encode( $config ) ),
					)
				);

				// Verificar que se guardó correctamente
				$saved_config = get_post_meta( $modal_id, 'ewm_modal_config', true );
				ewm_log_info(
					'UPDATE MODAL - Verification',
					array(
						'modal_id'     => $modal_id,
						'saved_config' => $saved_config,
						'saved_size'   => strlen( $saved_config ),
						'matches'      => $saved_config === wp_json_encode( $config ),
					)
				);
			}

			$execution_time = microtime( true ) - $start_time;

			ewm_log_info(
				'UPDATE MODAL - Completed',
				array(
					'modal_id'       => $modal_id,
					'execution_time' => $execution_time,
				)
			);

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
			ewm_log_error(
				'Error in PUT /modals/{id}',
				array(
					'modal_id' => $modal_id,
					'error'    => $e->getMessage(),
					'file'     => $e->getFile(),
					'line'     => $e->getLine(),
				)
			);

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

		ewm_log_info(
			'POST /preview endpoint called',
			array(
				'user_id' => get_current_user_id(),
				'ip'      => sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ),
			)
		);

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

			ewm_log_info(
				'Preview generated successfully',
				array(
					'execution_time' => $execution_time,
					'html_length'    => strlen( $preview_html ),
				)
			);

			return new WP_REST_Response(
				array(
					'html'           => $preview_html,
					'execution_time' => $execution_time,
				),
				200
			);

		} catch ( Exception $e ) {
			ewm_log_error(
				'Error in POST /preview',
				array(
					'error' => $e->getMessage(),
					'file'  => $e->getFile(),
					'line'  => $e->getLine(),
				)
			);

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

		ewm_log_info( 'GET /wc-coupons endpoint called' );

		try {
			if ( ! class_exists( 'WooCommerce' ) ) {
				ewm_log_warning( 'WooCommerce not active for coupons endpoint' );

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

			ewm_log_info(
				'WC coupons retrieved successfully',
				array(
					'total_coupons'  => count( $coupon_data ),
					'execution_time' => round( $execution_time * 1000, 2 ) . 'ms',
				)
			);

			return rest_ensure_response( $coupon_data );

		} catch ( Exception $e ) {
			ewm_log_error(
				'Error in GET /wc-coupons',
				array(
					'error' => $e->getMessage(),
					'file'  => $e->getFile(),
					'line'  => $e->getLine(),
				)
			);

			return new WP_Error(
				'ewm_wc_coupons_error',
				'Failed to retrieve coupons',
				array( 'status' => 500 )
			);
		}
	}

	/**
	 * PATRÓN ADAPTADOR: Transformar datos de Gutenberg al formato que espera el backend
	 *
	 * Ahora Gutenberg envía los datos en el mismo formato que el shortcode
	 */
	private function transform_gutenberg_data_to_legacy_format( $request ) {
		$gutenberg_data = $request->get_params();

		error_log( '🔄 ADAPTADOR: Datos de Gutenberg (ya en formato correcto): ' . wp_json_encode( $gutenberg_data ) );

		// Gutenberg ahora envía: { title: "...", config: { ... } }
		// Que es exactamente lo que espera el backend
		return $gutenberg_data['config'] ?? array();
	}

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
	 * Verificar permisos específicos para Gutenberg
	 */
	public function check_gutenberg_permissions( $request ) {
		// Para lectura de modales, permitir acceso público (solo lectura es segura)
		// Esto permite que Gutenberg funcione correctamente sin problemas de autenticación
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

		ewm_log_info(
			'REST API permission check',
			array(
				'user_id'            => $user_id,
				'user_login'         => $user->user_login ?? 'anonymous',
				'user_roles'         => $user->roles ?? array(),
				'has_edit_posts'     => current_user_can( 'edit_posts' ),
				'has_manage_options' => current_user_can( 'manage_options' ),
				'has_permission'     => $has_permission,
				'endpoint'           => $route,
				'method'             => $method,
				'is_user_logged_in'  => is_user_logged_in(),
				'is_admin_context'   => $is_admin_context,
				'nonce_header'       => $request->get_header( 'X-WP-Nonce' ),
				'nonce_param'        => $request->get_param( '_wpnonce' ),
				'referer'            => wp_get_referer(),
			)
		);

		if ( ! $has_permission ) {
			ewm_log_warning(
				'Permission denied for REST API request',
				array(
					'user_id'  => $user_id,
					'endpoint' => $route,
					'reason'   => $is_admin_context ? 'User lacks required capabilities' : 'Invalid nonce or insufficient permissions',
				)
			);
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
		// Aquí iría la lógica de procesamiento del formulario
		// Por ahora, simulamos creando un ID de envío

		$submission_id = wp_insert_post(
			array(
				'post_type'   => 'ewm_submission',
				'post_status' => 'private',
				'meta_input'  => array(
					'modal_id'        => $modal_id,
					'form_data'       => wp_json_encode( $form_data ),
					'step_data'       => wp_json_encode( $step_data ),
					'submission_time' => current_time( 'mysql' ),
					'ip_address'      => sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ),
				),
			)
		);

		return $submission_id;
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
