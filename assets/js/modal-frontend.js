/**
 * EWM Modal Frontend JavaScript
 * 
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

(function() {
    'use strict';

    /**
     * Clase principal del modal EWM
     */
    class EWMModal {
        constructor(modalId) {
            this.modalId = modalId;
            this.modalElement = document.getElementById(`ewm-modal-${modalId}`);
            this.config = {};
            this.currentStep = 1;
            this.totalSteps = 0;
            this.formData = {};
            this.isVisible = false;
            this.triggers = {};
            
            if (!this.modalElement) {
                console.warn(`EWM Modal: Modal element with ID ewm-modal-${modalId} not found`);
                return;
            }
            
            this.init();
        }

        /**
         * Inicializar el modal
         */
        init() {
            this.parseConfig();
            this.moveModalToBody(); // Mover modal al body para evitar problemas de posicionamiento
            this.setupElements();
            this.setupEventListeners();
            this.setupTriggers();
            this.initializeForm();

            if (window.ewmModal && window.ewmModal.debug) {
                console.log('EWM Modal initialized:', this.modalId, this.config);
            }
        }

        /**
         * Mover modal al body para evitar problemas de posicionamiento
         */
        moveModalToBody() {
            if (this.modalElement && this.modalElement.parentNode !== document.body) {
                // Remover el modal de su posición actual y agregarlo al body
                document.body.appendChild(this.modalElement);

                if (window.ewmModal && window.ewmModal.debug) {
                    console.log('EWM Modal moved to body:', this.modalId);
                }
            }
        }

        /**
         * Parsear configuración del modal
         */
        parseConfig() {
            const configData = this.modalElement.getAttribute('data-config');
            
            console.log('🔍 EWM FREQUENCY DEBUG - parseConfig iniciado', {
                modalId: this.modalId,
                hasConfigData: !!configData,
                configDataPreview: configData ? configData.substring(0, 200) + '...' : null
            });
            
            if (configData) {
                try {
                    this.config = JSON.parse(configData);
                    console.log('🔍 EWM FREQUENCY DEBUG - Config parseado exitosamente', {
                        configKeys: Object.keys(this.config),
                        hasDisplayRules: !!this.config.display_rules,
                        displayRules: this.config.display_rules,
                        frequency: this.config.display_rules?.frequency
                    });
                } catch (e) {
                    console.error('EWM Modal: Invalid config JSON', e);
                    this.config = {};
                }
            } else {
                console.log('🔍 EWM FREQUENCY DEBUG - No data-config found');
                this.config = {};
            }

            // Obtener configuración adicional de atributos data
            this.config.trigger = this.modalElement.getAttribute('data-trigger') || 'manual';
            this.config.delay = parseInt(this.modalElement.getAttribute('data-delay')) || 0;
            this.config.size = this.modalElement.getAttribute('data-size') || 'medium';
            this.config.animation = this.modalElement.getAttribute('data-animation') || 'fade';
            
            console.log('🔍 EWM FREQUENCY DEBUG - Config final', {
                trigger: this.config.trigger,
                delay: this.config.delay,
                hasFrequencyConfig: !!this.config.display_rules?.frequency,
                frequencyConfig: this.config.display_rules?.frequency
            });
        }

        /**
         * Configurar elementos del DOM
         */
        setupElements() {
            this.backdrop = this.modalElement.querySelector('.ewm-modal-backdrop');
            this.closeButton = this.modalElement.querySelector('.ewm-modal-close');
            this.form = this.modalElement.querySelector('.ewm-multi-step-form');
            this.steps = this.modalElement.querySelectorAll('.ewm-form-step');
            this.progressBar = this.modalElement.querySelector('.ewm-progress-bar');
            this.progressFill = this.modalElement.querySelector('.ewm-progress-fill');
            this.progressSteps = this.modalElement.querySelectorAll('.ewm-progress-step');
            
            this.totalSteps = this.steps.length;
            
            // Configurar CSS custom properties
            if (this.config.design && this.config.design.colors) {
                const colors = this.config.design.colors;
                this.modalElement.style.setProperty('--ewm-primary-color', colors.primary || '#ff6b35');
                this.modalElement.style.setProperty('--ewm-secondary-color', colors.secondary || '#333333');
                this.modalElement.style.setProperty('--ewm-background-color', colors.background || '#ffffff');
            }
        }

        /**
         * Configurar event listeners
         */
        setupEventListeners() {
            // Cerrar modal
            if (this.closeButton) {
                this.closeButton.addEventListener('click', () => this.close());
            }
            
            if (this.backdrop) {
                this.backdrop.addEventListener('click', () => this.close());
            }

            // Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isVisible) {
                    this.close();
                }
            });

            // Navegación de pasos
            this.modalElement.addEventListener('click', (e) => {
                if (e.target.classList.contains('ewm-btn-next')) {
                    e.preventDefault();
                    this.nextStep();
                } else if (e.target.classList.contains('ewm-btn-prev')) {
                    e.preventDefault();
                    this.prevStep();
                }
            });

            // Submit del formulario
            if (this.form) {
                this.form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.submitForm();
                });
            }

            // Validación en tiempo real
            this.modalElement.addEventListener('input', (e) => {
                if (e.target.classList.contains('ewm-field-input')) {
                    this.validateField(e.target);
                }
            });

            this.modalElement.addEventListener('blur', (e) => {
                if (e.target.classList.contains('ewm-field-input')) {
                    this.validateField(e.target);
                }
            }, true);

            // Triggers manuales
            document.addEventListener('click', (e) => {
                if (e.target.matches(`[data-ewm-modal="${this.modalId}"]`)) {
                    e.preventDefault();
                    this.open();
                }
            });
        }

        /**
         * Configurar triggers automáticos
         */
        setupTriggers() {
            const triggers = this.config.triggers || {};

            // Exit intent
            if (triggers.exit_intent && triggers.exit_intent.enabled) {
                this.setupExitIntent(triggers.exit_intent.sensitivity || 20);
            }

            // Time delay
            if (triggers.time_delay && triggers.time_delay.enabled) {
                this.setupTimeDelay(triggers.time_delay.delay || 5000);
            }

            // Scroll percentage
            if (triggers.scroll_percentage && triggers.scroll_percentage.enabled) {
                this.setupScrollTrigger(triggers.scroll_percentage.percentage || 50);
            }

            // Trigger automático basado en configuración
            if (this.config.trigger === 'auto' || this.config.trigger === 'time-delay') {
                this.setupTimeDelay(this.config.delay || 5000);
            }
        }

        /**
         * Configurar exit intent
         */
        setupExitIntent(sensitivity) {
            let triggered = false;
            
            document.addEventListener('mouseleave', (e) => {
                if (!triggered && e.clientY <= sensitivity) {
                    triggered = true;
                    this.open();
                }
            });
        }

        /**
         * Configurar trigger por tiempo
         */
        setupTimeDelay(delay) {
            console.log('🔍 EWM FREQUENCY DEBUG - setupTimeDelay iniciado', {
                modalId: this.modalId,
                delay
            });
            
            setTimeout(() => {
                const hasBeenShown = this.hasBeenShown();
                console.log('🔍 EWM FREQUENCY DEBUG - TimeDelay trigger evaluando', {
                    modalId: this.modalId,
                    isVisible: this.isVisible,
                    hasBeenShown,
                    shouldOpen: !this.isVisible && !hasBeenShown
                });
                
                if (!this.isVisible && !hasBeenShown) {
                    console.log('🔍 EWM FREQUENCY DEBUG - TimeDelay trigger abriendo modal');
                    this.open();
                } else {
                    console.log('🔍 EWM FREQUENCY DEBUG - TimeDelay trigger NO abre modal');
                }
            }, delay);
        }

        /**
         * Configurar trigger por scroll
         */
        setupScrollTrigger(percentage) {
            let triggered = false;
            
            window.addEventListener('scroll', () => {
                if (triggered) return;
                
                const scrollPercent = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
                
                if (scrollPercent >= percentage) {
                    triggered = true;
                    this.open();
                }
            });
        }

        /**
         * Verificar si el modal ya se mostró considerando la frecuencia configurada
         */
        hasBeenShown() {
            console.log('🔍 EWM FREQUENCY DEBUG - hasBeenShown() iniciado', {
                modalId: this.modalId,
                configExists: !!this.config,
                displayRulesExists: !!this.config?.display_rules,
                frequencyExists: !!this.config?.display_rules?.frequency
            });
            
            const frequencyConfig = this.config.display_rules?.frequency;
            
            console.log('🔍 EWM FREQUENCY DEBUG - frequencyConfig:', frequencyConfig);
            
            if (!frequencyConfig) {
                // Sin configuración de frecuencia, usar comportamiento legacy
                const cookieName = `ewm_modal_${this.modalId}_shown`;
                const hasLegacyCookie = document.cookie.includes(cookieName);
                console.log('🔍 EWM FREQUENCY DEBUG - Sin configuración, usando legacy', {
                    cookieName,
                    hasLegacyCookie,
                    allCookies: document.cookie
                });
                return hasLegacyCookie;
            }
            
            const type = frequencyConfig.type || 'session';
            const limit = parseInt(frequencyConfig.limit) || 1;
            const cookieName = `ewm_modal_${this.modalId}_count`;
            
            console.log('🔍 EWM FREQUENCY DEBUG - Configuración detectada', {
                type,
                limit,
                cookieName
            });
            
            // Obtener contador actual de la cookie
            const currentCount = this.getCookieValue(cookieName) || 0;
            const hasReachedLimit = parseInt(currentCount) >= limit;
            
            console.log('🔍 EWM FREQUENCY DEBUG - Verificación de límite', {
                currentCount,
                limit,
                hasReachedLimit,
                allCookies: document.cookie
            });
            
            return hasReachedLimit;
        }
        
        /**
         * Obtener valor de una cookie específica
         */
        getCookieValue(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) {
                return parts.pop().split(';').shift();
            }
            return null;
        }

        /**
         * Marcar modal como mostrado e incrementar contador
         */
        markAsShown() {
            console.log('🔍 EWM FREQUENCY DEBUG - markAsShown() iniciado', {
                modalId: this.modalId,
                configExists: !!this.config,
                displayRulesExists: !!this.config?.display_rules,
                frequencyExists: !!this.config?.display_rules?.frequency
            });
            
            const frequencyConfig = this.config.display_rules?.frequency;
            
            console.log('🔍 EWM FREQUENCY DEBUG - markAsShown frequencyConfig:', frequencyConfig);
            
            if (!frequencyConfig) {
                // Sin configuración de frecuencia, usar comportamiento legacy
                const cookieName = `ewm_modal_${this.modalId}_shown`;
                const expiryDate = new Date();
                expiryDate.setTime(expiryDate.getTime() + (24 * 60 * 60 * 1000)); // 24 horas
                const legacyCookie = `${cookieName}=1; expires=${expiryDate.toUTCString()}; path=/`;
                document.cookie = legacyCookie;
                
                console.log('🔍 EWM FREQUENCY DEBUG - Legacy cookie establecida', {
                    cookieName,
                    cookieString: legacyCookie,
                    expiryDate: expiryDate.toUTCString()
                });
                return;
            }
            
            const type = frequencyConfig.type || 'session';
            const cookieName = `ewm_modal_${this.modalId}_count`;
            
            // Obtener contador actual e incrementarlo
            const currentCount = parseInt(this.getCookieValue(cookieName)) || 0;
            const newCount = currentCount + 1;
            
            console.log('🔍 EWM FREQUENCY DEBUG - Incrementando contador', {
                type,
                cookieName,
                currentCount,
                newCount
            });
            
            // Calcular fecha de expiración según el tipo
            let expiryTime;
            let cookieString;
            
            switch (type) {
                case 'daily':
                    expiryTime = 24 * 60 * 60 * 1000; // 24 horas
                    break;
                case 'weekly':
                    expiryTime = 7 * 24 * 60 * 60 * 1000; // 7 días
                    break;
                case 'session':
                default:
                    // Session cookie (expires when browser closes)
                    cookieString = `${cookieName}=${newCount}; path=/`;
                    document.cookie = cookieString;
                    console.log('🔍 EWM FREQUENCY DEBUG - Session cookie establecida', {
                        cookieString,
                        allCookies: document.cookie
                    });
                    return;
            }
            
            const expiryDate = new Date();
            expiryDate.setTime(expiryDate.getTime() + expiryTime);
            cookieString = `${cookieName}=${newCount}; expires=${expiryDate.toUTCString()}; path=/`;
            document.cookie = cookieString;
            
            console.log('🔍 EWM FREQUENCY DEBUG - Cookie con tiempo establecida', {
                type,
                expiryTime,
                expiryDate: expiryDate.toUTCString(),
                cookieString,
                allCookies: document.cookie
            });
        }

        /**
         * Inicializar formulario
         */
        initializeForm() {
            if (!this.form) return;

            // Configurar validación en tiempo real
            const inputs = this.form.querySelectorAll('.ewm-field-input');
            inputs.forEach(input => {
                input.addEventListener('blur', () => this.validateField(input));
                input.addEventListener('input', () => this.clearFieldError(input));
            });

            // Cargar datos guardados del localStorage
            this.loadFormData();
        }

        /**
         * Abrir modal
         */
        open() {
            console.log('🔍 EWM FREQUENCY DEBUG - open() iniciado', {
                modalId: this.modalId,
                isVisible: this.isVisible
            });
            
            if (this.isVisible) {
                console.log('🔍 EWM FREQUENCY DEBUG - Modal ya visible, saliendo');
                return;
            }

            this.isVisible = true;
            this.modalElement.style.display = 'flex';
            this.modalElement.setAttribute('aria-hidden', 'false');
            
            // Trigger animation
            requestAnimationFrame(() => {
                this.modalElement.classList.add('ewm-modal-visible');
            });

            // Prevent body scroll
            document.body.style.overflow = 'hidden';
            
            // Focus management
            this.trapFocus();
            
            console.log('🔍 EWM FREQUENCY DEBUG - Llamando a markAsShown()');
            // Mark as shown
            this.markAsShown();

            // Trigger event
            this.triggerEvent('ewm:modal:opened', { modalId: this.modalId });
            
            console.log('🔍 EWM FREQUENCY DEBUG - open() completado');
        }

        /**
         * Cerrar modal
         */
        close() {
            if (!this.isVisible) return;

            this.isVisible = false;
            this.modalElement.classList.remove('ewm-modal-visible');
            
            setTimeout(() => {
                this.modalElement.style.display = 'none';
                this.modalElement.setAttribute('aria-hidden', 'true');
            }, 300);

            // Restore body scroll
            document.body.style.overflow = '';
            
            // Trigger event
            this.triggerEvent('ewm:modal:closed', { modalId: this.modalId });
        }

        /**
         * Ir al siguiente paso
         */
        nextStep() {
            console.log('EWM Modal: Attempting to go to next step');

            // Validar paso actual ANTES de avanzar
            const isValid = this.validateCurrentStep();
            console.log('EWM Modal: Current step validation result:', isValid);

            if (!isValid) {
                console.log('EWM Modal: Validation failed, preventing step advance');
                return false;
            }

            this.saveCurrentStepData();

            if (this.currentStep < this.totalSteps) {
                this.currentStep++;
                this.updateStepDisplay();
                this.updateProgress();
                console.log('EWM Modal: Advanced to step', this.currentStep);
            }

            return true;
        }

        /**
         * Ir al paso anterior
         */
        prevStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
                this.updateStepDisplay();
                this.updateProgress();
            }
        }

        /**
         * Actualizar visualización de pasos
         */
        updateStepDisplay() {
            this.steps.forEach((step, index) => {
                if (index + 1 === this.currentStep) {
                    step.classList.add('active');
                } else {
                    step.classList.remove('active');
                }
            });

            // Actualizar progress steps
            this.progressSteps.forEach((step, index) => {
                if (index + 1 < this.currentStep) {
                    step.classList.add('completed');
                    step.classList.remove('active');
                } else if (index + 1 === this.currentStep) {
                    step.classList.add('active');
                    step.classList.remove('completed');
                } else {
                    step.classList.remove('active', 'completed');
                }
            });
        }

        /**
         * Actualizar barra de progreso
         */
        updateProgress() {
            if (!this.progressFill) return;

            // Calcular progreso: en el último paso mostrar 90% hasta que se complete el envío
            let progress;
            if (this.currentStep === this.totalSteps) {
                // En el último paso, mostrar 90% hasta completar envío
                progress = 90;
            } else {
                // Progreso normal para pasos intermedios
                progress = ((this.currentStep - 1) / (this.totalSteps - 1)) * 100;
            }

            this.progressFill.style.width = `${Math.max(0, Math.min(100, progress))}%`;
            console.log('EWM Modal: Progress updated to', progress + '%', 'for step', this.currentStep, 'of', this.totalSteps);
        }

        /**
         * Actualizar barra de progreso al 100% (formulario completado)
         */
        updateProgressToComplete() {
            if (!this.progressFill) return;

            // Forzar progreso al 100%
            this.progressFill.style.width = '100%';

            // Marcar todos los pasos como completados
            this.progressSteps.forEach(step => {
                step.classList.remove('active');
                step.classList.add('completed');
            });

            console.log('EWM Modal: Progress updated to 100% - form completed');
        }

        /**
         * Validar paso actual
         */
        validateCurrentStep() {
            const currentStepElement = this.steps[this.currentStep - 1];
            if (!currentStepElement) {
                console.log('EWM Modal: No current step element found');
                return true;
            }

            console.log('EWM Modal: Validating current step', this.currentStep);

            // Validar TODOS los campos de entrada, especialmente los requeridos
            const inputs = currentStepElement.querySelectorAll('.ewm-field-input');
            let isValid = true;
            let errorCount = 0;

            console.log('EWM Modal: Found', inputs.length, 'inputs to validate');

            inputs.forEach((input, index) => {
                console.log('EWM Modal: Validating input', index, input.name, input.value);
                if (!this.validateField(input)) {
                    isValid = false;
                    errorCount++;
                }
            });

            console.log('EWM Modal: Validation complete. Valid:', isValid, 'Errors:', errorCount);

            // Si hay errores, hacer scroll al primer campo con error
            if (!isValid) {
                const firstError = currentStepElement.querySelector('.ewm-field-input.ewm-error');
                if (firstError) {
                    console.log('EWM Modal: Focusing first error field:', firstError.name);
                    firstError.focus();
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }

            return isValid;
        }

        /**
         * Validar campo individual
         */
        validateField(input) {
            const value = input.value.trim();
            const type = input.type;
            const required = input.hasAttribute('required');
            const errorElement = input.parentNode.querySelector('.ewm-field-error');

            let isValid = true;
            let errorMessage = '';

            console.log('EWM Modal: Validating field', input.name, 'value:', value, 'required:', required);

            // Validación de campo requerido
            if (required && (!value || value.length < 2)) {
                isValid = false;
                if (!value) {
                    errorMessage = window.ewmModal?.strings?.required_field || 'This field is required.';
                } else {
                    errorMessage = window.ewmModal?.strings?.min_length || 'Please enter at least 2 characters.';
                }
            }

            // Validación por tipo
            if (value && type === 'email') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    isValid = false;
                    errorMessage = window.ewmModal?.strings?.invalid_email || 'Please enter a valid email address.';
                }
            }

            // Validación para campos de nombre (mínimo 2 caracteres)
            if (value && (input.name === 'name' || input.id === 'name') && value.length < 2) {
                isValid = false;
                errorMessage = window.ewmModal?.strings?.name_min_length || 'Name must be at least 2 characters long.';
            }

            console.log('EWM Modal: Field validation result:', isValid, 'error:', errorMessage);

            // Validación específica por tipo de campo
            if (value) {
                switch (type) {
                    case 'url':
                        try {
                            new URL(value);
                        } catch {
                            isValid = false;
                            errorMessage = window.ewmModal?.strings?.invalid_url || 'Por favor, introduce una URL válida.';
                        }
                        break;

                    case 'time':
                    case 'datetime-local':
                    case 'month':
                    case 'week':
                    case 'date':
                        // Para estos tipos, input.checkValidity() es suficiente para formato básico
                        if (!input.checkValidity()) {
                            isValid = false;
                            const errorKey = `invalid_${type.replace('-', '_')}`;
                            errorMessage = window.ewmModal?.strings?.[errorKey] || `Por favor, introduce un ${type} válido.`;
                        }
                        break;

                    case 'color':
                        // Validación de formato hex color
                        if (!input.checkValidity() && !/^#[0-9A-Fa-f]{6}$/i.test(value)) {
                            isValid = false;
                            errorMessage = window.ewmModal?.strings?.invalid_color || 'Por favor, introduce un color válido (ej. #RRGGBB).';
                        }
                        break;

                    case 'range':
                        // input.checkValidity() ya valida min/max/step
                        if (!input.checkValidity()) {
                            isValid = false;
                            errorMessage = window.ewmModal?.strings?.invalid_range || 'El valor está fuera del rango permitido.';
                        }
                        break;
                }
            }

            // Mostrar/ocultar error
            if (isValid) {
                input.classList.remove('ewm-error');
                if (errorElement) {
                    errorElement.textContent = '';
                    errorElement.classList.remove('visible');
                    errorElement.style.display = 'none';
                }
            } else {
                input.classList.add('ewm-error');
                if (errorElement) {
                    errorElement.textContent = errorMessage;
                    errorElement.classList.add('visible');
                    errorElement.style.display = 'block';
                } else {
                    console.warn('EWM Modal: Error element not found for field', input.name);
                }
            }

            return isValid;
        }

        /**
         * Limpiar error de campo
         */
        clearFieldError(input) {
            input.classList.remove('ewm-error');
            const errorElement = input.parentNode.querySelector('.ewm-field-error');
            if (errorElement) {
                errorElement.classList.remove('visible');
            }
        }

        /**
         * Guardar datos del paso actual
         */
        saveCurrentStepData() {
            const currentStepElement = this.steps[this.currentStep - 1];
            if (!currentStepElement) return;

            // Debug: Log el elemento del paso actual
            console.log('EWM Modal Debug: Current Step Element:', currentStepElement);
            console.log('EWM Modal Debug: Current Step Index:', this.currentStep - 1);

            const inputs = currentStepElement.querySelectorAll('.ewm-field-input');
            console.log('EWM Modal Debug: Saving step data, found inputs:', inputs.length);

            // Limpiar datos de campos array de este paso para evitar acumulación
            const arrayFieldsInStep = new Set();
            inputs.forEach(input => {
                if (input.name && input.name.endsWith('[]')) {
                    const baseName = input.name.slice(0, -2);
                    arrayFieldsInStep.add(baseName);
                }
            });

            // Limpiar arrays de este paso
            arrayFieldsInStep.forEach(fieldName => {
                this.formData[fieldName] = [];
                console.log('EWM Modal Debug: Cleared array field:', fieldName);
            });

            inputs.forEach(input => {
                const fieldName = input.name || input.id;
                let fieldValue = input.value;

                // Debug: Log cada input antes de procesarlo
                console.log('EWM Modal Debug: Processing input:', {
                    name: input.name,
                    id: input.id,
                    type: input.type,
                    value: input.value,
                    checked: input.checked, // Para checkboxes
                    isVisible: input.offsetParent !== null, // True si es visible
                    displayStyle: window.getComputedStyle(input).display, // Estilo CSS display
                    parentDisplay: input.parentElement ? window.getComputedStyle(input.parentElement).display : 'N/A'
                });

                // Recolección de valor de checkbox
                if (input.type === 'checkbox') {
                    if (input.name.endsWith('[]')) {
                        // Checkbox con opciones múltiples - manejar como array
                        const baseName = input.name.slice(0, -2); // Remover '[]'

                        // Garantizar que el array existe (ya debería estar inicializado arriba)
                        if (!this.formData[baseName]) {
                            this.formData[baseName] = [];
                        }

                        // Solo agregar si está seleccionado
                        if (input.checked) {
                            this.formData[baseName].push(input.value);
                        }

                        console.log('EWM Modal Debug: Checkbox array value:', baseName, this.formData[baseName]);
                        return; // No procesar más abajo
                    } else {
                        // Checkbox simple - valor booleano como string
                        fieldValue = input.checked ? 'yes' : 'no';
                        console.log('EWM Modal Debug: Checkbox simple value converted:', fieldValue);
                    }
                }

                // Asegurarse de que el campo tiene un nombre/ID y es visible
                if (fieldName && input.offsetParent !== null) {
                    // Para campos que no son checkbox con array (ya procesados arriba)
                    if (!(input.type === 'checkbox' && input.name.endsWith('[]'))) {
                        this.formData[fieldName] = fieldValue;
                        console.log(`EWM Modal Debug: Field ${fieldName} ADDED to formData with value:`, fieldValue);
                    }
                } else if (fieldName) {
                    console.log(`EWM Modal Debug: Field ${fieldName} SKIPPED (not visible or no name/id).`);
                }
            });

            console.log('EWM Modal Debug: Final formData collected by saveCurrentStepData:', this.formData);

            // Guardar en localStorage
            this.saveFormData();
        }

        /**
         * Guardar datos del formulario en localStorage
         */
        saveFormData() {
            const key = `ewm_modal_${this.modalId}_data`;
            localStorage.setItem(key, JSON.stringify(this.formData));
        }

        /**
         * Cargar datos del formulario desde localStorage
         */
        loadFormData() {
            const key = `ewm_modal_${this.modalId}_data`;
            const savedData = localStorage.getItem(key);
            
            if (savedData) {
                try {
                    this.formData = JSON.parse(savedData);
                    
                    // Rellenar campos
                    Object.keys(this.formData).forEach(fieldName => {
                        const input = this.form.querySelector(`[name="${fieldName}"], [id="${fieldName}"]`);
                        if (input) {
                            input.value = this.formData[fieldName];
                        }
                    });
                } catch (e) {
                    console.error('EWM Modal: Error loading form data', e);
                }
            }
        }

        /**
         * Limpiar datos guardados
         */
        clearFormData() {
            const key = `ewm_modal_${this.modalId}_data`;
            localStorage.removeItem(key);
            this.formData = {};
            console.log('EWM Modal: Form data cleared');
        }



        /**
         * Enviar formulario
         */
        async submitForm() {
            if (!this.validateCurrentStep()) {
                return;
            }

            this.saveCurrentStepData();

            // Mostrar loading
            this.showLoading();

            try {
                const response = await this.sendFormData();
                
                if (response.success) {
                    this.showSuccessStep();
                    this.clearFormData();
                    this.triggerEvent('ewm:form:submitted', { 
                        modalId: this.modalId, 
                        data: this.formData 
                    });
                } else {
                    throw new Error(response.message || 'Error al enviar el formulario');
                }
            } catch (error) {
                console.error('EWM Modal: Form submission error', error);
                this.showError(error.message);
            } finally {
                this.hideLoading();
            }
        }

        /**
         * Enviar datos del formulario
         */
        async sendFormData() {
            console.log('EWM Modal Debug: === STARTING FORM SUBMISSION ===');
            console.log('EWM Modal Debug: Current formData before submission:', this.formData);
            console.log('EWM Modal Debug: Modal ID:', this.modalId);

            // Debug: Verificar todos los inputs en el DOM antes del envío
            const allInputs = this.form.querySelectorAll('.ewm-field-input');
            console.log('EWM Modal Debug: All inputs in DOM at submission time:', allInputs.length);
            allInputs.forEach((input, index) => {
                console.log(`EWM Modal Debug: Input ${index}:`, {
                    name: input.name,
                    id: input.id,
                    type: input.type,
                    value: input.value,
                    checked: input.checked,
                    isVisible: input.offsetParent !== null,
                    stepParent: input.closest('.ewm-form-step')?.dataset?.step || 'unknown'
                });
            });

            const requestData = {
                modal_id: this.modalId,
                form_data: this.formData,
                step_data: {} // Placeholder para datos de pasos si se necesitan
            };

            console.log('EWM Modal Debug: Final request data being sent:', requestData);
            console.log('EWM Modal Debug: Request JSON:', JSON.stringify(requestData, null, 2));

            const response = await fetch(window.ewmModal?.restUrl + 'submit-form', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': window.ewmModal?.nonce || ''
                },
                body: JSON.stringify(requestData)
            });

            if (!response.ok) {
                console.error('EWM Modal Debug: HTTP Error:', response.status, response.statusText);
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            console.log('EWM Modal Debug: Server response:', result);
            console.log('EWM Modal Debug: === FORM SUBMISSION COMPLETE ===');

            return result;
        }

        /**
         * Mostrar paso de éxito
         */
        showSuccessStep() {
            this.steps.forEach(step => step.classList.remove('active'));
            const successStep = this.modalElement.querySelector('.ewm-success-step');
            if (successStep) {
                successStep.style.display = 'block';
                successStep.classList.add('active');
            }

            // Actualizar barra de progreso al 100% al completar el formulario
            this.updateProgressToComplete();
        }

        /**
         * Mostrar loading
         */
        showLoading() {
            const submitButton = this.modalElement.querySelector('.ewm-btn-submit');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="ewm-spinner"></span> ' + 
                    (window.ewmModal?.strings?.loading || 'Enviando...');
            }
        }

        /**
         * Ocultar loading
         */
        hideLoading() {
            const submitButton = this.modalElement.querySelector('.ewm-btn-submit');
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = 'Enviar';
            }
        }

        /**
         * Mostrar error
         */
        showError(message) {
            // Implementar mostrar error
            alert(message); // Temporal
        }

        /**
         * Trap focus dentro del modal
         */
        trapFocus() {
            const focusableElements = this.modalElement.querySelectorAll(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            );
            
            if (focusableElements.length > 0) {
                focusableElements[0].focus();
            }
        }

        /**
         * Disparar evento personalizado
         */
        triggerEvent(eventName, detail) {
            const event = new CustomEvent(eventName, { detail });
            document.dispatchEvent(event);
        }
    }

    /**
     * Inicializar modales
     */
    window.EWMModal = {
        instances: {},
        
        init: function(modalId) {
            if (!this.instances[modalId]) {
                this.instances[modalId] = new EWMModal(modalId);
            }
            return this.instances[modalId];
        },
        
        open: function(modalId) {
            if (this.instances[modalId]) {
                this.instances[modalId].open();
            }
        },
        
        close: function(modalId) {
            if (this.instances[modalId]) {
                this.instances[modalId].close();
            }
        }
    };

    // Auto-inicializar modales al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        const modals = document.querySelectorAll('[id^="ewm-modal-"]');
        modals.forEach(modal => {
            const modalId = modal.id.replace('ewm-modal-', '');
            window.EWMModal.init(modalId);
        });
    });

})();
