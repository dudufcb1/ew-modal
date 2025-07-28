Comos e podría aplicar un cupon al cart? de manera programática, es que tengo un modal donde muestro cupones disponibles y quiero aplicarlo desde el producto
Fast
Searched across woocommerce/woocommerce
Go deeper
Para aplicar un cupón al carrito de manera programática en WooCommerce, puedes usar el método apply_coupon() de la clase WC_Cart. class-wc-cart.php:1766

Método Principal
El método apply_coupon($coupon_code) es la función principal que necesitas usar: class-wc-cart.php:1766-1849

Este método realiza las siguientes validaciones automáticamente:

Verifica que los cupones estén habilitados globalmente class-wc-cart.php:1768-1770
Sanitiza el código del cupón class-wc-cart.php:1773
Valida que el cupón existe y es válido class-wc-cart.php:1786-1789
Verifica que no esté ya aplicado class-wc-cart.php:1792-1795
Ejemplo de Uso
// Obtener la instancia del carrito  
$cart = WC()->cart;  
  
// Aplicar el cupón  
$result = $cart->apply_coupon('CODIGO_CUPON');  
  
if ($result) {  
    // Cupón aplicado exitosamente  
    // Recalcular totales del carrito  
    $cart->calculate_totals();  
} else {  
    // Error al aplicar el cupón  
    // Los mensajes de error se manejan automáticamente  
}
Gestión de Cupones Individuales
Si el cupón tiene la configuración de "uso individual", el método automáticamente removerá otros cupones aplicados: class-wc-cart.php:1798-1813

Verificar Cupones Aplicados
Puedes verificar si un cupón ya está aplicado usando: class-wc-cart.php:1747-1758

Obtener Cupones Aplicados
Para obtener la lista de cupones aplicados: class-wc-cart.php:173-175

Notes
El sistema de cupones en WooCommerce también incluye validaciones adicionales para restricciones de email y límites de uso que se ejecutan durante el checkout. OrderController.php:257-300 Estas validaciones son manejadas automáticamente por el sistema, pero es importante tener en cuenta que algunos cupones pueden ser removidos durante el proceso de checkout si no cumplen con las restricciones específicas.

Wiki pages you might want to explore:

WooCommerce Monorepo Overview (woocommerce/woocommerce)

Copy Response

Share

woocommerce/woocommerce
plugins/woocommerce/includes/class-wc-cart.php


	 *
	 * @return array of applied coupons
	 */
	public function get_applied_coupons() {
		return (array) $this->applied_coupons;
	}

	/**
	 * Return all calculated coupon totals.


	 * @param string $coupon_code Coupon code to check.
	 * @return bool
	 */
	public function has_discount( $coupon_code = '' ) {
		return $coupon_code ? in_array(
			wc_strtolower( wc_format_coupon_code( $coupon_code ) ),
			array_map(
				function ( $code ) {
					return wc_strtolower( wc_format_coupon_code( $code ) );
				},
				$this->applied_coupons
			),
			true
		) : count( $this->applied_coupons ) > 0;
	}

	/**
	 * Applies a coupon code passed to the method.

	 * @param string $coupon_code - The code to apply.
	 * @return bool True if the coupon is applied, false if it does not exist or cannot be applied.
	 */
	public function apply_coupon( $coupon_code ) {
		// Coupons are globally disabled.
		if ( ! wc_coupons_enabled() ) {
			return false;
		}

		// Sanitize coupon code.
		$coupon_code = wc_format_coupon_code( $coupon_code );

		// Get the coupon.
		$the_coupon = new WC_Coupon( $coupon_code );

		// Prevent adding coupons by post ID.
		if ( ! wc_is_same_coupon( $the_coupon->get_code(), $coupon_code ) ) {
			$the_coupon->set_code( $coupon_code );
			$the_coupon->add_coupon_message( WC_Coupon::E_WC_COUPON_NOT_EXIST );
			return false;
		}

		// Check it can be used with cart.
		if ( ! $the_coupon->is_valid() ) {
			wc_add_notice( $the_coupon->get_error_message(), 'error' );
			return false;
		}

		// Check if applied.
		if ( $this->has_discount( $coupon_code ) ) {
			$the_coupon->add_coupon_message( WC_Coupon::E_WC_COUPON_ALREADY_APPLIED );
			return false;
		}

		// If its individual use then remove other coupons.
		if ( $the_coupon->get_individual_use() ) {
			$coupons_to_keep = apply_filters( 'woocommerce_apply_individual_use_coupon', array(), $the_coupon, $this->applied_coupons );

			foreach ( $this->applied_coupons as $applied_coupon ) {
				$keep_key = array_search( $applied_coupon, $coupons_to_keep, true );
				if ( false === $keep_key ) {
					$this->remove_coupon( $applied_coupon );
				} else {
					unset( $coupons_to_keep[ $keep_key ] );
				}
			}

			if ( ! empty( $coupons_to_keep ) ) {
				$this->applied_coupons += $coupons_to_keep;
			}
		}

		// Check to see if an individual use coupon is set.
		if ( $this->applied_coupons ) {
			foreach ( $this->applied_coupons as $code ) {
				$coupon = new WC_Coupon( $code );

				if ( $coupon->get_individual_use() && false === apply_filters( 'woocommerce_apply_with_individual_use_coupon', false, $the_coupon, $coupon, $this->applied_coupons ) ) {

					// Reject new coupon.
					$coupon->add_coupon_message( WC_Coupon::E_WC_COUPON_ALREADY_APPLIED_INDIV_USE_ONLY );

					return false;
				}
			}
		}

		$this->applied_coupons[] = $coupon_code;

		// Choose free shipping.
		if ( $the_coupon->get_free_shipping() ) {
			$packages                = WC()->shipping()->get_packages();
			$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

			foreach ( $packages as $i => $package ) {
				$chosen_shipping_methods[ $i ] = 'free_shipping';
			}

			WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
		}

		$the_coupon->add_coupon_message( WC_Coupon::WC_COUPON_SUCCESS );

		do_action( 'woocommerce_applied_coupon', $coupon_code );

		return true;
	}

	/**
	 * Get array of applied coupon objects and codes.


woocommerce/woocommerce
plugins/woocommerce/src/StoreApi/Utilities/OrderController.php


	 * @param \WC_Order $order Order object.
	 * @param bool      $use_order_data Whether to use order data or cart data.
	 */
	protected function validate_coupons( \WC_Order $order, bool $use_order_data = false ) {
		$coupon_codes  = $order->get_coupon_codes();
		$coupons       = array_filter( array_map( array( $this, 'get_coupon' ), $coupon_codes ) );
		$validators    = array( 'validate_coupon_email_restriction', 'validate_coupon_usage_limit' );
		$coupon_errors = array();

		foreach ( $coupons as $coupon ) {
			try {
				array_walk(
					$validators,
					function ( $validator, $index, $params ) {
						call_user_func_array( array( $this, $validator ), $params );
					},
					array( $coupon, $order )
				);
			} catch ( Exception $error ) {
				$coupon_errors[ $coupon->get_code() ] = $error->getMessage();
			}
		}

		if ( $coupon_errors ) {
			// Remove all coupons that were not valid.
			if ( $use_order_data ) {
				$error_code = 'woocommerce_rest_order_coupon_errors';

				foreach ( $coupon_errors as $coupon_code => $message ) {
					$order->remove_coupon( $coupon_code );
				}

				// Recalculate totals.
				$order->calculate_totals();
			} else {
				$error_code = 'woocommerce_rest_cart_coupon_errors';

				foreach ( $coupon_errors as $coupon_code => $message ) {
					wc()->cart->remove_coupon( $coupon_code );
				}

				// Recalculate totals.
				wc()->cart->calculate_totals();

				// Re-sync order with cart.
				$this->update_order_from_cart( $order );
			}

			// Return exception so customer can review before payment.
			if ( 1 === count( $coupon_errors ) && $use_order_data ) {

return to woocommerce/woocommerce
ok, entonces hay hooks o funciones para manejar todos los estados, no? disponibles, aplicados, etc
Fast
Searched across woocommerce/woocommerce
Sí, WooCommerce proporciona varios hooks y funciones para manejar todos los estados de los cupones.

Hooks Principales para Estados de Cupones
Cuando se Aplica un Cupón
El hook woocommerce_applied_coupon se ejecuta cuando un cupón se aplica exitosamente: class-wc-cart.php:1846

Cuando se Remueve un Cupón
El hook woocommerce_removed_coupon se ejecuta cuando se remueve un cupón: class-wc-cart.php:1938

Recálculo Automático de Totales
El carrito automáticamente recalcula totales cuando se aplican o remueven cupones: class-wc-cart.php:119-121

Funciones para Verificar Estados
Verificar si un Cupón está Aplicado
La función has_discount() permite verificar si un cupón específico está aplicado o si hay algún cupón aplicado: class-wc-cart.php:1747-1758

Obtener Cupones Aplicados
La función get_applied_coupons() devuelve un array con todos los códigos de cupones aplicados: class-wc-cart.php:173-175

Obtener Objetos de Cupones
La función get_coupons() devuelve un array con los objetos WC_Coupon de todos los cupones aplicados: class-wc-cart.php:1857-1870

Validación Automática de Estados
Verificación de Cupones en el Carrito
WooCommerce automáticamente verifica la validez de los cupones aplicados: class-wc-cart.php:738-747

Verificación Durante Checkout
Durante el checkout, se ejecutan validaciones adicionales para restricciones de email y límites de uso: class-wc-cart.php:1672-1719

Ejemplo de Uso de Hooks
// Hook cuando se aplica un cupón  
add_action('woocommerce_applied_coupon', function($coupon_code) {  
    // Tu lógica personalizada  
    error_log("Cupón aplicado: " . $coupon_code);  
});  
  
// Hook cuando se remueve un cupón  
add_action('woocommerce_removed_coupon', function($coupon_code) {  
    // Tu lógica personalizada  
    error_log("Cupón removido: " . $coupon_code);  
});
Notes
El sistema de hooks de WooCommerce permite interceptar y personalizar el comportamiento en cada estado del ciclo de vida de los cupones. Los hooks se ejecutan automáticamente cuando se usan los métodos nativos de WC_Cart, proporcionando puntos de extensión para funcionalidad personalizada sin modificar el código core.

