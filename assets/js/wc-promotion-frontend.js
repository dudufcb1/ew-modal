/**
 * EWM WooCommerce Promotion Frontend
 * Maneja la funcionalidad específica para modales de promoción WooCommerce
 */
(function() {
    'use strict';

    /**
     * Clase para manejar modales de promoción WooCommerce
     */
    class EWMWCPromotion {
        constructor() {
            this.timers = new Map();
            this.init();
        }

        /**
         * Inicializar funcionalidad
         */
        init() {
            console.log('EWM WC Promotion: Initializing...');
            
            // Detectar si estamos en una página de producto
            this.detectProductPage();
            
            // Bind eventos para botones de cupones
            this.bindCouponEvents();
            
            console.log('EWM WC Promotion: Initialized');
        }

        /**
         * Detectar si estamos en una página de producto WooCommerce
         */
        detectProductPage() {
            // Verificar si estamos en una página de producto
            const productId = this.getProductId();
            
            if (productId) {
                console.log('EWM WC Promotion: Product page detected, ID:', productId);
                this.startProductTimer(productId);
            }
        }

        /**
         * Obtener ID del producto actual
         */
        getProductId() {
            // Método 1: Buscar en el body class
            const bodyClasses = document.body.className;
            const productMatch = bodyClasses.match(/postid-(\d+)/);
            
            if (productMatch && document.body.classList.contains('single-product')) {
                return parseInt(productMatch[1]);
            }

            // Método 2: Buscar en datos del producto (WooCommerce estándar)
            const productForm = document.querySelector('form.cart');
            if (productForm) {
                const productIdInput = productForm.querySelector('input[name="add-to-cart"]');
                if (productIdInput && productIdInput.value) {
                    return parseInt(productIdInput.value);
                }
            }

            // Método 3: Buscar en variables globales de WooCommerce
            if (typeof wc_add_to_cart_params !== 'undefined' && wc_add_to_cart_params.product_id) {
                return parseInt(wc_add_to_cart_params.product_id);
            }

            return null;
        }

        /**
         * Iniciar timer para producto específico
         */
        startProductTimer(productId) {
            // Verificar si ya hay un timer para este producto
            if (this.timers.has(productId)) {
                return;
            }

            console.log('EWM WC Promotion: Starting timer for product', productId);

            // Consultar modales disponibles para este producto
            this.checkProductModals(productId).then(modals => {
                if (modals && modals.length > 0) {
                    // Configurar timer para cada modal
                    modals.forEach(modalConfig => {
                        this.setupModalTimer(productId, modalConfig);
                    });
                }
            }).catch(error => {
                console.error('EWM WC Promotion: Error checking product modals:', error);
            });
        }

        /**
         * Configurar timer para un modal específico
         */
        setupModalTimer(productId, modalConfig) {
            const threshold = modalConfig.timer_config?.threshold_seconds || 180;
            const modalId = modalConfig.modal_id;

            console.log(`EWM WC Promotion: Setting up timer for modal ${modalId}, threshold: ${threshold}s`);

            const timerId = setTimeout(() => {
                console.log(`EWM WC Promotion: Timer triggered for modal ${modalId}`);
                this.showPromotionModal(modalId, modalConfig);
            }, threshold * 1000);

            // Guardar referencia del timer
            this.timers.set(`${productId}-${modalId}`, timerId);
        }

        /**
         * Consultar modales disponibles para un producto
         */
        async checkProductModals(productId) {
            try {
                const response = await fetch(`${ewmModal.restUrl}wc-products/${productId}/modals`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                        // No necesita nonce porque el endpoint es público
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    console.log('EWM WC Promotion: Product modals response:', data);
                    return data.modals || [];
                } else {
                    console.warn('EWM WC Promotion: Failed to fetch product modals, status:', response.status);
                    return [];
                }
            } catch (error) {
                console.error('EWM WC Promotion: Error fetching product modals:', error);
                return [];
            }
        }

        /**
         * Mostrar modal de promoción (con verificación de cupones aplicados)
         */
        async showPromotionModal(modalId, config) {
            console.log(`EWM WC Promotion: Attempting to show modal ${modalId}`);

            // NUEVA VERIFICACIÓN: Consultar si el modal debe mostrarse
            const productId = this.getProductId();
            if (productId) {
                const shouldShow = await this.checkModalVisibility(modalId, productId);
                if (!shouldShow.should_show) {
                    console.log(`EWM WC Promotion: Modal ${modalId} blocked:`, shouldShow.reason);
                    return;
                }
                console.log(`EWM WC Promotion: Modal ${modalId} approved for display`);
            }

            // Buscar el modal en el DOM
            const modalElement = document.getElementById(`ewm-modal-${modalId}`);

            if (modalElement) {
                // Usar el sistema existente de EWM Modal
                if (window.EWMModal) {
                    const modalInstance = new EWMModal(modalElement, config);
                    modalInstance.show();
                } else {
                    // Fallback: mostrar directamente
                    modalElement.style.display = 'flex';
                    modalElement.classList.add('ewm-modal-visible');
                }
                console.log(`EWM WC Promotion: Modal ${modalId} displayed successfully`);
            } else {
                console.warn('EWM WC Promotion: Modal element not found:', modalId);
            }
        }

        /**
         * Bind eventos para botones de cupones
         */
        bindCouponEvents() {
            // Aplicar cupón
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('ewm-apply-coupon')) {
                    e.preventDefault();
                    const couponCode = e.target.dataset.coupon;
                    this.applyCoupon(couponCode, e.target);
                }
            });

            // Copiar cupón
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('ewm-copy-coupon')) {
                    e.preventDefault();
                    const couponCode = e.target.dataset.coupon;
                    this.copyCoupon(couponCode, e.target);
                }
            });
        }

        /**
         * Verificar si un modal debe mostrarse consultando el endpoint de verificación
         */
        async checkModalVisibility(modalId, productId) {
            try {
                console.log(`EWM WC Promotion: Checking visibility for modal ${modalId}, product ${productId}`);

                const response = await fetch(`${ewmModal.restUrl}test-modal-visibility/${modalId}/${productId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    console.log('EWM WC Promotion: Visibility check response:', data);

                    return {
                        should_show: data.result === 'will show',
                        reason: data.reason || 'unknown',
                        modal_id: data.modal_id,
                        product_id: data.product_id
                    };
                } else {
                    console.warn('EWM WC Promotion: Visibility check failed, status:', response.status);
                    // En caso de error, permitir mostrar el modal (comportamiento por defecto)
                    return {
                        should_show: true,
                        reason: 'visibility check failed, allowing display'
                    };
                }
            } catch (error) {
                console.error('EWM WC Promotion: Error checking modal visibility:', error);
                // En caso de error, permitir mostrar el modal (comportamiento por defecto)
                return {
                    should_show: true,
                    reason: 'visibility check error, allowing display'
                };
            }
        }

        /**
         * Aplicar cupón al carrito
         */
        async applyCoupon(couponCode, buttonElement) {
            if (!couponCode) return;

            console.log('EWM WC Promotion: Applying coupon:', couponCode);

            // Mostrar estado de carga
            const originalText = buttonElement.textContent;
            buttonElement.textContent = 'Applying...';
            buttonElement.disabled = true;

            try {
                const response = await fetch(ewmModal.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'ewm_apply_coupon',
                        coupon_code: couponCode,
                        nonce: ewmModal.nonce
                    })
                });

                const data = await response.json();

                if (data.success) {
                    buttonElement.textContent = '✓ Applied';
                    buttonElement.classList.add('ewm-coupon-applied');
                    
                    // Mostrar mensaje de éxito
                    this.showMessage('Coupon applied successfully', 'success');
                    
                    // Opcional: redirigir al carrito
                    setTimeout(() => {
                        if (data.data.redirect_url) {
                            window.location.href = data.data.redirect_url;
                        }
                    }, 1500);
                } else {
                    throw new Error(data.data || 'Error applying coupon');
                }
            } catch (error) {
                console.error('EWM WC Promotion: Error applying coupon:', error);
                buttonElement.textContent = originalText;
                this.showMessage('Error applying coupon: ' + error.message, 'error');
            } finally {
                buttonElement.disabled = false;
            }
        }

        /**
         * Copiar código de cupón al portapapeles
         */
        async copyCoupon(couponCode, buttonElement) {
            if (!couponCode) return;

            try {
                await navigator.clipboard.writeText(couponCode);
                
                const originalText = buttonElement.textContent;
                buttonElement.textContent = '✓ Copied';
                
                setTimeout(() => {
                    buttonElement.textContent = originalText;
                }, 2000);
                
                this.showMessage('Code copied to clipboard', 'success');
            } catch (error) {
                console.error('EWM WC Promotion: Error copying coupon:', error);
                this.showMessage('Error copying code', 'error');
            }
        }

        /**
         * Mostrar mensaje temporal
         */
        showMessage(message, type = 'info') {
            const messageDiv = document.createElement('div');
            messageDiv.className = `ewm-message ewm-message-${type}`;
            messageDiv.textContent = message;
            messageDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#2196F3'};
                color: white;
                padding: 12px 20px;
                border-radius: 4px;
                z-index: 10000;
                font-family: Arial, sans-serif;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            `;

            document.body.appendChild(messageDiv);

            setTimeout(() => {
                messageDiv.remove();
            }, 3000);
        }

        /**
         * Limpiar timers
         */
        clearTimers() {
            this.timers.forEach(timerId => clearTimeout(timerId));
            this.timers.clear();
        }
    }

    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.EWMWCPromotion = new EWMWCPromotion();
        });
    } else {
        window.EWMWCPromotion = new EWMWCPromotion();
    }

})();
