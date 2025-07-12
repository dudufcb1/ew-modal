/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import {
	useBlockProps,
	InspectorControls,
	ColorPalette,
	PanelColorSettings
} from '@wordpress/block-editor';

/**
 * WordPress components
 */
import {
	PanelBody,
	PanelRow,
	SelectControl,
	ToggleControl,
	RangeControl,
	TextControl,
	TextareaControl,
	Button,
	ButtonGroup,
	Card,
	CardBody,
	CardHeader,
	Notice,
	Spinner,
	__experimentalNumberControl as NumberControl
} from '@wordpress/components';

/**
 * WordPress data
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { useState, useEffect } from '@wordpress/element';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes, clientId }) {
	const {
		modalId,
		autoGenerateShortcode,
		modalMode,
		triggerType,
		triggerDelay,
		modalSize,
		animation,
		primaryColor,
		secondaryColor,
		backgroundColor,
		showProgressBar,
		progressBarStyle,
		enableWooCommerce,
		selectedCoupon,
		enableExitIntent,
		exitIntentSensitivity,
		enableTimeDelay,
		timeDelay,
		enableScrollTrigger,
		scrollPercentage,
		customCSS,
		displayRules
	} = attributes;

	const [isLoading, setIsLoading] = useState(false);
	const [modalData, setModalData] = useState(null);
	const [error, setError] = useState(null);
	const [coupons, setCoupons] = useState([]);

	// Obtener datos del modal si existe modalId
	useEffect(() => {
		if (modalId) {
			fetchModalData();
		}
	}, [modalId]);

	// Obtener cupones de WooCommerce si está habilitado
	useEffect(() => {
		if (enableWooCommerce) {
			fetchWooCommerceCoupons();
		}
	}, [enableWooCommerce]);

	const fetchModalData = async () => {
		setIsLoading(true);
		try {
			const response = await fetch(`/wp-json/ewm/v1/modals/${modalId}`);
			if (response.ok) {
				const data = await response.json();
				setModalData(data);
			} else {
				setError(__('Error al cargar los datos del modal', 'ewm-modal-cta'));
			}
		} catch (err) {
			setError(__('Error de conexión', 'ewm-modal-cta'));
		} finally {
			setIsLoading(false);
		}
	};

	const fetchWooCommerceCoupons = async () => {
		try {
			const response = await fetch('/wp-json/ewm/v1/wc-coupons');
			if (response.ok) {
				const data = await response.json();
				setCoupons(data.map(coupon => ({
					label: coupon.code,
					value: coupon.id
				})));
			}
		} catch (err) {
			console.error('Error fetching coupons:', err);
		}
	};

	const createNewModal = async () => {
		setIsLoading(true);
		setError(null);

		try {
			const response = await fetch('/wp-json/ewm/v1/modals', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': window.wpApiSettings?.nonce || ''
				},
				body: JSON.stringify({
					title: `Modal ${clientId.slice(-8)}`,
					config: {
						mode: modalMode,
						design: {
							colors: {
								primary: primaryColor,
								secondary: secondaryColor,
								background: backgroundColor
							},
							modal_size: modalSize,
							animation: animation
						},
						triggers: {
							exit_intent: { enabled: enableExitIntent, sensitivity: exitIntentSensitivity },
							time_delay: { enabled: enableTimeDelay, delay: timeDelay },
							scroll_percentage: { enabled: enableScrollTrigger, percentage: scrollPercentage }
						}
					}
				})
			});

			if (response.ok) {
				const data = await response.json();
				setAttributes({ modalId: data.id.toString() });
				setModalData(data);
			} else {
				setError(__('Error al crear el modal', 'ewm-modal-cta'));
			}
		} catch (err) {
			setError(__('Error de conexión', 'ewm-modal-cta'));
		} finally {
			setIsLoading(false);
		}
	};

	const blockProps = useBlockProps({
		className: `ewm-modal-block ewm-modal-${modalMode} ewm-size-${modalSize}`
	});

	return (
		<>
			<InspectorControls>
				{/* Panel de Configuración General */}
				<PanelBody title={__('Configuración General', 'ewm-modal-cta')} initialOpen={true}>
					<PanelRow>
						<SelectControl
							label={__('Modo del Modal', 'ewm-modal-cta')}
							value={modalMode}
							options={[
								{ label: __('Formulario Multi-Paso', 'ewm-modal-cta'), value: 'formulario' },
								{ label: __('Anuncio/Notificación', 'ewm-modal-cta'), value: 'anuncio' }
							]}
							onChange={(value) => setAttributes({ modalMode: value })}
						/>
					</PanelRow>

					<PanelRow>
						<SelectControl
							label={__('Tamaño del Modal', 'ewm-modal-cta')}
							value={modalSize}
							options={[
								{ label: __('Pequeño', 'ewm-modal-cta'), value: 'small' },
								{ label: __('Mediano', 'ewm-modal-cta'), value: 'medium' },
								{ label: __('Grande', 'ewm-modal-cta'), value: 'large' }
							]}
							onChange={(value) => setAttributes({ modalSize: value })}
						/>
					</PanelRow>

					<PanelRow>
						<SelectControl
							label={__('Animación', 'ewm-modal-cta')}
							value={animation}
							options={[
								{ label: __('Fade', 'ewm-modal-cta'), value: 'fade' },
								{ label: __('Slide', 'ewm-modal-cta'), value: 'slide' },
								{ label: __('Zoom', 'ewm-modal-cta'), value: 'zoom' }
							]}
							onChange={(value) => setAttributes({ animation: value })}
						/>
					</PanelRow>

					<PanelRow>
						<ToggleControl
							label={__('Auto-generar Shortcode', 'ewm-modal-cta')}
							checked={autoGenerateShortcode}
							onChange={(value) => setAttributes({ autoGenerateShortcode: value })}
							help={__('Genera automáticamente un shortcode al guardar', 'ewm-modal-cta')}
						/>
					</PanelRow>
				</PanelBody>

				{/* Panel de Colores */}
				<PanelColorSettings
					title={__('Colores', 'ewm-modal-cta')}
					initialOpen={false}
					colorSettings={[
						{
							value: primaryColor,
							onChange: (value) => setAttributes({ primaryColor: value }),
							label: __('Color Primario', 'ewm-modal-cta')
						},
						{
							value: secondaryColor,
							onChange: (value) => setAttributes({ secondaryColor: value }),
							label: __('Color Secundario', 'ewm-modal-cta')
						},
						{
							value: backgroundColor,
							onChange: (value) => setAttributes({ backgroundColor: value }),
							label: __('Color de Fondo', 'ewm-modal-cta')
						}
					]}
				/>

				{/* Panel de Triggers */}
				<PanelBody title={__('Triggers y Eventos', 'ewm-modal-cta')} initialOpen={false}>
					<PanelRow>
						<SelectControl
							label={__('Tipo de Trigger', 'ewm-modal-cta')}
							value={triggerType}
							options={[
								{ label: __('Manual', 'ewm-modal-cta'), value: 'manual' },
								{ label: __('Automático', 'ewm-modal-cta'), value: 'auto' },
								{ label: __('Exit Intent', 'ewm-modal-cta'), value: 'exit-intent' },
								{ label: __('Tiempo', 'ewm-modal-cta'), value: 'time-delay' },
								{ label: __('Scroll', 'ewm-modal-cta'), value: 'scroll' }
							]}
							onChange={(value) => setAttributes({ triggerType: value })}
						/>
					</PanelRow>

					{triggerType === 'time-delay' && (
						<PanelRow>
							<NumberControl
								label={__('Retraso (ms)', 'ewm-modal-cta')}
								value={triggerDelay}
								onChange={(value) => setAttributes({ triggerDelay: parseInt(value) || 5000 })}
								min={1000}
								max={60000}
								step={1000}
							/>
						</PanelRow>
					)}

					<PanelRow>
						<ToggleControl
							label={__('Exit Intent', 'ewm-modal-cta')}
							checked={enableExitIntent}
							onChange={(value) => setAttributes({ enableExitIntent: value })}
						/>
					</PanelRow>

					{enableExitIntent && (
						<PanelRow>
							<RangeControl
								label={__('Sensibilidad Exit Intent', 'ewm-modal-cta')}
								value={exitIntentSensitivity}
								onChange={(value) => setAttributes({ exitIntentSensitivity: value })}
								min={10}
								max={100}
								step={10}
							/>
						</PanelRow>
					)}

					<PanelRow>
						<ToggleControl
							label={__('Trigger por Tiempo', 'ewm-modal-cta')}
							checked={enableTimeDelay}
							onChange={(value) => setAttributes({ enableTimeDelay: value })}
						/>
					</PanelRow>

					{enableTimeDelay && (
						<PanelRow>
							<NumberControl
								label={__('Tiempo de Espera (ms)', 'ewm-modal-cta')}
								value={timeDelay}
								onChange={(value) => setAttributes({ timeDelay: parseInt(value) || 5000 })}
								min={1000}
								max={60000}
								step={1000}
							/>
						</PanelRow>
					)}

					<PanelRow>
						<ToggleControl
							label={__('Trigger por Scroll', 'ewm-modal-cta')}
							checked={enableScrollTrigger}
							onChange={(value) => setAttributes({ enableScrollTrigger: value })}
						/>
					</PanelRow>

					{enableScrollTrigger && (
						<PanelRow>
							<RangeControl
								label={__('Porcentaje de Scroll (%)', 'ewm-modal-cta')}
								value={scrollPercentage}
								onChange={(value) => setAttributes({ scrollPercentage: value })}
								min={10}
								max={100}
								step={10}
							/>
						</PanelRow>
					)}
				</PanelBody>

				{/* Panel de Formulario */}
				{modalMode === 'formulario' && (
					<PanelBody title={__('Configuración de Formulario', 'ewm-modal-cta')} initialOpen={false}>
						<PanelRow>
							<ToggleControl
								label={__('Mostrar Barra de Progreso', 'ewm-modal-cta')}
								checked={showProgressBar}
								onChange={(value) => setAttributes({ showProgressBar: value })}
							/>
						</PanelRow>

						{showProgressBar && (
							<PanelRow>
								<SelectControl
									label={__('Estilo de Barra de Progreso', 'ewm-modal-cta')}
									value={progressBarStyle}
									options={[
										{ label: __('Línea', 'ewm-modal-cta'), value: 'line' },
										{ label: __('Puntos', 'ewm-modal-cta'), value: 'dots' }
									]}
									onChange={(value) => setAttributes({ progressBarStyle: value })}
								/>
							</PanelRow>
						)}
					</PanelBody>
				)}

				{/* Panel de WooCommerce */}
				<PanelBody title={__('Integración WooCommerce', 'ewm-modal-cta')} initialOpen={false}>
					<PanelRow>
						<ToggleControl
							label={__('Habilitar WooCommerce', 'ewm-modal-cta')}
							checked={enableWooCommerce}
							onChange={(value) => setAttributes({ enableWooCommerce: value })}
						/>
					</PanelRow>

					{enableWooCommerce && coupons.length > 0 && (
						<PanelRow>
							<SelectControl
								label={__('Cupón de Descuento', 'ewm-modal-cta')}
								value={selectedCoupon}
								options={[
									{ label: __('Seleccionar cupón...', 'ewm-modal-cta'), value: 0 },
									...coupons
								]}
								onChange={(value) => setAttributes({ selectedCoupon: parseInt(value) })}
							/>
						</PanelRow>
					)}

					{enableWooCommerce && coupons.length === 0 && (
						<Notice status="warning" isDismissible={false}>
							{__('No se encontraron cupones de WooCommerce.', 'ewm-modal-cta')}
						</Notice>
					)}
				</PanelBody>

				{/* Panel de CSS Personalizado */}
				<PanelBody title={__('CSS Personalizado', 'ewm-modal-cta')} initialOpen={false}>
					<PanelRow>
						<TextareaControl
							label={__('CSS Personalizado', 'ewm-modal-cta')}
							value={customCSS}
							onChange={(value) => setAttributes({ customCSS: value })}
							rows={10}
							help={__('Agrega CSS personalizado para el modal', 'ewm-modal-cta')}
						/>
					</PanelRow>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<Card>
					<CardHeader>
						<h3>{__('Modal CTA Multi-Paso', 'ewm-modal-cta')}</h3>
					</CardHeader>
					<CardBody>
						{error && (
							<Notice status="error" isDismissible={false}>
								{error}
							</Notice>
						)}

						{!modalId ? (
							<div className="ewm-block-setup">
								<p>{__('Configura tu modal interactivo de captura de leads', 'ewm-modal-cta')}</p>

								<div className="ewm-setup-options">
									<h4>{__('Configuración Rápida', 'ewm-modal-cta')}</h4>

									<div className="ewm-quick-setup">
										<SelectControl
											label={__('Tipo de Modal', 'ewm-modal-cta')}
											value={modalMode}
											options={[
												{ label: __('Formulario Multi-Paso', 'ewm-modal-cta'), value: 'formulario' },
												{ label: __('Anuncio/Notificación', 'ewm-modal-cta'), value: 'anuncio' }
											]}
											onChange={(value) => setAttributes({ modalMode: value })}
										/>

										<SelectControl
											label={__('Tamaño', 'ewm-modal-cta')}
											value={modalSize}
											options={[
												{ label: __('Pequeño', 'ewm-modal-cta'), value: 'small' },
												{ label: __('Mediano', 'ewm-modal-cta'), value: 'medium' },
												{ label: __('Grande', 'ewm-modal-cta'), value: 'large' }
											]}
											onChange={(value) => setAttributes({ modalSize: value })}
										/>

										<div className="ewm-color-preview">
											<div
												className="ewm-color-swatch"
												style={{ backgroundColor: primaryColor }}
												title={__('Color Primario', 'ewm-modal-cta')}
											></div>
											<div
												className="ewm-color-swatch"
												style={{ backgroundColor: secondaryColor }}
												title={__('Color Secundario', 'ewm-modal-cta')}
											></div>
											<div
												className="ewm-color-swatch"
												style={{ backgroundColor: backgroundColor }}
												title={__('Color de Fondo', 'ewm-modal-cta')}
											></div>
										</div>
									</div>

									<Button
										isPrimary
										onClick={createNewModal}
										disabled={isLoading}
									>
										{isLoading ? (
											<>
												<Spinner />
												{__('Creando Modal...', 'ewm-modal-cta')}
											</>
										) : (
											__('Crear Modal', 'ewm-modal-cta')
										)}
									</Button>
								</div>
							</div>
						) : (
							<div className="ewm-block-configured">
								{isLoading ? (
									<div className="ewm-loading">
										<Spinner />
										<p>{__('Cargando configuración del modal...', 'ewm-modal-cta')}</p>
									</div>
								) : (
									<>
										<div className="ewm-modal-info">
											<h4>{__('Modal Configurado', 'ewm-modal-cta')}</h4>
											<p>
												<strong>{__('ID:', 'ewm-modal-cta')}</strong> {modalId}<br />
												<strong>{__('Modo:', 'ewm-modal-cta')}</strong> {modalMode === 'formulario' ? __('Formulario Multi-Paso', 'ewm-modal-cta') : __('Anuncio', 'ewm-modal-cta')}<br />
												<strong>{__('Tamaño:', 'ewm-modal-cta')}</strong> {modalSize}<br />
												<strong>{__('Trigger:', 'ewm-modal-cta')}</strong> {triggerType}
											</p>
										</div>

										<div className="ewm-modal-preview">
											<div
												className={`ewm-preview-modal ewm-size-${modalSize} ewm-animation-${animation}`}
												style={{
													'--ewm-primary-color': primaryColor,
													'--ewm-secondary-color': secondaryColor,
													'--ewm-background-color': backgroundColor
												}}
											>
												<div className="ewm-preview-header">
													<span className="ewm-preview-close">×</span>
												</div>
												<div className="ewm-preview-content">
													{modalMode === 'formulario' ? (
														<>
															<h3>{__('Vista Previa del Formulario', 'ewm-modal-cta')}</h3>
															{showProgressBar && (
																<div className={`ewm-preview-progress ewm-progress-${progressBarStyle}`}>
																	<div className="ewm-progress-fill" style={{ width: '33%' }}></div>
																</div>
															)}
															<div className="ewm-preview-form">
																<div className="ewm-preview-field">
																	<label>{__('Campo de ejemplo', 'ewm-modal-cta')}</label>
																	<input type="text" placeholder={__('Introduce tu respuesta...', 'ewm-modal-cta')} />
																</div>
																<button className="ewm-preview-button" style={{ backgroundColor: primaryColor }}>
																	{__('Siguiente', 'ewm-modal-cta')}
																</button>
															</div>
														</>
													) : (
														<>
															<h3>{__('Vista Previa del Anuncio', 'ewm-modal-cta')}</h3>
															<p>{__('Contenido del anuncio aparecerá aquí', 'ewm-modal-cta')}</p>
															<button className="ewm-preview-button" style={{ backgroundColor: primaryColor }}>
																{__('Acción', 'ewm-modal-cta')}
															</button>
														</>
													)}
												</div>
											</div>
										</div>

										{autoGenerateShortcode && (
											<div className="ewm-shortcode-info">
												<h4>{__('Shortcode Generado', 'ewm-modal-cta')}</h4>
												<code>[ew_modal id="{modalId}"]</code>
												<p className="description">
													{__('Este shortcode se generará automáticamente al guardar el post.', 'ewm-modal-cta')}
												</p>
											</div>
										)}

										<div className="ewm-block-actions">
											<ButtonGroup>
												<Button
													isSecondary
													onClick={() => {
														if (window.confirm(__('¿Estás seguro de que quieres desconectar este modal?', 'ewm-modal-cta'))) {
															setAttributes({ modalId: '' });
															setModalData(null);
														}
													}}
												>
													{__('Desconectar Modal', 'ewm-modal-cta')}
												</Button>

												<Button
													isPrimary
													href={`/wp-admin/post.php?post=${modalId}&action=edit`}
													target="_blank"
												>
													{__('Editar Modal', 'ewm-modal-cta')}
												</Button>
											</ButtonGroup>
										</div>
									</>
								)}
							</div>
						)}
					</CardBody>
				</Card>
			</div>
		</>
	);
}
