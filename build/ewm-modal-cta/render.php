<?php
/**
 * Renderizado dinámico del bloque EWM Modal CTA
 *
 * Este archivo maneja el renderizado del bloque en el frontend,
 * conectándolo con el motor de renderizado universal del plugin.
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevenir acceso directo
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Log para confirmar que el archivo render.php se está cargando
error_log( 'EWM BLOCK RENDER: render.php loaded' );

// Validar que tenemos el modalId
if ( empty( $attributes['modalId'] ) ) {
	// En el editor, mostrar un placeholder. En el frontend, no mostrar nada.
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return '<div style="padding: 20px; border: 2px dashed #ccc; text-align: center; color: #666;">[EWM Modal: Por favor, selecciona un modal en el panel lateral]</div>';
	}
	return '';
}

$modal_id = (int) $attributes['modalId'];

// Log para debugging
error_log( 'EWM BLOCK RENDER: Rendering modal ID ' . $modal_id );

// Verificar que el modal existe
$modal_post = get_post( $modal_id );
if ( ! $modal_post || $modal_post->post_type !== 'ew_modal' ) {
	error_log( 'EWM BLOCK RENDER: Modal ' . $modal_id . ' not found or wrong post type' );
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return '<div style="padding: 20px; border: 2px solid #dc3232; text-align: center; color: #dc3232;">[EWM Modal: Modal ID ' . $modal_id . ' no encontrado]</div>';
	}
	return '';
}

// Verificar que las clases necesarias existen
if ( ! class_exists( 'EWM_Render_Core' ) ) {
	error_log( 'EWM BLOCK RENDER: EWM_Render_Core class not found' );
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return '<div style="padding: 20px; border: 2px solid #dc3232; text-align: center; color: #dc3232;">[EWM Modal: Error - Motor de renderizado no disponible]</div>';
	}
	return '';
}

// **NUEVO: Aplicar validaciones de frecuencia como en shortcodes**
if ( ! class_exists( 'EWM_Shortcodes' ) ) {
	error_log( 'EWM BLOCK RENDER: EWM_Shortcodes class not found for validation' );
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return '<div style="padding: 20px; border: 2px solid #dc3232; text-align: center; color: #dc3232;">[EWM Modal: Error - Sistema de validación no disponible]</div>';
	}
	return '';
}

// Aplicar las mismas validaciones que los shortcodes
$shortcodes_instance = EWM_Shortcodes::get_instance();

// Usar reflection para acceder al método privado can_display_modal
$reflection = new ReflectionClass( $shortcodes_instance );
$can_display_method = $reflection->getMethod( 'can_display_modal' );
$can_display_method->setAccessible( true );

// Log antes de validación
error_log( 'EWM BLOCK RENDER: Checking display rules for modal ' . $modal_id );

// Verificar permisos de visualización (incluye validación de frecuencia)
try {
	$can_display = $can_display_method->invoke( $shortcodes_instance, $modal_id );
	error_log( 'EWM BLOCK RENDER: Display validation result for modal ' . $modal_id . ': ' . ( $can_display ? 'ALLOWED' : 'BLOCKED' ) );
	
	if ( ! $can_display ) {
		error_log( 'EWM BLOCK RENDER: Modal ' . $modal_id . ' blocked by display rules (frequency, pages, roles, etc.)' );
		// No mostrar error en frontend si está bloqueado por reglas válidas
		return '';
	}
} catch ( Exception $e ) {
	error_log( 'EWM BLOCK RENDER: Error validating display rules for modal ' . $modal_id . ': ' . $e->getMessage() );
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return '<div style="padding: 20px; border: 2px solid #dc3232; text-align: center; color: #dc3232;">[EWM Modal: Error validando reglas - ' . esc_html( $e->getMessage() ) . ']</div>';
	}
	return '';
}

// Usar el motor de renderizado universal
try {
	$render_core = EWM_Render_Core::get_instance();
	$output = $render_core->render_modal( $modal_id, array( 'source' => 'gutenberg_block' ) );

	// Log del resultado para debugging
	error_log( 'EWM BLOCK RENDER: Output length for modal ' . $modal_id . ' is ' . strlen( $output ) . ' chars.' );

	if ( empty( $output ) ) {
		error_log( 'EWM BLOCK RENDER: Empty output from render_modal for modal ' . $modal_id );
		if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			echo '<div style="padding: 20px; border: 2px solid #ffb900; text-align: center; color: #996600;">[EWM Modal: Modal ' . $modal_id . ' renderizado pero sin contenido]</div>';
		}
	} else {
		echo $output;
	}

} catch ( Exception $e ) {
	error_log( 'EWM BLOCK RENDER: Exception rendering modal ' . $modal_id . ': ' . $e->getMessage() );
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		echo '<div style="padding: 20px; border: 2px solid #dc3232; text-align: center; color: #dc3232;">[EWM Modal: Error - ' . esc_html( $e->getMessage() ) . ']</div>';
	}
}
