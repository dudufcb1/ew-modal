/**
 * Componente React para configuración de pasos del formulario
 * Equivalente al Modal Builder pero en React para Gutenberg
 */

import { __ } from '@wordpress/i18n';
import { 
	PanelBody, 
	PanelRow, 
	Button, 
	TextControl, 
	TextareaControl,
	SelectControl,
	ToggleControl,
	Card,
	CardHeader,
	CardBody,
	Flex,
	FlexItem,
	Icon
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';

/**
 * Tipos de campo soportados
 */
const FIELD_TYPES = [
	{ label: __('Texto', 'ewm-modal-cta'), value: 'text' },
	{ label: __('Email', 'ewm-modal-cta'), value: 'email' },
	{ label: __('Teléfono', 'ewm-modal-cta'), value: 'tel' },
	{ label: __('Número', 'ewm-modal-cta'), value: 'number' },
	{ label: __('Área de Texto', 'ewm-modal-cta'), value: 'textarea' },
	{ label: __('Selección', 'ewm-modal-cta'), value: 'select' },
	{ label: __('Radio', 'ewm-modal-cta'), value: 'radio' },
	{ label: __('Checkbox', 'ewm-modal-cta'), value: 'checkbox' },
	{ label: __('URL', 'ewm-modal-cta'), value: 'url' },
	{ label: __('Fecha', 'ewm-modal-cta'), value: 'date' },
	{ label: __('Hora', 'ewm-modal-cta'), value: 'time' },
	{ label: __('Fecha y Hora', 'ewm-modal-cta'), value: 'datetime-local' },
	{ label: __('Rango', 'ewm-modal-cta'), value: 'range' },
	{ label: __('Color', 'ewm-modal-cta'), value: 'color' },
	{ label: __('Contraseña', 'ewm-modal-cta'), value: 'password' },
	{ label: __('Búsqueda', 'ewm-modal-cta'), value: 'search' },
	{ label: __('Mes', 'ewm-modal-cta'), value: 'month' },
	{ label: __('Semana', 'ewm-modal-cta'), value: 'week' }
];

/**
 * Componente para configurar un campo individual
 */
function FieldConfiguration({ field, onUpdate, onRemove }) {
	const [isExpanded, setIsExpanded] = useState(false);
	const [showOptions, setShowOptions] = useState(['select', 'radio', 'checkbox'].includes(field.type));

	useEffect(() => {
		setShowOptions(['select', 'radio', 'checkbox'].includes(field.type));
	}, [field.type]);

	const updateField = (key, value) => {
		onUpdate({ ...field, [key]: value });
	};

	const addOption = () => {
		const newOptions = [...(field.options || []), { label: '', value: '' }];
		updateField('options', newOptions);
	};

	const updateOption = (index, key, value) => {
		const newOptions = [...(field.options || [])];
		newOptions[index] = { ...newOptions[index], [key]: value };
		updateField('options', newOptions);
	};

	const removeOption = (index) => {
		const newOptions = [...(field.options || [])];
		newOptions.splice(index, 1);
		updateField('options', newOptions);
	};

	return (
		<Card className="ewm-field-config">
			<CardHeader>
				<Flex justify="space-between" align="center">
					<FlexItem>
						<Button
							variant="tertiary"
							onClick={() => setIsExpanded(!isExpanded)}
							icon={isExpanded ? 'arrow-down' : 'arrow-right'}
						>
							{field.label || field.id || __('Campo sin nombre', 'ewm-modal-cta')}
						</Button>
					</FlexItem>
					<FlexItem>
						<Button
							variant="tertiary"
							isDestructive
							onClick={onRemove}
						>
							✕
						</Button>
					</FlexItem>
				</Flex>
			</CardHeader>
			
			{isExpanded && (
				<CardBody>
					<TextControl
						label={__('ID del Campo', 'ewm-modal-cta')}
						value={field.id || ''}
						onChange={(value) => updateField('id', value)}
						help={__('Identificador único del campo (sin espacios)', 'ewm-modal-cta')}
					/>
					
					<SelectControl
						label={__('Tipo de Campo', 'ewm-modal-cta')}
						value={field.type || 'text'}
						options={FIELD_TYPES}
						onChange={(value) => updateField('type', value)}
					/>
					
					<TextControl
						label={__('Etiqueta', 'ewm-modal-cta')}
						value={field.label || ''}
						onChange={(value) => updateField('label', value)}
					/>
					
					<TextControl
						label={__('Placeholder', 'ewm-modal-cta')}
						value={field.placeholder || ''}
						onChange={(value) => updateField('placeholder', value)}
					/>
					
					<ToggleControl
						label={__('Campo Requerido', 'ewm-modal-cta')}
						checked={field.required || false}
						onChange={(value) => updateField('required', value)}
					/>

					{showOptions && (
						<div className="ewm-field-options">
							<h4>{__('Opciones', 'ewm-modal-cta')}</h4>
							{(field.options || []).map((option, index) => (
								<Flex key={index} gap={2} align="end">
									<FlexItem>
										<TextControl
											label={__('Etiqueta', 'ewm-modal-cta')}
											value={option.label || ''}
											onChange={(value) => updateOption(index, 'label', value)}
										/>
									</FlexItem>
									<FlexItem>
										<TextControl
											label={__('Valor', 'ewm-modal-cta')}
											value={option.value || ''}
											onChange={(value) => updateOption(index, 'value', value)}
										/>
									</FlexItem>
									<FlexItem>
										<Button
											variant="tertiary"
											isDestructive
											onClick={() => removeOption(index)}
										>
											✕
										</Button>
									</FlexItem>
								</Flex>
							))}
							<Button
								variant="secondary"
								onClick={addOption}
							>
								{__('Agregar Opción', 'ewm-modal-cta')}
							</Button>
						</div>
					)}
				</CardBody>
			)}
		</Card>
	);
}

/**
 * Componente principal para configuración de pasos
 */
export default function StepConfiguration({ steps = [], onStepsChange, finalStep = {}, onFinalStepChange }) {
	const [expandedStep, setExpandedStep] = useState(null);

	const addStep = () => {
		const newStep = {
			id: `step_${Date.now()}`,
			title: __('Nuevo Paso', 'ewm-modal-cta'),
			content: '',
			fields: []
		};
		onStepsChange([...steps, newStep]);
		setExpandedStep(steps.length);
	};

	const updateStep = (index, key, value) => {
		const newSteps = [...steps];
		newSteps[index] = { ...newSteps[index], [key]: value };
		onStepsChange(newSteps);
	};

	const removeStep = (index) => {
		const newSteps = [...steps];
		newSteps.splice(index, 1);
		onStepsChange(newSteps);
		if (expandedStep === index) {
			setExpandedStep(null);
		}
	};

	const addFieldToStep = (stepIndex) => {
		const newField = {
			id: `field_${Date.now()}`,
			type: 'text',
			label: __('Nuevo Campo', 'ewm-modal-cta'),
			placeholder: '',
			required: true,
			options: []
		};
		const newSteps = [...steps];
		newSteps[stepIndex].fields = [...(newSteps[stepIndex].fields || []), newField];
		onStepsChange(newSteps);
	};

	const updateFieldInStep = (stepIndex, fieldIndex, updatedField) => {
		const newSteps = [...steps];
		newSteps[stepIndex].fields[fieldIndex] = updatedField;
		onStepsChange(newSteps);
	};

	const removeFieldFromStep = (stepIndex, fieldIndex) => {
		const newSteps = [...steps];
		newSteps[stepIndex].fields.splice(fieldIndex, 1);
		onStepsChange(newSteps);
	};

	return (
		<div className="ewm-step-configuration">
			<PanelBody title={__('Configuración de Pasos', 'ewm-modal-cta')} initialOpen={true}>
				<PanelRow>
					<p>{__('Configura los pasos de tu formulario multi-paso. Cada paso puede tener múltiples campos.', 'ewm-modal-cta')}</p>
				</PanelRow>

				{steps.map((step, stepIndex) => (
					<Card key={step.id || stepIndex} className="ewm-step-card">
						<CardHeader>
							<Flex justify="space-between" align="center">
								<FlexItem>
									<Button
										variant="tertiary"
										onClick={() => setExpandedStep(expandedStep === stepIndex ? null : stepIndex)}
										icon={expandedStep === stepIndex ? 'arrow-down' : 'arrow-right'}
									>
										{__('Paso', 'ewm-modal-cta')} {stepIndex + 1}: {step.title || __('Sin título', 'ewm-modal-cta')}
									</Button>
								</FlexItem>
								<FlexItem>
									<Button
										variant="tertiary"
										isDestructive
										onClick={() => removeStep(stepIndex)}
									>
										✕
									</Button>
								</FlexItem>
							</Flex>
						</CardHeader>

						{expandedStep === stepIndex && (
							<CardBody>
								<TextControl
									label={__('Título del Paso', 'ewm-modal-cta')}
									value={step.title || ''}
									onChange={(value) => updateStep(stepIndex, 'title', value)}
								/>

								<TextareaControl
									label={__('Contenido/Descripción', 'ewm-modal-cta')}
									value={step.content || ''}
									onChange={(value) => updateStep(stepIndex, 'content', value)}
									help={__('Texto descriptivo que se mostrará en este paso', 'ewm-modal-cta')}
								/>

								<h4>{__('Campos del Paso', 'ewm-modal-cta')}</h4>
								
								{(step.fields || []).map((field, fieldIndex) => (
									<FieldConfiguration
										key={field.id || fieldIndex}
										field={field}
										onUpdate={(updatedField) => updateFieldInStep(stepIndex, fieldIndex, updatedField)}
										onRemove={() => removeFieldFromStep(stepIndex, fieldIndex)}
									/>
								))}

								<Button
									variant="secondary"
									onClick={() => addFieldToStep(stepIndex)}
								>
									{__('Agregar Campo', 'ewm-modal-cta')}
								</Button>
							</CardBody>
						)}
					</Card>
				))}

				<Button
					variant="primary"
					onClick={addStep}
				>
					{__('Agregar Paso', 'ewm-modal-cta')}
				</Button>
			</PanelBody>

			{/* Configuración del paso final */}
			<PanelBody title={__('Paso Final (Agradecimiento)', 'ewm-modal-cta')} initialOpen={false}>
				<TextControl
					label={__('Título del Mensaje Final', 'ewm-modal-cta')}
					value={finalStep.title || ''}
					onChange={(value) => onFinalStepChange({ ...finalStep, title: value })}
					placeholder={__('¡Gracias!', 'ewm-modal-cta')}
				/>

				<TextareaControl
					label={__('Mensaje de Agradecimiento', 'ewm-modal-cta')}
					value={finalStep.content || ''}
					onChange={(value) => onFinalStepChange({ ...finalStep, content: value })}
					placeholder={__('Gracias por tu información. Te contactaremos pronto.', 'ewm-modal-cta')}
				/>
			</PanelBody>
		</div>
	);
}
