/**
 * EWM Modal Frontend JavaScript
 * Maneja la funcionalidad del modal en el frontend (Vanilla JS)
 */
(function() {
    'use strict';

    class EWMModalFrontend {
        /**
         * Poblar campos del modal con datos del usuario (REST API)
         */
        async autofillFieldsFromUserProfile() {
            try {
                const response = await fetch('/wp-json/ewm/v1/user-profile', {
                    credentials: 'same-origin',
                    headers: {
                        'X-WP-Nonce': (window.ewmModal && window.ewmModal.nonce) ? window.ewmModal.nonce : ''
                    }
                });
                if (!response.ok) return;
                const data = await response.json();
                if (!data || typeof data !== 'object') return;

                // Poblar campos si existen en el modal
                if (this.modal) {
                    if (data.first_name && this.modal.querySelector('input[name="first_name"]')) {
                        this.modal.querySelector('input[name="first_name"]').value = data.first_name;
                    }
                    if (data.last_name && this.modal.querySelector('input[name="last_name"]')) {
                        this.modal.querySelector('input[name="last_name"]').value = data.last_name;
                    }
                    if (data.email && this.modal.querySelector('input[name="email"]')) {
                        this.modal.querySelector('input[name="email"]').value = data.email;
                    }
                }
            } catch (e) {
                console.log('EWM Modal Frontend: Error autofill user profile', e);
            }
        }
        constructor(config) {
            this.config = config;
            this.currentStep = 1;
            this.totalSteps = config.steps?.steps?.length || 1;
            this.formData = {};
            this.modal = null;
            this.isVisible = false;
            this.shownInThisPageLoad = false; // Flag para evitar re-mostrar en la misma carga de página
            console.log(`[EWM LOG] [PAGE LOAD] Inicializado flag shownInThisPageLoad = false para modal ${this.config.modal_id}`);
            this.triggers = config.triggers || {};
            // Guardar timestamp de entrada
            this.ewmEntryTimestamp = Date.now();
            this.init();
        }

        /**
         * Inicializar el modal
         */
        init() {
            console.log('EWM Modal Frontend: Initializing modal', this.config.modal_id);
            
            this.createModal();
            this.bindEvents();
            this.initTriggers();
            
            console.log('EWM Modal Frontend: Modal initialized successfully');
        }

        /**
         * Obtener modal existente del DOM
         */
        createModal() {
            // Buscar modal existente en el DOM (renderizado por el backend)
            this.modal = document.getElementById(`ewm-modal-${this.config.modal_id}`);

            if (!this.modal) {
                console.error(`EWM Modal Frontend: Modal with ID ewm-modal-${this.config.modal_id} not found in DOM`);
                return;
            }

            console.log('EWM Modal Frontend: Modal found in DOM');
        }



        /**
         * Generar HTML de los pasos
         */
        generateStepsHtml() {
            const steps = this.config.steps?.steps || [];
            const progressBar = this.config.steps?.progressBar || {};
            
            let html = '';
            
            // Barra de progreso
            if (progressBar.enabled !== false && steps.length > 1) {
                html += `
                    <div class="ewm-progress-bar" data-style="${progressBar.style || 'line'}">
                        <div class="ewm-progress-fill" style="width: ${(1 / steps.length) * 100}%; background-color: ${progressBar.color || '#ff6b35'};"></div>
                    </div>
                `;
            }
            
            // Título principal
            if (this.config.title) {
                html += `<h2 class="ewm-modal-title">${this.config.title}</h2>`;
            }
            
            // Contenedor de pasos
            html += '<div class="ewm-steps-container">';
            
            steps.forEach((step, index) => {
                const isActive = index === 0 ? 'active' : '';
                html += `
                    <div class="ewm-step ${isActive}" data-step="${index + 1}">
                        ${step.title ? `<h3 class="ewm-step-title">${step.title}</h3>` : ''}
                        ${step.subtitle ? `<p class="ewm-step-subtitle">${step.subtitle}</p>` : ''}
                        ${step.description ? `<p class="ewm-step-description">${step.description}</p>` : ''}
                        
                        <div class="ewm-step-fields">
                            ${this.generateFieldsHtml(step.fields || [])}
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            
            return html;
        }

        /**
         * Generar HTML de los campos
         */
        generateFieldsHtml(fields) {
            let html = '';
            
            fields.forEach(field => {
                html += `<div class="ewm-field-wrapper">`;
                
                if (field.label) {
                    html += `<label class="ewm-field-label">${field.label}${field.required ? ' *' : ''}</label>`;
                }
                
                switch (field.type) {
                    case 'text':
                    case 'email':
                    case 'tel':
                    case 'url':
                    case 'number':
                    case 'date':
                        html += `<input type="${field.type}" name="${field.id}" id="${field.id}" class="ewm-field-input" placeholder="${field.placeholder || ''}" ${field.required ? 'required' : ''}>`;
                        break;
                        
                    case 'textarea':
                        html += `<textarea name="${field.id}" id="${field.id}" class="ewm-field-textarea" placeholder="${field.placeholder || ''}" ${field.required ? 'required' : ''}></textarea>`;
                        break;
                        
                    case 'select':
                        html += `<select name="${field.id}" id="${field.id}" class="ewm-field-select" ${field.required ? 'required' : ''}>`;
                        html += `<option value="">Seleccionar...</option>`;
                        if (field.options) {
                            const options = field.options.split('\n');
                            options.forEach(option => {
                                const trimmed = option.trim();
                                if (trimmed) {
                                    html += `<option value="${trimmed}">${trimmed}</option>`;
                                }
                            });
                        }
                        html += `</select>`;
                        break;
                        
                    case 'radio':
                        if (field.options) {
                            const options = field.options.split('\n');
                            options.forEach((option, index) => {
                                const trimmed = option.trim();
                                if (trimmed) {
                                    html += `
                                        <div class="ewm-radio-option">
                                            <input type="radio" name="${field.id}" id="${field.id}_${index}" value="${trimmed}" ${field.required ? 'required' : ''}>
                                            <label for="${field.id}_${index}">${trimmed}</label>
                                        </div>
                                    `;
                                }
                            });
                        }
                        break;
                        
                    case 'checkbox':
                        if (field.options) {
                            const options = field.options.split('\n');
                            options.forEach((option, index) => {
                                const trimmed = option.trim();
                                if (trimmed) {
                                    html += `
                                        <div class="ewm-checkbox-option">
                                            <input type="checkbox" name="${field.id}[]" id="${field.id}_${index}" value="${trimmed}">
                                            <label for="${field.id}_${index}">${trimmed}</label>
                                        </div>
                                    `;
                                }
                            });
                        }
                        break;
                }
                
                html += `</div>`;
            });
            
            return html;
        }

        /**
         * Vincular eventos
         */
        bindEvents() {
            if (!this.modal) return;
            
            // Cerrar modal
            const closeBtn = this.modal.querySelector('.ewm-modal-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => this.hide());
            }

            // Cerrar modal de anuncio
            const closeAnnouncementBtn = this.modal.querySelector('.ewm-btn-close-announcement');
            if (closeAnnouncementBtn) {
                closeAnnouncementBtn.addEventListener('click', () => this.hide());
            }

            // Cerrar al hacer clic en el overlay
            this.modal.addEventListener('click', (e) => {
                if (e.target === this.modal) {
                    this.hide();
                }
            });
            
            // Navegación entre pasos
            const nextBtn = this.modal.querySelector('.ewm-next-step');
            const prevBtn = this.modal.querySelector('.ewm-prev-step');
            
            if (nextBtn) {
                nextBtn.addEventListener('click', () => this.nextStep());
            }
            
            if (prevBtn) {
                prevBtn.addEventListener('click', () => this.prevStep());
            }
            
            // NUEVO: Manejo de envío de formulario
            this.bindFormSubmit();
            
            // Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isVisible) {
                    this.hide();
                }
            });
            
            console.log('EWM Modal Frontend: Events bound');
        }

        /**
         * Vincular eventos de envío de formulario
         */
        bindFormSubmit() {
            if (!this.modal) return;

            // Buscar todos los botones de submit
            const submitButtons = this.modal.querySelectorAll('.ewm-btn-submit');
            const form = this.modal.querySelector('.ewm-multi-step-form');

            if (!form) {
                console.log('EWM Modal Frontend: No form found in modal');
                return;
            }

            // Event listener para botones submit
            submitButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.handleFormSubmit(e);
                });
            });

            // Event listener para submit del formulario
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmit(e);
            });

            console.log(`EWM Modal Frontend: Form submit events bound (${submitButtons.length} buttons)`);
        }

        /**
         * Manejar envío de formulario
         */
        async handleFormSubmit(e) {
            e.preventDefault();

            console.log('EWM Modal Frontend: Form submit initiated');
            console.log('EWM Modal Frontend: Modal ID:', this.config.modal_id);
            console.log('EWM Modal Frontend: Window ewmModal config:', window.ewmModal);

            const submitButton = e.target.closest('.ewm-btn-submit') || e.target;
            const form = this.modal.querySelector('.ewm-multi-step-form');

            // VALIDACIÓN HTML5 NATIVA ANTES DEL ENVÍO
            if (form && !form.checkValidity()) {
                console.log('EWM Modal Frontend: Form validation failed (HTML5)');

                // Mostrar errores nativos del navegador
                form.reportValidity();

                // Rehabilitar botón si estaba deshabilitado
                if (submitButton) {
                    submitButton.disabled = false;
                    if (submitButton.textContent === 'Enviando...') {
                        submitButton.textContent = 'Enviar';
                    }
                }

                return; // Detener el envío
            }

            // VALIDACIÓN ADICIONAL DE JAVASCRIPT
            if (window.EWMFormValidator && form) {
                const currentStep = form.querySelector('.ewm-form-step.active');
                if (currentStep) {
                    const validationResult = window.EWMFormValidator.validateStep(currentStep);
                    if (!validationResult.isValid) {
                        console.log('EWM Modal Frontend: Form validation failed (JavaScript)');

                        // Mostrar errores específicos
                        this.showErrorMessage('Por favor, corrige los errores en el formulario antes de continuar.');

                        // Rehabilitar botón
                        if (submitButton) {
                            submitButton.disabled = false;
                            if (submitButton.textContent === 'Enviando...') {
                                submitButton.textContent = 'Enviar';
                            }
                        }

                        return; // Detener el envío
                    }
                }
            }

            // Deshabilitar botón durante envío (solo si pasó todas las validaciones)
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Enviando...';
            }

            try {
                // Recopilar datos del formulario
                const formData = this.collectFormData();
                console.log('EWM Modal Frontend: Form data collected:', formData);

                // Validar datos básicos
                if (!formData || Object.keys(formData).length === 0) {
                    throw new Error('No se pudieron recopilar los datos del formulario');
                }

                // Enviar al backend
                console.log('EWM Modal Frontend: Attempting to submit form data...');
                const response = await this.submitFormData(formData);
                console.log('EWM Modal Frontend: Form submitted successfully:', response);

                // NUEVO: Marcar como enviado exitosamente (bloquear por 2 días)
                this.markAsSuccessfullySubmitted();

                // Mostrar paso de éxito
                this.showSuccessStep();

                // Auto-cerrar después de 3 segundos
                setTimeout(() => {
                    this.hide();
                }, 3000);

            } catch (error) {
                console.error('EWM Modal Frontend: Form submission error:', error);
                console.error('EWM Modal Frontend: Error details:', {
                    message: error.message,
                    stack: error.stack,
                    name: error.name
                });
                this.showErrorMessage(error.message || 'Error submitting form');
                
                // Rehabilitar botón
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Enviar';
                }
            }
        }

        /**
         * Recopilar datos del formulario multi-paso
         */
        collectFormData() {
            const form = this.modal.querySelector('.ewm-multi-step-form');
            if (!form) {
                console.error('EWM Modal Frontend: Form not found');
                return {};
            }

            const formData = {};
            const stepData = [];

            // Recopilar datos de todos los pasos
            const steps = form.querySelectorAll('.ewm-form-step');
            steps.forEach((step, index) => {
                if (step.classList.contains('ewm-success-step')) {
                    return; // Skip success step
                }

                const stepId = step.dataset.step || `step_${index + 1}`;
                const fields = step.querySelectorAll('.ewm-field-input');
                const stepFields = {};

                fields.forEach(field => {
                    const name = field.name || field.id;
                    if (!name) return;

                    let value = '';
                    
                    if (field.type === 'checkbox') {
                        if (field.name.endsWith('[]')) {
                            // Multiple checkboxes
                            const baseName = field.name.replace('[]', '');
                            if (!stepFields[baseName]) stepFields[baseName] = [];
                            if (field.checked) {
                                stepFields[baseName].push(field.value);
                            }
                        } else {
                            // Single checkbox
                            value = field.checked ? (field.value || '1') : '';
                        }
                    } else if (field.type === 'radio') {
                        if (field.checked) {
                            value = field.value;
                        } else {
                            return; // Skip unchecked radios
                        }
                    } else {
                        // Text, email, tel, etc.
                        value = field.value.trim();
                    }

                    if (value !== '' && !field.name.endsWith('[]')) {
                        stepFields[name] = value;
                        formData[name] = value; // Also add to flat structure
                    }
                });

                if (Object.keys(stepFields).length > 0) {
                    stepData.push({
                        step_id: stepId,
                        fields: stepFields
                    });
                }
            });

            console.log('EWM Modal Frontend: Collected step data:', stepData);
            console.log('EWM Modal Frontend: Collected form data:', formData);

            return {
                form_data: formData,
                step_data: stepData,
                modal_id: this.config.modal_id
            };
        }

        /**
         * Enviar datos al backend via REST API
         */
        async submitFormData(data) {
            const restUrl = (window.ewmModal && window.ewmModal.restUrl) ? 
                window.ewmModal.restUrl : '/wp-json/ewm/v1/';

            console.log('EWM Modal Frontend: REST URL:', restUrl);
            console.log('EWM Modal Frontend: Full submit URL:', restUrl + 'submit-form');

            // Para modales públicos, NUNCA usar nonce (evita rest_cookie_invalid_nonce)
            const headers = {
                'Content-Type': 'application/json'
            };

            console.log('EWM Modal Frontend: No nonce sent (public endpoint)');
            console.log('EWM Modal Frontend: Request headers:', headers);
            console.log('EWM Modal Frontend: Request data:', data);

            const requestOptions = {
                method: 'POST',
                headers: headers,
                credentials: 'same-origin',
                body: JSON.stringify(data)
            };

            console.log('EWM Modal Frontend: Full request options:', requestOptions);

            try {
                console.log('EWM Modal Frontend: Making fetch request...');
                const response = await fetch(restUrl + 'submit-form', requestOptions);
                
                console.log('EWM Modal Frontend: Response received:', {
                    status: response.status,
                    statusText: response.statusText,
                    ok: response.ok,
                    headers: Array.from(response.headers.entries())
                });

                if (!response.ok) {
                    let errorMessage = `HTTP ${response.status}: ${response.statusText}`;
                    
                    try {
                        const responseText = await response.text();
                        console.log('EWM Modal Frontend: Error response text:', responseText);
                        
                        const errorData = JSON.parse(responseText);
                        console.log('EWM Modal Frontend: Parsed error data:', errorData);
                        
                        if (errorData && errorData.message) {
                            errorMessage = errorData.message;
                        } else if (errorData && errorData.code) {
                            errorMessage = `Error ${errorData.code}: ${errorData.message || 'Error en la solicitud'}`;
                        }
                    } catch (parseError) {
                        console.log('EWM Modal Frontend: Could not parse error response:', parseError);
                        console.log('EWM Modal Frontend: Raw error response:', responseText || 'No response text');
                    }
                    
                    throw new Error(errorMessage);
                }

                const responseText = await response.text();
                console.log('EWM Modal Frontend: Success response text:', responseText);
                
                const result = JSON.parse(responseText);
                console.log('EWM Modal Frontend: Parsed success result:', result);
                
                return result;

            } catch (fetchError) {
                console.error('EWM Modal Frontend: Fetch error:', fetchError);
                console.error('EWM Modal Frontend: Fetch error type:', fetchError.constructor.name);
                throw fetchError;
            }
        }

        /**
         * Mostrar paso de éxito
         */
        showSuccessStep() {
            const form = this.modal.querySelector('.ewm-multi-step-form');
            if (!form) return;

            // Ocultar todos los pasos
            const steps = form.querySelectorAll('.ewm-form-step');
            steps.forEach(step => {
                step.style.display = 'none';
            });

            // Mostrar paso de éxito
            let successStep = form.querySelector('.ewm-success-step');
            
            // Valores por defecto (fallback)
            const defaultTitle = '¡Gracias!';
            const defaultMessage = 'Tu información ha sido enviada correctamente.';

            if (successStep) {
                // Obtener mensajes personalizados de los atributos data
                const customTitle = successStep.getAttribute('data-success-title');
                const customMessage = successStep.getAttribute('data-success-message');

                // Actualizar contenido usando textContent para prevenir XSS
                const titleElement = successStep.querySelector('h3');
                const messageElement = successStep.querySelector('p');

                if (titleElement) {
                    titleElement.textContent = customTitle || defaultTitle;
                }
                if (messageElement) {
                    messageElement.textContent = customMessage || defaultMessage;
                }

                successStep.style.display = 'block';
            } else {
                // Crear paso de éxito si no existe
                successStep = document.createElement('div');
                successStep.className = 'ewm-form-step ewm-success-step';
                
                const successContent = document.createElement('div');
                successContent.className = 'ewm-success-content';

                const title = document.createElement('h3');
                title.textContent = defaultTitle;

                const message = document.createElement('p');
                message.textContent = defaultMessage;

                const icon = document.createElement('div');
                icon.className = 'ewm-success-icon';
                icon.textContent = '✓';

                successContent.appendChild(title);
                successContent.appendChild(message);
                successContent.appendChild(icon);
                successStep.appendChild(successContent);
                form.appendChild(successStep);
            }

            console.log('EWM Modal Frontend: Success step shown');
        }

        /**
         * Mostrar mensaje de error
         */
        showErrorMessage(message) {
            // Buscar o crear contenedor de notificaciones
            let notificationContainer = this.modal.querySelector('.ewm-notifications-container');
            if (!notificationContainer) {
                notificationContainer = document.createElement('div');
                notificationContainer.className = 'ewm-notifications-container';
                const modalBody = this.modal.querySelector('.ewm-modal-body');
                if (modalBody) {
                    modalBody.insertBefore(notificationContainer, modalBody.firstChild);
                }
            }

            // Crear mensaje de error
            const errorDiv = document.createElement('div');
            errorDiv.className = 'ewm-notification ewm-error';
            errorDiv.innerHTML = `
                <span class="ewm-notification-text">${message}</span>
                <button type="button" class="ewm-notification-close">&times;</button>
            `;

            // Limpiar notificaciones previas y agregar nueva
            notificationContainer.innerHTML = '';
            notificationContainer.appendChild(errorDiv);
            notificationContainer.style.display = 'block';

            // Cerrar notificación al hacer clic
            errorDiv.querySelector('.ewm-notification-close').addEventListener('click', () => {
                errorDiv.remove();
                if (notificationContainer.children.length === 0) {
                    notificationContainer.style.display = 'none';
                }
            });

            // Auto-cerrar después de 5 segundos
            setTimeout(() => {
                if (errorDiv.parentNode) {
                    errorDiv.remove();
                    if (notificationContainer.children.length === 0) {
                        notificationContainer.style.display = 'none';
                    }
                }
            }, 5000);

            console.log('EWM Modal Frontend: Error message shown:', message);
        }

        /**
         * Inicializar triggers
         */
        initTriggers() {
            // Exit intent
            if (this.triggers.exit_intent?.enabled) {
                this.initExitIntent();
            }

            // Time delay
            if (this.triggers.time_delay?.enabled) {
                const delay = this.triggers.time_delay.delay || 5000;
                setTimeout(() => {
                    console.log(`[EWM LOG] [PAGE LOAD] Evaluando trigger time_delay para modal ${this.config.modal_id}. shownInThisPageLoad=${this.shownInThisPageLoad}`);
                    if (!this.shownInThisPageLoad) { // Solo mostrar si no se ha mostrado
                        console.log(`[EWM LOG] [PAGE LOAD] Trigger time_delay cumple condiciones, llamando show() para modal ${this.config.modal_id}`);
                        this.show();
                    } else {
                        console.log(`[EWM LOG] [PAGE LOAD] Trigger time_delay bloqueado por flag shownInThisPageLoad=true para modal ${this.config.modal_id}`);
                    }
                }, delay);
            }

            // Scroll percentage
            if (this.triggers.scroll_percentage?.enabled) {
                this.initScrollTrigger();
            }

            // Manual trigger
            if (this.triggers.manual?.enabled) {
                this.initManualTrigger();
            }

            console.log('EWM Modal Frontend: Triggers initialized');
        }

        /**
         * Inicializar exit intent
         */
        initExitIntent() {
            document.addEventListener('mouseleave', (e) => {
                // Solo mostrar si: mouse sale por arriba, modal no visible Y no se ha mostrado en esta carga
                if (e.clientY <= 0 && !this.isVisible && !this.shownInThisPageLoad) {
                    // Verificar tiempo mínimo de navegación
                    const minSeconds = this.triggers.exit_intent?.min_seconds ?? 10;
                    const now = Date.now();
                    const elapsed = (now - this.ewmEntryTimestamp) / 1000;
                    if (elapsed >= minSeconds) {
                        this.show();
                    } else {
                        console.log(`EWM Modal Frontend: Exit intent bloqueado, solo ${elapsed.toFixed(1)}s en página (mínimo ${minSeconds}s)`);
                    }
                }
            });
        }

        /**
         * Inicializar scroll trigger
         */
        initScrollTrigger() {
            const percentage = this.triggers.scroll_percentage.percentage || 50;
            
            window.addEventListener('scroll', () => {
                const scrolled = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
                
                // Solo mostrar si se alcanza porcentaje, no está visible Y no se ha mostrado
                if (scrolled >= percentage && !this.isVisible && !this.shownInThisPageLoad) {
                    this.show();
                }
            });
        }

        /**
         * Inicializar trigger manual
         */
        initManualTrigger() {
            // Buscar elementos con data-ewm-modal
            const triggers = document.querySelectorAll(`[data-ewm-modal="${this.config.modal_id}"]`);

            triggers.forEach(trigger => {
                trigger.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.show();
                });
            });
        }

        /**
         * Helper para obtener cookies
         */
        getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }

        /**
         * Registrar visualización del modal en el servidor (transients)
         */
        registerView() {
            // Solo registrar si hay configuración AJAX disponible
            if (typeof ewmModal === 'undefined' || !ewmModal.ajaxUrl) {
                console.log('EWM Modal Frontend: AJAX not available for view registration');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'ewm_register_modal_view');
            formData.append('modal_id', this.config.modal_id);
            formData.append('nonce', ewmModal.nonce);

            fetch(ewmModal.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('EWM Modal Frontend: View registered successfully', data.data);
                } else {
                    console.log('EWM Modal Frontend: Failed to register view', data.data);
                }
            })
            .catch(error => {
                console.log('EWM Modal Frontend: Error registering view', error);
            });
        }

        /**
         * Verificar si el modal está bloqueado por envío exitoso (localStorage)
         */
        isBlockedBySuccessfulSubmission() {
            try {
                const key = `ewm_modal_${this.config.modal_id}_submitted`;
                const submittedData = localStorage.getItem(key);

                if (!submittedData) {
                    console.log('EWM Modal Frontend: No submission data found in localStorage');
                    return false;
                }

                const { timestamp, modalId } = JSON.parse(submittedData);
                const twoDaysInMs = 2 * 24 * 60 * 60 * 1000; // 2 días en milisegundos
                const now = Date.now();
                const elapsed = now - timestamp;

                console.log('EWM Modal Frontend: Submission check:', {
                    modalId: modalId,
                    submittedAt: new Date(timestamp).toISOString(),
                    elapsedHours: (elapsed / (1000 * 60 * 60)).toFixed(1),
                    isBlocked: elapsed < twoDaysInMs
                });

                return elapsed < twoDaysInMs;
            } catch (error) {
                console.error('EWM Modal Frontend: Error checking submission block:', error);
                return false; // En caso de error, no bloquear
            }
        }

        /**
         * Marcar modal como enviado exitosamente (localStorage)
         */
        markAsSuccessfullySubmitted() {
            try {
                const key = `ewm_modal_${this.config.modal_id}_submitted`;
                const data = {
                    timestamp: Date.now(),
                    modalId: this.config.modal_id,
                    submittedAt: new Date().toISOString()
                };

                localStorage.setItem(key, JSON.stringify(data));
                console.log('EWM Modal Frontend: Modal marked as successfully submitted:', data);
            } catch (error) {
                console.error('EWM Modal Frontend: Error marking submission:', error);
            }
        }

        /**
         * Verificar si el modal puede mostrarse según la configuración de frecuencia
         */
        async checkFrequencyLimit() {
            const frequencyConfig = this.triggers.frequency || {};
            const frequencyType = frequencyConfig.type || 'always';

            console.log('EWM Modal Frontend: Checking frequency limit:', {
                type: frequencyType,
                limit: frequencyConfig.limit,
                modalId: this.config.modal_id
            });

            // Si es tipo 'always', permitir siempre
            if (frequencyType === 'always') {
                console.log('EWM Modal Frontend: Frequency type is always, showing modal');
                return true;
            }

            // Para otros tipos, verificar con el servidor
            if (typeof ewmModal === 'undefined' || !ewmModal.ajaxUrl) {
                console.log('EWM Modal Frontend: AJAX not available, defaulting to show');
                return true;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'ewm_check_modal_frequency');
                formData.append('modal_id', this.config.modal_id);
                formData.append('nonce', ewmModal.nonce);

                const response = await fetch(ewmModal.ajaxUrl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await response.json();

                if (data.success) {
                    console.log('EWM Modal Frontend: Frequency check result:', data.data);
                    return data.data.can_show || false;
                } else {
                    console.log('EWM Modal Frontend: Frequency check failed:', data.data);
                    return false;
                }
            } catch (error) {
                console.log('EWM Modal Frontend: Error checking frequency:', error);
                return false; // En caso de error, no mostrar por seguridad
            }
        }

        /**
         * Verificar visibilidad WooCommerce (solo para modales con integración WC)
         */
        async checkWooCommerceVisibility() {
            console.log(`[EWM LOG] [WC] checkWooCommerceVisibility iniciado para modal ${this.config.modal_id}`);
            console.log(`[EWM LOG] [WC] Config completa:`, this.config);

            // Solo verificar si el modal tiene configuración WooCommerce
            const hasWCConfig = this.config.wc_integration && this.config.wc_integration.enabled;
            console.log(`[EWM LOG] [WC] ¿Tiene config WC?`, hasWCConfig);
            console.log(`[EWM LOG] [WC] Config WC:`, this.config.wc_integration);

            if (!hasWCConfig) {
                console.log(`[EWM LOG] [WC] No es modal WooCommerce, permitiendo mostrar`);
                return { should_show: true, reason: 'not a WooCommerce modal' };
            }

            // Intentar obtener product_id de la página actual
            const productId = this.getProductIdFromPage();
            console.log(`[EWM LOG] [WC] Product ID detectado:`, productId);
            if (!productId) {
                console.log(`[EWM LOG] [WC] No es página de producto, permitiendo mostrar`);
                return { should_show: true, reason: 'not a product page' };
            }

            try {
                console.log(`[EWM LOG] [WC] Checking WooCommerce visibility for modal ${this.config.modal_id}, product ${productId}`);

                const response = await fetch(`${ewmModal.restUrl}test-modal-visibility/${this.config.modal_id}/${productId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    console.log(`[EWM LOG] [WC] Visibility check response:`, data);

                    return {
                        should_show: data.result === 'will show',
                        reason: data.reason || 'unknown',
                        modal_id: data.modal_id,
                        product_id: data.product_id
                    };
                } else {
                    console.warn(`[EWM LOG] [WC] Visibility check failed, status:`, response.status);
                    return { should_show: true, reason: 'visibility check failed, allowing display' };
                }
            } catch (error) {
                console.error(`[EWM LOG] [WC] Error checking WooCommerce visibility:`, error);
                return { should_show: true, reason: 'visibility check error, allowing display' };
            }
        }

        /**
         * Obtener product_id de la página actual
         */
        getProductIdFromPage() {
            // Método 1: Buscar en el body class
            const bodyClasses = document.body.className;
            const productMatch = bodyClasses.match(/postid-(\d+)/);
            if (productMatch) {
                return parseInt(productMatch[1]);
            }

            // Método 2: Buscar en datos del producto WooCommerce
            if (typeof wc_single_product_params !== 'undefined' && wc_single_product_params.post_id) {
                return parseInt(wc_single_product_params.post_id);
            }

            // Método 3: Buscar en el DOM
            const productElement = document.querySelector('[data-product-id]');
            if (productElement) {
                return parseInt(productElement.getAttribute('data-product-id'));
            }

            return null;
        }

        /**
         * Mostrar modal (con validación de frecuencia y envío exitoso)
         */
        async show() {
            console.log('EWM Modal Frontend: show() called');
            console.log('EWM Modal Frontend: this.modal exists:', !!this.modal);
            console.log('EWM Modal Frontend: this.isVisible:', this.isVisible);

            if (!this.modal || this.isVisible) {
                console.log(`[EWM LOG] [PAGE LOAD] Show abortado. Modal no encontrado (${!this.modal}) o ya visible (${this.isVisible}) para modal ${this.config.modal_id}`);
                return;
            }

            // NUEVO: Verificar si está bloqueado por envío exitoso
            if (this.isBlockedBySuccessfulSubmission()) {
                console.log(`[EWM LOG] [PAGE LOAD] Modal bloqueado por envío exitoso reciente para modal ${this.config.modal_id}`);
                return;
            }

            // Validar frecuencia antes de mostrar
            const canShow = await this.checkFrequencyLimit();
            if (!canShow) {
                console.log(`[EWM LOG] [PAGE LOAD] Modal bloqueado por frecuencia para modal ${this.config.modal_id}`);
                return;
            }

            // NUEVA VERIFICACIÓN: Para modales WooCommerce, verificar cupones aplicados
            console.log(`[EWM LOG] [WC] Iniciando verificación WooCommerce para modal ${this.config.modal_id}`);
            const wcVisibilityCheck = await this.checkWooCommerceVisibility();
            console.log(`[EWM LOG] [WC] Resultado verificación WooCommerce:`, wcVisibilityCheck);
            if (!wcVisibilityCheck.should_show) {
                console.log(`[EWM LOG] [WC] Modal bloqueado por WooCommerce: ${wcVisibilityCheck.reason} para modal ${this.config.modal_id}`);
                return;
            }
            console.log(`[EWM LOG] [WC] Modal aprobado por verificación WooCommerce para modal ${this.config.modal_id}`);

            this.modal.style.display = 'flex';
            this.isVisible = true;
            this.shownInThisPageLoad = true; // Marcar como mostrado en esta carga de página
            console.log(`[EWM LOG] [PAGE LOAD] Modal mostrado, flag shownInThisPageLoad=true para modal ${this.config.modal_id}`);
            document.body.style.overflow = 'hidden'; // Evitar scroll de la página de fondo

            // Poblar campos automáticamente con datos del usuario
            await this.autofillFieldsFromUserProfile();

            // Registrar visualización en servidor (transients)
            this.registerView();

            // Animación
            setTimeout(() => {
                this.modal.classList.add('ewm-modal-visible');
            }, 10);

            console.log(`[EWM LOG] [PAGE LOAD] Modal shown (animación activada) para modal ${this.config.modal_id}`);
        }

        /**
         * Ocultar modal
         */
        hide() {
            if (!this.modal || !this.isVisible) return;

            this.modal.classList.remove('ewm-modal-visible');

            setTimeout(() => {
                this.modal.style.display = 'none';
                this.isVisible = false;
                document.body.style.overflow = ''; // Restaurar scroll de la página de fondo
            }, 300);

            console.log('EWM Modal Frontend: Modal hidden');
        }
    }

    // Exponer globalmente
    window.EWMModalFrontend = EWMModalFrontend;

    // Auto-inicializar modales si hay configuración
    document.addEventListener('DOMContentLoaded', function() {
        console.log('EWM Modal Frontend: DOM loaded, checking for modal configs...');

        if (typeof window.ewm_modal_configs !== 'undefined' && window.ewm_modal_configs.length > 0) {
            console.log(`EWM Modal Frontend: Found ${window.ewm_modal_configs.length} modal configs`);
            window.ewm_modal_configs.forEach(config => {
                console.log('EWM Modal Frontend: Initializing modal', config.modal_id);
                new EWMModalFrontend(config);
            });
        } else {
            console.log('EWM Modal Frontend: No modal configs found');
        }
    });

})();
