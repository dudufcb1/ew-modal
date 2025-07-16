<?php
// This file is generated. Do not modify it manually.
return array(
	'ewm-modal-cta' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'ewm/modal-cta',
		'version' => '1.0.0',
		'title' => 'Modal CTA Multi-Paso',
		'category' => 'widgets',
		'icon' => 'admin-page',
		'description' => 'Crea modales interactivos de captura de leads con formularios multi-paso. Sistema unificado con auto-generación de shortcodes.',
		'keywords' => array(
			'modal',
			'formulario',
			'lead',
			'conversion',
			'multi-paso'
		),
		'attributes' => array(
			'modalId' => array(
				'type' => 'string',
				'default' => ''
			),
			'autoGenerateShortcode' => array(
				'type' => 'boolean',
				'default' => true
			),
			'modalMode' => array(
				'type' => 'string',
				'default' => 'formulario',
				'enum' => array(
					'formulario',
					'anuncio'
				)
			),
			'triggerType' => array(
				'type' => 'string',
				'default' => 'manual',
				'enum' => array(
					'auto',
					'manual',
					'exit-intent',
					'time-delay',
					'scroll'
				)
			),
			'triggerDelay' => array(
				'type' => 'number',
				'default' => 5000
			),
			'modalSize' => array(
				'type' => 'string',
				'default' => 'medium',
				'enum' => array(
					'small',
					'medium',
					'large'
				)
			),
			'animation' => array(
				'type' => 'string',
				'default' => 'fade',
				'enum' => array(
					'fade',
					'slide',
					'zoom'
				)
			),
			'primaryColor' => array(
				'type' => 'string',
				'default' => '#ff6b35'
			),
			'secondaryColor' => array(
				'type' => 'string',
				'default' => '#333333'
			),
			'backgroundColor' => array(
				'type' => 'string',
				'default' => '#ffffff'
			),
			'showProgressBar' => array(
				'type' => 'boolean',
				'default' => true
			),
			'progressBarStyle' => array(
				'type' => 'string',
				'default' => 'line',
				'enum' => array(
					'line',
					'dots'
				)
			),
			'enableWooCommerce' => array(
				'type' => 'boolean',
				'default' => false
			),
			'selectedCoupon' => array(
				'type' => 'number',
				'default' => 0
			),
			'enableExitIntent' => array(
				'type' => 'boolean',
				'default' => false
			),
			'exitIntentSensitivity' => array(
				'type' => 'number',
				'default' => 20
			),
			'enableTimeDelay' => array(
				'type' => 'boolean',
				'default' => false
			),
			'timeDelay' => array(
				'type' => 'number',
				'default' => 5000
			),
			'enableScrollTrigger' => array(
				'type' => 'boolean',
				'default' => false
			),
			'scrollPercentage' => array(
				'type' => 'number',
				'default' => 50
			),
			'customCSS' => array(
				'type' => 'string',
				'default' => ''
			),
			'displayRules' => array(
				'type' => 'object',
				'default' => array(
					'pages' => array(
						'include' => array(
							
						),
						'exclude' => array(
							
						)
					),
					'userRoles' => array(
						
					),
					'devices' => array(
						'desktop' => true,
						'tablet' => true,
						'mobile' => true
					),
					'frequency' => array(
						'type' => 'session',
						'limit' => 1
					)
				)
			),
			'modalConfigData' => array(
				'type' => 'object',
				'default' => array(
					'steps' => array(
						
					),
					'final_step' => array(
						'title' => '',
						'content' => ''
					)
				)
			)
		),
		'usesContext' => array(
			'postId',
			'postType'
		),
		'providesContext' => array(
			'ewm/modalId' => 'modalId',
			'ewm/modalMode' => 'modalMode'
		),
		'example' => array(
			'attributes' => array(
				'modalMode' => 'formulario',
				'triggerType' => 'manual',
				'modalSize' => 'medium',
				'primaryColor' => '#ff6b35',
				'showProgressBar' => true
			)
		),
		'supports' => array(
			'html' => false,
			'anchor' => true,
			'className' => true,
			'customClassName' => true,
			'spacing' => array(
				'margin' => true,
				'padding' => true
			),
			'color' => array(
				'background' => true,
				'text' => true,
				'gradients' => true
			),
			'typography' => array(
				'fontSize' => true,
				'lineHeight' => true
			)
		),
		'textdomain' => 'ewm-modal-cta',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	)
);
