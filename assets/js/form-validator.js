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
         * Validar un campo individual
         */
        validateField(field, rules = {}) {
            const value = this.getFieldValue(field);
            const errors = [];

            // Validar cada regla
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
         * Validar un formulario completo
         */
        validateForm(form) {
            const fields = form.querySelectorAll('input, select, textarea');
            const results = [];
            let isFormValid = true;

            fields.forEach(field => {
                const rules = this.getFieldRules(field);
                const result = this.validateField(field, rules);
                
                results.push(result);
                
                if (!result.isValid) {
                    isFormValid = false;
                    this.showFieldError(field, result.errors[0]);
                } else {
                    this.clearFieldError(field);
                }
            });

            return {
                isValid: isFormValid,
                results: results,
                errors: results.filter(r => !r.isValid)
            };
        }

        /**
         * Validar paso del modal
         */
        validateStep(stepElement) {
            const fields = stepElement.querySelectorAll('input, select, textarea');
            let isStepValid = true;
            const errors = [];

            fields.forEach(field => {
                const rules = this.getFieldRules(field);
                const result = this.validateField(field, rules);
                
                if (!result.isValid) {
                    isStepValid = false;
                    errors.push(result);
                    this.showFieldError(field, result.errors[0]);
                } else {
                    this.clearFieldError(field);
                }
            });

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
         * Obtener mensaje de error
         */
        getErrorMessage(ruleName, ruleValue, field) {
            const fieldName = field.dataset.label || field.placeholder || field.name || 'Este campo';
            
            const messages = {
                required: `${fieldName} es obligatorio`,
                email: `${fieldName} debe ser un email válido`,
                url: `${fieldName} debe ser una URL válida`,
                phone: `${fieldName} debe ser un teléfono válido`,
                number: `${fieldName} debe ser un número válido`,
                minLength: `${fieldName} debe tener al menos ${ruleValue} caracteres`,
                maxLength: `${fieldName} no puede tener más de ${ruleValue} caracteres`,
                pattern: `${fieldName} no tiene el formato correcto`
            };

            return field.dataset.errorMessage || messages[ruleName] || `${fieldName} no es válido`;
        }

        /**
         * Mostrar error en el campo
         */
        showFieldError(field, message) {
            this.clearFieldError(field);
            
            field.classList.add('ewm-field-error');
            
            const errorElement = document.createElement('div');
            errorElement.className = 'ewm-field-error-message';
            errorElement.textContent = message;
            
            const wrapper = field.closest('.ewm-field-wrapper') || field.parentNode;
            wrapper.appendChild(errorElement);
        }

        /**
         * Limpiar error del campo
         */
        clearFieldError(field) {
            field.classList.remove('ewm-field-error');
            
            const wrapper = field.closest('.ewm-field-wrapper') || field.parentNode;
            const errorElement = wrapper.querySelector('.ewm-field-error-message');
            
            if (errorElement) {
                errorElement.remove();
            }
        }

        /**
         * Limpiar todos los errores del formulario
         */
        clearFormErrors(form) {
            const errorFields = form.querySelectorAll('.ewm-field-error');
            const errorMessages = form.querySelectorAll('.ewm-field-error-message');
            
            errorFields.forEach(field => field.classList.remove('ewm-field-error'));
            errorMessages.forEach(message => message.remove());
        }

        /**
         * Validación en tiempo real
         */
        enableRealTimeValidation(form) {
            const fields = form.querySelectorAll('input, select, textarea');
            
            fields.forEach(field => {
                // Validar al perder el foco
                field.addEventListener('blur', () => {
                    const rules = this.getFieldRules(field);
                    const result = this.validateField(field, rules);
                    
                    if (!result.isValid) {
                        this.showFieldError(field, result.errors[0]);
                    } else {
                        this.clearFieldError(field);
                    }
                });

                // Limpiar error al escribir
                field.addEventListener('input', () => {
                    if (field.classList.contains('ewm-field-error')) {
                        this.clearFieldError(field);
                    }
                });
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
