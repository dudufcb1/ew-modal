/**
 * EWM Modal CTA - Admin JavaScript
 * Funcionalidad para el Modal Builder y páginas de administración
 */

(function($) {
    'use strict';

    // Objeto principal del admin
    const EWMAdmin = {
        
        // Configuración
        config: {
            ajaxUrl: ewm_admin_vars.ajax_url,
            nonce: ewm_admin_vars.nonce,
            restNonce: ewm_admin_vars.rest_nonce,
            restUrl: ewm_admin_vars.rest_url,
            currentModalId: ewm_admin_vars.modal_id || null
        },
        
        // Cache de elementos DOM
        cache: {
            $tabs: null,
            $tabPanes: null,
            $previewContainer: null,
            $shortcodeOutput: null,
            $form: null
        },
        
        // Inicializar
        init: function() {
            console.log('EWM Admin initializing...', {
                config: this.config,
                hasEwmVars: typeof ewm_admin_vars !== 'undefined',
                ewmVars: typeof ewm_admin_vars !== 'undefined' ? ewm_admin_vars : 'undefined'
            });

            this.cacheElements();
            this.bindEvents();
            this.initTabs();
            this.initColorPickers();
            this.initStepsConfig();
            this.loadModalData();

            console.log('EWM Admin initialized successfully');
        },
        
        // Cachear elementos DOM
        cacheElements: function() {
            this.cache.$tabs = $('.ewm-tabs-nav a');
            this.cache.$tabPanes = $('.ewm-tab-pane');
            this.cache.$previewContainer = $('.ewm-preview-container');
            this.cache.$shortcodeOutput = $('.ewm-shortcode-output code');
            this.cache.$form = $('#ewm-modal-form');
        },
        
        // Vincular eventos
        bindEvents: function() {
            const self = this;
            
            // Navegación por pestañas
            this.cache.$tabs.on('click', this.handleTabClick.bind(this));
            
            // Cambios en formulario
            this.cache.$form.on('change input', 'input, select, textarea', this.handleFormChange.bind(this));
            
            // Botones de acción
            $(document).on('click', '.ewm-btn[data-action]', this.handleButtonClick.bind(this));
            
            // Configuración de pasos
            $(document).on('click', '.ewm-step-header', this.toggleStep.bind(this));
            $(document).on('click', '.ewm-add-step', this.addStep.bind(this));
            $(document).on('click', '.ewm-remove-step', this.removeStep.bind(this));
            
            // Guardar modal
            $(document).on('click', '#ewm-save-modal', this.saveModal.bind(this));
            
            // Vista previa
            $(document).on('click', '#ewm-preview-modal', this.previewModal.bind(this));
            
            // Copiar shortcode
            $(document).on('click', '.ewm-copy-shortcode', this.copyShortcode.bind(this));
        },
        
        // Inicializar pestañas
        initTabs: function() {
            // Activar primera pestaña por defecto
            this.cache.$tabs.first().addClass('active');
            this.cache.$tabPanes.first().addClass('active');
        },
        
        // Manejar clic en pestaña
        handleTabClick: function(e) {
            e.preventDefault();
            
            const $tab = $(e.currentTarget);
            const targetPane = $tab.attr('href');
            
            // Actualizar pestañas activas
            this.cache.$tabs.removeClass('active');
            $tab.addClass('active');
            
            // Actualizar paneles activos
            this.cache.$tabPanes.removeClass('active');
            $(targetPane).addClass('active');
            
            // Actualizar vista previa si es necesario
            if (targetPane === '#preview') {
                this.updatePreview();
            }
        },
        
        // Inicializar color pickers
        initColorPickers: function() {
            if ($.fn.wpColorPicker) {
                $('.ewm-color-picker input[type="text"]').wpColorPicker({
                    change: this.handleFormChange.bind(this),
                    clear: this.handleFormChange.bind(this)
                });
            }
        },
        
        // Inicializar configuración de pasos
        initStepsConfig: function() {
            this.updateStepsDisplay();
        },
        
        // Manejar cambios en formulario
        handleFormChange: function(e) {
            // Actualizar shortcode generado
            this.updateShortcode();
            
            // Actualizar vista previa si está visible
            if ($('#preview').hasClass('active')) {
                this.updatePreview();
            }
            
            // Marcar como modificado
            this.markAsModified();
        },
        
        // Manejar clic en botones
        handleButtonClick: function(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const action = $btn.data('action');
            
            switch (action) {
                case 'save':
                    this.saveModal();
                    break;
                case 'preview':
                    this.previewModal();
                    break;
                case 'clear':
                    this.clearForm();
                    break;
                case 'copy-shortcode':
                    this.copyShortcode();
                    break;
            }
        },
        
        // Alternar paso
        toggleStep: function(e) {
            const $header = $(e.currentTarget);
            const $step = $header.closest('.ewm-step-item');
            
            $step.toggleClass('active');
        },
        
        // Agregar paso
        addStep: function(e) {
            e.preventDefault();
            
            const stepCount = $('.ewm-step-item').length + 1;
            const stepHtml = this.getStepTemplate(stepCount);
            
            $('.ewm-steps-config').append(stepHtml);
            this.updateStepsDisplay();
        },
        
        // Remover paso
        removeStep: function(e) {
            e.preventDefault();
            
            if ($('.ewm-step-item').length <= 1) {
                this.showAlert('Debe haber al menos un paso', 'warning');
                return;
            }
            
            $(e.currentTarget).closest('.ewm-step-item').remove();
            this.updateStepsDisplay();
        },
        
        // Obtener template de paso
        getStepTemplate: function(stepNumber) {
            return `
                <div class="ewm-step-item">
                    <div class="ewm-step-header">
                        <h4 class="ewm-step-title">Paso ${stepNumber}</h4>
                        <div class="ewm-step-actions">
                            <button type="button" class="ewm-btn small ewm-remove-step">Eliminar</button>
                        </div>
                    </div>
                    <div class="ewm-step-content">
                        <div class="ewm-form-group">
                            <label>Título del Paso</label>
                            <input type="text" name="steps[${stepNumber-1}][title]" class="ewm-form-control" value="Paso ${stepNumber}">
                        </div>
                        <div class="ewm-form-group">
                            <label>Contenido</label>
                            <textarea name="steps[${stepNumber-1}][content]" class="ewm-form-control" rows="4"></textarea>
                        </div>
                        <div class="ewm-form-group">
                            <label>Campos del Formulario</label>
                            <div class="ewm-fields-builder" data-step="${stepNumber-1}">
                                <div class="ewm-fields-list">
                                    <!-- Los campos se agregarán dinámicamente aquí -->
                                </div>
                                <button type="button" class="ewm-btn ewm-btn-secondary ewm-add-field" data-step="${stepNumber-1}">
                                    + Agregar Campo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        },
        
        // Actualizar display de pasos
        updateStepsDisplay: function() {
            $('.ewm-step-item').each(function(index) {
                $(this).find('.ewm-step-title').text('Paso ' + (index + 1));
            });
            this.initFieldBuilders();
        },

        // Inicializar builders de campos
        initFieldBuilders: function() {
            const self = this;

            // Event listener para agregar campos
            $(document).off('click', '.ewm-add-field').on('click', '.ewm-add-field', function(e) {
                e.preventDefault();
                const stepIndex = $(this).data('step');
                self.addFieldToStep(stepIndex);
            });

            // Event listener para eliminar campos
            $(document).off('click', '.ewm-remove-field').on('click', '.ewm-remove-field', function(e) {
                e.preventDefault();
                $(this).closest('.ewm-field-item').remove();
            });

            // Event listener para cambio de tipo de campo
            $(document).off('change', 'select[name*="[type]"]').on('change', 'select[name*="[type]"]', function(e) {
                self.handleFieldTypeChange($(this));
            });

            // Event listeners para opciones
            $(document).off('click', '.ewm-add-option').on('click', '.ewm-add-option', function(e) {
                e.preventDefault();
                self.addOptionToField($(this));
            });

            $(document).off('click', '.ewm-remove-option').on('click', '.ewm-remove-option', function(e) {
                e.preventDefault();
                $(this).closest('.ewm-option-item').remove();
            });

            // Inicializar sortable para opciones existentes
            this.initOptionsSortable();
        },

        // Agregar campo a un paso
        addFieldToStep: function(stepIndex) {
            const fieldHTML = this.generateFieldHTML(stepIndex);
            $(`.ewm-fields-builder[data-step="${stepIndex}"] .ewm-fields-list`).append(fieldHTML);
        },

        // Generar HTML para un campo individual
        generateFieldHTML: function(stepIndex, fieldData = {}) {
            const fieldId = fieldData.id || '';
            const fieldType = fieldData.type || 'text';
            const fieldLabel = fieldData.label || '';
            const fieldPlaceholder = fieldData.placeholder || '';
            const fieldRequired = fieldData.required || false;

            return `
                <div class="ewm-field-item" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; border-radius: 5px;">
                    <div class="ewm-field-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <strong>Campo</strong>
                        <button type="button" class="ewm-remove-field" style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">
                            Eliminar
                        </button>
                    </div>
                    <div class="ewm-field-config" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">ID del Campo:</label>
                            <input type="text" name="steps[${stepIndex}][fields][id][]" value="${fieldId}" placeholder="name, email, phone..." style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 3px;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Tipo:</label>
                            <select name="steps[${stepIndex}][fields][type][]" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 3px;">
                                ${this.generateFieldTypeOptions(fieldType)}
                            </select>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Etiqueta:</label>
                            <input type="text" name="steps[${stepIndex}][fields][label][]" value="${fieldLabel}" placeholder="Nombre completo" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 3px;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Placeholder:</label>
                            <input type="text" name="steps[${stepIndex}][fields][placeholder][]" value="${fieldPlaceholder}" placeholder="Ingresa tu nombre..." style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 3px;">
                        </div>
                        <div style="grid-column: span 2;">
                            <label style="display: flex; align-items: center; font-weight: bold;">
                                <input type="checkbox" name="steps[${stepIndex}][fields][required][]" value="1" ${fieldRequired ? 'checked' : ''} style="margin-right: 8px; width: auto; flex-shrink: 0;">
                                Campo Requerido
                            </label>
                        </div>
                    </div>

                    <!-- Sección de opciones para campos dinámicos -->
                    <div class="ewm-field-options-section" style="display: none; margin-top: 15px;">
                        <details class="ewm-options-details">
                            <summary style="cursor: pointer; font-weight: bold; margin: 10px 0; padding: 8px; background: #f1f1f1; border-radius: 3px;">
                                📋 Opciones del Campo
                            </summary>
                            <div class="ewm-options-container" style="padding: 10px; border: 1px solid #ddd; border-radius: 3px; margin-top: 5px;">
                                <div class="ewm-options-list" data-step="${stepIndex}">
                                    <!-- Las opciones se agregarán aquí dinámicamente -->
                                </div>
                                <button type="button" class="ewm-add-option" style="margin-top: 10px; padding: 8px 12px; background: #0073aa; color: white; border: none; border-radius: 3px; cursor: pointer;">
                                    + Agregar Opción
                                </button>
                            </div>
                        </details>
                    </div>
                </div>
            `;
        },

        // Generar opciones de tipos de campo
        generateFieldTypeOptions: function(selectedType = 'text') {
            let optionsHtml = '';

            // Verificar si tenemos los tipos de campo disponibles
            if (typeof ewm_admin_vars !== 'undefined' && ewm_admin_vars.supported_field_types) {
                // Iterar sobre los tipos de campo soportados
                for (const [value, label] of Object.entries(ewm_admin_vars.supported_field_types)) {
                    optionsHtml += `<option value="${value}" ${selectedType === value ? 'selected' : ''}>${label}</option>`;
                }
            } else {
                // Fallback a tipos básicos si no están disponibles
                const basicTypes = {
                    'text': 'Texto',
                    'email': 'Email',
                    'tel': 'Teléfono',
                    'textarea': 'Área de Texto'
                };
                for (const [value, label] of Object.entries(basicTypes)) {
                    optionsHtml += `<option value="${value}" ${selectedType === value ? 'selected' : ''}>${label}</option>`;
                }
            }

            return optionsHtml;
        },

        // Generar HTML para una opción individual
        generateOptionHTML: function(stepIndex, optionData = {}) {
            const optionValue = optionData.value || '';
            const optionLabel = optionData.label || '';
            const optionId = 'option_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

            return `
                <div class="ewm-option-item" data-option-id="${optionId}" style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px; padding: 8px; border: 1px solid #e1e1e1; border-radius: 3px; background: #fafafa;">
                    <div class="ewm-drag-handle" style="cursor: move; color: #666; font-size: 16px;" title="Arrastrar para reordenar">
                        ⋮⋮
                    </div>
                    <div style="flex: 1;">
                        <input type="text"
                               name="steps[${stepIndex}][fields][options][value][]"
                               value="${optionValue}"
                               placeholder="valor"
                               style="width: 100%; padding: 6px; border: 1px solid #ccc; border-radius: 3px; margin-bottom: 4px;">
                        <input type="text"
                               name="steps[${stepIndex}][fields][options][label][]"
                               value="${optionLabel}"
                               placeholder="Etiqueta visible"
                               style="width: 100%; padding: 6px; border: 1px solid #ccc; border-radius: 3px;">
                    </div>
                    <button type="button" class="ewm-remove-option" style="padding: 6px 10px; background: #dc3232; color: white; border: none; border-radius: 3px; cursor: pointer;" title="Eliminar opción">
                        ✕
                    </button>
                </div>
            `;
        },

        // Manejar cambio de tipo de campo
        handleFieldTypeChange: function($select) {
            const fieldType = $select.val();
            const $fieldItem = $select.closest('.ewm-field-item');
            const $optionsSection = $fieldItem.find('.ewm-field-options-section');

            // Tipos que requieren opciones
            const typesWithOptions = ['select', 'radio', 'checkbox'];

            console.log('Field type changed to:', fieldType, 'Needs options:', typesWithOptions.includes(fieldType));

            if (typesWithOptions.includes(fieldType)) {
                // Mostrar sección de opciones
                $optionsSection.show();

                // Abrir automáticamente si no hay opciones
                const $optionsList = $optionsSection.find('.ewm-options-list');
                if ($optionsList.children().length === 0) {
                    $optionsSection.find('details').prop('open', true);
                    // Agregar una opción por defecto
                    this.addOptionToField($optionsSection.find('.ewm-add-option'));
                }
            } else {
                // Ocultar sección de opciones
                $optionsSection.hide();

                // Limpiar opciones si se cambia a un tipo que no las necesita
                const $optionsList = $optionsSection.find('.ewm-options-list');
                $optionsList.empty();

                // Cerrar el details si estaba abierto
                $optionsSection.find('details').prop('open', false);

                console.log('Options section hidden and cleared for field type:', fieldType);
            }
        },

        // Agregar opción a un campo
        addOptionToField: function($button) {
            const $optionsContainer = $button.closest('.ewm-options-container');
            const $optionsList = $optionsContainer.find('.ewm-options-list');
            const stepIndex = $optionsList.data('step');

            const optionHTML = this.generateOptionHTML(stepIndex);
            $optionsList.append(optionHTML);

            // Reinicializar sortable
            this.initOptionsSortable();
        },

        // Inicializar sortable para opciones
        initOptionsSortable: function() {
            $('.ewm-options-list').sortable({
                handle: '.ewm-drag-handle',
                axis: 'y',
                placeholder: 'ewm-option-placeholder',
                tolerance: 'pointer',
                update: function(event, ui) {
                    console.log('Options reordered');
                }
            });
        },

        // Actualizar shortcode
        updateShortcode: function() {
            const formData = this.getFormData();
            const shortcode = this.generateShortcode(formData);
            
            if (this.cache.$shortcodeOutput.length) {
                this.cache.$shortcodeOutput.text(shortcode);
            }
        },
        
        // Generar shortcode
        generateShortcode: function(data) {
            let shortcode = '[ew_modal';

            // Usar el ID del modal actual si existe, sino usar el del data
            const modalId = this.config.currentModalId || data.id;
            if (modalId) {
                shortcode += ` id="${modalId}"`;
            }

            if (data.mode && data.mode !== 'popup') {
                shortcode += ` mode="${data.mode}"`;
            }

            if (data.trigger && data.trigger !== 'manual') {
                shortcode += ` trigger="${data.trigger}"`;
            }
            
            if (data.size && data.size !== 'medium') {
                shortcode += ` size="${data.size}"`;
            }
            
            shortcode += ']';
            
            return shortcode;
        },
        
        // Obtener datos del formulario
        getFormData: function() {
            const data = {};

            // Obtener campos básicos
            this.cache.$form.find('input, select, textarea').each(function() {
                const $field = $(this);
                const name = $field.attr('name');
                let value = $field.val();

                // Manejar checkboxes
                if ($field.attr('type') === 'checkbox') {
                    value = $field.is(':checked');
                }

                if (name) {
                    // CORRECCIÓN: Manejar arrays correctamente
                    if (name.endsWith('[]')) {
                        // Es un campo array, necesitamos acumular valores
                        if (!data[name]) {
                            data[name] = [];
                        }
                        data[name].push(value || '');
                    } else {
                        // Campo simple
                        data[name] = value || '';
                    }
                }
            });

            // Obtener datos específicos de pasos - MEJORADO para manejar campos con nombres array
            const steps = [];
            const stepTitles = {};
            const stepContents = {};
            const stepFields = {};

            console.log('STEPS PROCESSING - Raw data keys:', Object.keys(data).filter(k => k.includes('steps')));
            console.log('STEPS PROCESSING - All form data:', data);

            // DEBUG: Verificar arrays de campos
            Object.keys(data).forEach(key => {
                if (key.includes('fields') && Array.isArray(data[key])) {
                    console.log('FIELD ARRAY DEBUG - Key:', key, 'Values:', data[key]);
                }
            });

            // Procesar campos con formato steps[0][title], steps[0][content], etc.
            // También manejar la nueva estructura de campos: steps[0][fields][id][], steps[0][fields][type][], etc.
            const stepFieldsData = {};

            Object.keys(data).forEach(key => {
                // Manejar campos básicos de pasos
                const stepMatch = key.match(/^steps\[(\d+)\]\[(\w+)\](\[\])?$/);
                if (stepMatch) {
                    const stepIndex = parseInt(stepMatch[1]);
                    const fieldName = stepMatch[2];
                    const isArray = !!stepMatch[3];

                    if (fieldName === 'title') {
                        stepTitles[stepIndex] = data[key];
                    } else if (fieldName === 'content') {
                        stepContents[stepIndex] = data[key];
                    } else if (fieldName === 'fields' && isArray) {
                        // Compatibilidad con el formato anterior
                        stepFields[stepIndex] = Array.isArray(data[key]) ? data[key] : [data[key]];
                    }

                    delete data[key];
                }

                // Manejar nueva estructura de campos: steps[0][fields][property][]
                const fieldMatch = key.match(/^steps\[(\d+)\]\[fields\]\[(\w+)\]\[\]$/);
                if (fieldMatch) {
                    const stepIndex = parseInt(fieldMatch[1]);
                    const fieldProperty = fieldMatch[2]; // id, type, label, placeholder, required

                    if (!stepFieldsData[stepIndex]) {
                        stepFieldsData[stepIndex] = {};
                    }

                    stepFieldsData[stepIndex][fieldProperty] = Array.isArray(data[key]) ? data[key] : [data[key]];
                    delete data[key];
                }

                // Manejar opciones de campos: steps[0][fields][options][value][] y steps[0][fields][options][label][]
                const optionMatch = key.match(/^steps\[(\d+)\]\[fields\]\[options\]\[(\w+)\]\[\]$/);
                if (optionMatch) {
                    const stepIndex = parseInt(optionMatch[1]);
                    const optionProperty = optionMatch[2]; // value o label

                    if (!stepFieldsData[stepIndex]) {
                        stepFieldsData[stepIndex] = {};
                    }
                    if (!stepFieldsData[stepIndex].options) {
                        stepFieldsData[stepIndex].options = {};
                    }

                    stepFieldsData[stepIndex].options[optionProperty] = Array.isArray(data[key]) ? data[key] : [data[key]];
                    delete data[key];
                }
            });

            // Convertir stepFieldsData a formato de campos estructurados
            Object.keys(stepFieldsData).forEach(stepIndex => {
                const fieldData = stepFieldsData[stepIndex];
                const fields = [];

                if (fieldData.id && Array.isArray(fieldData.id)) {
                    fieldData.id.forEach((id, index) => {
                        if (id && id.trim()) { // Solo procesar campos con ID válido
                            const field = {
                                id: id.trim(),
                                type: fieldData.type?.[index] || 'text',
                                label: fieldData.label?.[index] || '',
                                placeholder: fieldData.placeholder?.[index] || '',
                                required: !!(fieldData.required?.[index]) // Convertir a boolean
                            };

                            // Agregar opciones si existen
                            if (fieldData.options && fieldData.options.value && fieldData.options.label) {
                                const options = [];
                                const values = fieldData.options.value;
                                const labels = fieldData.options.label;

                                // Construir array de opciones
                                for (let i = 0; i < values.length; i++) {
                                    if (values[i] && values[i].trim()) {
                                        options.push({
                                            value: values[i].trim(),
                                            label: labels[i] || values[i].trim()
                                        });
                                    }
                                }

                                if (options.length > 0) {
                                    field.options = options;
                                }
                            }

                            fields.push(field);
                        }
                    });
                }

                if (fields.length > 0) {
                    stepFields[stepIndex] = fields;
                }
            });

            // Construir array de steps estructurado
            const maxStepIndex = Math.max(
                ...Object.keys(stepTitles).map(i => parseInt(i)),
                ...Object.keys(stepContents).map(i => parseInt(i)),
                ...Object.keys(stepFields).map(i => parseInt(i)),
                -1
            );

            console.log('STEPS PROCESSING - Max step index:', maxStepIndex);
            console.log('STEPS PROCESSING - Step titles:', stepTitles);
            console.log('STEPS PROCESSING - Step contents:', stepContents);
            console.log('STEPS PROCESSING - Step fields:', stepFields);

            for (let i = 0; i <= maxStepIndex; i++) {
                const stepData = {
                    title: stepTitles[i] || '',
                    content: stepContents[i] || '',
                    fields: stepFields[i] || []
                };

                console.log('STEPS PROCESSING - Step ' + i + ':', stepData);

                // Solo agregar si tiene contenido
                if (stepData.title || stepData.content || stepData.fields.length > 0) {
                    steps.push(stepData);
                }
            }

            console.log('STEPS PROCESSING - Final steps array:', steps);
            console.log('STEPS PROCESSING - Steps array length:', steps.length);

            // CONTRACTOR FIX: Unificar la estructura de 'steps' para que coincida con el validador de PHP
            data.steps = {
                steps: steps,
                final_step: {}, // Puedes expandir esto si manejas un paso final por separado
                progressBar: {
                    enabled: $('#show-progress-bar').is(':checked'),
                    color: data.primary_color || '#ff6b35',
                    style: 'line' // O tomarlo de un campo si existe
                }
            };
            console.log('CONTRACTOR DEBUG - PREPARED DATA:', data.steps);
            console.log('STEPS PROCESSING - Steps JSON (Unified Structure):', JSON.stringify(data.steps));

            // Obtener configuración de diseño - COMPLETAR todos los campos esperados
            const design = {
                theme: 'default',
                colors: {
                    primary: data.primary_color || '#ff6b35',
                    secondary: data.secondary_color || '#333333',
                    background: data.background_color || '#ffffff'
                },
                typography: {
                    font_family: 'inherit',
                    font_size: '16px'
                },
                modal_size: data.size || 'medium',  // CORREGIR: usar 'size' no 'modal_size'
                animation: data.animation || 'fade'
            };

            data.design = design;

            // Estructurar triggers - COMPLETAR estructura esperada
            const triggers = {
                exit_intent: {
                    enabled: !!data.exit_intent_enabled,
                    sensitivity: 20
                },
                time_delay: {
                    enabled: !!data.time_delay_enabled,
                    delay: parseInt(data.time_delay) || 5000
                },
                scroll_percentage: {
                    enabled: !!data.scroll_trigger_enabled,
                    percentage: parseInt(data.scroll_percentage) || 50
                },
                manual: {
                    enabled: !!data.manual_trigger_enabled,
                    selector: ''
                }
            };

            data.triggers = triggers;

            // Estructurar WooCommerce integration - COMPLETAR estructura esperada
            const wc_integration = {
                enabled: !!data.wc_integration_enabled,
                cart_abandonment: {
                    enabled: false,
                    delay: 300
                },
                product_recommendations: {
                    enabled: false,
                    count: 3
                }
            };

            data.wc_integration = wc_integration;

            // Estructurar display_rules - CORREGIR frecuencia para leer del formulario
            const frequencyType = data.display_frequency || 'always';
            let frequencyConfig;

            switch (frequencyType) {
                case 'always':
                    frequencyConfig = { type: 'never', limit: 0 };
                    break;
                case 'once_per_session':
                    frequencyConfig = { type: 'session', limit: 1 };
                    break;
                case 'once_per_day':
                    frequencyConfig = { type: 'daily', limit: 1 };
                    break;
                case 'once_per_week':
                    frequencyConfig = { type: 'weekly', limit: 1 };
                    break;
                default:
                    frequencyConfig = { type: 'never', limit: 0 };
            }

            const display_rules = {
                pages: {
                    include: [],
                    exclude: []
                },
                user_roles: [],
                devices: {
                    desktop: true,
                    tablet: true,
                    mobile: true
                },
                frequency: frequencyConfig
            };

            data.display_rules = display_rules;

            // Limpiar campos procesados
            delete data.exit_intent_enabled;
            delete data.time_delay_enabled;
            delete data.time_delay;
            delete data.scroll_trigger_enabled;
            delete data.scroll_percentage;
            delete data.manual_trigger_enabled;
            delete data.wc_integration_enabled;
            delete data.primary_color;
            delete data.secondary_color;
            delete data.background_color;
            delete data.modal_size;
            delete data.display_frequency; // Limpiar campo de frecuencia procesado

            // Logging para debug
            console.log('GET FORM DATA - Collected data:', {
                totalFields: Object.keys(data).length,
                hasSteps: !!data.steps,
                stepsCount: data.steps && data.steps.steps ? data.steps.steps.length : 0,
                hasDesign: !!data.design,
                hasTriggers: !!data.triggers,
                triggersCount: data.triggers ? data.triggers.length : 0,
                dataKeys: Object.keys(data),
                fullData: data
            });

            return data;
        },
        
        // Actualizar vista previa
        updatePreview: function() {
            const formData = this.getFormData();
            
            this.cache.$previewContainer.html('<div class="ewm-loading"></div>');
            
            $.ajax({
                url: this.config.restUrl + 'ewm/v1/preview',
                method: 'POST',
                data: formData,
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', EWMAdmin.config.restNonce);
                },
                success: function(response) {
                    EWMAdmin.cache.$previewContainer.html(response.html || '<p class="ewm-preview-placeholder">Vista previa no disponible</p>');
                },
                error: function() {
                    EWMAdmin.cache.$previewContainer.html('<p class="ewm-preview-placeholder">Error al cargar vista previa</p>');
                }
            });
        },
        
        // Guardar modal usando REST API
        saveModal: function() {
            const formData = this.getFormData();
            const $saveBtn = $('#ewm-save-modal');
            const modalId = this.config.currentModalId;
            const isNewModal = !modalId || modalId === 'new';

            console.log('SAVE MODAL - Starting save process:', {
                modalId: modalId,
                isNewModal: isNewModal,
                formDataKeys: Object.keys(formData),
                formDataSize: JSON.stringify(formData).length
            });

            $saveBtn.prop('disabled', true).html('<span class="ewm-loading"></span> Guardando...');

            // Preparar datos para REST API - ASEGURAR estructura unificada
            const requestData = {
                title: formData.title || 'Modal sin título',
                config: {
                    mode: formData.mode || 'formulario',
                    steps: formData.steps || {steps: [], final_step: {}, progressBar: {enabled: false, color: '#ff6b35', style: 'line'}},
                    design: formData.design || {},
                    triggers: formData.triggers || {},
                    wc_integration: formData.wc_integration || {},
                    display_rules: formData.display_rules || {},
                    custom_css: formData.custom_css || ''
                }
            };

            // LOGGING DETALLADO para debug
            console.log('REQUEST DATA - Final structure:', {
                title: requestData.title,
                configKeys: Object.keys(requestData.config),
                stepsType: typeof requestData.config.steps,
                stepsIsArray: Array.isArray(requestData.config.steps),
                stepsLength: requestData.config.steps ? requestData.config.steps.length : 0,
                stepsContent: requestData.config.steps,
                designType: typeof requestData.config.design,
                triggersType: typeof requestData.config.triggers
            });

            console.log('SAVE MODAL - Request data prepared:', {
                requestData: requestData,
                requestSize: JSON.stringify(requestData).length
            });

            // Determinar URL y método
            const url = isNewModal
                ? this.config.restUrl + 'ewm/v1/modals'
                : this.config.restUrl + 'ewm/v1/modals/' + modalId;
            const method = isNewModal ? 'POST' : 'PUT';

            console.log('SAVE MODAL - Making request:', {
                url: url,
                method: method,
                nonce: this.config.restNonce
            });

            $.ajax({
                url: url,
                method: method,
                contentType: 'application/json',
                data: JSON.stringify(requestData),
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', EWMAdmin.config.restNonce);
                },
                success: function(response) {
                    console.log('SAVE MODAL - Success response:', response);

                    EWMAdmin.showAlert('Modal guardado correctamente', 'success');

                    // Actualizar ID si es nuevo modal
                    if (isNewModal && response.id) {
                        EWMAdmin.config.currentModalId = response.id;
                        console.log('SAVE MODAL - Updated modal ID:', response.id);

                        // Actualizar URL del navegador
                        const newUrl = window.location.pathname + '?page=ewm-modal-builder&modal_id=' + response.id;
                        window.history.replaceState({}, '', newUrl);
                    }

                    // Actualizar shortcode
                    EWMAdmin.updateShortcode();
                },
                error: function(xhr, status, error) {
                    console.error('SAVE MODAL - Error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });

                    let errorMessage = 'Error al guardar el modal';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 403) {
                        errorMessage = 'Sin permisos para guardar el modal';
                    } else if (xhr.status === 404) {
                        errorMessage = 'Endpoint no encontrado';
                    }

                    EWMAdmin.showAlert(errorMessage, 'error');
                },
                complete: function() {
                    $saveBtn.prop('disabled', false).html('Guardar Modal');
                }
            });
        },
        
        // Vista previa del modal
        previewModal: function() {
            this.updatePreview();
            
            // Cambiar a pestaña de vista previa
            $('a[href="#preview"]').trigger('click');
        },
        
        // Copiar shortcode
        copyShortcode: function() {
            const shortcode = this.cache.$shortcodeOutput.text();
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(shortcode).then(function() {
                    EWMAdmin.showAlert('Shortcode copiado al portapapeles', 'success');
                });
            } else {
                // Fallback para navegadores antiguos
                const $temp = $('<textarea>');
                $('body').append($temp);
                $temp.val(shortcode).select();
                document.execCommand('copy');
                $temp.remove();
                
                this.showAlert('Shortcode copiado al portapapeles', 'success');
            }
        },
        
        // Limpiar formulario
        clearForm: function() {
            if (confirm('¿Estás seguro de que quieres limpiar el formulario?')) {
                this.cache.$form[0].reset();
                this.handleFormChange();
            }
        },
        
        // Cargar datos del modal
        loadModalData: function() {
            console.log('Loading modal data...', {
                currentModalId: this.config.currentModalId,
                restUrl: this.config.restUrl,
                nonce: this.config.nonce
            });

            if (!this.config.currentModalId) {
                console.log('No modal ID provided, skipping data load');
                return;
            }

            const requestUrl = this.config.restUrl + 'ewm/v1/modals/' + this.config.currentModalId;
            console.log('Making request to:', requestUrl);

            $.ajax({
                url: requestUrl,
                method: 'GET',
                beforeSend: function(xhr) {
                    console.log('Setting REST nonce header:', EWMAdmin.config.restNonce);
                    xhr.setRequestHeader('X-WP-Nonce', EWMAdmin.config.restNonce);
                },
                xhrFields: {
                    withCredentials: true
                },
                success: function(response) {
                    console.log('Modal data loaded successfully:', response);
                    EWMAdmin.populateForm(response);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading modal data:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });

                    // Si es error 403, intentar con AJAX tradicional
                    if (xhr.status === 403) {
                        console.log('403 error, trying AJAX fallback...');
                        EWMAdmin.loadModalDataAjax();
                    } else {
                        EWMAdmin.showAlert('Error al cargar datos del modal: ' + xhr.status + ' ' + xhr.statusText, 'error');
                    }
                }
            });
        },

        // Cargar datos del modal usando AJAX tradicional (fallback)
        loadModalDataAjax: function() {
            console.log('Loading modal data via AJAX fallback...');

            $.ajax({
                url: this.config.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'ewm_load_modal_builder',
                    nonce: this.config.nonce,
                    modal_id: this.config.currentModalId
                },
                success: function(response) {
                    console.log('Modal data loaded via AJAX:', response);
                    if (response.success) {
                        EWMAdmin.populateForm(response.data);
                    } else {
                        EWMAdmin.showAlert('Error: ' + (response.data.message || 'Unknown error'), 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX fallback also failed:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        error: error
                    });
                    EWMAdmin.showAlert('Error al cargar datos del modal (AJAX fallback)', 'error');
                }
            });
        },

        // Poblar formulario con datos
        populateForm: function(data) {
            console.log('Populating form with data:', data);

            // Manejar campos básicos
            Object.keys(data).forEach(function(key) {
                if (key !== 'steps' && key !== 'display_rules') { // Excluir steps y display_rules para manejo especial
                    const $field = $(`[name="${key}"]`);
                    if ($field.length) {
                        $field.val(data[key]);
                    }
                }
            });

            // Manejar display_rules especialmente la frecuencia
            if (data.display_rules && data.display_rules.frequency) {
                const frequency = data.display_rules.frequency;
                let displayFrequencyValue = 'always'; // valor por defecto

                // Mapear de la estructura de BD al valor del formulario
                switch (frequency.type) {
                    case 'never':
                        displayFrequencyValue = 'always';
                        break;
                    case 'session':
                        displayFrequencyValue = 'once_per_session';
                        break;
                    case 'daily':
                        displayFrequencyValue = 'once_per_day';
                        break;
                    case 'weekly':
                        displayFrequencyValue = 'once_per_week';
                        break;
                }

                console.log('Setting display_frequency to:', displayFrequencyValue);
                $('[name="display_frequency"]').val(displayFrequencyValue);
            }

            // Manejar steps con estructura unificada
            if (data.steps && data.steps.steps) {
                this.populateSteps(data.steps.steps);
            }

            this.handleFormChange();
        },

        // Poblar pasos en el formulario
        populateSteps: function(steps) {
            console.log('Populating steps:', steps);

            // Limpiar pasos existentes
            $('.ewm-steps-config').empty();

            // Agregar cada paso
            steps.forEach((step, index) => {
                const stepHtml = this.getStepTemplate(index + 1);
                $('.ewm-steps-config').append(stepHtml);

                // Poblar datos del paso
                if (step.title) {
                    $(`[name="steps[${index}][title]"]`).val(step.title);
                }
                if (step.content) {
                    $(`[name="steps[${index}][content]"]`).val(step.content);
                }
                if (step.fields && Array.isArray(step.fields)) {
                    // Poblar campos completos en el builder visual
                    console.log('POPULATE STEPS - Processing fields for step', index, ':', step.fields);

                    const $fieldsContainer = $(`.ewm-fields-builder[data-step="${index}"] .ewm-fields-list`);
                    $fieldsContainer.empty(); // Limpiar campos existentes

                    step.fields.forEach(field => {
                        // Asegurar que field es un objeto con todas las propiedades
                        const fieldData = typeof field === 'string' ? { id: field, type: 'text', label: '', placeholder: '', required: false } : field;
                        console.log('Adding field to builder:', fieldData);

                        const fieldHTML = this.generateFieldHTML(index, fieldData);
                        const $fieldElement = $(fieldHTML);
                        $fieldsContainer.append($fieldElement);

                        // Inicializar el estado de opciones basado en el tipo de campo
                        const $typeSelect = $fieldElement.find('select[name*="[type]"]');
                        this.handleFieldTypeChange($typeSelect);

                        // Si el campo tiene opciones Y el tipo las requiere, poblarlas
                        if (fieldData.options && Array.isArray(fieldData.options)) {
                            const $optionsSection = $fieldElement.find('.ewm-field-options-section');
                            const $optionsList = $fieldElement.find('.ewm-options-list');

                            // Tipos que requieren opciones
                            const typesWithOptions = ['select', 'radio', 'checkbox'];

                            if (typesWithOptions.includes(fieldData.type)) {
                                // Mostrar la sección de opciones solo si el tipo las necesita
                                $optionsSection.show();
                                $optionsSection.find('details').prop('open', true);

                                // Agregar cada opción
                                fieldData.options.forEach(option => {
                                    const optionHTML = this.generateOptionHTML(index, option);
                                    $optionsList.append(optionHTML);
                                });
                            } else {
                                // Ocultar opciones si el tipo no las necesita
                                $optionsSection.hide();
                                console.log('Field type', fieldData.type, 'does not need options, hiding section');
                            }
                        }
                    });
                }
            });

            this.updateStepsDisplay();
        },
        
        // Marcar como modificado
        markAsModified: function() {
            if (!$('body').hasClass('ewm-modified')) {
                $('body').addClass('ewm-modified');
                
                // Advertir antes de salir
                $(window).on('beforeunload', function() {
                    return 'Tienes cambios sin guardar. ¿Estás seguro de que quieres salir?';
                });
            }
        },
        
        // Mostrar alerta
        showAlert: function(message, type = 'info') {
            const alertHtml = `
                <div class="ewm-alert ${type}">
                    ${message}
                    <button type="button" class="notice-dismiss" onclick="this.parentElement.remove()">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
            `;
            
            $('.ewm-tab-content').prepend(alertHtml);
            
            // Auto-remover después de 5 segundos
            setTimeout(function() {
                $('.ewm-alert').first().fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };
    
    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        EWMAdmin.init();
    });
    
    // Exponer globalmente para debugging
    window.EWMAdmin = EWMAdmin;
    
})(jQuery);
