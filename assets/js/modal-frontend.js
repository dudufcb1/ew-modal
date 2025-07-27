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
            this.triggers = config.triggers || {};
            
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
            
            // Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isVisible) {
                    this.hide();
                }
            });
            
            console.log('EWM Modal Frontend: Events bound');
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
                    if (!this.shownInThisPageLoad) { // Solo mostrar si no se ha mostrado
                        this.show();
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
                    this.show();
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
         * Mostrar modal (con validación de frecuencia)
         */
        async show() {
            console.log('EWM Modal Frontend: show() called');
            console.log('EWM Modal Frontend: this.modal exists:', !!this.modal);
            console.log('EWM Modal Frontend: this.isVisible:', this.isVisible);

            if (!this.modal || this.isVisible) {
                console.log('EWM Modal Frontend: Show aborted. Modal not found or already visible.');
                console.log('EWM Modal Frontend: Reason - !this.modal:', !this.modal, 'this.isVisible:', this.isVisible);
                return;
            }

            // Validar frecuencia antes de mostrar
            const canShow = await this.checkFrequencyLimit();
            if (!canShow) {
                console.log('EWM Modal Frontend: Modal blocked by frequency limit');
                return;
            }

            this.modal.style.display = 'flex';
            this.isVisible = true;
            this.shownInThisPageLoad = true; // Marcar como mostrado en esta carga de página
            document.body.style.overflow = 'hidden'; // Evitar scroll de la página de fondo

            // Poblar campos automáticamente con datos del usuario
            await this.autofillFieldsFromUserProfile();

            // Registrar visualización en servidor (transients)
            this.registerView();

            // Animación
            setTimeout(() => {
                this.modal.classList.add('ewm-modal-visible');
            }, 10);

            console.log('EWM Modal Frontend: Modal shown');
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
