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
        init: async function() {
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
                await this.loadModalData();
            } else {
                // Si no hay modal, inicializar estado básico de WooCommerce
                this.initWCIntegration();
            }

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
        loadModalData: async function() {
            if (!this.currentModalId || this.isLoading) {
                return;
            }

            this.isLoading = true;
            this.showLoading('Cargando datos del modal...');

            try {
                const response = await $.ajax({
                    url: ewm_admin_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'ewm_load_modal_builder',
                        modal_id: this.currentModalId,
                        nonce: ewm_admin_vars.nonce
                    }
                });

                if (response.success) {
                    // 🔍 LOG ANTES: Datos cargados desde el backend
                    console.log('🔍 PAYLOAD LOG - ANTES (Datos cargados desde backend):', JSON.stringify(response.data, null, 2));
                    console.log('🔍 PAYLOAD LOG - ANTES (Timestamp):', new Date().toISOString());

                    // Detectar si es un modal WooCommerce y preparar la UI
                    const hasWCIntegration = response.data.wc_integration && response.data.wc_integration.enabled;
                    if (hasWCIntegration) {
                        console.log('EWM Modal Admin: WooCommerce modal detected, preparing UI...');
                        await this.prepareWCModalUI();
                    }

                    await this.populateForm(response.data);

                    // Disparar evento para que el builder v2 pueda cargar los steps
                    $(document).trigger('ewm-modal-data-loaded', [response.data]);

                    console.log('EWM Modal Admin: Modal data loaded successfully');
                } else {
                    this.showError('Error al cargar el modal: ' + response.data);
                }
            } catch (error) {
                console.error('EWM Modal Admin: AJAX error loading modal', error);
                this.showError('Error de conexión al cargar el modal');
            } finally {
                this.isLoading = false;
                this.hideLoading();
            }
        },

        /**
         * Preparar UI para modales WooCommerce
         */
        prepareWCModalUI: async function() {
            console.log('EWM Modal Admin: Preparing WooCommerce modal UI...');

            // DEBUGGING: Inspeccionar el HTML del tab WooCommerce
            const $wcTab = $('#woocommerce-tab');
            const $wcPane = $('#woocommerce');
            
            console.log('🔍 WC TAB DEBUG - Tab element:', $wcTab.length, $wcTab[0]);
            console.log('🔍 WC TAB DEBUG - Pane element:', $wcPane.length, $wcPane[0]);
            console.log('🔍 WC TAB DEBUG - Pane HTML:', $wcPane.html());
            console.log('🔍 WC TAB DEBUG - Pane text length:', $wcPane.text().length);
            console.log('🔍 WC TAB DEBUG - WC integration enabled checkbox:', $('#wc-integration-enabled').length);
            console.log('🔍 WC TAB DEBUG - WC coupon select:', $('#wc-coupon-select').length);
            
            const $nonWcTabs = $('.non-wc-tab');

            // Mostrar tab WooCommerce
            $wcTab.show();
            console.log('🔍 WC TAB DEBUG - Tab shown, visible:', $wcTab.is(':visible'));

            // Ocultar tabs no esenciales para WooCommerce (mantener General y WooCommerce)
            $nonWcTabs.hide();
            console.log('🔍 WC TAB DEBUG - Non-WC tabs hidden, count:', $nonWcTabs.length);

            // Activar tab General por defecto (para que el usuario vea algo inmediatamente)
            $('.ewm-tabs-nav a').removeClass('active');
            $('.ewm-tab-pane').removeClass('active');
            $('.ewm-tabs-nav a[href="#general"]').addClass('active');
            $('#general').addClass('active');
            
            console.log('🔍 WC TAB DEBUG - General tab activated');

            // DEBUGGING: Inspeccionar después de activar
            console.log('🔍 WC TAB DEBUG - After activation:');
            console.log('  - WC pane HTML length:', $wcPane.html().length);
            console.log('  - Active tabs:', $('.ewm-tabs-nav a.active').map((i, el) => $(el).attr('href')).get());
            console.log('  - Active panes:', $('.ewm-tab-pane.active').map((i, el) => el.id).get());

            // DEBUGGING: Agregar listener para el click en tab WooCommerce
            $wcTab.off('click.debug').on('click.debug', function() {
                console.log('🔍 WC TAB DEBUG - Tab clicked!');
                setTimeout(() => {
                    console.log('🔍 WC TAB DEBUG - After tab switch:');
                    console.log('  - WC pane visible:', $('#woocommerce').is(':visible'));
                    console.log('  - WC pane display style:', $('#woocommerce').css('display'));
                    console.log('  - WC form elements count:', $('#woocommerce input, #woocommerce select, #woocommerce textarea').length);
                    console.log('  - First few form elements:', $('#woocommerce input, #woocommerce select, #woocommerce textarea').slice(0, 3).map((i, el) => el.id || el.name).get());
                }, 50);
            });

            // Garantizar que EWMWCBuilderIntegration esté inicializado
            await this.ensureWCIntegrationReady();

            // Precargar cupones de WooCommerce 
            console.log('EWM Modal Admin: Preloading WooCommerce coupons...');
            this.showLoading('Preparando cupones de WooCommerce...');

            try {
                if (window.EWMWCBuilderIntegration.loadCouponsAsync) {
                    await window.EWMWCBuilderIntegration.loadCouponsAsync();
                } else if (window.EWMWCBuilderIntegration.loadCoupons) {
                    await new Promise(resolve => {
                        window.EWMWCBuilderIntegration.loadCoupons(resolve);
                    });
                }
                console.log('EWM Modal Admin: WooCommerce coupons preloaded successfully');
                
                // DEBUGGING: Inspeccionar después de cargar cupones
                console.log('🔍 WC TAB DEBUG - After loading coupons:');
                console.log('  - Coupon select options:', $('#wc-coupon-select option').length);
                console.log('  - Coupon select HTML:', $('#wc-coupon-select')[0]?.outerHTML);
                console.log('  - WC settings visible:', $('#wc-integration-settings').is(':visible'));
                
            } catch (error) {
                console.error('EWM Modal Admin: Error preloading WooCommerce coupons:', error);
            }

            this.hideLoading();
            console.log('EWM Modal Admin: WooCommerce modal UI prepared');
            
            // DEBUGGING FINAL: Estado completo del tab
            console.log('🔍 WC TAB DEBUG - FINAL STATE:');
            console.log('  - WC pane HTML:', $wcPane.html());
            console.log('  - WC pane visible:', $wcPane.is(':visible'));
            console.log('  - All form elements in WC pane:', $wcPane.find('input, select, textarea').length);
        },

        /**
         * Garantizar que EWMWCBuilderIntegration esté listo antes de usarlo
         */
        ensureWCIntegrationReady: async function() {
            console.log('EWM Modal Admin: Ensuring WC Integration is ready...');

            // Si ya existe, verificar que tenga los métodos necesarios
            if (window.EWMWCBuilderIntegration && 
                typeof window.EWMWCBuilderIntegration.loadCoupons === 'function') {
                console.log('EWM Modal Admin: WC Integration already ready');
                return;
            }

            // Si no existe, intentar acceder a la clase y crear una instancia
            if (!window.EWMWCBuilderIntegration) {
                console.log('EWM Modal Admin: WC Integration not found, attempting to initialize...');
                
                // Buscar la clase en el scope global o en el window
                if (typeof EWMWCBuilderIntegration !== 'undefined') {
                    console.log('EWM Modal Admin: Creating WC Integration instance manually...');
                    window.EWMWCBuilderIntegration = new EWMWCBuilderIntegration();
                } else {
                    // Si la clase no está disponible, esperar un poco más
                    console.log('EWM Modal Admin: EWMWCBuilderIntegration class not found, waiting...');
                }
            }

            // Esperar hasta que esté listo, con timeout de seguridad
            let attempts = 0;
            const maxAttempts = 50; // 5 segundos máximo
            
            while ((!window.EWMWCBuilderIntegration || 
                    typeof window.EWMWCBuilderIntegration.loadCoupons !== 'function') && 
                   attempts < maxAttempts) {
                await new Promise(resolve => setTimeout(resolve, 100));
                attempts++;
                
                // Intentar crear instancia en cada intento si la clase está disponible
                if (!window.EWMWCBuilderIntegration && typeof EWMWCBuilderIntegration !== 'undefined') {
                    console.log('EWM Modal Admin: Attempting to create instance, attempt', attempts);
                    try {
                        window.EWMWCBuilderIntegration = new EWMWCBuilderIntegration();
                    } catch (error) {
                        console.log('EWM Modal Admin: Failed to create instance:', error.message);
                    }
                }
            }

            if (attempts >= maxAttempts) {
                throw new Error('WC Integration failed to initialize within timeout');
            }

            console.log('EWM Modal Admin: WC Integration ready after', attempts * 100, 'ms');
        },

        /**
         * Seleccionar cupón de forma robusta con retry inteligente
         */
        setCouponWithRetry: async function(couponCode) {
            console.log('EWM Modal Admin: Setting coupon with retry:', couponCode);

            const $select = $('#wc-coupon-select');
            let attempts = 0;
            const maxAttempts = 30; // 3 segundos máximo
            
            return new Promise((resolve, reject) => {
                const attemptSetCoupon = () => {
                    attempts++;
                    
                    // Verificar si el select tiene opciones (no solo la primera "-- Selecciona --")
                    const optionCount = $select.find('option').length;
                    const hasRealOptions = optionCount > 1;
                    
                    if (!hasRealOptions && attempts < maxAttempts) {
                        console.log(`EWM Modal Admin: Select not populated yet (${optionCount} options), attempt ${attempts}/${maxAttempts}`);
                        setTimeout(attemptSetCoupon, 100);
                        return;
                    }
                    
                    // Buscar la opción específica
                    const optionExists = $select.find(`option[value="${couponCode}"]`).length > 0;
                    
                    if (optionExists) {
                        $select.val(couponCode);
                        
                        // Trigger change event para que la integración WC procese la selección
                        $select.trigger('change');
                        
                        console.log('EWM Modal Admin: Coupon selected successfully:', couponCode);
                        resolve(true);
                    } else if (attempts < maxAttempts) {
                        console.log(`EWM Modal Admin: Coupon option "${couponCode}" not found, attempt ${attempts}/${maxAttempts}`);
                        setTimeout(attemptSetCoupon, 100);
                    } else {
                        console.warn('EWM Modal Admin: Failed to set coupon after maximum attempts:', couponCode);
                        console.warn('EWM Modal Admin: Available options:', $select.find('option').map((i, opt) => opt.value).get());
                        resolve(false); // No rechazar, solo indicar que falló
                    }
                };
                
                attemptSetCoupon();
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

            // LOGGING: Payload generado antes de enviar
            console.log('[EWM LOG] [GUARDAR] Payload generado:', JSON.stringify(formData, null, 2));

            // LOGGING: Stack trace JS en el momento de guardar
            try {
                throw new Error('[EWM LOG] [GUARDAR] Stack trace al guardar modal');
            } catch (err) {
                if (err.stack) {
                    console.log(err.stack);
                }
            }

            $.ajax({
                url: ewm_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'ewm_save_modal_builder',
                    modal_id: this.currentModalId || 0,
                    modal_data: JSON.stringify(formData),
                    nonce: ewm_admin_vars.nonce
                },
                beforeSend: function() {
                    // LOGGING: Payload enviado
                    console.log('[EWM LOG] [GUARDAR] Payload enviado:', JSON.stringify(formData, null, 2));
                },
                success: function(response) {
                    // LOGGING: Respuesta del backend al guardar
                    console.log('[EWM LOG] [GUARDAR] Respuesta backend:', JSON.stringify(response, null, 2));

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
            // Recopilar pasos
            const steps = [];
            $('.ewm-steps-config .ewm-step-config').each(function(index) {
                const stepTitle = $(this).find('.ewm-step-title').val();
                const stepData = {
                    id: index + 1,
                    title: stepTitle || `Paso ${index + 1}`,
                    subtitle: $(this).find('.ewm-step-subtitle').val(),
                    description: $(this).find('.ewm-step-description').val(),
                    fields: []
                };

                // Recopilar campos del paso
                $(this).find('.ewm-field-config').each(function() {
                    const fieldId = $(this).find('.ewm-field-id').val();
                    const fieldType = $(this).find('.ewm-field-type').val();
                    const fieldData = {
                        id: fieldId || `campo_${stepData.fields.length + 1}`,
                        type: fieldType || 'text',
                        label: $(this).find('.ewm-field-label').val(),
                        placeholder: $(this).find('.ewm-field-placeholder').val(),
                        required: $(this).find('.ewm-field-required').is(':checked'),
                        options: $(this).find('.ewm-field-options').val()
                    };
                    if (fieldData.type) {
                        stepData.fields.push(fieldData);
                    }
                });
                steps.push(stepData);
            });

            // Recopilar barra de progreso
            const progressBarEnabled = $('#show-progress-bar').is(':checked');

            return {
                title: $('#modal-title').val(),
                mode: $('#modal-mode').val(),
                steps: {
                    steps: steps,
                    progressBar: {
                        enabled: progressBarEnabled,
                        style: 'line',
                        color: $('#primary-color').val() || '#ff6b35'
                    }
                },
                design: this.collectDesignData(),
                triggers: this.collectTriggersData(),
                wc_integration: this.collectWCData(),
                display_rules: this.collectDisplayRules(),
                custom_css: $('#custom-css').val()
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
        populateForm: async function(data) {
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
                // Restaurar campos extra desde la subclave modal_woocomerce
                if (data.wc_integration.modal_woocomerce && typeof data.wc_integration.modal_woocomerce === 'object') {
                    Object.keys(data.wc_integration.modal_woocomerce).forEach(function(key) {
                        var value = data.wc_integration.modal_woocomerce[key];
                        // Intenta poblar el campo si existe un input con ese ID
                        var $input = $('#' + key);
                        if ($input.length) {
                            if ($input.is(':checkbox')) {
                                $input.prop('checked', !!value);
                            } else {
                                $input.val(value);
                            }
                        }
                    });
                }
        
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
        
            if (data.wc_integration) {
                console.log('🔍 WC POPULATE DEBUG - Starting WC integration processing...');
                console.log('🔍 WC POPULATE DEBUG - WC integration data:', data.wc_integration);
                console.log('🔍 WC POPULATE DEBUG - WC tab exists:', $('#woocommerce').length);
                console.log('🔍 WC POPULATE DEBUG - WC tab HTML before populate:', $('#woocommerce').html().substring(0, 200) + '...');

                // Marcar checkbox de integración WooCommerce
                $('#wc-integration-enabled').prop('checked', data.wc_integration.enabled || false);
                $('#enable-woocommerce').prop('checked', data.wc_integration.enabled || false);

                console.log('🔍 WC POPULATE DEBUG - Checkboxes set:', {
                    'wc-integration-enabled': $('#wc-integration-enabled').is(':checked'),
                    'enable-woocommerce': $('#enable-woocommerce').is(':checked'),
                    'wc-integration-enabled-exists': $('#wc-integration-enabled').length
                });

                if (data.wc_integration.enabled) {
                    // FORZAR la visualización de configuraciones WooCommerce
                    $('#wc-integration-settings').show();
                    
                    // TAMBIÉN forzar trigger del evento change para activar la lógica JS de WC
                    $('#wc-integration-enabled').trigger('change');
                    
                    console.log('🔍 WC POPULATE DEBUG - WC settings FORCED visible:', $('#wc-integration-settings').is(':visible'));

                    // Los cupones ya deberían estar cargados por prepareWCModalUI
                    // Solo necesitamos seleccionar el cupón guardado
                    if (data.wc_integration.discount_code !== undefined) {
                        console.log('🔍 WC POPULATE DEBUG - Setting saved coupon:', data.wc_integration.discount_code);
                        console.log('🔍 WC POPULATE DEBUG - Coupon select before set:', $('#wc-coupon-select').length, $('#wc-coupon-select option').length);

                        // Usar método robusto para seleccionar el cupón
                        await this.setCouponWithRetry(data.wc_integration.discount_code);
                        
                        console.log('🔍 WC POPULATE DEBUG - Coupon select after set:', $('#wc-coupon-select').val());
                    }
                } else {
                    console.log('🔍 WC POPULATE DEBUG - WC integration not enabled');
                }
                
                // Promoción
                if (data.wc_integration.wc_promotion) {
                    console.log('🔍 WC POPULATE DEBUG - Processing promotion data:', data.wc_integration.wc_promotion);
                    
                    $('#wc-promotion-title').val(data.wc_integration.wc_promotion.title || '');
                    $('#wc-promotion-description').val(data.wc_integration.wc_promotion.description || '');
                    $('#wc-promotion-cta').val(data.wc_integration.wc_promotion.cta_text || '');
                    $('#wc-auto-apply').prop('checked', data.wc_integration.wc_promotion.auto_apply || false);
                    $('#wc-show-restrictions').prop('checked', data.wc_integration.wc_promotion.show_restrictions || false);

                    // Timer
                    if (data.wc_integration.wc_promotion.timer_config) {
                        $('#wc-timer-enabled').prop('checked', data.wc_integration.wc_promotion.timer_config.enabled || false);
                        $('#wc-timer-threshold').val(data.wc_integration.wc_promotion.timer_config.threshold_seconds || 180);
                    }
                    
                    console.log('🔍 WC POPULATE DEBUG - Promotion fields populated');
                } else {
                    console.log('🔍 WC POPULATE DEBUG - No promotion data found');
                }
                
                console.log('🔍 WC POPULATE DEBUG - WC tab HTML after populate:', $('#woocommerce').html().substring(0, 200) + '...');
                console.log('🔍 WC POPULATE DEBUG - All WC form elements:', $('#woocommerce input, #woocommerce select, #woocommerce textarea').length);
                console.log('🔍 WC POPULATE DEBUG - Final wc-integration-settings visible:', $('#wc-integration-settings').is(':visible'));
            } else {
                console.log('🔍 WC POPULATE DEBUG - No WC integration data found in response');
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
         * Inicializar estado de WooCommerce (solo para modales nuevos)
         */
        initWCIntegration: function() {
            // Solo ejecutar si no hay modal ID (modal nuevo)
            if (this.currentModalId) {
                console.log('EWM Modal Admin: Skipping WC init for existing modal (handled by prepareWCModalUI)');
                return;
            }

            const isEnabled = $('#wc-integration-enabled').is(':checked');
            if (isEnabled) {
                $('#woocommerce-tab').show();
                $('#wc-integration-settings').show();
                $('.non-wc-tab').hide();

                // Cargar cupones si está disponible la integración
                if (window.EWMWCBuilderIntegration) {
                    window.EWMWCBuilderIntegration.loadCoupons();
                }
            } else {
                $('#woocommerce-tab').hide();
                $('#wc-integration-settings').hide();
                $('.non-wc-tab').show();
            }
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
        toggleWCIntegration: async function(e) {
            const enabled = $(e.target).is(':checked');
            const $wcTab = $('#woocommerce-tab');
            const $wcPane = $('#woocommerce');
            const $wcSettings = $('#wc-integration-settings');
            const $nonWcTabs = $('.non-wc-tab');

            if (enabled) {
                console.log('EWM Modal Admin: Enabling WooCommerce integration...');

                // Mostrar pestaña y configuraciones de WooCommerce
                $wcTab.show();
                $wcSettings.slideDown(300);

                // Ocultar otras pestañas (pero no la General)
                $nonWcTabs.hide();

                // Cambiar a la pestaña WooCommerce
                $('.ewm-tabs-nav a').removeClass('active');
                $('.ewm-tab-pane.non-wc-tab').removeClass('active').hide(); // Solo ocultar pestañas no-WC
                $wcTab.find('a').addClass('active');
                $wcPane.addClass('active').show();

                // Cargar cupones si está disponible la integración
                if (window.EWMWCBuilderIntegration) {
                    this.showLoading('Cargando cupones de WooCommerce...');
                    try {
                        if (window.EWMWCBuilderIntegration.loadCouponsAsync) {
                            await window.EWMWCBuilderIntegration.loadCouponsAsync();
                        } else {
                            await new Promise(resolve => {
                                window.EWMWCBuilderIntegration.loadCoupons(resolve);
                            });
                        }
                        console.log('EWM Modal Admin: WooCommerce coupons loaded successfully');
                    } catch (error) {
                        console.error('EWM Modal Admin: Error loading WooCommerce coupons:', error);
                    }
                    this.hideLoading();
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
        // Usar async/await para inicialización completa
        (async () => {
            await EWMModalAdmin.init();
        })();
    });

    // Exponer globalmente para debugging
    window.EWMModalAdmin = EWMModalAdmin;

})(jQuery);
