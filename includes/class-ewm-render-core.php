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
	 * Modales renderizados en la página actual
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
	 * Función principal de renderizado (usada por bloques y shortcodes)
	 */
	public function render_modal( $modal_id, $config = array() ) {
		$start_time = microtime( true );

		// LOGGING CRÍTICO: Inicio del renderizado
		ewm_log_info(
			'RENDER DEBUG - render_modal STARTED',
			array(
				'modal_id'     => $modal_id,
				'config_keys'  => array_keys( $config ),
				'source'       => $config['source'] ?? 'unknown',
				'is_admin'     => is_admin(),
				'current_user' => get_current_user_id(),
			)
		);

		// Validar modal
		$is_valid = $this->validate_modal( $modal_id );
		// TEMPORAL: Logging directo para debugging
		error_log( 'EWM RENDER DEBUG - validate_modal result: modal_id=' . $modal_id . ', valid=' . ( $is_valid ? 'true' : 'false' ) );
		if ( get_post( $modal_id ) ) {
			error_log( 'EWM RENDER DEBUG - post exists: type=' . get_post( $modal_id )->post_type . ', status=' . get_post( $modal_id )->post_status );
		} else {
			error_log( 'EWM RENDER DEBUG - post does NOT exist for modal_id=' . $modal_id );
		}

		ewm_log_info(
			'RENDER DEBUG - validate_modal result',
			array(
				'modal_id'    => $modal_id,
				'valid'       => $is_valid,
				'post_exists' => get_post( $modal_id ) ? true : false,
				'post_type'   => get_post( $modal_id ) ? get_post( $modal_id )->post_type : 'none',
				'post_status' => get_post( $modal_id ) ? get_post( $modal_id )->post_status : 'none',
			)
		);

		if ( ! $is_valid ) {
			ewm_log_warning(
				'RENDER DEBUG - Modal validation FAILED',
				array(
					'modal_id' => $modal_id,
					'source'   => $config['source'] ?? 'unknown',
				)
			);
			return '';
		}

		ewm_log_info(
			'RENDER DEBUG - Modal validation SUCCESS',
			array(
				'modal_id' => $modal_id,
				'source'   => $config['source'] ?? 'unknown',
			)
		);

		// Evitar renderizado duplicado SOLO para shortcodes, NO para bloques de Gutenberg
		$source = $config['source'] ?? 'unknown';
		if ( in_array( $modal_id, $this->rendered_modals ) && $source !== 'gutenberg_block' ) {
			ewm_log_debug( 'Modal already rendered, skipping', array( 'modal_id' => $modal_id, 'source' => $source ) );
			return '';
		}

		// Obtener configuración del modal
		$modal_config = $this->get_modal_configuration( $modal_id );
		ewm_log_info(
			'RENDER DEBUG - modal_config',
			array(
				'modal_id'     => $modal_id,
				'config_empty' => empty( $modal_config ),
				'config_keys'  => is_array( $modal_config ) ? array_keys( $modal_config ) : 'not_array',
				'steps_empty'  => empty( $modal_config['steps'] ),
				'mode'         => $modal_config['mode'] ?? 'none',
			)
		);

		if ( empty( $modal_config ) ) {
			return '';
		}

		// Combinar configuración
		$render_config = array_merge( $modal_config, $config );

		// Encolar assets si es necesario
		$this->enqueue_modal_assets();

		// LOGGING CRÍTICO: Antes de generar HTML
		ewm_log_info(
			'RENDER DEBUG - About to generate modal HTML',
			array(
				'modal_id'           => $modal_id,
				'render_config_keys' => array_keys( $render_config ),
				'modal_config_mode'  => $render_config['mode'] ?? 'none',
			)
		);

		// Generar HTML del modal
		$html = $this->generate_modal_html( $modal_id, $render_config );

		ewm_log_info(
			'RENDER DEBUG - Modal HTML generated',
			array(
				'modal_id'     => $modal_id,
				'html_length'  => strlen( $html ),
				'html_empty'   => empty( $html ),
				'html_preview' => substr( $html, 0, 100 ) . '...',
			)
		);

		// Registrar modal como renderizado
		$this->rendered_modals[] = $modal_id;

		$execution_time = microtime( true ) - $start_time;

		ewm_log_debug(
			'Modal rendered successfully',
			array(
				'modal_id'       => $modal_id,
				'source'         => $config['source'] ?? 'unknown',
				'execution_time' => round( $execution_time * 1000, 2 ) . 'ms',
			)
		);

		return $html;
	}

	/**
	 * Validar modal
	 */
	private function validate_modal( $modal_id ) {
		ewm_log_info(
			'VALIDATE DEBUG - validate_modal detailed check',
			array(
				'modal_id'     => $modal_id,
				'is_numeric'   => is_numeric( $modal_id ),
				'greater_zero' => $modal_id > 0,
			)
		);

		if ( ! is_numeric( $modal_id ) || $modal_id <= 0 ) {
			ewm_log_warning( 'Invalid modal ID', array( 'modal_id' => $modal_id ) );
			return false;
		}

		$post = get_post( $modal_id );

		ewm_log_info(
			'VALIDATE DEBUG - post retrieval detailed',
			array(
				'modal_id'           => $modal_id,
				'post_exists'        => ! empty( $post ),
				'post_type'          => $post ? $post->post_type : 'none',
				'post_status'        => $post ? $post->post_status : 'none',
				'post_title'         => $post ? $post->post_title : 'none',
				'condition_1'        => ! $post,
				'condition_2'        => $post ? ( $post->post_type !== 'ewm_modal' ) : 'n/a',
				'condition_3'        => $post ? ( $post->post_status !== 'publish' ) : 'n/a',
				'overall_condition'  => ( ! $post || $post->post_type !== 'ewm_modal' || $post->post_status !== 'publish' ),
			)
		);

		if ( ! $post || $post->post_type !== 'ew_modal' || $post->post_status !== 'publish' ) {
			ewm_log_warning( 'Modal not found or not published', array(
				'modal_id' => $modal_id,
				'post_type' => $post ? $post->post_type : 'none',
				'post_status' => $post ? $post->post_status : 'none'
			) );
			return false;
		}

		ewm_log_info( 'VALIDATE DEBUG - validation SUCCESS', array( 'modal_id' => $modal_id ) );
		return true;
	}

	/**
	 * Obtener configuración completa del modal
	 */
	private function get_modal_configuration( $modal_id ) {
		// DEBUGGING PROFUNDO según recomendación del consultor
		error_log( 'EWM CORE DEBUG: get_modal_configuration called. Passed modal_id: ' . $modal_id . '. Global post ID: ' . ( get_the_ID() ?: 'none' ) );

		// Validación robusta del ID según consultor
		if ( ! is_numeric( $modal_id ) || $modal_id <= 0 ) {
			error_log( 'EWM CORE DEBUG: Invalid modal_id provided. Returning empty array.' );
			return array();
		}

		// Obtener TODOS los meta fields usando el ID explícito
		$config = array(
			'modal_id'       => $modal_id,
			'title'          => get_the_title( $modal_id ),
			'mode'           => get_post_meta( $modal_id, 'ewm_modal_mode', true ) ?: 'formulario',
			'steps'          => EWM_Meta_Fields::get_meta( $modal_id, 'ewm_steps_config', array() ),
			'design'         => EWM_Meta_Fields::get_meta( $modal_id, 'ewm_design_config', array() ),
			'triggers'       => EWM_Meta_Fields::get_meta( $modal_id, 'ewm_trigger_config', array() ),
			'wc_integration' => EWM_Meta_Fields::get_meta( $modal_id, 'ewm_wc_integration', array() ),
			'display_rules'  => EWM_Meta_Fields::get_meta( $modal_id, 'ewm_display_rules', array() ),
			'field_mapping'  => EWM_Meta_Fields::get_meta( $modal_id, 'ewm_field_mapping', array() ),
		);

		// Log de la configuración obtenida ANTES de devolverla (según consultor)
		error_log( 'EWM CORE DEBUG: Config loaded. Steps empty? ' . ( empty( $config['steps'] ) ? 'YES' : 'NO' ) );
		if ( ! empty( $config['steps'] ) ) {
			error_log( 'EWM CORE DEBUG: Steps content preview: ' . substr( json_encode( $config['steps'] ), 0, 200 ) );
		} else {
			error_log( 'EWM CORE DEBUG: Steps config is EMPTY - this is the problem!' );
		}

		// DEBUGGING: Log de configuración cargada
		error_log( 'EWM RENDER DEBUG - get_modal_configuration: ' . json_encode( $config['steps'] ) );
		if ( isset( $config['steps']['steps'] ) && is_array( $config['steps']['steps'] ) ) {
			foreach ( $config['steps']['steps'] as $index => $step ) {
				error_log( "EWM RENDER DEBUG - Step $index: title='" . ( $step['title'] ?? 'none' ) . "', content='" . ( $step['content'] ?? 'none' ) . "'" );
			}
		}

		// Aplicar valores por defecto
		$config = $this->apply_default_config( $config );

		return apply_filters( 'ewm_modal_configuration', $config, $modal_id );
	}

	/**
	 * Aplicar configuración por defecto
	 */
	private function apply_default_config( $config ) {
		// Configuración de diseño por defecto
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

		// Configuración de triggers por defecto
		$config['triggers'] = array_merge(
			array(
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
			),
			$config['triggers']
		);

		return $config;
	}

	/**
	 * Generar HTML del modal
	 */
	private function generate_modal_html( $modal_id, $config ) {
		ewm_log_info(
			'RENDER DEBUG - generate_modal_html started',
			array(
				'modal_id'    => $modal_id,
				'config_keys' => array_keys( $config ),
				'mode'        => $config['mode'] ?? 'none',
			)
		);

		$modal_class = $this->get_modal_css_classes( $config );
		$modal_data  = $this->get_modal_data_attributes( $modal_id, $config );

		ob_start();
		?>
		<div id="ewm-modal-<?php echo $modal_id; ?>" 
			class="<?php echo esc_attr( $modal_class ); ?>"
			<?php echo $modal_data; ?>
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
		ewm_log_info(
			'RENDER DEBUG - generate_modal_html completed',
			array(
				'modal_id'    => $modal_id,
				'html_length' => strlen( $html_output ),
				'html_empty'  => empty( $html_output ),
			)
		);

		return $html_output;
	}

	/**
	 * Generar contenido del modal según el modo
	 */
	private function generate_modal_content( $modal_id, $config ) {
		error_log( 'EWM RENDER DEBUG - generate_modal_content started: mode=' . ( $config['mode'] ?? 'none' ) );
		error_log( 'EWM RENDER DEBUG - steps data: ' . json_encode( $config['steps'] ?? array() ) );

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

		error_log( 'EWM RENDER DEBUG - generate_form_content: steps_count=' . count( $steps ) );
		error_log( 'EWM RENDER DEBUG - steps detail: ' . json_encode( $steps ) );

		if ( empty( $steps ) ) {
			error_log( 'EWM RENDER DEBUG - NO STEPS FOUND - returning empty content' );
			return '<div class="ewm-error">No hay pasos configurados para este modal.</div>';
		}

		ob_start();
		?>
		<div class="ewm-form-container" data-modal-id="<?php echo $modal_id; ?>">
			
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
						<div class="ewm-progress-step <?php echo $i === 1 ? 'active' : ''; ?>"
							data-step="<?php echo $i; ?>">
							<span class="ewm-step-number"><?php echo $i; ?></span>
						</div>
					<?php endfor; ?>
				</div>
			</div>
			<?php endif; ?>
			
			<form class="ewm-multi-step-form" method="post">
				
				<?php foreach ( $steps as $index => $step ) : ?>
				<div class="ewm-form-step <?php echo $index === 0 ? 'active' : ''; ?>" 
					data-step="<?php echo $step['id']; ?>">
					 
					<?php if ( ! empty( $step['title'] ) ) : ?>
						<h3 class="ewm-step-title"><?php echo esc_html( $step['title'] ); ?></h3>
					<?php endif; ?>
					
					<?php if ( ! empty( $step['subtitle'] ) ) : ?>
						<p class="ewm-step-subtitle"><?php echo esc_html( $step['subtitle'] ); ?></p>
					<?php endif; ?>

					<?php if ( ! empty( $step['content'] ) ) : ?>
						<div class="ewm-step-content"><?php echo wp_kses_post( $step['content'] ); ?></div>
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
								<?php _e( 'Previous', 'ewm-modal-cta' ); ?>
							</button>
						<?php endif; ?>

						<?php
						// Determinar si este es el último paso
						$has_final_step = ! empty( $final_step['title'] ) || ! empty( $final_step['fields'] );
						$is_last_step   = ( $index === count( $steps ) - 1 ) && ! $has_final_step;
						?>

						<?php if ( $is_last_step ) : ?>
							<button type="submit" class="ewm-btn ewm-btn-primary ewm-btn-submit">
								<?php _e( 'Submit', 'ewm-modal-cta' ); ?>
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
							<?php _e( 'Previous', 'ewm-modal-cta' ); ?>
						</button>

						<button type="submit" class="ewm-btn ewm-btn-primary ewm-btn-submit">
							<?php _e( 'Submit', 'ewm-modal-cta' ); ?>
						</button>
					</div>
					
				</div>
				<?php endif; ?>
				
				<!-- Mensaje de éxito -->
				<div class="ewm-form-step ewm-success-step" data-step="success" style="display: none;">
					<div class="ewm-success-content">
						<h3><?php _e( 'Thank You!', 'ewm-modal-cta' ); ?></h3>
						<p><?php _e( 'Your information has been submitted successfully.', 'ewm-modal-cta' ); ?></p>
					</div>
				</div>
				
				<?php wp_nonce_field( 'ewm_form_submit', 'ewm_nonce' ); ?>
				<input type="hidden" name="action" value="ewm_submit_form">
				<input type="hidden" name="modal_id" value="<?php echo $modal_id; ?>">
				
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
			$field_id          = esc_attr( $field['id'] ?? '' );
			$field_type        = esc_attr( $field['type'] ?? 'text' );
			$field_label       = esc_html( $field['label'] ?? '' );
			$field_placeholder = esc_attr( $field['placeholder'] ?? '' );
			$field_required    = ! empty( $field['required'] );
			$field_class       = 'ewm-field ewm-field-' . $field_type;

			if ( $field_required ) {
				$field_class .= ' ewm-field-required';
			}

			?>
			<div class="<?php echo $field_class; ?>">
				
				<?php if ( $field_label ) : ?>
					<label for="<?php echo $field_id; ?>" class="ewm-field-label">
						<?php echo $field_label; ?>
						<?php if ( $field_required ) : ?>
							<span class="ewm-required">*</span>
						<?php endif; ?>
					</label>
				<?php endif; ?>
				
				<?php echo $this->generate_field_input( $field ); ?>
				
				<div class="ewm-field-error" style="display: none;"></div>
				
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

		// Agregar atributos de validación
		if ( ! empty( $validation_rules ) ) {
			if ( isset( $validation_rules['min_length'] ) ) {
				$attributes['minlength'] = $validation_rules['min_length'];
			}
			if ( isset( $validation_rules['max_length'] ) ) {
				$attributes['maxlength'] = $validation_rules['max_length'];
			}
			if ( isset( $validation_rules['pattern'] ) ) {
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

				// Determinar si es un campo con múltiples opciones
				$is_multi_option = ! empty( $options );

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
		// Placeholder para contenido de anuncio
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
		$data_attrs = array(
			'data-modal-id' => $modal_id,
			'data-trigger'  => $config['trigger'] ?? 'manual',
			'data-config'   => esc_attr(
				wp_json_encode(
					array(
						'triggers'       => $config['triggers'],
						'design'         => $config['design'],
						'wc_integration' => $config['wc_integration'],
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
			'ewm-modal-styles',
			EWM_PLUGIN_URL . 'assets/css/modal-frontend.css',
			array(),
			EWM_VERSION
		);

		wp_enqueue_script(
			'ewm-modal-scripts',
			EWM_PLUGIN_URL . 'assets/js/modal-frontend.js',
			array(),
			EWM_VERSION,
			true
		);

		wp_localize_script(
			'ewm-modal-scripts',
			'ewmModal',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'restUrl' => rest_url( 'ewm/v1/' ),
				'nonce'   => wp_create_nonce( 'ewm_frontend_nonce' ),
				'debug'   => defined( 'WP_DEBUG' ) && WP_DEBUG,
				'strings' => array(
					'loading'                => __( 'Cargando...', 'ewm-modal-cta' ),
					'error'                  => __( 'Ha ocurrido un error. Por favor, inténtalo de nuevo.', 'ewm-modal-cta' ),
					'required_field'         => __( 'Este campo es obligatorio.', 'ewm-modal-cta' ),
					'invalid_email'          => __( 'Por favor, introduce un email válido.', 'ewm-modal-cta' ),
					'invalid_url'            => __( 'Por favor, introduce una URL válida.', 'ewm-modal-cta' ),
					'invalid_time'           => __( 'Por favor, introduce una hora válida.', 'ewm-modal-cta' ),
					'invalid_datetime_local' => __( 'Por favor, introduce una fecha y hora válidas.', 'ewm-modal-cta' ),
					'invalid_color'          => __( 'Por favor, introduce un color válido (ej. #RRGGBB).', 'ewm-modal-cta' ),
					'invalid_range'          => __( 'El valor está fuera del rango permitido.', 'ewm-modal-cta' ),
					'invalid_month'          => __( 'Por favor, introduce un mes válido.', 'ewm-modal-cta' ),
					'invalid_week'           => __( 'Por favor, introduce una semana válida.', 'ewm-modal-cta' ),
					'min_length'             => __( 'Por favor, introduce al menos 2 caracteres.', 'ewm-modal-cta' ),
				),
			)
		);

		$this->assets_enqueued = true;

		ewm_log_debug( 'Modal assets enqueued' );
	}

	/**
	 * Renderizar scripts del modal en el footer
	 */
	public function render_modal_scripts() {
		if ( empty( $this->rendered_modals ) ) {
			return;
		}

		?>
		<script type="text/javascript">
		document.addEventListener('DOMContentLoaded', function() {
			if (typeof EWMModal !== 'undefined') {
				<?php foreach ( $this->rendered_modals as $modal_id ) : ?>
				EWMModal.init(<?php echo $modal_id; ?>);
				<?php endforeach; ?>
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
	 * Obtener modales renderizados
	 */
	public function get_rendered_modals() {
		return $this->rendered_modals;
	}
}

/**
 * Función global para renderizado universal
 */
function ewm_render_modal_core( $modal_id, $config = array() ) {
	return EWM_Render_Core::get_instance()->render_modal( $modal_id, $config );
}
