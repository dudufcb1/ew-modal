/**
 * JavaScript para funcionalidad específica de modales WooCommerce
 * 
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

(function() {
    'use strict';
    
    console.log('[EWM WooCommerce] Initializing WooCommerce modal functionality');
    
    // Inicializar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        initWooCommerceModals();
    });
    
    /**
     * Inicializar funcionalidad de modales WooCommerce
     */
    function initWooCommerceModals() {
        // Buscar modales WooCommerce
        const wcModals = document.querySelectorAll('.ewm-woocommerce-content');
        
        wcModals.forEach(modal => {
            initCopyButtons(modal);
            initCTAButtons(modal);
            initTimers(modal);
        });
        
        console.log('[EWM WooCommerce] Initialized', wcModals.length, 'WooCommerce modals');
    }
    
    /**
     * Inicializar botones de copiar cupón
     */
    function initCopyButtons(modal) {
        const copyButtons = modal.querySelectorAll('.ewm-copy-coupon');
        
        copyButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const couponCode = this.getAttribute('data-coupon');
                if (!couponCode) return;
                
                copyToClipboard(couponCode, this);
            });
        });
    }
    
    /**
     * Copiar texto al portapapeles
     */
    function copyToClipboard(text, button) {
        // Método moderno
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                showCopyFeedback(button, 'Copiado!');
            }).catch(err => {
                console.error('[EWM WooCommerce] Error copying to clipboard:', err);
                fallbackCopyToClipboard(text, button);
            });
        } else {
            // Fallback para navegadores antiguos
            fallbackCopyToClipboard(text, button);
        }
    }
    
    /**
     * Método fallback para copiar al portapapeles
     */
    function fallbackCopyToClipboard(text, button) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            showCopyFeedback(button, 'Copiado!');
        } catch (err) {
            console.error('[EWM WooCommerce] Fallback copy failed:', err);
            showCopyFeedback(button, 'Error al copiar');
        }
        
        document.body.removeChild(textArea);
    }
    
    /**
     * Mostrar feedback visual al copiar
     */
    function showCopyFeedback(button, message) {
        // Cambiar texto del botón temporalmente
        const originalText = button.textContent;
        button.textContent = message;
        button.classList.add('copied');
        
        // Crear indicador flotante
        const feedback = document.createElement('div');
        feedback.className = 'ewm-copy-feedback';
        feedback.textContent = message;
        
        const buttonRect = button.getBoundingClientRect();
        feedback.style.position = 'fixed';
        feedback.style.left = buttonRect.left + (buttonRect.width / 2) + 'px';
        feedback.style.top = (buttonRect.top - 35) + 'px';
        feedback.style.transform = 'translateX(-50%)';
        
        document.body.appendChild(feedback);
        
        // Restaurar después de 2 segundos
        setTimeout(() => {
            button.textContent = originalText;
            button.classList.remove('copied');
            if (feedback.parentNode) {
                feedback.parentNode.removeChild(feedback);
            }
        }, 2000);
    }
    
    /**
     * Inicializar botones CTA
     */
    function initCTAButtons(modal) {
        const ctaButtons = modal.querySelectorAll('.ewm-wc-cta-button');
        
        ctaButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const action = this.getAttribute('data-action');
                const couponCode = this.getAttribute('data-coupon');
                
                if (action === 'apply-coupon' && couponCode) {
                    applyCoupon(couponCode, this);
                }
            });
        });
    }
    
    /**
     * Aplicar cupón automáticamente
     */
    function applyCoupon(couponCode, button) {
        console.log('[EWM WooCommerce] Applying coupon:', couponCode);
        
        // Cambiar estado del botón
        const originalText = button.textContent;
        button.textContent = 'Aplicando...';
        button.disabled = true;
        
        // Si WooCommerce está disponible, intentar aplicar el cupón
        if (typeof wc_checkout_params !== 'undefined' || typeof woocommerce_params !== 'undefined') {
            // Redirigir al carrito con el cupón
            const cartUrl = getCartUrl();
            if (cartUrl) {
                window.location.href = cartUrl + '?coupon_code=' + encodeURIComponent(couponCode);
                return;
            }
        }
        
        // Fallback: copiar cupón y mostrar instrucciones
        copyToClipboard(couponCode, button);
        
        setTimeout(() => {
            button.textContent = 'Cupón copiado - Ve al carrito';
            button.style.background = '#28a745';
        }, 1000);
        
        setTimeout(() => {
            button.textContent = originalText;
            button.disabled = false;
            button.style.background = '';
        }, 4000);
    }
    
    /**
     * Obtener URL del carrito
     */
    function getCartUrl() {
        // Intentar obtener URL del carrito de WooCommerce
        if (typeof woocommerce_params !== 'undefined' && woocommerce_params.cart_url) {
            return woocommerce_params.cart_url;
        }
        
        // Fallback: buscar enlace al carrito en la página
        const cartLink = document.querySelector('a[href*="cart"]');
        if (cartLink) {
            return cartLink.href;
        }
        
        return null;
    }
    
    /**
     * Inicializar timers de cuenta regresiva
     */
    function initTimers(modal) {
        const timers = modal.querySelectorAll('.ewm-wc-timer');
        
        timers.forEach(timer => {
            const threshold = parseInt(timer.getAttribute('data-threshold')) || 180;
            startCountdown(timer, threshold);
        });
    }
    
    /**
     * Iniciar cuenta regresiva
     */
    function startCountdown(timerElement, seconds) {
        const minutesSpan = timerElement.querySelector('.ewm-timer-minutes');
        const secondsSpan = timerElement.querySelector('.ewm-timer-seconds');
        
        if (!minutesSpan || !secondsSpan) return;
        
        let timeLeft = seconds;
        
        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const secs = timeLeft % 60;
            
            minutesSpan.textContent = minutes.toString().padStart(2, '0');
            secondsSpan.textContent = secs.toString().padStart(2, '0');
            
            // Agregar clase urgente cuando queden menos de 30 segundos
            if (timeLeft <= 30) {
                timerElement.classList.add('urgent');
            }
            
            if (timeLeft <= 0) {
                // Timer terminado
                timerElement.textContent = '¡Tiempo agotado!';
                timerElement.classList.add('expired');
                return;
            }
            
            timeLeft--;
            setTimeout(updateTimer, 1000);
        }
        
        updateTimer();
    }
    
    /**
     * Utilidad para detectar si es un modal WooCommerce
     */
    function isWooCommerceModal(modal) {
        return modal.querySelector('.ewm-woocommerce-content') !== null;
    }
    
    // Exponer funciones globalmente para uso externo
    window.EWMWooCommerce = {
        initWooCommerceModals: initWooCommerceModals,
        copyToClipboard: copyToClipboard,
        applyCoupon: applyCoupon,
        isWooCommerceModal: isWooCommerceModal
    };
    
})();
