/**
 * EWM WooCommerce Builder Integration
 * Maneja la funcionalidad específica de WooCommerce en el builder
 */
(function($) {
    'use strict';

    /**
     * Clase para manejar la integración WooCommerce en el builder
     */
    class EWMWCBuilderIntegration {
        constructor() {
            this.coupons = [];
            this.selectedCoupon = null;
            this.init();
        }

        /**
         * Inicializar funcionalidad
         */
        init() {
            console.log('EWM WC Builder: Initializing...');
            
            this.bindEvents();
            this.loadCoupons();
            
            console.log('EWM WC Builder: Initialized');
        }

        /**
         * Bind eventos del builder
         */
        bindEvents() {
            // Toggle de integración WooCommerce
            $('#wc-integration-enabled').on('change', (e) => {
                this.toggleWCIntegration(e.target.checked);
            });

            // Selector de cupón
            $('#wc-coupon-select').on('change', (e) => {
                this.selectCoupon(e.target.value);
            });

            // Timer enabled toggle
            $('#wc-timer-enabled').on('change', (e) => {
                this.toggleTimerSettings(e.target.checked);
            });

            // Validación de campos
            $('#wc-timer-threshold').on('input', (e) => {
                this.validateTimerThreshold(e.target);
            });

            // Auto-completar título y descripción basado en cupón
            $('#wc-coupon-select').on('change', () => {
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
                this.loadCoupons(); // Cargar cupones cuando se habilita
            } else {
                $settings.slideUp(300);
                this.clearCouponSelection();
            }
        }

        /**
         * Cargar cupones disponibles
         */
        async loadCoupons() {
            console.log('EWM WC Builder: Loading coupons...');

            // Verificar que ewmModal esté disponible
            if (typeof ewmModal === 'undefined') {
                console.error('EWM WC Builder: ewmModal is not defined');
                this.showCouponError('Error de configuración: variables no disponibles');
                return;
            }

            try {
                // Usar AJAX en lugar de REST API para evitar problemas de nonce en admin
                console.log('EWM WC Builder: Using AJAX method (not REST API)');
                console.log('EWM WC Builder: AJAX URL:', ewmModal.ajaxUrl);

                const response = await $.ajax({
                    url: ewmModal.ajaxUrl,
                    method: 'POST',
                    data: {
                        action: 'ewm_get_wc_coupons_enhanced',
                        nonce: ewmModal.nonce,
                        only_valid: false,
                        include_restrictions: true,
                        include_products: true
                    }
                });

                if (response.success) {
                    this.coupons = response.data.coupons || [];
                    this.populateCouponSelect();
                    console.log('EWM WC Builder: Loaded', this.coupons.length, 'coupons');
                } else {
                    console.warn('EWM WC Builder: Failed to load coupons:', response.data);
                    this.showCouponError('Error al cargar cupones: ' + (response.data || 'Error desconocido'));
                }
            } catch (error) {
                console.error('EWM WC Builder: Error loading coupons:', error);
                this.showCouponError('Error de conexión al cargar cupones');
            }
        }

        /**
         * Poblar el selector de cupones
         */
        populateCouponSelect() {
            const $select = $('#wc-coupon-select');
            const currentValue = $select.val();

            // Limpiar opciones existentes (excepto la primera)
            $select.find('option:not(:first)').remove();

            // Agregar cupones
            this.coupons.forEach(coupon => {
                const option = $('<option></option>')
                    .attr('value', coupon.code)
                    .text(`${coupon.code} - ${coupon.modal_info.display_text}`)
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
                this.showCouponPreview(this.selectedCoupon);
                console.log('EWM WC Builder: Selected coupon:', couponCode);
            } else {
                console.warn('EWM WC Builder: Coupon not found:', couponCode);
                this.clearCouponSelection();
            }
        }

        /**
         * Mostrar preview del cupón
         */
        showCouponPreview(coupon) {
            const $preview = $('#wc-coupon-preview');
            
            // Actualizar información del preview
            $('#preview-coupon-code').text(coupon.code);
            $('#preview-coupon-description').text(coupon.modal_info.display_text);
            
            // Mostrar restricciones si existen
            const restrictions = this.formatCouponRestrictions(coupon);
            $('#preview-coupon-restrictions').text(restrictions);
            
            $preview.slideDown(300);
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
            $('#wc-coupon-preview').slideUp(300);
        }

        /**
         * Auto-completar campos de promoción basado en cupón
         */
        autoFillPromotionFields() {
            if (!this.selectedCoupon) return;

            const coupon = this.selectedCoupon;
            
            // Auto-completar título si está vacío
            const $title = $('#wc-promotion-title');
            if (!$title.val().trim()) {
                $title.val(`Oferta Especial: ${coupon.modal_info.display_text}`);
            }
            
            // Auto-completar descripción si está vacía
            const $description = $('#wc-promotion-description');
            if (!$description.val().trim()) {
                let description = `Aprovecha esta oferta de ${coupon.modal_info.display_text}`;
                
                if (coupon.restrictions && coupon.restrictions.minimum_amount) {
                    description += ` en compras desde ${coupon.restrictions.minimum_amount.formatted}`;
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
        // Solo inicializar si estamos en la página del builder y ewmModal está disponible
        if ($('#wc-integration-enabled').length > 0) {
            if (typeof ewmModal !== 'undefined') {
                window.EWMWCBuilderIntegration = new EWMWCBuilderIntegration();
            } else {
                console.warn('EWM WC Builder: ewmModal not available, WooCommerce integration disabled');
            }
        }
    });

})(jQuery);
