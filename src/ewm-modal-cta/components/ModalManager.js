/**
 * Componente para gestión de modales (crear, cargar, guardar)
 * Maneja la comunicación con la REST API
 */

import { __ } from '@wordpress/i18n';
import { 
	Button, 
	TextControl, 
	SelectControl,
	Spinner,
	Notice,
	Card,
	CardBody,
	Flex,
	FlexItem
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * Componente principal para gestión de modales
 */
export default function ModalManager({ 
	modalId, 
	onModalIdChange, 
	attributes, 
	setAttributes,
	onModalDataLoaded 
}) {
	const [isLoading, setIsLoading] = useState(false);
	const [isSaving, setIsSaving] = useState(false);
	const [availableModals, setAvailableModals] = useState([]);
	const [error, setError] = useState(null);
	const [success, setSuccess] = useState(null);
	const [newModalTitle, setNewModalTitle] = useState('');
	const [saveStatus, setSaveStatus] = useState('saved'); // 'saved', 'saving', 'error', 'pending'

	// Cargar lista de modales disponibles
	useEffect(() => {
		loadAvailableModals();
	}, []);

	// Auto-guardar optimizado con debounce inteligente y feedback visual
	useEffect(() => {
		if (modalId && modalId !== '') {
			// Marcar como pendiente de guardado
			setSaveStatus('pending');

			// Debounce más largo para evitar guardados excesivos
			const timeoutId = setTimeout(() => {
				// Solo guardar si hay cambios significativos
				if (hasSignificantChanges()) {
					setSaveStatus('saving');
					saveModal();
				} else {
					setSaveStatus('saved');
				}
			}, 5000); // Guardar después de 5 segundos de inactividad

			return () => clearTimeout(timeoutId);
		}
	}, [attributes]);

	/**
	 * Verificar si hay cambios significativos que justifiquen un auto-save
	 */
	const hasSignificantChanges = () => {
		// Evitar auto-save para cambios menores como hover states
		const significantFields = ['modalId', 'modalMode', 'modalSize', 'primaryColor', 'secondaryColor', 'backgroundColor'];
		return significantFields.some(field => attributes[field] !== undefined);
	};

	/**
	 * Cargar lista de modales disponibles
	 */
	const loadAvailableModals = async () => {
		try {
			setIsLoading(true);
			const response = await apiFetch({
				path: '/ewm/v1/modals',
				method: 'GET'
			});

			// El endpoint devuelve {modals: [...], total: X, pages: Y}
			// Extraer el array de modales de la respuesta
			const modals = response.modals || response || [];

			const modalOptions = [
				{ label: __('Seleccionar modal existente...', 'ewm-modal-cta'), value: '' },
				...modals.map(modal => ({
					label: `${modal.title} (ID: ${modal.id})`,
					value: modal.id.toString()
				}))
			];

			setAvailableModals(modalOptions);
		} catch (err) {
			console.error('Error loading modals:', err);
			setError(__('Error al cargar la lista de modales', 'ewm-modal-cta'));
		} finally {
			setIsLoading(false);
		}
	};

	/**
	 * Crear un nuevo modal
	 */
	const createNewModal = async () => {
		if (!newModalTitle.trim()) {
			setError(__('Por favor ingresa un título para el modal', 'ewm-modal-cta'));
			return;
		}

		try {
			setIsLoading(true);
			setError(null);

			const newModal = await apiFetch({
				path: '/ewm/v1/modals',
				method: 'POST',
				data: {
					title: newModalTitle,
					mode: attributes.modalMode || 'formulario',
					steps: [],
					final_step: {
						title: __('¡Gracias!', 'ewm-modal-cta'),
						content: __('Gracias por tu información. Te contactaremos pronto.', 'ewm-modal-cta')
					},
					design: {
						size: attributes.modalSize || 'medium',
						animation: attributes.animation || 'fade',
						primary_color: attributes.primaryColor || '#ff6b35',
						secondary_color: attributes.secondaryColor || '#333333',
						background_color: attributes.backgroundColor || '#ffffff'
					},
					triggers: {
						type: attributes.triggerType || 'manual',
						delay: attributes.triggerDelay || 5000
					},
					display_rules: attributes.displayRules || {},
					wc_integration: {
						enabled: attributes.enableWooCommerce || false,
						coupon_id: attributes.selectedCoupon || 0
					},
					custom_css: attributes.customCSS || '',
					source: 'gutenberg_block'
				}
			});

			// Actualizar el modalId en los atributos
			onModalIdChange(newModal.id.toString());
			setNewModalTitle('');
			setSuccess(__('Modal creado exitosamente', 'ewm-modal-cta'));
			
			// Recargar lista de modales
			loadAvailableModals();

			// Notificar que se cargaron datos del modal
			if (onModalDataLoaded) {
				onModalDataLoaded(newModal);
			}

		} catch (err) {
			console.error('Error creating modal:', err);
			setError(__('Error al crear el modal', 'ewm-modal-cta'));
		} finally {
			setIsLoading(false);
		}
	};

	/**
	 * Cargar datos de un modal existente
	 */
	const loadModal = async (selectedModalId) => {
		if (!selectedModalId) return;

		try {
			setIsLoading(true);
			setError(null);

			const modal = await apiFetch({
				path: `/ewm/v1/modals/${selectedModalId}`,
				method: 'GET'
			});

			// Actualizar atributos del bloque con datos del modal
			const updatedAttributes = {
				modalId: selectedModalId,
				modalMode: modal.mode || 'formulario',
				modalSize: modal.design?.size || 'medium',
				animation: modal.design?.animation || 'fade',
				primaryColor: modal.design?.primary_color || '#ff6b35',
				secondaryColor: modal.design?.secondary_color || '#333333',
				backgroundColor: modal.design?.background_color || '#ffffff',
				triggerType: modal.triggers?.type || 'manual',
				triggerDelay: modal.triggers?.delay || 5000,
				enableWooCommerce: modal.wc_integration?.enabled || false,
				selectedCoupon: modal.wc_integration?.coupon_id || 0,
				customCSS: modal.custom_css || '',
				displayRules: modal.display_rules || {}
			};

			setAttributes(updatedAttributes);
			onModalIdChange(selectedModalId);
			setSuccess(__('Modal cargado exitosamente', 'ewm-modal-cta'));

			// Notificar que se cargaron datos del modal
			if (onModalDataLoaded) {
				onModalDataLoaded(modal);
			}

		} catch (err) {
			console.error('Error loading modal:', err);
			setError(__('Error al cargar el modal', 'ewm-modal-cta'));
		} finally {
			setIsLoading(false);
		}
	};

	/**
	 * Guardar modal actual
	 */
	const saveModal = async () => {
		if (!modalId) return;

		try {
			setIsSaving(true);
			setError(null);

			// 📊 LOG: Datos de atributos antes de procesar
			console.log('🔍 GUTENBERG DEBUG: Attributes before processing:', {
				modalMode: attributes.modalMode,
				modalConfigData: attributes.modalConfigData,
				steps: attributes.modalConfigData?.steps,
				stepsLength: attributes.modalConfigData?.steps?.length,
				primaryColor: attributes.primaryColor,
				modalSize: attributes.modalSize
			});

			// 🔧 CORREGIR: Enviar datos en el mismo formato que el shortcode builder
			const dataToSave = {
				title: `Modal ${modalId}`, // Agregar título
				config: {
					mode: attributes.modalMode,
					steps: {
						steps: attributes.modalConfigData?.steps || [],
						final_step: attributes.modalConfigData?.final_step || {},
						progressBar: {
							enabled: true,
							color: attributes.primaryColor || '#ff6b35',
							style: 'line'
						}
					},
					design: {
						theme: 'default',
						colors: {
							primary: attributes.primaryColor,
							secondary: attributes.secondaryColor,
							background: attributes.backgroundColor
						},
						typography: {
							font_family: 'inherit',
							font_size: '16px'
						},
						modal_size: attributes.modalSize,
						animation: attributes.animation
					},
					triggers: {
						exit_intent: {
							enabled: attributes.triggerType === 'exit-intent',
							sensitivity: 20
						},
						time_delay: {
							enabled: attributes.triggerType === 'time-delay',
							delay: attributes.triggerDelay || 5000
						},
						scroll_percentage: {
							enabled: attributes.triggerType === 'scroll',
							percentage: 50
						},
						manual: {
							enabled: attributes.triggerType === 'click',
							selector: ''
						}
					},
					wc_integration: {
						enabled: attributes.enableWooCommerce,
						cart_abandonment: {
							enabled: false,
							delay: 300
						},
						product_recommendations: {
							enabled: false,
							count: 3
						}
					},
					display_rules: attributes.displayRules || {
						pages: { include: [], exclude: [] },
						user_roles: [],
						devices: { desktop: true, tablet: true, mobile: true },
						frequency: { type: 'session', limit: 1 }
					},
					custom_css: attributes.customCSS || ''
				}
			};

			// 📊 LOG: Datos finales que se enviarán
			console.log('🚀 GUTENBERG DEBUG: Final data to save:', {
				dataToSave: dataToSave,
				dataSize: JSON.stringify(dataToSave).length,
				hasTitle: !!dataToSave.title,
				hasConfig: !!dataToSave.config,
				configKeys: dataToSave.config ? Object.keys(dataToSave.config) : [],
				stepsStructure: dataToSave.config?.steps,
				stepsArray: dataToSave.config?.steps?.steps,
				stepsCount: dataToSave.config?.steps?.steps?.length || 0
			});

			console.log('🚀 GUTENBERG DEBUG: Steps detail:', dataToSave.config?.steps?.steps);
			console.log('🔍 EWM DEBUG - Modal ID:', modalId);
			console.log('🔍 EWM DEBUG - Attributes completos:', attributes);

			const response = await apiFetch({
				path: `/ewm/v1/modals/${modalId}`,
				method: 'PUT',
				data: dataToSave
			});

			// LOG DE DEPURACIÓN: Respuesta del servidor
			console.log('✅ EWM DEBUG - Respuesta del servidor:', response);

			setSuccess(__('Modal guardado automáticamente', 'ewm-modal-cta'));
			setSaveStatus('saved');

			// Limpiar mensaje de éxito después de 3 segundos
			setTimeout(() => setSuccess(null), 3000);

		} catch (err) {
			console.error('Error saving modal:', err);
			setError(__('Error al guardar el modal', 'ewm-modal-cta'));
			setSaveStatus('error');
		} finally {
			setIsSaving(false);
		}
	};

	return (
		<div className="ewm-modal-manager">
			{error && (
				<Notice status="error" onRemove={() => setError(null)}>
					{error}
				</Notice>
			)}

			{success && (
				<Notice status="success" onRemove={() => setSuccess(null)}>
					{success}
				</Notice>
			)}

			{/* Indicador de estado de guardado */}
			{modalId && (
				<div className="ewm-save-status" style={{
					padding: '8px 12px',
					marginBottom: '12px',
					borderRadius: '4px',
					fontSize: '12px',
					display: 'flex',
					alignItems: 'center',
					gap: '6px',
					backgroundColor: saveStatus === 'saved' ? '#d1fae5' :
									saveStatus === 'saving' ? '#fef3c7' :
									saveStatus === 'pending' ? '#e0e7ff' : '#fee2e2',
					color: saveStatus === 'saved' ? '#065f46' :
						   saveStatus === 'saving' ? '#92400e' :
						   saveStatus === 'pending' ? '#3730a3' : '#991b1b',
					border: `1px solid ${saveStatus === 'saved' ? '#a7f3d0' :
											saveStatus === 'saving' ? '#fde68a' :
											saveStatus === 'pending' ? '#c7d2fe' : '#fecaca'}`
				}}>
					{saveStatus === 'saved' && '✅ Guardado'}
					{saveStatus === 'saving' && '💾 Guardando...'}
					{saveStatus === 'pending' && '⏳ Cambios pendientes'}
					{saveStatus === 'error' && '❌ Error al guardar'}
				</div>
			)}

			{!modalId ? (
				<Card>
					<CardBody>
						<h3>{__('Configurar Modal', 'ewm-modal-cta')}</h3>
						
						{/* Crear nuevo modal */}
						<div className="ewm-create-modal">
							<h4>{__('Crear Nuevo Modal', 'ewm-modal-cta')}</h4>
							<Flex gap={2} align="end">
								<FlexItem>
									<TextControl
										label={__('Título del Modal', 'ewm-modal-cta')}
										value={newModalTitle}
										onChange={setNewModalTitle}
										placeholder={__('Ej: Modal de Contacto', 'ewm-modal-cta')}
									/>
								</FlexItem>
								<FlexItem>
									<Button
										variant="primary"
										onClick={createNewModal}
										disabled={isLoading || !newModalTitle.trim()}
									>
										{isLoading ? <Spinner /> : __('Crear', 'ewm-modal-cta')}
									</Button>
								</FlexItem>
							</Flex>
						</div>

						<hr />

						{/* Seleccionar modal existente */}
						<div className="ewm-select-modal">
							<h4>{__('O Seleccionar Modal Existente', 'ewm-modal-cta')}</h4>
							<SelectControl
								label={__('Modal Existente', 'ewm-modal-cta')}
								value=""
								options={availableModals}
								onChange={loadModal}
								disabled={isLoading}
							/>
						</div>
					</CardBody>
				</Card>
			) : (
				<Card>
					<CardBody>
						<Flex justify="space-between" align="center">
							<FlexItem>
								<h4>{__('Modal Configurado', 'ewm-modal-cta')}: ID {modalId}</h4>
							</FlexItem>
							<FlexItem>
								{isSaving && <Spinner />}
								<Button
									variant="tertiary"
									onClick={() => {
										onModalIdChange('');
										setAttributes({ modalId: '' });
									}}
								>
									{__('Cambiar Modal', 'ewm-modal-cta')}
								</Button>
							</FlexItem>
						</Flex>
					</CardBody>
				</Card>
			)}
		</div>
	);
}
