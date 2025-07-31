<?php
/**
 * EWM Render Core - Motor de renderizado universal
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clase para el motor de renderizado universal
 */
class EWM_Render_Core {

	/**
	 * Instancia singleton
	 */
	private static $instance = null;

	/**
	 * Modales renderizados en la página actual con sus configuraciones
	 */
	private $rendered_modals = array();

	/**
	 * Assets encolados
	 */
	private $assets_enqueued = false;

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
		add_action( 'wp_footer', array( $this, 'render_modal_scripts' ), 20 );
		add_action( 'wp_head', array( $this, 'add_modal_styles' ), 10 );
	}

	/**
	 * Función principal de renderizado (usada por shortcodes)
	 */
	public function render_modal( $modal_id, $config = array() ) {
		// Si es preview y se recibe config, asignar automáticamente al global
		if ( $modal_id === 'preview' && ! empty( $config ) && is_array( $config ) ) {
			$GLOBALS['ewm_preview_config'] = $config;
		}

		// Validar modal
		$is_valid = $this->validate_modal( $modal_id );
		if ( ! $is_valid ) {
			return '';
		}

		// Evitar renderizado duplicado
		if ( isset( $this->rendered_modals[ $modal_id ] ) ) {
			$previous_source = $this->rendered_modals[ $modal_id ]['source'] ?? 'unknown';
			$current_source  = $config['source'] ?? 'shortcode';

			return '';
		}

		// Obtener configuración del modal
		$modal_config = $this->get_modal_configuration( $modal_id );

		if ( empty( $modal_config ) ) {
			return '';
		}

		// Combinar configuración (config pasado tiene prioridad para flags especiales como is_woocommerce)
		$render_config = array_merge( $modal_config, $config );

		// IMPORTANTE: Si es modal WooCommerce, forzar modo 'anuncio'
		if ( isset( $render_config['is_woocommerce'] ) && $render_config['is_woocommerce'] === true ) {
			$render_config['mode'] = 'anuncio';
		}

		// Encolar assets si es necesario
		$this->enqueue_modal_assets();

		// Generar HTML del modal
		$html = $this->generate_modal_html( $modal_id, $render_config );

		// Registrar modal como renderizado con su configuración para JavaScript
		$this->rendered_modals[ $modal_id ] = $render_config;

		return $html;
	}

	/**
	 * Validar modal
	 */
	private function validate_modal( $modal_id ) {
		if ( $modal_id === 'preview' ) {
			return true;
		}
		if ( ! is_numeric( $modal_id ) || $modal_id <= 0 ) {
			return false;
		}

		$post = get_post( $modal_id );

		if ( ! $post || $post->post_type !== 'ew_modal' || $post->post_status !== 'publish' ) {
			return false;
		}

		return true;
	}

	/**
	 * Obtener configuración completa del modal
	 */
	private function get_modal_configuration( $modal_id ) {
		// Si es preview, retornar la configuración global temporal
		if ( $modal_id === 'preview' && ! empty( $GLOBALS['ewm_preview_config'] ) && is_array( $GLOBALS['ewm_preview_config'] ) ) {
			return $GLOBALS['ewm_preview_config'];
		}

		// DEBUGGING PROFUNDO según recomendación del consultor

		// Validación robusta del ID según consultor
		if ( ! is_numeric( $modal_id ) || $modal_id <= 0 ) {
			return array();
		}

		// ARQUITECTURA CONSISTENTE: Leer de campos separados (igual que admin)
		$steps_json    = get_post_meta( $modal_id, 'ewm_steps_config', true );
		$design_json   = get_post_meta( $modal_id, 'ewm_design_config', true );
		$triggers_json = get_post_meta( $modal_id, 'ewm_trigger_config', true );
		$wc_json       = get_post_meta( $modal_id, 'ewm_wc_integration', true );
		$rules_json    = get_post_meta( $modal_id, 'ewm_display_rules', true );

		// Unificar datos en memoria para el frontend con validación de tipos
		$config = array(
			'steps'          => is_string( $steps_json ) ? ( json_decode( $steps_json, true ) ?: array() ) : array(),
			'design'         => is_string( $design_json ) ? ( json_decode( $design_json, true ) ?: array() ) : array(),
			'triggers'       => is_string( $triggers_json ) ? ( json_decode( $triggers_json, true ) ?: array() ) : array(),
			'wc_integration' => is_string( $wc_json ) ? ( json_decode( $wc_json, true ) ?: array() ) : array(),
			'display_rules'  => is_string( $rules_json ) ? ( json_decode( $rules_json, true ) ?: array() ) : array(),
		);

		// Agregar datos básicos del modal
		$config['modal_id'] = $modal_id;
		$config['title']    = get_the_title( $modal_id );

		// Asegurar que existe un modo por defecto
		if ( ! isset( $config['mode'] ) || empty( $config['mode'] ) ) {
			$config['mode'] = 'formulario';
		}

		// Mantener compatibilidad con custom_css por separado
		if ( ! isset( $config['custom_css'] ) || empty( $config['custom_css'] ) ) {
			$config['custom_css'] = get_post_meta( $modal_id, 'ewm_custom_css', true ) ?: '';
		}

		// Aplicar valores por defecto
		$config = $this->apply_default_config( $config );

		// Aplicar filtros para el sistema actual
		return apply_filters( 'ew_modal_configuration', $config, $modal_id );
	}

	/**
	 * Aplicar configuración por defecto
	 */
	private function apply_default_config( $config ) {
		// Configuración de diseño por defecto
		if ( ! is_array( $config['design'] ?? null ) ) {
			$config['design'] = array();
		}
		$config['design'] = array_merge(
			array(
				'theme'      => 'default',
				'colors'     => array(
					'primary'    => '#ff6b35',
					'secondary'  => '#333333',
					'background' => '#ffffff',
				),
				'typography' => array(
					'font_family' => 'inherit',
					'font_size'   => '16px',
				),
				'modal_size' => 'medium',
				'animation'  => 'fade',
			),
			$config['design']
		);

		// BYPASS COMPLETO: Si ya hay configuración de triggers, NO aplicar defaults
		if ( empty( $config['triggers'] ) ) {
			// Solo aplicar defaults si NO hay configuración
			$config['triggers'] = array(
				'exit_intent'       => array(
					'enabled'     => false,
					'sensitivity' => 20,
				),
				'time_delay'        => array(
					'enabled' => false,
					'delay'   => 5000,
				),
				'scroll_percentage' => array(
					'enabled'    => false,
					'percentage' => 50,
				),
				'manual'            => array(
					'enabled'  => true,
					'selector' => '',
				),
			);
		}
		// Si ya hay configuración, mantenerla intacta

		// BYPASS COMPLETO: Si ya hay configuración de display_rules, NO aplicar defaults
		if ( empty( $config['display_rules'] ) ) {
			// Solo aplicar defaults si NO hay configuración
			$config['display_rules'] = array(
				'pages'      => array(
					'include' => array(),
					'exclude' => array(), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Default empty configuration array
				),
				'user_roles' => array(),
				'devices'    => array(
					'desktop' => true,
					'tablet'  => true,
					'mobile'  => true,
				),
				'frequency'  => array(
					'type'  => 'always',
					'limit' => 0,
				),
			);
		}
		// Si ya hay configuración, mantenerla intacta

		return $config;
	}

	/**
	 * Generar HTML del modal
	 */
	private function generate_modal_html( $modal_id, $config ) {
		$modal_class = $this->get_modal_css_classes( $config );
		$modal_data  = $this->get_modal_data_attributes( $modal_id, $config );

		ob_start();
		?>
		<div id="ewm-modal-<?php echo esc_attr( (string) $modal_id ); ?>"
			class="<?php echo esc_attr( $modal_class ); ?>"
			<?php echo wp_kses( $modal_data, array( 'data-*' => array() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Data attributes are sanitized with wp_kses ?>
			style="display: none;">
			
			<div class="ewm-modal-backdrop"></div>
			
			<div class="ewm-modal-container">
				<div class="ewm-modal-content">
					
					<!-- Header del modal -->
					<div class="ewm-modal-header">
						<button type="button" class="ewm-modal-close" aria-label="Cerrar modal">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					
					<!-- Contenido del modal -->
					<div class="ewm-modal-body">
						<?php echo $this->generate_modal_content( $modal_id, $config ); ?>
					</div>
					
				</div>
			</div>
			
		</div>
		<?php

		$html_output = ob_get_clean();

		// CONFIGURACIÓN DEL MODAL PARA AUTO-INICIALIZACIÓN (sin botón de test)
		$html_output .= "
	   <script>
	   // Auto-inicializar modal {$modal_id}
	   if (typeof window.ew_modal_configs === 'undefined') {
		   window.ew_modal_configs = [];
	   }

	   window.ew_modal_configs.push(" . wp_json_encode( $config ) . ');
	   </script>';

		return $html_output;
	}

	/**
	 * Generar contenido del modal según el modo
	 */
	private function generate_modal_content( $modal_id, $config ) {
		switch ( $config['mode'] ) {
			case 'formulario':
				return $this->generate_form_content( $modal_id, $config );
			case 'anuncio':
				return $this->generate_announcement_content( $modal_id, $config );
			default:
				return $this->generate_form_content( $modal_id, $config );
		}
	}

	/**
	 * Generar contenido de formulario multi-paso
	 */
	private function generate_form_content( $modal_id, $config ) {
		$steps        = $config['steps']['steps'] ?? array();
		$final_step   = $config['steps']['final_step'] ?? array();
		$progress_bar = $config['steps']['progressBar'] ?? array( 'enabled' => true );

		if ( empty( $steps ) ) {
			return '<div class="ewm-error">No hay pasos configurados para este modal.</div>';
		}

		ob_start();
		?>
		<div class="ewm-form-container" data-modal-id="<?php echo esc_attr( (string) $modal_id ); ?>">
			
			<!-- Contenedor de notificaciones centralizado -->
			<div id="ewm-notifications-container" class="ewm-notifications-container" style="display: none;">
				<!-- Las notificaciones se insertarán dinámicamente aquí -->
			</div>
			
			<?php if ( $progress_bar['enabled'] ) : ?>
			<div class="ewm-progress-bar" 
				data-style="<?php echo esc_attr( $progress_bar['style'] ?? 'line' ); ?>"
				data-color="<?php echo esc_attr( $progress_bar['color'] ?? '#ff6b35' ); ?>">
				<div class="ewm-progress-fill" style="width: 0%;"></div>
				<div class="ewm-progress-steps">
					<?php
					// Calcular número total de pasos (solo agregar +1 si final_step tiene contenido)
					$has_final_step = ! empty( $final_step['title'] ) || ! empty( $final_step['fields'] );
					$total_steps    = count( $steps ) + ( $has_final_step ? 1 : 0 );
					?>
					<?php for ( $i = 1; $i <= $total_steps; $i++ ) : ?>
						<div class="ewm-progress-step <?php echo esc_attr( $i === 1 ? 'active' : '' ); ?>"
							data-step="<?php echo esc_attr( (string) $i ); ?>">
							<span class="ewm-step-number"><?php echo esc_html( (string) $i ); ?></span>
						</div>
					<?php endfor; ?>
				</div>
			</div>
			<?php endif; ?>
			
			<form class="ewm-multi-step-form" method="post">
				
				<?php foreach ( $steps as $index => $step ) : ?>
				<div class="ewm-form-step <?php echo esc_attr( $index === 0 ? 'active' : '' ); ?>"
					data-step="<?php echo esc_attr( $step['id'] ?? '' ); ?>">
					 
					<?php if ( ! empty( $step['title'] ) ) : ?>
						<h3 class="ewm-step-title"><?php echo esc_html( $step['title'] ); ?></h3>
					<?php endif; ?>
					
					<?php if ( ! empty( $step['subtitle'] ) ) : ?>
						<p class="ewm-step-subtitle"><?php echo esc_html( $step['subtitle'] ); ?></p>
					<?php endif; ?>

					<?php if ( ! empty( $step['content'] ) ) : ?>
						<div class="ewm-step-content"><?php echo esc_html( $step['content'] ); ?></div>
					<?php endif; ?>

					<div class="ewm-step-fields">
						<?php echo $this->generate_form_fields( $step['fields'] ?? array() ); ?>
					</div>

					<?php if ( ! empty( $step['description'] ) ) : ?>
						<p class="ewm-step-description"><?php echo esc_html( $step['description'] ); ?></p>
					<?php endif; ?>
					
					<div class="ewm-step-navigation">
						<?php if ( $index > 0 ) : ?>
							<button type="button" class="ewm-btn ewm-btn-secondary ewm-btn-prev">
								<?php esc_html_e( 'Previous', 'ewm-modal-cta' ); ?>
							</button>
						<?php endif; ?>

						<?php
						// Determinar si este es el último paso
						$has_final_step = ! empty( $final_step['title'] ) || ! empty( $final_step['fields'] );
						$is_last_step   = ( $index === count( $steps ) - 1 ) && ! $has_final_step;
						?>

						<?php if ( $is_last_step ) : ?>
							<button type="submit" class="ewm-btn ewm-btn-primary ewm-btn-submit">
								<?php esc_html_e( 'Submit', 'ewm-modal-cta' ); ?>
							</button>
						<?php else : ?>
							<button type="button" class="ewm-btn ewm-btn-primary ewm-btn-next">
								<?php echo esc_html( $step['button_text'] ?? __( 'Next', 'ewm-modal-cta' ) ); ?>
							</button>
						<?php endif; ?>
					</div>
					
				</div>
				<?php endforeach; ?>
				
				<!-- Paso final -->
				<?php if ( ! empty( $final_step['title'] ) || ! empty( $final_step['fields'] ) ) : ?>
				<div class="ewm-form-step ewm-final-step" data-step="final">
					
					<?php if ( ! empty( $final_step['title'] ) ) : ?>
						<h3 class="ewm-step-title"><?php echo esc_html( $final_step['title'] ); ?></h3>
					<?php endif; ?>
					
					<?php if ( ! empty( $final_step['subtitle'] ) ) : ?>
						<p class="ewm-step-subtitle"><?php echo esc_html( $final_step['subtitle'] ); ?></p>
					<?php endif; ?>
					
					<div class="ewm-step-fields">
						<?php echo $this->generate_form_fields( $final_step['fields'] ?? array() ); ?>
					</div>
					
					<div class="ewm-step-navigation">
						<button type="button" class="ewm-btn ewm-btn-secondary ewm-btn-prev">
							<?php esc_html_e( 'Previous', 'ewm-modal-cta' ); ?>
						</button>

						<button type="submit" class="ewm-btn ewm-btn-primary ewm-btn-submit">
							<?php esc_html_e( 'Submit', 'ewm-modal-cta' ); ?>
						</button>
					</div>
					
				</div>
				<?php endif; ?>
				
				<!-- Mensaje de éxito -->
				<div class="ewm-form-step ewm-success-step" data-step="success" style="display: none;">
					<div class="ewm-success-content">
						<h3><?php esc_html_e( 'Thank You!', 'ewm-modal-cta' ); ?></h3>
						<p><?php esc_html_e( 'Your information has been submitted successfully.', 'ewm-modal-cta' ); ?></p>
					</div>
				</div>
				
				<?php wp_nonce_field( 'ewm_form_submit', 'ewm_nonce' ); ?>
				<input type="hidden" name="action" value="ewm_submit_form">
				<input type="hidden" name="modal_id" value="<?php echo esc_attr( (string) $modal_id ); ?>">
				
			</form>
			
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Generar campos de formulario
	 */
	private function generate_form_fields( $fields ) {
		if ( empty( $fields ) ) {
			// TEMPORAL: Mostrar mensaje cuando no hay campos configurados
			return '<div class="ewm-no-fields-message" style="padding: 20px; text-align: center; color: #666; border: 1px dashed #ccc; margin: 10px 0;">
						<p><strong>Este paso no tiene campos configurados.</strong></p>
						<p><small>Agrega campos en el Modal Builder para mostrar contenido del formulario.</small></p>
					</div>';
		}

		ob_start();

		foreach ( $fields as $field ) {
			// Validar que el campo es un array válido
			if ( ! is_array( $field ) ) {
				continue;
			}

			$field_id          = esc_attr( (string) ( $field['id'] ?? '' ) );
			$field_type        = esc_attr( (string) ( $field['type'] ?? 'text' ) );
			$field_label       = esc_html( (string) ( $field['label'] ?? '' ) );
			$field_placeholder = esc_attr( (string) ( $field['placeholder'] ?? '' ) );
			$field_required    = ! empty( $field['required'] );
			$field_class       = 'ewm-field ewm-field-' . $field_type;

			if ( $field_required ) {
				$field_class .= ' ewm-field-required';
			}

			?>
			<div class="<?php echo esc_attr( $field_class ); ?>">

				<?php if ( $field_label ) : ?>
					<label for="<?php echo esc_attr( $field_id ); ?>" class="ewm-field-label">
						<?php echo esc_html( $field_label ); ?>
						<?php if ( $field_required ) : ?>
							<span class="ewm-required">*</span>
						<?php endif; ?>
					</label>
				<?php endif; ?>

				<?php echo $this->generate_field_input( $field ); ?>
				
			</div>
			<?php
		}

		return ob_get_clean();
	}

	/**
	 * Generar input del campo
	 */
	private function generate_field_input( $field ) {
		$field_id          = esc_attr( $field['id'] ?? '' );
		$field_type        = $field['type'] ?? 'text';
		$field_placeholder = esc_attr( $field['placeholder'] ?? '' );
		$field_required    = ! empty( $field['required'] );
		$validation_rules  = $field['validation_rules'] ?? array();

		$attributes = array(
			'id'          => $field_id,
			'name'        => $field_id,
			'class'       => 'ewm-field-input',
			'placeholder' => $field_placeholder,
		);

		if ( $field_required ) {
			$attributes['required'] = 'required';
		}

		// Agregar patrones HTML5 nativos según el tipo de campo
		switch ( $field_type ) {
			case 'tel':
				// Patrón para teléfonos: solo números, espacios, guiones, paréntesis y signo +
				if ( ! isset( $attributes['pattern'] ) ) {
					$attributes['pattern'] = '[+]?[0-9\s\-\(\)]+';
				}
				$attributes['inputmode'] = 'tel';
				break;
			case 'email':
				$attributes['inputmode'] = 'email';
				break;
			case 'url':
				$attributes['inputmode'] = 'url';
				break;
			case 'number':
				$attributes['inputmode'] = 'numeric';
				break;
		}

		// Agregar atributos de validación personalizados
		if ( ! empty( $validation_rules ) ) {
			if ( isset( $validation_rules['min_length'] ) ) {
				$attributes['minlength'] = $validation_rules['min_length'];
			}
			if ( isset( $validation_rules['max_length'] ) ) {
				$attributes['maxlength'] = $validation_rules['max_length'];
			}
			if ( isset( $validation_rules['pattern'] ) ) {
				// Los patrones personalizados sobrescriben los automáticos
				$attributes['pattern'] = $validation_rules['pattern'];
			}
			// Para campos de rango (range)
			if ( isset( $validation_rules['min'] ) ) {
				$attributes['min'] = $validation_rules['min'];
			}
			if ( isset( $validation_rules['max'] ) ) {
				$attributes['max'] = $validation_rules['max'];
			}
			if ( isset( $validation_rules['step'] ) ) {
				$attributes['step'] = $validation_rules['step'];
			}
		}

		// Agregar atributos específicos del campo
		if ( isset( $field['min'] ) ) {
			$attributes['min'] = $field['min'];
		}
		if ( isset( $field['max'] ) ) {
			$attributes['max'] = $field['max'];
		}
		if ( isset( $field['step'] ) ) {
			$attributes['step'] = $field['step'];
		}

		$attr_string = '';
		foreach ( $attributes as $key => $value ) {
			$attr_string .= ' ' . $key . '="' . esc_attr( $value ) . '"';
		}

		switch ( $field_type ) {
			case 'textarea':
				return '<textarea' . $attr_string . '></textarea>';

			case 'select':
				$options = $field['options'] ?? array();
				$select  = '<select' . $attr_string . '>';
				if ( $field_placeholder ) {
					$select .= '<option value="">' . esc_html( $field_placeholder ) . '</option>';
				}
				foreach ( $options as $option ) {
					$select .= '<option value="' . esc_attr( $option['value'] ) . '">' .
								esc_html( $option['label'] ) . '</option>';
				}
				$select .= '</select>';
				return $select;

			case 'radio':
			case 'checkbox':
				$options = $field['options'] ?? array();

				// Si no hay opciones definidas, renderizar como input simple
				if ( empty( $options ) ) {
					// Para checkbox simple, agregar valor por defecto
					if ( $field_type === 'checkbox' ) {
						$attributes['value'] = '1';
					}
					// Asegurar que tiene la clase ewm-field-input
					$attributes['class'] = 'ewm-field-input';
					$attr_string         = '';
					foreach ( $attributes as $key => $value ) {
						$attr_string .= ' ' . $key . '="' . esc_attr( $value ) . '"';
					}
					return '<input type="' . esc_attr( $field_type ) . '"' . $attr_string . '>';
				}

				// Renderizar con opciones múltiples
				$inputs = '';

				// Determinar si es un campo con múltiples opciones (siempre true aquí porque ya verificamos empty arriba)
				$is_multi_option = count( $options ) > 1;

				foreach ( $options as $option ) {
					$option_id = $field_id . '_' . sanitize_key( $option['value'] );

					// Aplicar notación de array solo a checkboxes con múltiples opciones
					// Radio buttons nunca usan array notation (solo una selección)
					// Checkbox simples tampoco (valor único: yes/no)
					// Checkbox con opciones múltiples SÍ usan array notation
					$input_name = ( $field_type === 'checkbox' && $is_multi_option ) ? $field_id . '[]' : $field_id;

					$inputs .= '<label class="ewm-option-label">';
					$inputs .= '<input type="' . $field_type . '" name="' . $input_name .
								'" id="' . $option_id . '" value="' . esc_attr( $option['value'] ) . '" class="ewm-field-input">';
					$inputs .= '<span>' . esc_html( $option['label'] ) . '</span>';
					$inputs .= '</label>';
				}
				return '<div class="ewm-options-group">' . $inputs . '</div>';

			default:
				return '<input type="' . esc_attr( $field_type ) . '"' . $attr_string . '>';
		}
	}

	/**
	 * Generar contenido de anuncio
	 */
	private function generate_announcement_content( $modal_id, $config ) {
		// Verificar si es un modal WooCommerce
		$is_woocommerce = isset( $config['is_woocommerce'] ) && $config['is_woocommerce'] === true;

		if ( $is_woocommerce ) {
			return $this->generate_woocommerce_content( $modal_id, $config );
		}

		// Contenido de anuncio genérico
		return $this->generate_generic_announcement_content( $modal_id, $config );
	}

	/**
	 * Generar contenido especializado para modales WooCommerce
	 */
	private function generate_woocommerce_content( $modal_id, $config ) {
		$wc_config    = $config['wc_integration'] ?? array();
		$wc_promotion = $wc_config['wc_promotion'] ?? array();

		// Obtener información del cupón
		$discount_code         = $wc_config['discount_code'] ?? '';
		$promotion_title       = $wc_promotion['title'] ?? 'Oferta Especial';
		$promotion_description = $wc_promotion['description'] ?? 'Aprovecha esta oferta limitada';
		$cta_text              = $wc_promotion['cta_text'] ?? 'Aplicar Cupón';

		// Configuración del timer
		$timer_config    = $wc_promotion['timer_config'] ?? array();
		$timer_enabled   = $timer_config['enabled'] ?? false;
		$timer_threshold = $timer_config['threshold_seconds'] ?? 180;

		$html = '<div class="ewm-woocommerce-content">';

		// Título de la promoción
		if ( ! empty( $promotion_title ) ) {
			$html .= '<div class="ewm-wc-promotion-title">';
			$html .= '<h2>' . esc_html( $promotion_title ) . '</h2>';
			$html .= '</div>';
		}

		// Descripción de la promoción
		if ( ! empty( $promotion_description ) ) {
			$html .= '<div class="ewm-wc-promotion-description">';
			$html .= '<p>' . esc_html( $promotion_description ) . '</p>';
			$html .= '</div>';
		}

		// Información del cupón
		if ( ! empty( $discount_code ) ) {
			$html .= '<div class="ewm-wc-coupon-section">';
			$html .= '<div class="ewm-wc-coupon-label">Código de descuento:</div>';
			$html .= '<div class="ewm-wc-coupon-code">';
			$html .= '<span class="ewm-coupon-text">' . esc_html( $discount_code ) . '</span>';
			$html .= '<button class="ewm-copy-coupon" data-coupon="' . esc_attr( $discount_code ) . '">Copiar</button>';
			$html .= '</div>';
			$html .= '</div>';
		}

		// Timer si está habilitado
		if ( $timer_enabled ) {
			$html .= '<div class="ewm-wc-timer-section">';
			$html .= '<div class="ewm-wc-timer-label">Oferta válida por:</div>';
			$html .= '<div class="ewm-wc-timer" data-threshold="' . esc_attr( $timer_threshold ) . '">';
			$html .= '<span class="ewm-timer-minutes">00</span>:<span class="ewm-timer-seconds">00</span>';
			$html .= '</div>';
			$html .= '</div>';
		}

		// CTA Button
		$html .= '<div class="ewm-wc-cta-section">';
		$html .= '<button class="ewm-wc-cta-button" data-action="apply-coupon" data-coupon="' . esc_attr( $discount_code ) . '">';
		$html .= esc_html( $cta_text );
		$html .= '</button>';
		$html .= '</div>';

		$html .= '</div>';

		return $html;
	}

	/**
	 * Generar contenido de anuncio genérico
	 */
	private function generate_generic_announcement_content( $modal_id, $config ) {
		// Contenido de anuncio genérico básico
		return '<div class="ewm-announcement-content"><p>Contenido de anuncio aquí</p></div>';
	}

	/**
	 * Obtener clases CSS del modal
	 */
	private function get_modal_css_classes( $config ) {
		$classes = array(
			'ewm-modal',
			'ewm-modal-' . ( $config['mode'] ?? 'formulario' ),
			'ewm-modal-size-' . ( $config['design']['modal_size'] ?? 'medium' ),
			'ewm-modal-animation-' . ( $config['design']['animation'] ?? 'fade' ),
		);

		if ( ! empty( $config['class'] ) ) {
			$classes[] = $config['class'];
		}

		return implode( ' ', $classes );
	}

	/**
	 * Obtener atributos data del modal
	 */
	private function get_modal_data_attributes( $modal_id, $config ) {
		// Determinar si WooCommerce está habilitado
		$is_woocommerce = isset( $config['wc_integration']['enabled'] ) && $config['wc_integration']['enabled'] === true;

		$data_attrs = array(
			'data-modal-id' => $modal_id,
			'data-trigger'  => $config['trigger'] ?? 'manual',
			'data-config'   => esc_attr(
				wp_json_encode(
					array(
						'triggers'       => $config['triggers'],
						'design'         => $config['design'],
						'wc_integration' => $config['wc_integration'],
						'display_rules'  => $config['display_rules'],
						'is_woocommerce' => $is_woocommerce,
					)
				)
			),
		);

		if ( ! empty( $config['delay'] ) ) {
			$data_attrs['data-delay'] = $config['delay'];
		}

		$attr_string = '';
		foreach ( $data_attrs as $key => $value ) {
			$attr_string .= ' ' . $key . '="' . $value . '"';
		}

		return $attr_string;
	}

	/**
	 * Encolar assets del modal
	 */
	private function enqueue_modal_assets() {
		if ( $this->assets_enqueued ) {
			return;
		}

		wp_enqueue_style(
			'ewm-modal-frontend',
			EWM_PLUGIN_URL . 'assets/css/modal-frontend.css',
			array(),
			EWM_VERSION . '-styled-' . time() // Forzar recarga para styling fix
		);

		wp_enqueue_script(
			'ewm-form-validator',
			EWM_PLUGIN_URL . 'assets/js/form-validator.js',
			array(),
			EWM_VERSION,
			true
		);

		wp_enqueue_script(
			'ewm-modal-frontend',
			EWM_PLUGIN_URL . 'assets/js/modal-frontend.js',
			array( 'ewm-form-validator' ),
			EWM_VERSION . '-styled-' . time(), // Forzar recarga para styling fix
			true
		);

		wp_localize_script(
			'ewm-modal-frontend',
			'ewmModal',
			array(
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'restUrl'        => rest_url( 'ewm/v1/' ),
				// Para endpoints públicos REST, usar wp_rest nonce solo si el usuario está logueado
				'nonce'          => is_user_logged_in() ? wp_create_nonce( 'wp_rest' ) : '',
				'debug'          => defined( 'WP_DEBUG' ) && WP_DEBUG,
				'frequencyDebug' => ( get_option( 'ewm_debug_frequency_enabled', '0' ) === '1' ),
				'strings'        => array(
					'loading'                => __( 'Loading...', 'ewm-modal-cta' ),
					'error'                  => __( 'An error occurred. Please try again.', 'ewm-modal-cta' ),
					'required_field'         => __( 'This field is required.', 'ewm-modal-cta' ),
					'invalid_email'          => __( 'Please enter a valid email.', 'ewm-modal-cta' ),
					'invalid_url'            => __( 'Please enter a valid URL.', 'ewm-modal-cta' ),
					'invalid_time'           => __( 'Please enter a valid time.', 'ewm-modal-cta' ),
					'invalid_datetime_local' => __( 'Please enter a valid date and time.', 'ewm-modal-cta' ),
					'invalid_color'          => __( 'Please enter a valid color (e.g. #RRGGBB).', 'ewm-modal-cta' ),
					'invalid_range'          => __( 'The value is outside the allowed range.', 'ewm-modal-cta' ),
					'invalid_month'          => __( 'Please enter a valid month.', 'ewm-modal-cta' ),
					'invalid_week'           => __( 'Please enter a valid week.', 'ewm-modal-cta' ),
					'min_length'             => __( 'Please enter at least 2 characters.', 'ewm-modal-cta' ),
				),
			)
		);

		$this->assets_enqueued = true;
	}

	/**
	 * Renderizar scripts del modal en el footer
	 */
	public function render_modal_scripts() {
		if ( empty( $this->rendered_modals ) ) {
			return;
		}

		// Preparar configuraciones para JavaScript con validación de tipos
		$modal_configs = array();
		foreach ( $this->rendered_modals as $modal_id => $config ) {
			// Asegurar que $config es un array válido
			if ( ! is_array( $config ) ) {
				continue;
			}

			$modal_configs[] = array(
				'modal_id'       => (string) $modal_id,
				'triggers'       => is_array( $config['triggers'] ?? null ) ? $config['triggers'] : array(),
				'design'         => is_array( $config['design'] ?? null ) ? $config['design'] : array(),
				'steps'          => is_array( $config['steps'] ?? null ) ? $config['steps'] : array(),
				'wc_integration' => is_array( $config['wc_integration'] ?? null ) ? $config['wc_integration'] : array(),
				'display_rules'  => is_array( $config['display_rules'] ?? null ) ? $config['display_rules'] : array(),
				'title'          => (string) ( $config['title'] ?? '' ),
				'mode'           => (string) ( $config['mode'] ?? 'formulario' ),
			);
		}

		?>
		<script type="text/javascript">
		// Configuraciones de modales para JavaScript
		window.ewm_modal_configs = <?php echo wp_json_encode( $modal_configs ); ?>;

		document.addEventListener('DOMContentLoaded', function() {
			if (typeof EWMModalFrontend !== 'undefined') {
				// Modales listos para auto-inicialización
			}
		});
		</script>
		<?php
	}

	/**
	 * Agregar estilos del modal en el head
	 */
	public function add_modal_styles() {
		if ( empty( $this->rendered_modals ) ) {
			return;
		}

		// Aquí se pueden agregar estilos dinámicos si es necesario
	}

	/**
	 * Obtener modales renderizados (solo IDs)
	 */
	public function get_rendered_modals() {
		return array_keys( $this->rendered_modals );
	}

	/**
	 * Obtener información completa de modales renderizados
	 */
	public function get_rendered_modals_info() {
		return $this->rendered_modals;
	}
}

/**
 * Función global para renderizado universal
 */
function ewm_render_modal_core( $modal_id, $config = array() ) {
	return EWM_Render_Core::get_instance()->render_modal( $modal_id, $config );
}
