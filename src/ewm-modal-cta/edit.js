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
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal components
 */
import ModalManager from './components/ModalManager';
import StepConfiguration from './components/StepConfiguration';

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
	// Estado para controlar qué paso se muestra en el preview
	const [currentPreviewStep, setCurrentPreviewStep] = useState(0);
	// Estado para feedback visual de cambios
	const [recentChanges, setRecentChanges] = useState(new Set());
	// Inicializar steps desde atributos si existen
	const [steps, setSteps] = useState(() => {
		if (attributes.modalConfigData?.steps?.steps) {
			console.log('🎯 Inicializando steps desde atributos:', attributes.modalConfigData.steps.steps);
			return attributes.modalConfigData.steps.steps;
		} else if (attributes.modalConfigData?.steps) {
			console.log('🎯 Inicializando steps desde atributos (estructura directa):', attributes.modalConfigData.steps);
			return attributes.modalConfigData.steps;
		}
		console.log('🎯 Inicializando steps como array vacío');
		return [];
	});
	const [finalStep, setFinalStep] = useState(() => {
		if (attributes.modalConfigData?.steps?.final_step) {
			return attributes.modalConfigData.steps.final_step;
		} else if (attributes.modalConfigData?.final_step) {
			return attributes.modalConfigData.final_step;
		}
		return {};
	});

	/**
	 * Marcar un campo como recientemente cambiado para feedback visual
	 */
	const markFieldChanged = (fieldName) => {
		setRecentChanges(prev => new Set([...prev, fieldName]));
		// Limpiar el marcador después de 2 segundos
		setTimeout(() => {
			setRecentChanges(prev => {
				const newSet = new Set(prev);
				newSet.delete(fieldName);
				return newSet;
			});
		}, 2000);
	};

	/**
	 * Wrapper para setAttributes que incluye feedback visual
	 */
	const setAttributesWithFeedback = (newAttributes) => {
		// Marcar los campos que cambiaron
		Object.keys(newAttributes).forEach(key => {
			if (attributes[key] !== newAttributes[key]) {
				markFieldChanged(key);
			}
		});
		setAttributes(newAttributes);
	};

	// Obtener datos del modal si existe modalId
	useEffect(() => {
		if (modalId) {
			fetchModalData();
		}
	}, [modalId]);

	// Inicializar con datos existentes de los atributos al cargar el componente
	useEffect(() => {
		console.log('🚀 Inicializando componente con atributos:', attributes);
		if (attributes.modalConfigData) {
			console.log('📦 Procesando modalConfigData existente:', attributes.modalConfigData);
			console.log('📦 Estructura de steps en modalConfigData:', attributes.modalConfigData.steps);
			handleModalDataLoaded(attributes);
		} else {
			console.log('❌ No hay modalConfigData en attributes');
		}
	}, []); // Solo ejecutar una vez al montar el componente

	// Obtener cupones de WooCommerce si está habilitado
	useEffect(() => {
		if (enableWooCommerce) {
			fetchWooCommerceCoupons();
		}
	}, [enableWooCommerce]);

	const fetchModalData = async () => {
		setIsLoading(true);
		try {
			const data = await apiFetch({
				path: `/ewm/v1/modals/${modalId}`,
				method: 'GET'
			});
			setModalData(data);
			// Procesar los datos cargados para actualizar el preview
			handleModalDataLoaded(data);
		} catch (err) {
			console.error('Error fetching modal data:', err);
			setError(__('Error al cargar los datos del modal', 'ewm-modal-cta'));
		} finally {
			setIsLoading(false);
		}
	};

	const fetchWooCommerceCoupons = async () => {
		try {
			const data = await apiFetch({
				path: '/ewm/v1/wc-coupons',
				method: 'GET'
			});
			setCoupons(data.map(coupon => ({
				label: coupon.code,
				value: coupon.id
			})));
		} catch (err) {
			console.error('Error fetching coupons:', err);
		}
	};

	// Función para manejar cuando se cargan datos del modal
	const handleModalDataLoaded = (modalData) => {
		console.log('🔍 handleModalDataLoaded - modalData recibido:', modalData);
		setModalData(modalData);

		// Extraer pasos de la estructura de datos (puede estar en diferentes ubicaciones)
		let stepsData = [];
		let finalStepData = {};

		// Verificar diferentes estructuras de datos
		if (modalData.config?.steps?.steps) {
			// Estructura nueva: modalData.config.steps.steps
			console.log('📁 Usando estructura: modalData.config.steps.steps');
			stepsData = modalData.config.steps.steps;
			finalStepData = modalData.config.steps.final_step || {};
		} else if (modalData.modalConfigData?.steps?.steps) {
			// Estructura de atributos anidada: modalData.modalConfigData.steps.steps
			console.log('📁 Usando estructura: modalData.modalConfigData.steps.steps');
			stepsData = modalData.modalConfigData.steps.steps;
			finalStepData = modalData.modalConfigData.steps.final_step || {};
		} else if (modalData.steps?.steps) {
			// Estructura: modalData.steps.steps
			console.log('📁 Usando estructura: modalData.steps.steps');
			stepsData = modalData.steps.steps;
			finalStepData = modalData.steps.final_step || {};
		} else if (modalData.steps) {
			// Estructura directa: modalData.steps
			console.log('📁 Usando estructura: modalData.steps (directa)');
			stepsData = modalData.steps;
			finalStepData = modalData.final_step || {};
		} else if (modalData.modalConfigData?.steps) {
			// Estructura de atributos: modalData.modalConfigData.steps
			console.log('📁 Usando estructura: modalData.modalConfigData.steps');
			stepsData = modalData.modalConfigData.steps;
			finalStepData = modalData.modalConfigData.final_step || {};
		}

		console.log('📊 stepsData extraído:', stepsData);
		console.log('📊 finalStepData extraído:', finalStepData);

		// Actualizar estados locales
		if (stepsData && stepsData.length > 0) {
			console.log('✅ Actualizando steps con:', stepsData);
			setSteps(stepsData);
		} else {
			console.log('❌ No se encontraron pasos válidos');
		}

		if (finalStepData && Object.keys(finalStepData).length > 0) {
			setFinalStep(finalStepData);
		}

		// Actualizar atributos del bloque con la configuración completa
		setAttributes({
			modalConfigData: {
				steps: stepsData,
				final_step: finalStepData
			}
		});
	};

	// Función para actualizar pasos en el modal
	const handleStepsChange = (newSteps) => {
		setSteps(newSteps);
		// Actualizar atributos del bloque para activar auto-save
		setAttributes({
			modalConfigData: {
				...attributes.modalConfigData,
				steps: newSteps
			}
		});
	};

	// Función para actualizar paso final
	const handleFinalStepChange = (newFinalStep) => {
		setFinalStep(newFinalStep);
		// Actualizar atributos del bloque para activar auto-save
		setAttributes({
			modalConfigData: {
				...attributes.modalConfigData,
				final_step: newFinalStep
			}
		});
	};

	const createNewModal = async () => {
		setIsLoading(true);
		setError(null);

		try {
			const data = await apiFetch({
				path: '/ewm/v1/modals',
				method: 'POST',
				data: {
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
				}
			});

			setAttributes({ modalId: data.id.toString() });
			setModalData(data);
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
				{/* Gestión de Modal */}
				<ModalManager
					modalId={modalId}
					onModalIdChange={(newModalId) => setAttributes({ modalId: newModalId })}
					attributes={attributes}
					setAttributes={setAttributes}
					onModalDataLoaded={handleModalDataLoaded}
				/>

				{/* Configuración de Pasos - Solo si hay modalId */}
				{modalId && (
					<StepConfiguration
						steps={steps}
						onStepsChange={handleStepsChange}
						finalStep={finalStep}
						onFinalStepChange={handleFinalStepChange}
					/>
				)}

				{/* Panel de Configuración General */}
				<PanelBody title={__('Configuración General', 'ewm-modal-cta')} initialOpen={!modalId}>
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

				{/* Panel de Colores con feedback visual */}
				<PanelColorSettings
					title={
						<span style={{
							display: 'flex',
							alignItems: 'center',
							gap: '6px',
							transition: 'all 0.3s ease'
						}}>
							{__('Colores', 'ewm-modal-cta')}
							{(recentChanges.has('primaryColor') || recentChanges.has('secondaryColor') || recentChanges.has('backgroundColor')) && (
								<span style={{
									fontSize: '12px',
									color: '#00a32a',
									animation: 'ewm-pulse 1s ease-in-out'
								}}>
									✨ Actualizado
								</span>
							)}
						</span>
					}
					initialOpen={false}
					colorSettings={[
						{
							value: primaryColor,
							onChange: (value) => setAttributesWithFeedback({ primaryColor: value }),
							label: __('Color Primario', 'ewm-modal-cta')
						},
						{
							value: secondaryColor,
							onChange: (value) => setAttributesWithFeedback({ secondaryColor: value }),
							label: __('Color Secundario', 'ewm-modal-cta')
						},
						{
							value: backgroundColor,
							onChange: (value) => setAttributesWithFeedback({ backgroundColor: value }),
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

				{/* Panel de Reglas de Visualización */}
				<PanelBody title={__('Reglas de Visualización', 'ewm-modal-cta')} initialOpen={false}>
					<PanelRow>
						<SelectControl
							label={__('Frecuencia de Visualización', 'ewm-modal-cta')}
							value={displayRules?.frequency?.type || 'session'}
							options={[
								{ value: 'always', label: __('Siempre mostrar', 'ewm-modal-cta') },
								{ value: 'session', label: __('Una vez por sesión', 'ewm-modal-cta') },
								{ value: 'daily', label: __('Una vez por día', 'ewm-modal-cta') },
								{ value: 'weekly', label: __('Una vez por semana', 'ewm-modal-cta') }
							]}
							onChange={(value) => {
								const newDisplayRules = {
									...displayRules,
									frequency: {
										...displayRules?.frequency,
										type: value,
										limit: value === 'always' ? 0 : 1
									}
								};
								setAttributes({ displayRules: newDisplayRules });
							}}
							help={__('Controla con qué frecuencia se muestra el modal al mismo usuario', 'ewm-modal-cta')}
						/>
					</PanelRow>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				{/* Contenedor específico para aislamiento de estilos */}
				<div className="ewm-modal-block-editor-wrapper">
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
											{/* Overlay simulado */}
											<div className="ewm-preview-overlay">
												<div
													className={`ewm-modal-container ewm-size-${modalSize} ewm-animation-${animation}`}
													style={{
														'--ewm-primary-color': primaryColor,
														'--ewm-secondary-color': secondaryColor,
														'--ewm-background-color': backgroundColor
													}}
												>
													<div className="ewm-modal-content">
													{/* Header del modal */}
													<div className="ewm-preview-header">
														<div className="ewm-preview-title">
															{modalMode === 'formulario' && steps && steps.length > 0 && currentPreviewStep < steps.length ? (
																<h3>{steps[currentPreviewStep].title || __('Paso', 'ewm-modal-cta') + ' ' + (currentPreviewStep + 1)}</h3>
															) : modalMode === 'formulario' && currentPreviewStep === steps.length && finalStep && finalStep.title ? (
																<h3>{finalStep.title}</h3>
															) : (
																<h3>{modalMode === 'formulario' ? __('Formulario Interactivo', 'ewm-modal-cta') : __('Anuncio Promocional', 'ewm-modal-cta')}</h3>
															)}
														</div>
														<button className="ewm-preview-close" aria-label="Cerrar">×</button>
													</div>

													{/* Barra de progreso dinámica */}
													{modalMode === 'formulario' && showProgressBar && (
														<div className={`ewm-preview-progress-container ewm-progress-${progressBarStyle}`}>
															<div className="ewm-progress-label">
																<span>
																	{__('Paso', 'ewm-modal-cta')} {currentPreviewStep + 1} {__('de', 'ewm-modal-cta')} {steps.length + (finalStep && Object.keys(finalStep).length > 0 ? 1 : 0)}
																</span>
																<span>
																	{Math.round(((currentPreviewStep + 1) / (steps.length + (finalStep && Object.keys(finalStep).length > 0 ? 1 : 0))) * 100)}%
																</span>
															</div>
															<div className="ewm-preview-progress">
																<div
																	className="ewm-progress-fill"
																	style={{
																		width: `${((currentPreviewStep + 1) / (steps.length + (finalStep && Object.keys(finalStep).length > 0 ? 1 : 0))) * 100}%`,
																		backgroundColor: primaryColor
																	}}
																></div>
															</div>
														</div>
													)}

													{/* Contenido del modal */}
													<div className="ewm-preview-content">
														{modalMode === 'formulario' ? (
															<div className="ewm-preview-form">
																{/* DEBUG: Logs para verificar datos */}
																{console.log('🔍 EWM DEBUG - Datos completos de attributes:', attributes)}
																{console.log('🔍 EWM DEBUG - modalConfigData:', attributes.modalConfigData)}
																{console.log('🔍 EWM DEBUG - steps state:', steps)}
																{console.log('🔍 EWM DEBUG - steps.length:', steps.length)}
																{console.log('🔍 EWM DEBUG - modalData state:', modalData)}

																{steps && steps.length > 0 ? (
																	<>
																		{/* Contenido del paso actual o paso final */}
																		{currentPreviewStep < steps.length ? (
																			<>
																				{/* Título y contenido del paso actual */}
																				<div className="ewm-step-header">
																					<h4 className="ewm-step-title">{steps[currentPreviewStep].title || __('Paso', 'ewm-modal-cta') + ' ' + (currentPreviewStep + 1)}</h4>
																					{steps[currentPreviewStep].content && (
																						<p className="ewm-step-content">{steps[currentPreviewStep].content}</p>
																					)}
																				</div>
																			</>
																		) : (
																			<>
																				{/* Contenido del paso final */}
																				<div className="ewm-final-step-preview">
																					<div className="ewm-step-header">
																						<div className="ewm-final-step-icon" style={{ fontSize: '48px', textAlign: 'center', marginBottom: '16px' }}>
																							🎉
																						</div>
																						<h4 className="ewm-step-title" style={{ textAlign: 'center', color: primaryColor }}>
																							{finalStep.title || __('¡Gracias!', 'ewm-modal-cta')}
																						</h4>
																						{finalStep.content && (
																							<p className="ewm-step-content" style={{ textAlign: 'center', marginTop: '12px' }}>
																								{finalStep.content}
																							</p>
																						)}
																						<div style={{ textAlign: 'center', marginTop: '20px', padding: '16px', backgroundColor: '#f8f9fa', borderRadius: '8px' }}>
																							<small style={{ color: '#666' }}>
																								{__('✅ Formulario completado - Los datos se enviarían al servidor', 'ewm-modal-cta')}
																							</small>
																						</div>
																					</div>
																				</div>
																			</>
																		)}

																		{/* Campos del formulario - Solo mostrar si no estamos en el paso final */}
																		{currentPreviewStep < steps.length && (
																			<div className="ewm-form-fields">
																				{steps[currentPreviewStep].fields && steps[currentPreviewStep].fields.length > 0 ? (
																					steps[currentPreviewStep].fields.map((field, index) => (
																					<div key={index} className={`ewm-preview-field ewm-field-${field.type || 'text'}`}>
																						<label className="ewm-field-label">
																							{field.label || field.id}
																							{field.required && <span className="ewm-required">*</span>}
																						</label>
																						{field.type === 'select' ? (
																							<select className="ewm-field-input">
																								<option value="">{field.placeholder || __('Selecciona una opción...', 'ewm-modal-cta')}</option>
																								{field.options && field.options.map((option, optIndex) => (
																									<option key={optIndex} value={option.value}>{option.label}</option>
																								))}
																							</select>
																						) : field.type === 'textarea' ? (
																							<textarea
																								className="ewm-field-input"
																								placeholder={field.placeholder || __('Introduce tu respuesta...', 'ewm-modal-cta')}
																								rows="3"
																							></textarea>
																						) : field.type === 'checkbox' ? (
																							<div className="ewm-checkbox-group">
																								{field.options && field.options.length > 0 ? field.options.map((option, optIndex) => (
																									<label key={optIndex} className="ewm-checkbox-item">
																										<input type="checkbox" value={option.value} />
																										<span>{option.label}</span>
																									</label>
																								)) : (
																									<label className="ewm-checkbox-item">
																										<input type="checkbox" />
																										<span>{field.label || __('Opción de ejemplo', 'ewm-modal-cta')}</span>
																									</label>
																								)}
																							</div>
																						) : field.type === 'radio' ? (
																							<div className="ewm-radio-group">
																								{field.options && field.options.length > 0 ? field.options.map((option, optIndex) => (
																									<label key={optIndex} className="ewm-radio-item">
																										<input type="radio" name={`field_${index}`} value={option.value} />
																										<span>{option.label}</span>
																									</label>
																								)) : (
																									<label className="ewm-radio-item">
																										<input type="radio" name={`field_${index}`} />
																										<span>{field.label || __('Opción de ejemplo', 'ewm-modal-cta')}</span>
																									</label>
																								)}
																							</div>
																						) : (
																							<input
																								type={field.type || 'text'}
																								className="ewm-field-input"
																								placeholder={field.placeholder || __('Introduce tu respuesta...', 'ewm-modal-cta')}
																							/>
																						)}
																					</div>
																				))
																			) : (
																				<div className="ewm-preview-field ewm-field-text">
																					<label className="ewm-field-label">
																						{__('Campo de ejemplo', 'ewm-modal-cta')}
																						<span className="ewm-required">*</span>
																					</label>
																					<input
																						type="text"
																						className="ewm-field-input"
																						placeholder={__('Introduce tu respuesta...', 'ewm-modal-cta')}
																					/>
																				</div>
																				)}
																			</div>
																		)}

																		{/* Información del formulario */}
																		<div className="ewm-form-info">
																			<div className="ewm-step-indicator">
																				{Array.from({ length: steps.length + (finalStep && Object.keys(finalStep).length > 0 ? 1 : 0) }, (_, i) => (
																					<span
																						key={i}
																						className={`ewm-step-dot ${i === currentPreviewStep ? 'active' : ''}`}
																						style={{ backgroundColor: i === currentPreviewStep ? primaryColor : '#e0e0e0' }}
																					></span>
																				))}
																			</div>
																			<small className="ewm-step-count">
																				{__('Pasos configurados:', 'ewm-modal-cta')} {steps.length}
																				{finalStep && Object.keys(finalStep).length > 0 && (
																					<span> + {__('paso final', 'ewm-modal-cta')}</span>
																				)}
																			</small>
																		</div>
																	</>
																) : (
																	<>
																		{/* Estado sin configurar */}
																		<div className="ewm-empty-state">
																			<div className="ewm-empty-icon">📝</div>
																			<h4>{__('Formulario sin configurar', 'ewm-modal-cta')}</h4>
																			<p>{__('Agrega pasos y campos en el panel lateral para ver el preview', 'ewm-modal-cta')}</p>
																		</div>
																		<div className="ewm-preview-field ewm-field-text">
																			<label className="ewm-field-label">
																				{__('Campo de ejemplo', 'ewm-modal-cta')}
																				<span className="ewm-required">*</span>
																			</label>
																			<input
																				type="text"
																				className="ewm-field-input"
																				placeholder={__('Introduce tu respuesta...', 'ewm-modal-cta')}
																			/>
																		</div>
																	</>
																)}

																{/* Botones de acción con navegación funcional */}
																<div className="ewm-form-actions">
																	{/* Botón Anterior - Solo mostrar si no estamos en el primer paso */}
																	{currentPreviewStep > 0 && (
																		<button
																			className="ewm-preview-button ewm-btn-secondary"
																			style={{ color: secondaryColor, borderColor: secondaryColor }}
																			onClick={() => setCurrentPreviewStep(currentPreviewStep - 1)}
																		>
																			{__('← Anterior', 'ewm-modal-cta')}
																		</button>
																	)}

																	{/* Botón Siguiente/Enviar */}
																	{currentPreviewStep < steps.length ? (
																		<button
																			className="ewm-preview-button ewm-btn-primary"
																			style={{ backgroundColor: primaryColor, borderColor: primaryColor }}
																			onClick={() => setCurrentPreviewStep(currentPreviewStep + 1)}
																		>
																			{currentPreviewStep === steps.length - 1 && finalStep && Object.keys(finalStep).length > 0 ?
																				__('Finalizar →', 'ewm-modal-cta') :
																				__('Siguiente →', 'ewm-modal-cta')
																			}
																		</button>
																	) : (
																		<button
																			className="ewm-preview-button ewm-btn-primary"
																			style={{ backgroundColor: primaryColor, borderColor: primaryColor }}
																			disabled={true}
																			title={__('Preview del formulario - No funcional', 'ewm-modal-cta')}
																		>
																			{__('Enviar', 'ewm-modal-cta')}
																		</button>
																	)}
																</div>
															</div>
														) : (
															<>
																{/* Vista previa del anuncio */}
																<div className="ewm-preview-announcement">
																	<div className="ewm-announcement-icon">🎯</div>
																	<h3>{__('¡Oferta Especial!', 'ewm-modal-cta')}</h3>
																	<p>{__('Contenido del anuncio aparecerá aquí. Personaliza el mensaje y la acción en el panel lateral.', 'ewm-modal-cta')}</p>
																	<div className="ewm-form-actions">
																		<button
																			className="ewm-preview-button ewm-btn-primary"
																			style={{ backgroundColor: primaryColor, borderColor: primaryColor }}
																		>
																			{__('¡Quiero la oferta!', 'ewm-modal-cta')}
																		</button>
																		<button
																			className="ewm-preview-button ewm-btn-secondary"
																			style={{ color: secondaryColor, borderColor: secondaryColor }}
																		>
																			{__('Más información', 'ewm-modal-cta')}
																		</button>
																	</div>
																</div>
															</>
														)}
													</div>

													{/* Footer del modal */}
													<div className="ewm-preview-footer">
														<small style={{ color: secondaryColor }}>
															{triggerType === 'exit-intent' && '🚪 Se activa al intentar salir'}
															{triggerType === 'time-delay' && `⏰ Se activa después de ${triggerDelay/1000}s`}
															{triggerType === 'scroll' && '📜 Se activa al hacer scroll'}
															{triggerType === 'click' && '👆 Se activa al hacer clic'}
														</small>
													</div>
													</div> {/* Cierre de ewm-modal-content */}
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
				</div> {/* Cierre del contenedor ewm-modal-block-editor-wrapper */}
			</div>
		</>
	);
}
