/**
 * EWM WooCommerce Builder Integration
 * Handles WooCommerce-specific functionality in the builder
 */
(function($) {
    'use strict';

    /**
     * Class to handle WooCommerce integration in the builder
     */
    class EWMWCBuilderIntegration {
        constructor() {
            this.coupons = [];
            this.selectedCoupon = null;
            this.init();
        }

        /**
         * Initialize functionality
         */
        init() {
            
            this.bindEvents();
            this.loadCoupons();
            
        }

        /**
         * Bind builder events
         */
        bindEvents() {
            // Toggle WooCommerce integration
            $('#wc-integration-enabled').on('change', (e) => {
                this.toggleWCIntegration(e.target.checked);
            });

            // Coupon selector
            $('#wc-coupon-select').on('change', (e) => {
                this.selectCoupon(e.target.value);
            });

            // Timer enabled toggle
            $('#wc-timer-enabled').on('change', (e) => {
                this.toggleTimerSettings(e.target.checked);
            });

            // Field validation
            $('#wc-timer-threshold').on('input', (e) => {
                this.validateTimerThreshold(e.target);
            });

            // Auto-fill title and description based on coupon
            $('#wc-coupon-select').on('change', () => {
                this.autoFillPromotionFields();
            });

            // Button to auto-fill fields
            $(document).on('click', '#wc-auto-fill-fields', () => {
                this.autoFillPromotionFields();
            });
        }

        /**
         * Toggle de la integración WooCommerce
         */
        toggleWCIntegration(enabled) {
            const $settings = $('#wc-integration-settings');
            
            if (enabled) {
                $settings.slideDown(300);
                this.loadCoupons(); // Load coupons when enabled
            } else {
                $settings.slideUp(300);
                this.clearCouponSelection();
            }
        }

        /**
         * Cargar cupones disponibles (versión async/await)
         */
        async loadCouponsAsync() {
            return await this.loadCoupons();
        }

        /**
         * Cargar cupones disponibles
         */
        async loadCoupons(callback) {

            // Verify that ewmModal is available
            if (typeof ewmModal === 'undefined') {
                console.error('EWM WC Builder: ewmModal is not defined');
                this.showCouponError('Configuration error: variables not available');
                return;
            }

            try {
                // Use REST API directly (more efficient and already working)

                const response = await $.ajax({
                    url: ewmModal.restUrl + 'coupons',
                    method: 'GET',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', ewmModal.restNonce);
                    }
                });


                // The REST API endpoint returns the coupons array directly
                this.coupons = response || [];
                this.populateCouponSelect();

                // Execute callback if provided
                if (callback && typeof callback === 'function') {
                    callback();
                }

            } catch (error) {
                console.error('EWM WC Builder: Error loading coupons:', error);
                console.error('EWM WC Builder: Error details:', {
                    status: error.status,
                    statusText: error.statusText,
                    responseText: error.responseText,
                    responseJSON: error.responseJSON
                });
                this.showCouponError('Connection error loading coupons');

                // Actualizar el selector para mostrar el error
                const $select = $('#wc-coupon-select');
                const $firstOption = $select.find('option:first');
                $firstOption.text('Error loading coupons');

                // Ejecutar callback incluso en caso de error
                if (callback && typeof callback === 'function') {
                    callback();
                }
            }
        }

        /**
         * Poblar el selector de cupones
         */
        populateCouponSelect() {
            const $select = $('#wc-coupon-select');
            const currentValue = $select.val();

            // Update first option text to indicate loading completed
            const $firstOption = $select.find('option:first');
            if (this.coupons.length > 0) {
                $firstOption.text('-- Select a coupon --');
            } else {
                $firstOption.text('No coupons available');
            }

            // Limpiar opciones existentes (excepto la primera)
            $select.find('option:not(:first)').remove();

            // Agregar cupones
            this.coupons.forEach(coupon => {
                // Usar la estructura real del cupón del endpoint
                const displayText = coupon.description || `${coupon.discount_type} ${coupon.amount}${coupon.discount_type === 'percent' ? '%' : ''}`;

                const option = $('<option></option>')
                    .attr('value', coupon.code)
                    .text(`${coupon.code} - ${displayText}`)
                    .data('coupon', coupon);

                $select.append(option);
            });

            // Restaurar valor seleccionado si existe
            if (currentValue) {
                $select.val(currentValue);
                this.selectCoupon(currentValue);
            }
        }

        /**
         * Seleccionar un cupón
         */
        selectCoupon(couponCode) {
            if (!couponCode) {
                this.clearCouponSelection();
                return;
            }

            this.selectedCoupon = this.coupons.find(c => c.code === couponCode);

            if (this.selectedCoupon) {
                this.showCouponDetails(this.selectedCoupon);
            } else {
                console.warn('EWM WC Builder: Coupon not found:', couponCode);
                this.clearCouponSelection();
            }
        }

        /**
         * Mostrar detalles del cupón en el panel
         */
        showCouponDetails(coupon) {
            const $details = $('#wc-coupon-details');

            // Formatear tipo de descuento
            const discountTypeText = coupon.discount_type === 'percent' ? 'Porcentaje' :
                                   coupon.discount_type === 'fixed_cart' ? 'Cantidad fija del carrito' :
                                   coupon.discount_type === 'fixed_product' ? 'Cantidad fija del producto' :
                                   coupon.discount_type;

            // Formatear cantidad
            const amountText = coupon.discount_type === 'percent' ?
                              `${coupon.amount}%` :
                              `$${coupon.amount}`;

            // Formatear fecha de expiración
            const expiresText = coupon.date_expires ?
                               new Date(coupon.date_expires).toLocaleDateString('es-ES') :
                               'No expiration';

            // Formatear límite de uso
            const usageLimitText = coupon.usage_limit > 0 ?
                                  coupon.usage_limit :
                                  'No limit';

            // Formatear monto mínimo
            const minimumText = coupon.minimum_amount && coupon.minimum_amount !== '' ?
                               `$${coupon.minimum_amount}` :
                               'No minimum';

            // Actualizar los campos del panel
            $('#coupon-detail-code').text(coupon.code);
            $('#coupon-detail-type').text(discountTypeText);
            $('#coupon-detail-amount').text(amountText);
            $('#coupon-detail-description').text(coupon.description || 'No description');
            $('#coupon-detail-minimum').text(minimumText);
            $('#coupon-detail-expires').text(expiresText);
            $('#coupon-detail-usage-limit').text(usageLimitText);
            $('#coupon-detail-usage-count').text(coupon.usage_count || 0);

            // Mostrar el panel
            $details.slideDown(300);
        }

        /**
         * Formatear restricciones del cupón
         */
        formatCouponRestrictions(coupon) {
            const restrictions = [];
            
            if (coupon.restrictions) {
                if (coupon.restrictions.minimum_amount) {
                    restrictions.push(`Compra mínima: ${coupon.restrictions.minimum_amount.formatted}`);
                }
                
                if (coupon.restrictions.maximum_amount) {
                    restrictions.push(`Compra máxima: ${coupon.restrictions.maximum_amount.formatted}`);
                }
                
                if (coupon.usage && coupon.usage.limit > 0) {
                    restrictions.push(`Usos restantes: ${coupon.usage.remaining}`);
                }
                
                if (coupon.dates && coupon.dates.expires) {
                    const expireDate = new Date(coupon.dates.expires);
                    restrictions.push(`Expira: ${expireDate.toLocaleDateString()}`);
                }
            }
            
            return restrictions.length > 0 ? restrictions.join(' • ') : 'Sin restricciones especiales';
        }

        /**
         * Limpiar selección de cupón
         */
        clearCouponSelection() {
            this.selectedCoupon = null;
            $('#wc-coupon-details').slideUp(300);
        }

        /**
         * Auto-completar campos de promoción basado en cupón
         */
        autoFillPromotionFields() {
            if (!this.selectedCoupon) return;

            const coupon = this.selectedCoupon;

            // Usar estructura real del cupón
            const displayText = coupon.description || `${coupon.discount_type} ${coupon.amount}${coupon.discount_type === 'percent' ? '%' : ''}`;

            // Auto-completar título si está vacío
            const $title = $('#wc-promotion-title');
            if (!$title.val().trim()) {
                $title.val(`Oferta Especial: ${displayText}`);
            }

            // Auto-completar descripción si está vacía
            const $description = $('#wc-promotion-description');
            if (!$description.val().trim()) {
                let description = `Aprovecha esta oferta de ${displayText}`;

                if (coupon.minimum_amount && coupon.minimum_amount !== '') {
                    description += ` en compras desde $${coupon.minimum_amount}`;
                }
                
                $description.val(description);
            }
        }

        /**
         * Toggle configuración de timer
         */
        toggleTimerSettings(enabled) {
            const $threshold = $('#wc-timer-threshold');
            
            if (enabled) {
                $threshold.prop('disabled', false).closest('.ewm-form-group').show();
            } else {
                $threshold.prop('disabled', true).closest('.ewm-form-group').hide();
            }
        }

        /**
         * Validar threshold del timer
         */
        validateTimerThreshold(input) {
            const value = parseInt(input.value);
            const min = parseInt(input.min);
            const max = parseInt(input.max);
            
            if (value < min) {
                input.value = min;
            } else if (value > max) {
                input.value = max;
            }
        }

        /**
         * Mostrar error de cupones
         */
        showCouponError(message) {
            const $select = $('#wc-coupon-select');
            $select.after(`<div class="ewm-error-message" style="color: #d63638; font-size: 12px; margin-top: 5px;">${message}</div>`);
            
            // Remover mensaje después de 5 segundos
            setTimeout(() => {
                $('.ewm-error-message').remove();
            }, 5000);
        }

        /**
         * Obtener datos de configuración WooCommerce
         */
        getWCConfigData() {
            const data = {
                enabled: $('#wc-integration-enabled').is(':checked'),
                discount_code: $('#wc-coupon-select').val(),
                wc_promotion: {
                    title: $('#wc-promotion-title').val(),
                    description: $('#wc-promotion-description').val(),
                    cta_text: $('#wc-promotion-cta').val(),
                    timer_config: {
                        enabled: $('#wc-timer-enabled').is(':checked'),
                        threshold_seconds: parseInt($('#wc-timer-threshold').val()) || 180
                    },
                    auto_apply: $('#wc-auto-apply').is(':checked'),
                    show_restrictions: $('#wc-show-restrictions').is(':checked')
                }
            };

            return data;
        }
    }

    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        // Solo inicializar si estamos en la página del builder
        if ($('#wc-integration-enabled').length > 0 || $('.ewm-modal-builder').length > 0) {
            
            // Verificar ewmModal con retry para casos de carga asíncrona
            const initWithRetry = (attempts = 0) => {
                if (typeof ewmModal !== 'undefined') {
                    window.EWMWCBuilderIntegration = new EWMWCBuilderIntegration();

                    // Inicializar estado basado en checkbox actual si existe
                    const $checkbox = $('#wc-integration-enabled');
                    if ($checkbox.length > 0 && $checkbox.is(':checked')) {
                        $('#wc-integration-settings').show();
                        window.EWMWCBuilderIntegration.loadCoupons();
                    }
                } else if (attempts < 20) { // 2 segundos máximo
                    setTimeout(() => initWithRetry(attempts + 1), 100);
                } else {
                    console.warn('EWM WC Builder: ewmModal not available after retries, WooCommerce integration disabled');
                }
            };
            
            initWithRetry();
        } else {
        }
    });

    // Exponer la clase globalmente para uso en otros módulos
    window.EWMWCBuilderIntegration = EWMWCBuilderIntegration;

})(jQuery);
