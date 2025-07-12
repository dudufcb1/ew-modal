/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {Element} Element to render.
 */
export default function save({ attributes }) {
	const {
		modalId,
		autoGenerateShortcode,
		triggerType,
		triggerDelay,
		modalSize,
		animation,
		primaryColor,
		customCSS
	} = attributes;

	// Si no hay modalId, no renderizar nada
	if (!modalId) {
		return null;
	}

	const blockProps = useBlockProps.save({
		className: `ewm-modal-block-wrapper ewm-modal-${modalId}`,
		'data-modal-id': modalId,
		'data-trigger': triggerType,
		'data-delay': triggerDelay,
		'data-size': modalSize,
		'data-animation': animation
	});

	// Construir atributos del shortcode
	let shortcodeAttrs = `id="${modalId}"`;

	if (triggerType && triggerType !== 'manual') {
		shortcodeAttrs += ` trigger="${triggerType}"`;
	}

	if (triggerDelay && triggerType === 'time-delay') {
		shortcodeAttrs += ` delay="${triggerDelay}"`;
	}

	// Generar shortcode
	const shortcode = `[ew_modal ${shortcodeAttrs}]`;

	return (
		<div {...blockProps}>
			{autoGenerateShortcode ? (
				<>
					{/* Comentario HTML con información del bloque */}
					<div
						className="ewm-block-comment"
						style={{ display: 'none' }}
						data-ewm-block="true"
						data-ewm-version="1.0.0"
						data-ewm-modal-id={modalId}
						data-ewm-shortcode={shortcode}
					>
						EWM Modal Block - ID: {modalId}
					</div>

					{/* Shortcode generado automáticamente */}
					<div
						className="ewm-generated-shortcode"
						data-ewm-shortcode={shortcode}
						dangerouslySetInnerHTML={{ __html: `<!-- ${shortcode} -->` }}
					/>

					{/* Placeholder visual para el editor */}
					<div className="ewm-block-placeholder">
						<div className="ewm-placeholder-content">
							<span className="ewm-placeholder-icon">📋</span>
							<h4>Modal CTA Multi-Paso</h4>
							<p>ID: {modalId}</p>
							<code>{shortcode}</code>
						</div>
					</div>
				</>
			) : (
				/* Solo placeholder si no se auto-genera shortcode */
				<div className="ewm-block-placeholder">
					<div className="ewm-placeholder-content">
						<span className="ewm-placeholder-icon">📋</span>
						<h4>Modal CTA Multi-Paso</h4>
						<p>ID: {modalId}</p>
						<p><em>Auto-generación de shortcode deshabilitada</em></p>
					</div>
				</div>
			)}

			{/* CSS personalizado si existe */}
			{customCSS && (
				<style
					dangerouslySetInnerHTML={{
						__html: `
							.ewm-modal-${modalId} {
								${customCSS}
							}
						`
					}}
				/>
			)}
		</div>
	);
}
