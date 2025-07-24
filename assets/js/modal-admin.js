/**
 * EWM Modal Admin JavaScript
 * Maneja la interfaz de administración del Modal Builder
 */
(function($) {
    'use strict';

    console.log('🔥 MODAL-ADMIN.JS LOADED WITH FIXES! 🔥');

    const EWMModalAdmin = {
        // Variables globales
        currentModalId: null,
        isLoading: false,

        /**
         * Inicializar el admin
         */
        init: function() {
            console.log('EWM Modal Admin: Initializing...');

            // Verificar que tenemos las variables necesarias
            if (typeof ewm_admin_vars === 'undefined') {
                console.error('EWM Modal Admin: ewm_admin_vars not found');
                return;
            }

            this.currentModalId = ewm_admin_vars.modal_id;
            this.bindEvents();
            this.initTabs();
            this.initColorPickers();
            this.initStepsManager();

            // Cargar datos del modal si estamos editando
            if (this.currentModalId) {
                this.loadModalData();
            }

            // Inicializar estado de WooCommerce
            this.initWCIntegration();

            console.log('EWM Modal Admin: Initialized successfully');
        },

        /**
         * Vincular eventos
         */
        bindEvents: function() {
            // Botón guardar modal
            $(document).on('click', '#ewm-save-modal', this.saveModal.bind(this));

            // Botón preview
            $(document).on('click', '#ewm-preview-modal', this.previewModal.bind(this));

            // Botón copiar shortcode
            $(document).on('click', '.ewm-copy-shortcode', this.copyShortcode.bind(this));

            // Botón limpiar formulario
            $(document).on('click', '[data-action="clear"]', this.clearForm.bind(this));

            // Agregar paso
            $(document).on('click', '.ewm-add-step', this.addStep.bind(this));

            // Eliminar paso
            $(document).on('click', '.ewm-remove-step', this.removeStep.bind(this));

            // WooCommerce integration toggle
            $(document).on('change', '#wc-integration-enabled', this.toggleWCIntegration.bind(this));

            // WooCommerce timer toggle
            $(document).on('change', '#wc-timer-enabled', this.toggleWCTimer.bind(this));
        },

        /**
         * Inicializar sistema de tabs
         */
        initTabs: function() {
            $('.ewm-tabs-nav a').on('click', function(e) {
                e.preventDefault();

                const targetTab = $(this).attr('href');

                // Remover clase active de todos los tabs
                $('.ewm-tabs-nav a').removeClass('active');
                $('.ewm-tab-pane').removeClass('active');

                // Activar tab seleccionado
                $(this).addClass('active');
                $(targetTab).addClass('active');

                console.log('EWM Modal Admin: Tab switched to', targetTab);
            });
        },

        /**
         * Inicializar color pickers
         */
        initColorPickers: function() {
            if ($.fn.wpColorPicker) {
                $('.ewm-color-picker input[type="text"]').wpColorPicker({
                    change: function(event, ui) {
                        const color = ui.color.toString();
                        $(this).siblings('.ewm-color-preview').css('background-color', color);
                    }
                });
            }
        },

        /**
         * Inicializar gestor de pasos
         */
        initStepsManager: function() {
            // Hacer los pasos sortables si jQuery UI está disponible
            if ($.fn.sortable) {
                $('.ewm-steps-config').sortable({
                    handle: '.ewm-step-handle',
                    placeholder: 'ewm-step-placeholder',
                    update: function() {
                        console.log('EWM Modal Admin: Steps reordered');
                    }
                });
            }
        },

        /**
         * Cargar datos del modal
         */
        loadModalData: function() {
            if (!this.currentModalId || this.isLoading) {
                return;
            }

            this.isLoading = true;
            this.showLoading('Cargando datos del modal...');

            $.ajax({
                url: ewm_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'ewm_load_modal_builder',
                    modal_id: this.currentModalId,
                    nonce: ewm_admin_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // 🔍 LOG ANTES: Datos cargados desde el backend
                        console.log('🔍 PAYLOAD LOG - ANTES (Datos cargados desde backend):', JSON.stringify(response.data, null, 2));
                        console.log('🔍 PAYLOAD LOG - ANTES (Timestamp):', new Date().toISOString());

                        EWMModalAdmin.populateForm(response.data);

                        // Disparar evento para que el builder v2 pueda cargar los steps
                        $(document).trigger('ewm-modal-data-loaded', [response.data]);

                        console.log('EWM Modal Admin: Modal data loaded successfully');
                    } else {
                        EWMModalAdmin.showError('Error al cargar el modal: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('EWM Modal Admin: AJAX error loading modal', error);
                    EWMModalAdmin.showError('Error de conexión al cargar el modal');
                },
                complete: function() {
                    EWMModalAdmin.isLoading = false;
                    EWMModalAdmin.hideLoading();
                }
            });
        },

        /**
         * Guardar modal
         */
        saveModal: function(e) {
            e.preventDefault();

            if (this.isLoading) {
                return;
            }

            this.isLoading = true;
            this.showLoading('Guardando modal...');

            const formData = this.collectFormData();
            
            // LOG TEMPORAL: Payload enviado al servidor al guardar (modal-enabled y enable-manual-trigger)
            console.log('[EWM TEST LOG] JS → Servidor: formData.display_rules.enabled =', formData.display_rules?.enabled);
            console.log('[EWM TEST LOG] JS → Servidor: formData.triggers.manual.enabled =', formData.triggers?.manual?.enabled);
            console.log('[EWM TEST LOG] JS → Servidor: formData =', JSON.stringify(formData, null, 2));
            
            $.ajax({
                url: ewm_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'ewm_save_modal_builder',
                    modal_id: this.currentModalId || 0,
                    modal_data: JSON.stringify(formData),
                    nonce: ewm_admin_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        EWMModalAdmin.showSuccess('Modal guardado correctamente');

                        // Si es un modal nuevo, actualizar la URL
                        if (!EWMModalAdmin.currentModalId && response.data.modal_id) {
                            EWMModalAdmin.currentModalId = response.data.modal_id;
                            const newUrl = window.location.href + '&modal_id=' + response.data.modal_id;
                            window.history.replaceState({}, '', newUrl);

                            // Mostrar shortcode
                            EWMModalAdmin.showShortcode(response.data.modal_id);
                        }
                    } else {
                        EWMModalAdmin.showError('Error al guardar: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('EWM Modal Admin: AJAX error saving modal', error);
                    EWMModalAdmin.showError('Error de conexión al guardar el modal');
                },
                complete: function() {
                    EWMModalAdmin.isLoading = false;
                    EWMModalAdmin.hideLoading();
                }
            });
        },

        /**
         * Preview del modal
         */
        previewModal: function(e) {
            e.preventDefault();

            if (this.isLoading) {
                return;
            }

            this.isLoading = true;
            this.showLoading('Generando vista previa...');

            const formData = this.collectFormData();

            $.ajax({
                url: ewm_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'ewm_preview_modal',
                    modal_data: JSON.stringify(formData),
                    nonce: ewm_admin_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('.ewm-preview-container').html(response.data.html);

                        // Cambiar al tab de preview
                        $('.ewm-tabs-nav a[href="#preview"]').trigger('click');

                        console.log('EWM Modal Admin: Preview generated successfully');
                    } else {
                        EWMModalAdmin.showError('Error al generar preview: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('EWM Modal Admin: AJAX error generating preview', error);
                    EWMModalAdmin.showError('Error de conexión al generar preview');
                },
                complete: function() {
                    EWMModalAdmin.isLoading = false;
                    EWMModalAdmin.hideLoading();
                }
            });
        },

        /**
         * Recopilar datos del formulario
         */
        collectFormData: function() {
            const data = {
                title: $('#modal-title').val(),
                mode: $('#modal-mode').val(),
                steps: this.collectStepsData(),
                design: this.collectDesignData(),
                triggers: this.collectTriggersData(),
                wc_integration: this.collectWCData(),
                display_rules: this.collectDisplayRules(),
                custom_css: $('#custom-css').val()
            };

            // 🔍 LOG DURANTE: Datos recopilados para enviar al backend (ESTRUCTURA UNIFICADA)
            console.log('🔍 PAYLOAD LOG - DURANTE (Datos recopilados para guardar - ESTRUCTURA UNIFICADA):', JSON.stringify(data, null, 2));
            console.log('🔍 PAYLOAD LOG - DURANTE (Timestamp):', new Date().toISOString());
            console.log('🔍 PAYLOAD LOG - DURANTE (Frequency Structure Check):', {
                has_frequency_object: !!data.triggers?.frequency,
                frequency_type: data.triggers?.frequency?.type,
                frequency_limit: data.triggers?.frequency?.limit
            });

            console.log('EWM Modal Admin: Collected form data', data);
            return data;
        },

        /**
         * Recopilar datos de pasos
         */
        collectStepsData: function() {
            const steps = [];
            const progressBarEnabled = $('#show-progress-bar').is(':checked');

            $('.ewm-step-config').each(function(index) {
                const stepTitle = $(this).find('.ewm-step-title').val();
                const stepData = {
                    id: index + 1,
                    title: stepTitle || `Paso ${index + 1}`, // Título por defecto si está vacío
                    subtitle: $(this).find('.ewm-step-subtitle').val(),
                    description: $(this).find('.ewm-step-description').val(),
                    fields: []
                };

                // Recopilar campos del paso
                $(this).find('.ewm-field-config').each(function() {
                    const fieldId = $(this).find('.ewm-field-id').val();
                    const fieldType = $(this).find('.ewm-field-type').val();
                    const fieldData = {
                        id: fieldId || `campo_${stepData.fields.length + 1}`, // ID por defecto si está vacío
                        type: fieldType || 'text', // Tipo por defecto
                        label: $(this).find('.ewm-field-label').val(),
                        placeholder: $(this).find('.ewm-field-placeholder').val(),
                        required: $(this).find('.ewm-field-required').is(':checked'),
                        options: $(this).find('.ewm-field-options').val()
                    };

                    // Incluir campo si tiene al menos un tipo válido
                    if (fieldData.type) {
                        stepData.fields.push(fieldData);
                    }
                });

                // Siempre incluir el step, incluso si está vacío
                steps.push(stepData);
            });

            return {
                steps: steps,
                progressBar: {
                    enabled: progressBarEnabled,
                    style: 'line',
                    color: $('#primary-color').val() || '#ff6b35'
                }
            };
        },

        /**
         * Recopilar datos de diseño
         */
        collectDesignData: function() {
            return {
                modal_size: $('#modal-size').val(),
                animation: $('#modal-animation').val(),
                colors: {
                    primary: $('#primary-color').val(),
                    secondary: $('#secondary-color').val(),
                    background: $('#background-color').val()
                }
            };
        },

        /**
         * Recopilar datos de triggers
         */
        collectTriggersData: function() {
            const frequencyType = $('#display-frequency').val();

            // Determinar el límite basado en el tipo de frecuencia
            let frequencyLimit = 1; // Valor por defecto
            if (frequencyType === 'always') {
                frequencyLimit = 0; // Sin límite para 'always'
            }

            // 🔍 LOG ESPECÍFICO: Campo de frecuencia (NUEVA ESTRUCTURA)
            console.log('🔍 FREQUENCY LOG - Campo frecuencia actual (NUEVA ESTRUCTURA):', {
                type: frequencyType,
                limit: frequencyLimit,
                element_exists: $('#display-frequency').length > 0,
                all_options: $('#display-frequency option').map(function() { return $(this).val(); }).get(),
                timestamp: new Date().toISOString()
            });

            return {
                exit_intent: {
                    enabled: $('#enable-exit-intent').is(':checked')
                },
                time_delay: {
                    enabled: $('#enable-time-delay').is(':checked'),
                    delay: parseInt($('#time-delay').val()) || 5000
                },
                scroll_percentage: {
                    enabled: $('#enable-scroll-trigger').is(':checked'),
                    percentage: parseInt($('#scroll-percentage').val()) || 50
                },
                manual: {
                    enabled: $('#enable-manual-trigger').is(':checked')
                },
                frequency: {
                    type: frequencyType,
                    limit: frequencyLimit
                }
            };
        },

        /**
         * Recopilar datos de WooCommerce
         */
        collectWCData: function() {
            const enabled = $('#wc-integration-enabled').is(':checked');

            if (!enabled) {
                return { enabled: false };
            }

            return {
                enabled: true,
                discount_code: $('#wc-coupon-select').val(),
                wc_promotion: {
                    title: $('#wc-promotion-title').val(),
                    description: $('#wc-promotion-description').val(),
                    cta_text: $('#wc-promotion-cta').val(),
                    auto_apply: $('#wc-auto-apply').is(':checked'),
                    show_restrictions: $('#wc-show-restrictions').is(':checked'),
                    timer_config: {
                        enabled: $('#wc-timer-enabled').is(':checked'),
                        threshold_seconds: parseInt($('#wc-timer-threshold').val()) || 180
                    }
                }
            };
        },

        /**
         * Recopilar reglas de visualización
         */
        collectDisplayRules: function() {
            return {
                enabled: $('#modal-enabled').is(':checked'),
                devices: {
                    desktop: $('#device-desktop').is(':checked'),
                    tablet: $('#device-tablet').is(':checked'),
                    mobile: $('#device-mobile').is(':checked')
                },
                pages: {
                    include: $('#pages-include').val() || [],
                    exclude: $('#pages-exclude').val() || []
                },
                user_roles: $('#user-roles').val() || []
            };
        },

        /**
         * Poblar formulario con datos
         */
        populateForm: function(data) {
            if (!data) return;
        
            // LOG TEMPORAL: Interpretación JS de los datos recibidos (modal-enabled y enable-manual-trigger)
            console.log('[EWM TEST LOG] Interpretación JS: data.display_rules.enabled =', data.display_rules?.enabled);
            console.log('[EWM TEST LOG] Interpretación JS: data.triggers.manual.enabled =', data.triggers?.manual?.enabled);
            console.log('[EWM TEST LOG] Interpretación JS: data =', JSON.stringify(data, null, 2));
        
            // Datos generales
            $('#modal-title').val(data.title || '');
            $('#modal-mode').val(data.mode || 'formulario');
            $('#custom-css').val(data.custom_css || '');
        
            // Diseño
            if (data.design) {
                $('#modal-size').val(data.design.modal_size || 'medium');
                $('#modal-animation').val(data.design.animation || 'fade');
        
                if (data.design.colors) {
                    $('#primary-color').val(data.design.colors.primary || '#ff6b35').trigger('change');
                    $('#secondary-color').val(data.design.colors.secondary || '#333333').trigger('change');
                    $('#background-color').val(data.design.colors.background || '#ffffff').trigger('change');
                }
            }
        
            // Triggers
            if (data.triggers) {
                $('#enable-exit-intent').prop('checked', data.triggers.exit_intent?.enabled || false);
                $('#enable-time-delay').prop('checked', data.triggers.time_delay?.enabled || false);
                $('#time-delay').val(data.triggers.time_delay?.delay || 5000);
                $('#enable-scroll-trigger').prop('checked', data.triggers.scroll_percentage?.enabled || false);
                $('#scroll-percentage').val(data.triggers.scroll_percentage?.percentage || 50);
                $('#enable-manual-trigger').prop('checked', data.triggers.manual?.enabled || true);
        
                // 🔍 LOG ESPECÍFICO: Poblando campo de frecuencia (NUEVA ESTRUCTURA)
                const frequencyFromData = data.triggers.frequency?.type || 'always';
                const frequencyLimit = data.triggers.frequency?.limit || 1;
        
                console.log('🔍 FREQUENCY LOG - Poblando frecuencia (NUEVA ESTRUCTURA):', {
                    frequency_object_from_backend: data.triggers.frequency,
                    type_from_backend: frequencyFromData,
                    limit_from_backend: frequencyLimit,
                    element_before_set: $('#display-frequency').val(),
                    timestamp: new Date().toISOString()
                });
        
                $('#display-frequency').val(frequencyFromData);
        
                // Verificar si se estableció correctamente
                console.log('🔍 FREQUENCY LOG - Después de establecer (NUEVA ESTRUCTURA):', {
                    element_after_set: $('#display-frequency').val(),
                    matches_expected: $('#display-frequency').val() === frequencyFromData,
                    full_structure_available: !!data.triggers.frequency
                });
            }
        
            // WooCommerce
            if (data.wc_integration) {
                $('#enable-woocommerce').prop('checked', data.wc_integration.enabled || false);
            }
        
            // Reglas de visualización
            if (data.display_rules) {
                $('#modal-enabled').prop('checked', data.display_rules.enabled !== false);

                // Devices
                if (data.display_rules.devices) {
                    $('#device-desktop').prop('checked', data.display_rules.devices.desktop !== false);
                    $('#device-tablet').prop('checked', data.display_rules.devices.tablet !== false);
                    $('#device-mobile').prop('checked', data.display_rules.devices.mobile !== false);
                }

                // Pages
                if (data.display_rules.pages) {
                    if (data.display_rules.pages.include) {
                        $('#pages-include').val(data.display_rules.pages.include);
                    }
                    if (data.display_rules.pages.exclude) {
                        $('#pages-exclude').val(data.display_rules.pages.exclude);
                    }
                }

                // User roles
                if (data.display_rules.user_roles) {
                    $('#user-roles').val(data.display_rules.user_roles);
                }
            }
        
            // Barra de progreso
            if (data.steps && data.steps.progressBar) {
                $('#show-progress-bar').prop('checked', data.steps.progressBar.enabled !== false);
            }
        
            console.log('EWM Modal Admin: Form populated with data');
        },

        /**
         * Funciones auxiliares
         */
        showLoading: function(message) {
            console.log('EWM Modal Admin: Loading -', message);
            // Implementar indicador de carga visual
        },

        hideLoading: function() {
            console.log('EWM Modal Admin: Loading hidden');
            // Ocultar indicador de carga
        },

        showSuccess: function(message) {
            console.log('EWM Modal Admin: Success -', message);
            alert(message); // Temporal, implementar notificación mejor
        },

        showError: function(message) {
            console.error('EWM Modal Admin: Error -', message);
            alert('Error: ' + message); // Temporal, implementar notificación mejor
        },

        copyShortcode: function(e) {
            e.preventDefault();
            const shortcode = $(e.target).siblings('code').text();
            navigator.clipboard.writeText(shortcode).then(function() {
                EWMModalAdmin.showSuccess('Shortcode copiado al portapapeles');
            });
        },

        clearForm: function(e) {
            e.preventDefault();
            if (confirm('¿Estás seguro de que quieres limpiar el formulario?')) {
                $('#ewm-modal-form')[0].reset();
                $('.ewm-steps-config').empty();
                console.log('EWM Modal Admin: Form cleared');
            }
        },

        addStep: function(e) {
            e.preventDefault();
            console.log('EWM Modal Admin: Add step clicked');
            // Implementar lógica para agregar paso
        },

        removeStep: function(e) {
            e.preventDefault();
            console.log('EWM Modal Admin: Remove step clicked');
            // Implementar lógica para eliminar paso
        },

        /**
         * Inicializar estado de WooCommerce
         */
        initWCIntegration: function() {
            // Verificar estado inicial del checkbox
            const isEnabled = $('#wc-integration-enabled').is(':checked');

            if (isEnabled) {
                // Mostrar pestaña y configuraciones de WooCommerce
                $('#woocommerce-tab').show();
                $('#wc-integration-settings').show();

                // Ocultar otras pestañas
                $('.non-wc-tab').hide();

                // Cargar cupones si está disponible la integración
                if (window.EWMWCBuilderIntegration) {
                    window.EWMWCBuilderIntegration.loadCoupons();
                }
            } else {
                // Estado por defecto: mostrar todas las pestañas excepto WooCommerce
                $('#woocommerce-tab').hide();
                $('#wc-integration-settings').hide();
                $('.non-wc-tab').show();
            }

            // Inicializar estado del timer
            const timerEnabled = $('#wc-timer-enabled').is(':checked');
            if (timerEnabled) {
                $('#wc-timer-settings').show();
            } else {
                $('#wc-timer-settings').hide();
            }
        },

        /**
         * Toggle WooCommerce integration
         */
        toggleWCIntegration: function(e) {
            const enabled = $(e.target).is(':checked');
            const $wcTab = $('#woocommerce-tab');
            const $wcPane = $('#woocommerce');
            const $wcSettings = $('#wc-integration-settings');
            const $nonWcTabs = $('.non-wc-tab');

            if (enabled) {
                // Mostrar pestaña y configuraciones de WooCommerce
                $wcTab.show();
                $wcSettings.slideDown(300);

                // Ocultar otras pestañas
                $nonWcTabs.hide();

                // Cambiar a la pestaña WooCommerce
                $('.ewm-tabs-nav a').removeClass('active');
                $('.ewm-tab-pane').removeClass('active').hide();
                $wcTab.find('a').addClass('active');
                $wcPane.addClass('active').show();

                // Cargar cupones si está disponible la integración
                if (window.EWMWCBuilderIntegration) {
                    window.EWMWCBuilderIntegration.loadCoupons();
                }
            } else {
                // Ocultar pestaña y configuraciones de WooCommerce
                $wcTab.hide();
                $wcSettings.slideUp(300);

                // Mostrar otras pestañas
                $nonWcTabs.show();

                // Volver a la pestaña General si estamos en WooCommerce
                if ($wcPane.hasClass('active')) {
                    $('.ewm-tabs-nav a').removeClass('active');
                    $('.ewm-tab-pane').removeClass('active').hide();
                    $('.ewm-tabs-nav a[href="#general"]').addClass('active');
                    $('#general').addClass('active').show();
                }
            }
        },

        /**
         * Toggle WooCommerce timer settings
         */
        toggleWCTimer: function(e) {
            const enabled = $(e.target).is(':checked');
            const $timerSettings = $('#wc-timer-settings');

            if (enabled) {
                $timerSettings.slideDown(300);
            } else {
                $timerSettings.slideUp(300);
            }
        },

        showShortcode: function(modalId) {
            const shortcode = '[ew_modal id="' + modalId + '"]';
            console.log('EWM Modal Admin: Generated shortcode:', shortcode);
            // Mostrar shortcode en la interfaz
        }
    };

    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        EWMModalAdmin.init();
    });

    // Exponer globalmente para debugging
    window.EWMModalAdmin = EWMModalAdmin;

})(jQuery);
