<?php
/**
 * Template: Modal Coupon Display
 *
 * Template optimizado para mostrar cupones en el modal
 *
 * Variables disponibles:
 *
 * @var array $coupons Lista de cupones disponibles
 * @var int   $modal_id ID del modal
 * @var array $settings Configuraciones del modal
 *
 * @package EWM_Modal_CTA
 * @subpackage Templates
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Validar variables requeridas
if ( ! isset( $coupons ) || ! is_array( $coupons ) ) {
	$coupons = array();
}

if ( ! isset( $modal_id ) ) {
	$modal_id = 'unknown';
}

// Configuraciones por defecto
$settings = wp_parse_args(
	$settings ?? array(),
	array(
		'show_header'       => true,
		'show_descriptions' => true,
		'show_expiry'       => true,
		'max_coupons'       => 5,
		'layout'            => 'grid', // grid, list
		'theme'             => 'default', // default, minimal, premium
		'animations'        => true,
	)
);

// Clases CSS dinámicas
$section_classes = array(
	'ewm-coupon-section',
	'ewm-theme-' . sanitize_html_class( $settings['theme'] ),
	'ewm-layout-' . sanitize_html_class( $settings['layout'] ),
);

if ( $settings['animations'] ) {
	$section_classes[] = 'ewm-animations-enabled';
}

$section_class = implode( ' ', $section_classes );
?>

<div class="<?php echo esc_attr( $section_class ); ?>" 
	data-modal-id="<?php echo esc_attr( $modal_id ); ?>"
	data-coupon-count="<?php echo count( $coupons ); ?>"
	role="region" 
	aria-label="<?php esc_attr_e( 'Cupones disponibles', 'ewm-modal-cta' ); ?>">
	
	<?php if ( $settings['show_header'] ) : ?>
	<div class="ewm-coupon-header">
		<h4 class="ewm-coupon-title">
			<?php
			$modal_title = apply_filters( 'ewm_coupon_modal_title', __( 'Cupones Disponibles', 'ewm-modal-cta' ), $modal_id );
			echo esc_html( $modal_title );
			?>
		</h4>
		<p class="ewm-coupon-subtitle">
			<?php
			$subtitle = apply_filters( 'ewm_coupon_modal_subtitle', __( 'Apply a coupon and save on your purchase', 'ewm-modal-cta' ), $modal_id );
			echo esc_html( $subtitle );
			?>
		</p>
	</div>
	<?php endif; ?>
	
	<div class="ewm-coupon-content">
		<!-- Loading indicator -->
		<div class="ewm-coupon-loading" style="display: none;" role="status" aria-live="polite">
			<div class="ewm-loading-spinner" aria-hidden="true"></div>
			<span class="ewm-loading-text"><?php esc_html_e( 'Cargando cupones...', 'ewm-modal-cta' ); ?></span>
		</div>
		
		<!-- Coupon list -->
		<div class="ewm-coupon-list" role="list">
			<?php if ( ! empty( $coupons ) ) : ?>
				<?php
				// Limitar cupones según configuración
				$limited_coupons = array_slice( $coupons, 0, $settings['max_coupons'] );

				foreach ( $limited_coupons as $index => $coupon ) :
					// Validar estructura del cupón
					$coupon = wp_parse_args(
						$coupon,
						array(
							'code'          => '',
							'description'   => '',
							'discount_text' => '',
							'discount_type' => 'percent',
							'expires'       => '',
							'featured'      => false,
							'usage_limit'   => null,
							'usage_count'   => 0,
						)
					);

					// Clases CSS para el item
					$item_classes = array( 'ewm-coupon-item' );

					if ( $coupon['featured'] ) {
						$item_classes[] = 'ewm-featured';
					}

					// Determinar urgencia por expiración
					if ( ! empty( $coupon['expires'] ) ) {
						$expires_date      = strtotime( $coupon['expires'] );
						$days_until_expiry = ( $expires_date - time() ) / DAY_IN_SECONDS;

						if ( $days_until_expiry <= 1 ) {
							$item_classes[] = 'ewm-expires-soon';
						} elseif ( $days_until_expiry <= 7 ) {
							$item_classes[] = 'ewm-expires-week';
						}
					}

					// Determinar disponibilidad
					$is_limited       = ! empty( $coupon['usage_limit'] );
					$usage_percentage = $is_limited ? ( $coupon['usage_count'] / $coupon['usage_limit'] ) * 100 : 0;

					if ( $usage_percentage >= 90 ) {
						$item_classes[] = 'ewm-almost-exhausted';
					} elseif ( $usage_percentage >= 75 ) {
						$item_classes[] = 'ewm-limited-availability';
					}

					$item_class = implode( ' ', $item_classes );
					?>
				
				<div class="<?php echo esc_attr( $item_class ); ?>" 
					data-coupon-code="<?php echo esc_attr( $coupon['code'] ); ?>"
					data-index="<?php echo esc_attr( $index ); ?>"
					data-discount-type="<?php echo esc_attr( $coupon['discount_type'] ); ?>"
					role="listitem">
					
					<div class="ewm-coupon-info">
						<!-- Código del cupón -->
						<span class="ewm-coupon-code"
								<?php /* translators: %s: coupon code */ ?>
								title="<?php echo esc_attr( sprintf( __( 'Coupon code: %s', 'ewm-modal-cta' ), $coupon['code'] ) ); ?>">
							<?php echo esc_html( $coupon['code'] ); ?>
						</span>
						
						<!-- Descripción -->
						<?php if ( $settings['show_descriptions'] && ! empty( $coupon['description'] ) ) : ?>
						<span class="ewm-coupon-description">
							<?php echo esc_html( $coupon['description'] ); ?>
						</span>
						<?php endif; ?>
						
						<!-- Descuento -->
						<?php if ( ! empty( $coupon['discount_text'] ) ) : ?>
						<span class="ewm-coupon-discount ewm-discount-<?php echo esc_attr( $coupon['discount_type'] ); ?>">
							<?php echo esc_html( $coupon['discount_text'] ); ?>
						</span>
						<?php endif; ?>
						
						<!-- Información adicional -->
						<div class="ewm-coupon-meta">
							<!-- Fecha de expiración -->
							<?php if ( $settings['show_expiry'] && ! empty( $coupon['expires'] ) ) : ?>
							<span class="ewm-coupon-expires">
								<?php
								$expires_date = date_i18n( get_option( 'date_format' ), strtotime( $coupon['expires'] ) );
								/* translators: %s: expiration date */
								echo esc_html( sprintf( __( 'Expires: %s', 'ewm-modal-cta' ), $expires_date ) );
								?>
							</span>
							<?php endif; ?>
							
							<!-- Indicador de disponibilidad limitada -->
							<?php if ( $is_limited && $usage_percentage >= 50 ) : ?>
							<span class="ewm-coupon-availability">
								<?php
								$remaining = $coupon['usage_limit'] - $coupon['usage_count'];
								/* translators: %d: number of remaining uses */
								echo esc_html( sprintf( _n( '%d use remaining', '%d uses remaining', $remaining, 'ewm-modal-cta' ), $remaining ) );
								?>
							</span>
							<?php endif; ?>
						</div>
					</div>
					
					<!-- Botón de aplicar -->
					<button class="ewm-apply-coupon-btn"
							type="button"
							data-coupon="<?php echo esc_attr( $coupon['code'] ); ?>"
							<?php /* translators: %s: coupon code */ ?>
							aria-label="<?php echo esc_attr( sprintf( __( 'Apply coupon %s', 'ewm-modal-cta' ), $coupon['code'] ) ); ?>"
							<?php echo $settings['animations'] ? 'data-animate="true"' : ''; ?>>
						<span class="ewm-btn-text">
							<?php esc_html_e( 'Apply', 'ewm-modal-cta' ); ?>
						</span>
						<span class="ewm-btn-loading" style="display: none;" aria-hidden="true">
							<span class="ewm-loading-spinner-small"></span>
						</span>
					</button>
				</div>
				
				<?php endforeach; ?>
				
			<?php else : ?>
				<!-- Estado sin cupones -->
				<div class="ewm-no-coupons" role="status">
					<div class="ewm-no-coupons-icon" aria-hidden="true">🎫</div>
					<p class="ewm-no-coupons-message">
						<?php
						$no_coupons_message = apply_filters(
							'ewm_no_coupons_message',
							__( 'No hay cupones disponibles en este momento', 'ewm-modal-cta' ),
							$modal_id
						);
						echo esc_html( $no_coupons_message );
						?>
					</p>
					<button class="ewm-retry-btn" type="button" aria-label="<?php esc_attr_e( 'Try loading coupons again', 'ewm-modal-cta' ); ?>">
						<span><?php esc_html_e( 'Try again', 'ewm-modal-cta' ); ?></span>
					</button>
				</div>
			<?php endif; ?>
		</div>
		
		<!-- Mensajes de estado -->
		<div class="ewm-coupon-messages" role="status" aria-live="polite">
			<div class="ewm-coupon-success" style="display: none;"></div>
			<div class="ewm-coupon-error" style="display: none;"></div>
		</div>
	</div>
	
	<?php
	// Hook para contenido adicional
	do_action( 'ewm_after_coupon_content', $modal_id, $coupons, $settings );
	?>
</div>

<?php
// Agregar datos JSON para JavaScript
$coupon_data = array(
	'modalId'     => $modal_id,
	'couponCount' => count( $coupons ),
	'settings'    => array(
		'animations' => $settings['animations'],
		'theme'      => $settings['theme'],
		'layout'     => $settings['layout'],
	),
	'strings'     => array(
		'applying' => __( 'Applying coupon...', 'ewm-modal-cta' ),
		'applied'  => __( 'Coupon applied!', 'ewm-modal-cta' ),
		'error'    => __( 'Error applying coupon', 'ewm-modal-cta' ),
		'loading'  => __( 'Loading coupons...', 'ewm-modal-cta' ),
		'retry'    => __( 'Try again', 'ewm-modal-cta' ),
	),
);

// Escapar y agregar al DOM
?>
<script type="application/json" id="ewm-coupon-data-<?php echo esc_attr( $modal_id ); ?>">
<?php echo wp_json_encode( $coupon_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?>
</script>

<?php
// Agregar estilos inline si es necesario
$inline_styles = apply_filters( 'ewm_coupon_inline_styles', '', $modal_id, $settings );
if ( ! empty( $inline_styles ) ) :
	?>
<style type="text/css">
	<?php echo wp_kses( $inline_styles, array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS content is sanitized with wp_kses ?>
</style>
<?php endif; ?>

<?php
// Schema.org markup para SEO
if ( ! empty( $coupons ) ) :
	?>
<script type="application/ld+json">
{
	"@context": "https://schema.org",
	"@type": "OfferCatalog",
	"name": "<?php echo esc_js( __( 'Cupones Disponibles', 'ewm-modal-cta' ) ); ?>",
	"numberOfItems": <?php echo count( $coupons ); ?>,
	"itemListElement": [
	<?php foreach ( $coupons as $index => $coupon ) : ?>
	{
		"@type": "Offer",
		"name": "<?php echo esc_js( $coupon['code'] ); ?>",
		"description": "<?php echo esc_js( $coupon['description'] ?? '' ); ?>",
		"priceSpecification": {
		"@type": "UnitPriceSpecification",
		"priceCurrency": "<?php echo esc_js( get_woocommerce_currency() ); ?>"
		}
		<?php if ( ! empty( $coupon['expires'] ) ) : ?>
		,"validThrough": "<?php echo esc_js( gmdate( 'c', strtotime( $coupon['expires'] ) ) ); ?>"
		<?php endif; ?>
	}<?php echo $index < count( $coupons ) - 1 ? ',' : ''; ?>
	<?php endforeach; ?>
	]
}
</script>
<?php endif; ?>
