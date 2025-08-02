/**
 * EWM Form Validator JavaScript
 * Sistema de validación para formularios del modal (Vanilla JS)
 */
(function() {
    'use strict';

    class EWMFormValidator {
        constructor() {
            this.rules = {
                required: this.validateRequired,
                email: this.validateEmail,
                url: this.validateUrl,
                phone: this.validatePhone,
                number: this.validateNumber,
                minLength: this.validateMinLength,
                maxLength: this.validateMaxLength,
                pattern: this.validatePattern
            };
        }

        /**
         * Función debounce para optimizar validaciones en tiempo real
         */
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        /**
         * Validar un campo individual
         */
        validateField(field, rules = {}) {
            // DEBUG 1.3: Inspeccionar el elemento HTML del campo
            console.log('DEBUG: Validating field element:', field);

            const value = this.getFieldValue(field);
            const errors = [];

            // Primero verificar validación HTML5 nativa
            if (field.checkValidity && !field.checkValidity()) {
                // Usar mensaje de validación nativo del navegador si está disponible
                const nativeMessage = field.validationMessage;
                if (nativeMessage) {
                    errors.push(nativeMessage);
                } else {
                    // Fallback a mensaje personalizado basado en el tipo de campo
                    const fieldName = this.getFieldName(field);
                    switch (field.type) {
                        case 'tel':
                            errors.push(`${fieldName} must contain only numbers and valid phone characters`);
                            break;
                        case 'email':
                            errors.push(`${fieldName} must be a valid email`);
                            break;
                        case 'url':
                            errors.push(`${fieldName} must be a valid URL`);
                            break;
                        default:
                            errors.push(`${fieldName} does not have a valid format`);
                    }
                }
                // Si falla la validación nativa, no continuar con validaciones personalizadas
                return {
                    isValid: false,
                    errors: errors,
                    field: field,
                    value: value
                };
            }

            // Si pasa la validación nativa, continuar con validaciones personalizadas
            Object.keys(rules).forEach(ruleName => {
                const ruleValue = rules[ruleName];
                const validator = this.rules[ruleName];

                if (validator && !validator.call(this, value, ruleValue, field)) {
                    errors.push(this.getErrorMessage(ruleName, ruleValue, field));
                }
            });

            return {
                isValid: errors.length === 0,
                errors: errors,
                field: field,
                value: value
            };
        }

        /**
         * Validar un formulario completo con notificaciones centralizadas
         */
        validateForm(form) {
            const fields = form.querySelectorAll('input, select, textarea');
            const results = [];
            let isFormValid = true;

            // Limpiar notificaciones anteriores
            this.clearNotifications();

            fields.forEach(field => {
                const rules = this.getFieldRules(field);
                const result = this.validateField(field, rules);
                
                results.push(result);
                
                if (!result.isValid) {
                    isFormValid = false;
                    field.classList.add('ewm-error');
                } else {
                    field.classList.remove('ewm-error');
                }
            });

            // Mostrar notificación centralizada si hay errores
            if (!isFormValid) {
                const errors = results.filter(r => !r.isValid);
                this.showNotification('error', 'Validation Error', this.buildErrorMessage(errors));
            }

            return {
                isValid: isFormValid,
                results: results,
                errors: results.filter(r => !r.isValid)
            };
        }

        /**
         * Validar paso del modal con notificaciones centralizadas
         */
        validateStep(stepElement) {
            const fields = stepElement.querySelectorAll('input, select, textarea');
            let isStepValid = true;
            const errors = [];

            // No limpiar notificaciones al inicio para mantener feedback visual

            fields.forEach(field => {
                const rules = this.getFieldRules(field);
                const result = this.validateField(field, rules);
                
                if (!result.isValid) {
                    isStepValid = false;
                    errors.push(result);
                    // Marcar el campo como error y mostrar notificación inmediatamente
                    field.classList.add('ewm-error');
                } else {
                    field.classList.remove('ewm-error');
                }
            });

            // Mostrar notificación centralizada si hay errores
            if (!isStepValid) {
                const firstError = errors[0];
                this.showNotification('error', 'Validation Error', firstError.errors[0]);
                
                // Hacer focus al primer campo con error para mejor UX
                const firstErrorField = errors[0].field;
                if (firstErrorField && typeof firstErrorField.focus === 'function') {
                    setTimeout(() => firstErrorField.focus(), 100);
                }
            } else {
                // Solo limpiar notificaciones si no hay errores
                this.clearNotifications();
            }

            // DEBUG 1.2: Registrar el resultado de la validación para el paso actual
            console.log('DEBUG: Step validation result', { isValid: isStepValid, errors: errors });

            return {
                isValid: isStepValid,
                errors: errors
            };
        }

        /**
         * Obtener valor del campo
         */
        getFieldValue(field) {
            if (field.type === 'checkbox') {
                if (field.name.endsWith('[]')) {
                    // Múltiples checkboxes
                    const checkboxes = document.querySelectorAll(`input[name="${field.name}"]:checked`);
                    return Array.from(checkboxes).map(cb => cb.value);
                } else {
                    return field.checked;
                }
            } else if (field.type === 'radio') {
                const checked = document.querySelector(`input[name="${field.name}"]:checked`);
                return checked ? checked.value : '';
            } else {
                return field.value.trim();
            }
        }

        /**
         * Obtener reglas de validación del campo
         */
        getFieldRules(field) {
            const rules = {};

            // Required
            if (field.hasAttribute('required')) {
                rules.required = true;
            }

            // Tipo específico
            switch (field.type) {
                case 'email':
                    rules.email = true;
                    break;
                case 'url':
                    rules.url = true;
                    break;
                case 'tel':
                    rules.phone = true;
                    break;
                case 'number':
                    rules.number = true;
                    break;
            }

            // Atributos HTML5
            if (field.hasAttribute('minlength')) {
                rules.minLength = parseInt(field.getAttribute('minlength'));
            }
            
            if (field.hasAttribute('maxlength')) {
                rules.maxLength = parseInt(field.getAttribute('maxlength'));
            }

            if (field.hasAttribute('pattern')) {
                rules.pattern = field.getAttribute('pattern');
            }

            // Reglas personalizadas desde data attributes
            if (field.dataset.ewmRules) {
                try {
                    const customRules = JSON.parse(field.dataset.ewmRules);
                    Object.assign(rules, customRules);
                } catch (e) {
                    console.warn('EWM Validator: Invalid rules JSON', field.dataset.ewmRules);
                }
            }

            // DEBUG 1.1: Registrar las reglas de validación generadas para cada campo
            console.log('DEBUG: Rules for field', field.name || field.id, rules);

            return rules;
        }

        /**
         * Validadores específicos
         */
        validateRequired(value, ruleValue, field) {
            if (field.type === 'checkbox' && !field.name.endsWith('[]')) {
                return field.checked;
            } else if (Array.isArray(value)) {
                return value.length > 0;
            } else {
                return value !== '' && value !== null && value !== undefined;
            }
        }

        validateEmail(value, ruleValue, field) {
            if (!value) return true; // Solo validar si hay valor
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(value);
        }

        validateUrl(value, ruleValue, field) {
            if (!value) return true;
            try {
                new URL(value);
                return true;
            } catch {
                return false;
            }
        }

        validatePhone(value, ruleValue, field) {
            if (!value) return true;
            const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
            return phoneRegex.test(value.replace(/[\s\-\(\)]/g, ''));
        }

        validateNumber(value, ruleValue, field) {
            if (!value) return true;
            return !isNaN(value) && isFinite(value);
        }

        validateMinLength(value, minLength, field) {
            if (!value) return true;
            return value.length >= minLength;
        }

        validateMaxLength(value, maxLength, field) {
            if (!value) return true;
            return value.length <= maxLength;
        }

        validatePattern(value, pattern, field) {
            if (!value) return true;
            const regex = new RegExp(pattern);
            return regex.test(value);
        }

        /**
         * Obtener nombre del campo para mensajes de error
         */
        getFieldName(field) {
            // Buscar label asociado al campo
            const label = field.closest('.ewm-field')?.querySelector('.ewm-field-label');
            if (label) {
                return label.textContent.replace('*', '').trim();
            } else if (field.dataset.label) {
                return field.dataset.label;
            } else if (field.name && field.name !== field.id) {
                return field.name;
            }
            return 'This field';
        }

        /**
         * Obtener mensaje de error
         */
        getErrorMessage(ruleName, ruleValue, field) {
            // Priorizar label del campo, luego nombre, después placeholder como último recurso
            let fieldName = this.getFieldName(field);
            
            const messages = {
                required: `${fieldName} is required`,
                email: `${fieldName} must be a valid email`,
                url: `${fieldName} must be a valid URL`,
                phone: `${fieldName} must be a valid phone`,
                number: `${fieldName} must be a valid number`,
                minLength: `${fieldName} must have at least ${ruleValue} characters`,
                maxLength: `${fieldName} cannot have more than ${ruleValue} characters`,
                pattern: `${fieldName} does not have the correct format`
            };

            return field.dataset.errorMessage || messages[ruleName] || `${fieldName} no es válido`;
        }

        /**
         * Construir mensaje de error consolidado
         */
        buildErrorMessage(errors) {
            if (errors.length === 1) {
                return errors[0].errors[0];
            }
            
            const errorList = errors.map(error => `• ${error.errors[0]}`).join('\n');
            return `Found ${errors.length} errors:\n${errorList}`;
        }

        /**
         * Mostrar notificación centralizada
         */
        showNotification(type = 'error', title = '', message = '') {
            const container = document.getElementById('ewm-notifications-container');
            if (!container) {
                console.warn('EWM Validator: Notification container not found');
                return;
            }

            // Evitar duplicados - verificar si ya existe una notificación con el mismo mensaje
            const existingNotification = container.querySelector('.ewm-notification-message');
            if (existingNotification && existingNotification.textContent.includes(message)) {
                return; // No mostrar duplicado
            }

            // Limpiar notificaciones existentes del mismo tipo
            const existingOfSameType = container.querySelector(`.ewm-notification-${type}`);
            if (existingOfSameType) {
                this.hideNotification(existingOfSameType);
            }

            // Crear notificación
            const notification = document.createElement('div');
            notification.className = `ewm-notification ewm-notification-${type}`;
            
            // Iconos según el tipo
            const icons = {
                error: '⚠️',
                success: '✅',
                warning: '⚠️',
                info: 'ℹ️'
            };

            notification.innerHTML = `
                <div class="ewm-notification-icon">${icons[type] || icons.error}</div>
                <div class="ewm-notification-content">
                    <div class="ewm-notification-title">${title}</div>
                    <div class="ewm-notification-message">${message.replace(/\n/g, '<br>')}</div>
                </div>
                <button type="button" class="ewm-notification-close" aria-label="Close notification">&times;</button>
            `;

            // Agregar al contenedor
            container.appendChild(notification);
            container.style.display = 'block';

            // Configurar botón de cerrar
            const closeBtn = notification.querySelector('.ewm-notification-close');
            closeBtn.addEventListener('click', () => {
                this.hideNotification(notification);
            });

            // Auto-cerrar después de 8 segundos para notificaciones no críticas
            if (type !== 'error') {
                setTimeout(() => {
                    if (notification.parentNode) {
                        this.hideNotification(notification);
                    }
                }, 8000);
            }

            // Hacer scroll hacia la notificación si no está visible
            container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        /**
         * Ocultar notificación específica
         */
        hideNotification(notification) {
            notification.classList.add('ewm-notification-hiding');
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                    
                    // Ocultar contenedor si no hay más notificaciones
                    const container = document.getElementById('ewm-notifications-container');
                    if (container && container.children.length === 0) {
                        container.style.display = 'none';
                    }
                }
            }, 300);
        }

        /**
         * Limpiar todas las notificaciones
         */
        clearNotifications() {
            const container = document.getElementById('ewm-notifications-container');
            if (container) {
                container.innerHTML = '';
                container.style.display = 'none';
            }
            
            // También limpiar estados de error en campos
            const errorFields = document.querySelectorAll('.ewm-error');
            errorFields.forEach(field => field.classList.remove('ewm-error'));
        }

        /**
         * Validación en tiempo real con notificaciones centralizadas
         */
        enableRealTimeValidation(form) {
            const fields = form.querySelectorAll('input, select, textarea');

            fields.forEach(field => {
                // DEBUG 2.1: Desactivar validación en tiempo real para mejorar UX
                // Solo mantener la limpieza visual de errores al escribir

                // Validar al escribir con debounce optimizado - SOLO LIMPIAR ERRORES
                field.addEventListener('input', () => {
                    // Limpiar error visual inmediatamente al escribir
                    if (field.classList.contains('ewm-error')) {
                        field.classList.remove('ewm-error');
                    }
                    // COMENTADO: No ejecutar validación en tiempo real
                    // debouncedValidation();
                });

                // COMENTADO: Validación en tiempo real desactivada
                // field.addEventListener('blur', () => {
                //     this.validateFieldRealTime(field, form);
                // });

                // COMENTADO: Validación en tiempo real desactivada
                // field.addEventListener('change', () => {
                //     this.validateFieldRealTime(field, form);
                // });
            });
        }

        /**
         * Validar campo individual en tiempo real
         */
        validateFieldRealTime(field, form) {
            const rules = this.getFieldRules(field);
            const result = this.validateField(field, rules);

            if (!result.isValid) {
                field.classList.add('ewm-error');

                // Mostrar error directamente debajo del campo
                this.showFieldError(field, result.errors[0]);

                // También mostrar notificación centralizada para errores críticos
                this.showNotification('error', 'Validation Error', result.errors[0]);
            } else {
                field.classList.remove('ewm-error');

                // Ocultar error del campo
                this.hideFieldError(field);

                // Si no hay más campos con error, limpiar notificaciones
                if (!form.querySelector('.ewm-error')) {
                    this.clearNotifications();
                }
            }

            // Actualizar estado del botón de envío
            this.updateSubmitButtonState(form);
        }

        /**
         * Mostrar error directamente debajo del campo
         */
        showFieldError(field, message) {
            // Buscar o crear elemento de error
            let errorElement = field.parentNode.querySelector('.ewm-field-error');

            if (!errorElement) {
                errorElement = document.createElement('div');
                errorElement.className = 'ewm-field-error';
                field.parentNode.appendChild(errorElement);
            }

            errorElement.textContent = message;
            errorElement.classList.add('visible');
        }

        /**
         * Ocultar error del campo
         */
        hideFieldError(field) {
            const errorElement = field.parentNode.querySelector('.ewm-field-error');
            if (errorElement) {
                errorElement.classList.remove('visible');
            }
        }

        /**
         * Actualizar estado del botón de envío basado en validación
         */
        updateSubmitButtonState(form) {
            const submitButtons = form.querySelectorAll('.ewm-btn-submit');
            const hasErrors = form.querySelector('.ewm-error');

            submitButtons.forEach(button => {
                if (hasErrors) {
                    button.disabled = true;
                    button.classList.add('ewm-btn-disabled');
                    button.title = 'Corrige los errores antes de enviar';
                } else {
                    button.disabled = false;
                    button.classList.remove('ewm-btn-disabled');
                    button.title = '';
                }
            });
        }
    }

    // Crear instancia global
    window.EWMFormValidator = new EWMFormValidator();

    // Auto-inicializar validación en tiempo real para modales
    document.addEventListener('DOMContentLoaded', function() {
        const modalForms = document.querySelectorAll('.ewm-modal-content form, .ewm-step');
        modalForms.forEach(form => {
            window.EWMFormValidator.enableRealTimeValidation(form);
        });
    });

})();
