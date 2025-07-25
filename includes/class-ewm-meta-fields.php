
<?php
/**
 * EWM Meta Fields Manager
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clase para manejar meta fields flexibles con soporte JSON y serializado
 */
class EWM_Meta_Fields {
   /**
	* Instancia singleton
	*/
   private static $instance = null;
   /**
	* Resuelve un valor de página/categoría especial, slug o numérico a su ID o valor lógico.
	* Si es numérico, lo retorna como int. Si es un slug, busca el ID de la página o categoría.
	* Si no encuentra nada, retorna null.
	*/
   public static function resolve_to_id($value) {
	   if (is_numeric($value)) {
		   return (int)$value;
	   }
	   switch ($value) {
		   case 'home':
			   $id = (int) get_option('page_on_front');
			   return $id > 0 ? $id : null;
		   case 'blog':
			   $id = (int) get_option('page_for_posts');
			   return $id > 0 ? $id : null;
		   case 'none':
			   return 0;
		   case 'all':
			   return -1;
		   default:
			   $page = get_page_by_path($value);
			   if ($page) {
				   return (int)$page->ID;
			   }
			   // Soporte para categorías por slug
			   if (function_exists('get_category_by_slug')) {
				   $cat = get_category_by_slug($value);
				   if ($cat && isset($cat->term_id)) {
					   return (int)$cat->term_id;
				   }
			   }
			   return null;
	   }
   }
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

















	/**
	 * Validar configuración de pasos
	 */
	private function validate_steps_config( $config ) {
		// CONTRACTOR FIX: Siempre esperamos estructura completa, eliminada condición problemática
		$validated = array(
			'steps'       => array(), // Inicializar vacío
			'final_step'  => array(),
			'progressBar' => array(
				'enabled' => true,
				'color'   => '#ff6b35',
				'style'   => 'line',
			),
		);


		// Validar pasos
		if ( isset( $config['steps'] ) && is_array( $config['steps'] ) ) {
			foreach ( $config['steps'] as $index => $step ) {
				if ( is_array( $step ) ) {
					// 🔧 CORREGIR: Mantener el ID original del paso y estructura del JavaScript
					$validated_step = array(
						'id'          => $step['id'] ?? 'step_' . $index, // Usar ID original del paso
						'title'       => sanitize_text_field( $step['title'] ?? 'Paso ' . ( $index + 1 ) ),
						'subtitle'    => sanitize_text_field( $step['subtitle'] ?? '' ),
						'content'     => wp_kses_post( $step['content'] ?? '' ),
						'fields'      => array(),
						'button_text' => sanitize_text_field( $step['button_text'] ?? 'NEXT' ),
						'description' => sanitize_text_field( $step['description'] ?? '' ),
					);


					// Validar campos del paso - CORREGIR para manejar strings simples
					if ( isset( $step['fields'] ) && is_array( $step['fields'] ) ) {
						foreach ( $step['fields'] as $field ) {
							if ( is_array( $field ) ) {
								// Solo procesar campos complejos (objetos) del builder
								$validated_step['fields'][] = $this->validate_form_field( $field );
							}
							// ELIMINADO: Conversión automática de strings a campos
							// Los campos deben venir del builder como objetos completos
						}
					}

					$validated['steps'][] = $validated_step;
				}
			}
		}


		// Validar paso final
		if ( isset( $config['final_step'] ) && is_array( $config['final_step'] ) ) {
			$final_step              = $config['final_step'];
			$validated['final_step'] = array(
				'title'    => sanitize_text_field( $final_step['title'] ?? '' ),
				'subtitle' => sanitize_text_field( $final_step['subtitle'] ?? '' ),
				'content'  => wp_kses_post( $final_step['content'] ?? '' ), // AÑADIDO: Campo content con sanitización
				'fields'   => array(),
			);

			if ( isset( $final_step['fields'] ) && is_array( $final_step['fields'] ) ) {
				foreach ( $final_step['fields'] as $field ) {
					if ( is_array( $field ) ) {
						$validated['final_step']['fields'][] = $this->validate_form_field( $field );
					}
				}
			}
		}

		// Validar barra de progreso
		if ( isset( $config['progressBar'] ) && is_array( $config['progressBar'] ) ) {
			$progress                 = $config['progressBar'];
			$validated['progressBar'] = array(
				'enabled' => ! empty( $progress['enabled'] ),
				'color'   => sanitize_hex_color( $progress['color'] ?? '#ff6b35' ) ?: '#ff6b35',
				'style'   => in_array( $progress['style'] ?? 'line', array( 'line', 'dots' ) ) ? $progress['style'] : 'line',
			);
		}

		return $validated;
	}

	// ELIMINADAS: Funciones de mapeo automático de campos legacy
	// get_field_type_from_name() y get_field_label_from_name()
	// Ya no se necesitan porque todos los campos vienen del builder como objetos completos

	/**
	 * Obtener tipos de campo soportados
	 */
	public static function get_supported_field_types() {
		return array(
			'text'           => __( 'Texto', 'ewm-modal-cta' ),
			'email'          => __( 'Email', 'ewm-modal-cta' ),
			'tel'            => __( 'Teléfono', 'ewm-modal-cta' ),
			'textarea'       => __( 'Área de Texto', 'ewm-modal-cta' ),
			'select'         => __( 'Lista Desplegable', 'ewm-modal-cta' ),
			'radio'          => __( 'Botones de Opción', 'ewm-modal-cta' ),
			'checkbox'       => __( 'Casillas de Verificación', 'ewm-modal-cta' ),
			'number'         => __( 'Número', 'ewm-modal-cta' ),
			'url'            => __( 'URL', 'ewm-modal-cta' ),
			'date'           => __( 'Fecha', 'ewm-modal-cta' ),
			'hidden'         => __( 'Oculto', 'ewm-modal-cta' ),
			// Nuevos tipos de campo
			'time'           => __( 'Hora', 'ewm-modal-cta' ),
			'datetime-local' => __( 'Fecha y Hora Local', 'ewm-modal-cta' ),
			'range'          => __( 'Rango (Slider)', 'ewm-modal-cta' ),
			'color'          => __( 'Selector de Color', 'ewm-modal-cta' ),
			'password'       => __( 'Contraseña', 'ewm-modal-cta' ),
			'search'         => __( 'Búsqueda', 'ewm-modal-cta' ),
			'month'          => __( 'Mes', 'ewm-modal-cta' ),
			'week'           => __( 'Semana', 'ewm-modal-cta' ),
		);
	}

	/**
	 * Validar campo de formulario
	 */
	private function validate_form_field( $field ) {
		$allowed_types = array_keys( self::get_supported_field_types() );

		$validated = array(
			'id'          => sanitize_key( $field['id'] ?? '' ),
			'type'        => in_array( $field['type'] ?? 'text', $allowed_types ) ? $field['type'] : 'text',
			'label'       => sanitize_text_field( $field['label'] ?? '' ),
			'placeholder' => sanitize_text_field( $field['placeholder'] ?? '' ),
			'required'    => ! empty( $field['required'] ),
			'step'        => intval( $field['step'] ?? 1 ),
			'order'       => intval( $field['order'] ?? 1 ),
		);

		// Validar opciones para select/radio/checkbox
		if ( isset( $field['options'] ) && is_array( $field['options'] ) ) {
			$validated['options'] = array();
			foreach ( $field['options'] as $option ) {
				if ( is_array( $option ) && isset( $option['value'], $option['label'] ) ) {
					$validated['options'][] = array(
						'value' => sanitize_text_field( $option['value'] ),
						'label' => sanitize_text_field( $option['label'] ),
					);
				}
			}
		}

		// Validar reglas de validación
		if ( isset( $field['validation_rules'] ) && is_array( $field['validation_rules'] ) ) {
			$rules                         = $field['validation_rules'];
			$validated['validation_rules'] = array(
				'type'       => sanitize_text_field( $rules['type'] ?? '' ),
				'min_length' => intval( $rules['min_length'] ?? 0 ),
				'max_length' => intval( $rules['max_length'] ?? 0 ),
				'pattern'    => sanitize_text_field( $rules['pattern'] ?? '' ),
				'message'    => sanitize_text_field( $rules['message'] ?? '' ),
			);
		}

		return $validated;
	}

	/**
	 * Validar configuración de diseño
	 */
	private function validate_design_config( $config ) {
		return array(
			'theme'      => sanitize_text_field( $config['theme'] ?? 'default' ),
			'colors'     => array(
				'primary'    => sanitize_hex_color( $config['colors']['primary'] ?? '#ff6b35' ) ?: '#ff6b35',
				'secondary'  => sanitize_hex_color( $config['colors']['secondary'] ?? '#333333' ) ?: '#333333',
				'background' => sanitize_hex_color( $config['colors']['background'] ?? '#ffffff' ) ?: '#ffffff',
			),
			'typography' => array(
				'font_family' => sanitize_text_field( $config['typography']['font_family'] ?? 'inherit' ),
				'font_size'   => sanitize_text_field( $config['typography']['font_size'] ?? '16px' ),
			),
			'modal_size' => in_array( $config['modal_size'] ?? 'medium', array( 'small', 'medium', 'large' ) ) ?
							$config['modal_size'] : 'medium',
			'animation'  => in_array( $config['animation'] ?? 'fade', array( 'fade', 'slide', 'zoom' ) ) ?
							$config['animation'] : 'fade',
		);
	}

	/**
	 * Validar configuración de triggers
	 */
	private function validate_trigger_config( $config ) {
		return array(
			'exit_intent'       => array(
				'enabled'     => ! empty( $config['exit_intent']['enabled'] ),
				'sensitivity' => intval( $config['exit_intent']['sensitivity'] ?? 20 ),
			),
			'time_delay'        => array(
				'enabled' => ! empty( $config['time_delay']['enabled'] ),
				'delay'   => intval( $config['time_delay']['delay'] ?? 5000 ),
			),
			'scroll_percentage' => array(
				'enabled'    => ! empty( $config['scroll_percentage']['enabled'] ),
				'percentage' => intval( $config['scroll_percentage']['percentage'] ?? 50 ),
			),
			'manual'            => array(
				'enabled'  => ! empty( $config['manual']['enabled'] ),
				'selector' => sanitize_text_field( $config['manual']['selector'] ?? '' ),
			),
			'frequency'         => array(
				'type'  => in_array( $config['frequency']['type'] ?? 'session', array( 'always', 'session', 'daily', 'weekly' ) ) ?
						$config['frequency']['type'] : 'session',
				'limit' => intval( $config['frequency']['limit'] ?? 1 ),
			),
		);
	}

	/**
	 * Validar integración WooCommerce
	 */
private function validate_wc_integration( $config ) {
	// Asegurar que la configuración es un array. Si no, devolver array vacío.
	if ( ! is_array( $config ) ) {
		return array();
	}
	// Devolver la configuración recibida sin modificarla.
	return $config;
}

	/**
	 * Validar reglas de visualización
	 */
	private function validate_display_rules( $config ) {
		return array(
			'enabled'    => ! empty( $config['enabled'] ),
			'pages'      => array(
			'include' => array_filter(array_map( [self::class, 'resolve_to_id'], $config['pages']['include'] ?? array() ), function($v){return $v !== null;}),
			'exclude' => array_filter(array_map( [self::class, 'resolve_to_id'], $config['pages']['exclude'] ?? array() ), function($v){return $v !== null;}),
			),
			'user_roles' => array_map( 'sanitize_text_field', $config['user_roles'] ?? array() ),
			'devices'    => array(
				'desktop' => ! empty( $config['devices']['desktop'] ),
				'tablet'  => ! empty( $config['devices']['tablet'] ),
				'mobile'  => ! empty( $config['devices']['mobile'] ),
			),
		);
	}

	/**
	 * Validar mapeo de campos
	 */
	private function validate_field_mapping( $config ) {
		$validated = array();

		if ( is_array( $config ) ) {
			foreach ( $config as $field_id => $mapping ) {
				if ( is_array( $mapping ) ) {
					$validated[ sanitize_key( $field_id ) ] = array(
						'wp_field'     => sanitize_text_field( $mapping['wp_field'] ?? '' ),
						'custom_field' => sanitize_text_field( $mapping['custom_field'] ?? '' ),
						'integration'  => sanitize_text_field( $mapping['integration'] ?? '' ),
					);
				}
			}
		}

		return $validated;
	}

	/**
	 * Obtener meta field con fallback
	 */
	public static function get_meta( $post_id, $meta_key, $default = array() ) {
		$value = get_post_meta( $post_id, $meta_key, true );

		if ( empty( $value ) ) {
			return $default;
		}

		// Si es string, intentar decodificar JSON
		if ( is_string( $value ) ) {
			$decoded = json_decode( $value, true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				return $decoded;
			}
		}

		return is_array( $value ) ? $value : $default;
	}

// ...existing code...
}
