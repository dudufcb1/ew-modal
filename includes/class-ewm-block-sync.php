<?php
/**
 * Sincronización automática entre bloques Gutenberg y CPT
 * 
 * Esta clase maneja la sincronización automática de datos entre
 * los bloques Gutenberg y el Custom Post Type ew_modal.
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevenir acceso directo
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clase para sincronización de bloques
 */
class EWM_Block_Sync {

	/**
	 * Instancia única de la clase
	 */
	private static $instance = null;

	/**
	 * Constructor privado para patrón singleton
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Obtener instancia única
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Inicializar hooks de WordPress
	 */
	private function init_hooks() {
		// Hook para sincronizar al guardar post
		add_action( 'save_post', array( $this, 'sync_blocks_on_save' ), 20, 2 );
		
		// Hook para limpiar modales huérfanos
		add_action( 'before_delete_post', array( $this, 'cleanup_orphaned_modals' ) );
		
		// Hook para sincronización en tiempo real (REST API)
		add_action( 'rest_after_insert_post', array( $this, 'sync_blocks_on_rest_save' ), 10, 3 );
		
		ewm_log_debug( 'EWM Block Sync hooks initialized' );
	}

	/**
	 * Sincronizar bloques al guardar post
	 */
	public function sync_blocks_on_save( $post_id, $post ) {
		// Si la actualización viene de la REST API (Gutenberg), no hacer nada.
		// El endpoint de la API ya se encargó de guardar los datos.
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			ewm_log_debug( 'Block sync skipped: REST_REQUEST context.', [ 'post_id' => $post_id ] );
			return;
		}

		// Verificar que no sea un auto-save
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Verificar permisos
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Solo procesar posts que contengan bloques EWM
		if ( ! has_block( 'ewm/modal-cta', $post ) ) {
			return;
		}

		ewm_log_debug( 
			'Starting block sync for post', 
			array( 
				'post_id' => $post_id,
				'post_type' => $post->post_type,
				'post_status' => $post->post_status
			)
		);

		$this->process_ewm_blocks( $post_id, $post->post_content );
	}

	/**
	 * Sincronizar bloques en REST API save
	 */
	public function sync_blocks_on_rest_save( $post, $request, $creating ) {
		if ( has_block( 'ewm/modal-cta', $post ) ) {
			ewm_log_debug( 
				'Starting REST block sync', 
				array( 
					'post_id' => $post->ID,
					'creating' => $creating,
					'method' => $request->get_method()
				)
			);
			
			$this->process_ewm_blocks( $post->ID, $post->post_content );
		}
	}

	/**
	 * Procesar bloques EWM en el contenido
	 */
	private function process_ewm_blocks( $post_id, $content ) {
		// Parsear bloques del contenido
		$blocks = parse_blocks( $content );
		
		if ( empty( $blocks ) ) {
			return;
		}

		$processed_modals = array();
		$this->extract_ewm_blocks( $blocks, $processed_modals );

		ewm_log_debug( 
			'Found EWM blocks to process', 
			array( 
				'post_id' => $post_id,
				'blocks_found' => count( $processed_modals )
			)
		);

		// Procesar cada bloque encontrado
		foreach ( $processed_modals as $block_data ) {
			$this->sync_block_to_modal( $block_data, $post_id );
		}
	}

	/**
	 * Extraer bloques EWM recursivamente
	 */
	private function extract_ewm_blocks( $blocks, &$ewm_blocks ) {
		foreach ( $blocks as $block ) {
			// Si es un bloque EWM, agregarlo
			if ( 'ewm/modal-cta' === $block['blockName'] ) {
				$ewm_blocks[] = $block;
			}
			
			// Si tiene bloques internos, procesarlos recursivamente
			if ( ! empty( $block['innerBlocks'] ) ) {
				$this->extract_ewm_blocks( $block['innerBlocks'], $ewm_blocks );
			}
		}
	}

	/**
	 * Sincronizar bloque individual con modal CPT
	 */
	private function sync_block_to_modal( $block_data, $source_post_id ) {
		$attributes = $block_data['attrs'] ?? array();
		$modal_id = $attributes['modalId'] ?? '';

		// Si no hay modalId, crear nuevo modal
		if ( empty( $modal_id ) ) {
			$modal_id = $this->create_modal_from_block( $attributes, $source_post_id );
			if ( $modal_id ) {
				// Actualizar el bloque con el nuevo modalId
				$this->update_block_modal_id( $source_post_id, $modal_id );
			}
			return;
		}

		// Verificar que el modal existe
		$modal_post = get_post( $modal_id );
		if ( ! $modal_post || 'ew_modal' !== $modal_post->post_type ) {
			ewm_log_warning( 
				'Modal referenced in block does not exist', 
				array( 
					'modal_id' => $modal_id,
					'source_post_id' => $source_post_id
				)
			);
			
			// Crear nuevo modal
			$modal_id = $this->create_modal_from_block( $attributes, $source_post_id );
			if ( $modal_id ) {
				$this->update_block_modal_id( $source_post_id, $modal_id );
			}
			return;
		}

		// Actualizar modal existente con datos del bloque
		$this->update_modal_from_block( $modal_id, $attributes, $source_post_id );
	}

	/**
	 * Crear nuevo modal desde datos del bloque
	 */
	private function create_modal_from_block( $attributes, $source_post_id ) {
		$modal_title = sprintf( 
			__( 'Modal desde Bloque - Post %d', 'ewm-modal-cta' ), 
			$source_post_id 
		);

		$modal_id = wp_insert_post( array(
			'post_title' => $modal_title,
			'post_type' => 'ew_modal',
			'post_status' => 'publish',
			'post_author' => get_current_user_id(),
			'meta_input' => array(
				'_ewm_source' => 'gutenberg_block',
				'_ewm_source_post_id' => $source_post_id,
				'_ewm_created_from_block' => current_time( 'mysql' )
			)
		));

		if ( is_wp_error( $modal_id ) ) {
			ewm_log_error( 
				'Failed to create modal from block', 
				array( 
					'error' => $modal_id->get_error_message(),
					'source_post_id' => $source_post_id
				)
			);
			return false;
		}

		// Guardar configuración del bloque en el modal
		$this->save_block_config_to_modal( $modal_id, $attributes );

		ewm_log_info( 
			'Created new modal from block', 
			array( 
				'modal_id' => $modal_id,
				'source_post_id' => $source_post_id
			)
		);

		return $modal_id;
	}

	/**
	 * Actualizar modal existente con datos del bloque
	 */
	private function update_modal_from_block( $modal_id, $attributes, $source_post_id ) {
		// Verificar que el modal fue creado desde un bloque
		$modal_source = get_post_meta( $modal_id, '_ewm_source', true );
		
		if ( 'gutenberg_block' !== $modal_source ) {
			ewm_log_debug( 
				'Skipping update - modal not created from block', 
				array( 
					'modal_id' => $modal_id,
					'source' => $modal_source
				)
			);
			return;
		}

		// Actualizar configuración
		$this->save_block_config_to_modal( $modal_id, $attributes );

		// Actualizar timestamp de última sincronización
		update_post_meta( $modal_id, '_ewm_last_sync', current_time( 'mysql' ) );
		update_post_meta( $modal_id, '_ewm_source_post_id', $source_post_id );

		ewm_log_debug( 
			'Updated modal from block', 
			array( 
				'modal_id' => $modal_id,
				'source_post_id' => $source_post_id
			)
		);
	}

	/**
	 * Guardar configuración del bloque en el modal
	 */
	private function save_block_config_to_modal( $modal_id, $attributes ) {
		// Configuración de diseño
		$design_config = array(
			'size' => $attributes['modalSize'] ?? 'medium',
			'animation' => $attributes['animation'] ?? 'fade',
			'primary_color' => $attributes['primaryColor'] ?? '#ff6b35',
			'secondary_color' => $attributes['secondaryColor'] ?? '#333333',
			'background_color' => $attributes['backgroundColor'] ?? '#ffffff',
			'show_progress_bar' => $attributes['showProgressBar'] ?? true,
			'progress_bar_style' => $attributes['progressBarStyle'] ?? 'line'
		);

		// Configuración de triggers
		$triggers_config = array(
			'type' => $attributes['triggerType'] ?? 'manual',
			'delay' => $attributes['triggerDelay'] ?? 5000,
			'exit_intent' => $attributes['enableExitIntent'] ?? false,
			'exit_intent_sensitivity' => $attributes['exitIntentSensitivity'] ?? 20,
			'scroll_trigger' => $attributes['enableScrollTrigger'] ?? false,
			'scroll_percentage' => $attributes['scrollPercentage'] ?? 50
		);

		// Configuración de WooCommerce
		$wc_config = array(
			'enabled' => $attributes['enableWooCommerce'] ?? false,
			'coupon_id' => $attributes['selectedCoupon'] ?? 0
		);

		// Configuración general
		$modal_config = array(
			'mode' => $attributes['modalMode'] ?? 'formulario',
			'display_rules' => $attributes['displayRules'] ?? array(),
			'custom_css' => $attributes['customCSS'] ?? ''
		);

		// Pasos del formulario (si existen)
		$steps = $attributes['modalConfigData']['steps'] ?? array();
		$final_step = $attributes['modalConfigData']['final_step'] ?? array();

		// Guardar en meta fields
		update_post_meta( $modal_id, '_ewm_design', $design_config );
		update_post_meta( $modal_id, '_ewm_triggers', $triggers_config );
		update_post_meta( $modal_id, '_ewm_wc_integration', $wc_config );
		update_post_meta( $modal_id, '_ewm_modal_config', $modal_config );
		
		if ( ! empty( $steps ) ) {
			update_post_meta( $modal_id, '_ewm_steps', $steps );
		}
		
		if ( ! empty( $final_step ) ) {
			update_post_meta( $modal_id, '_ewm_final_step', $final_step );
		}

		ewm_log_debug( 
			'Saved block config to modal', 
			array( 
				'modal_id' => $modal_id,
				'steps_count' => count( $steps ),
				'has_final_step' => ! empty( $final_step )
			)
		);
	}

	/**
	 * Actualizar modalId en el bloque (para nuevos modales)
	 */
	private function update_block_modal_id( $post_id, $modal_id ) {
		// Esta función requeriría actualizar el contenido del post
		// Por simplicidad, se registra para que el usuario sepa que debe refrescar
		ewm_log_info( 
			'New modal created - user should refresh editor', 
			array( 
				'post_id' => $post_id,
				'modal_id' => $modal_id
			)
		);
		
		// Guardar en transient para mostrar notificación
		set_transient( 
			"ewm_new_modal_created_{$post_id}", 
			$modal_id, 
			300 // 5 minutos
		);
	}

	/**
	 * Limpiar modales huérfanos al eliminar post
	 */
	public function cleanup_orphaned_modals( $post_id ) {
		// Buscar modales creados desde este post
		$orphaned_modals = get_posts( array(
			'post_type' => 'ew_modal',
			'meta_query' => array(
				array(
					'key' => '_ewm_source_post_id',
					'value' => $post_id,
					'compare' => '='
				),
				array(
					'key' => '_ewm_source',
					'value' => 'gutenberg_block',
					'compare' => '='
				)
			),
			'posts_per_page' => -1
		));

		foreach ( $orphaned_modals as $modal ) {
			wp_delete_post( $modal->ID, true );
			ewm_log_info( 
				'Cleaned up orphaned modal', 
				array( 
					'modal_id' => $modal->ID,
					'source_post_id' => $post_id
				)
			);
		}
	}
}

// Inicializar la sincronización
EWM_Block_Sync::get_instance();
