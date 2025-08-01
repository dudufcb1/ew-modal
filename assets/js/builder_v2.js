/**
 * EWM Modal Builder v2 JavaScript
 * Builder avanzado para la página ewm-modal-builder
 */
(function($) {
    'use strict';

    console.log('🔍 EWM Builder v2: Script loaded and executing!');

    const EWMBuilderV2 = {
        // Variables globales
        currentModalId: null,
        isLoading: false,
        stepCounter: 1,

        /**
         * Inicializar el builder
         */
        init: function() {
            console.log('EWM Builder v2: Initializing...');

            // Verificar que estamos en la página correcta
            if (!$('.ewm-modal-builder').length) {
                console.log('EWM Builder v2: Not on builder page, skipping initialization');
                return;
            }

            // Verificar variables necesarias
            if (typeof ewm_admin_vars === 'undefined') {
                console.error('EWM Builder v2: ewm_admin_vars not found');
                return;
            }

            this.currentModalId = ewm_admin_vars.modal_id;
            this.bindEvents();
            this.initStepBuilder();
            this.initDragAndDrop();

            console.log('EWM Builder v2: Initialized successfully');
        },

        /**
         * Vincular eventos específicos del builder
         */
        bindEvents: function() {
            // Agregar paso
            $(document).on('click', '.ewm-add-step', this.addStep.bind(this));

            // Eliminar paso
            $(document).on('click', '.ewm-remove-step', this.removeStep.bind(this));

            // Agregar campo
            $(document).on('click', '.ewm-add-field', function(e) {
                console.log('🔍 EWM Builder v2: .ewm-add-field clicked!', e.target);
                EWMBuilderV2.addField(e);
            });

            // Eliminar campo
            $(document).on('click', '.ewm-remove-field', this.removeField.bind(this));

            // Cambio de tipo de campo
            $(document).on('change', '.ewm-field-type', this.onFieldTypeChange.bind(this));

            // Duplicar paso
            $(document).on('click', '.ewm-duplicate-step', this.duplicateStep.bind(this));

            // Toggle para expandir/colapsar pasos
            $(document).on('click', '.ewm-step-header', this.toggleStepContent.bind(this));
        },

        /**
         * Inicializar constructor de pasos
         */
        initStepBuilder: function() {
            // Intentar cargar steps existentes desde el backend
            this.loadExistingSteps();
        },

        /**
         * Inicializar drag and drop
         */
        initDragAndDrop: function() {
            if ($.fn.sortable) {
                // Hacer pasos sortables
                $('.ewm-steps-config').sortable({
                    handle: '.ewm-step-handle',
                    placeholder: 'ewm-step-placeholder',
                    update: this.onStepsReordered.bind(this)
                });

                // Hacer campos sortables dentro de cada paso
                $('.ewm-step-config').each(function() {
                    $(this).find('.ewm-fields-container').sortable({
                        handle: '.ewm-field-handle',
                        placeholder: 'ewm-field-placeholder',
                        update: function() {
                            console.log('EWM Builder v2: Fields reordered');
                        }
                    });
                });
            }
        },

        /**
         * Agregar nuevo paso
         */
        addStep: function(e) {
            if (e) e.preventDefault();

            const stepNumber = this.stepCounter++;
            const stepHtml = this.generateStepHtml(stepNumber);

            $('.ewm-steps-config').append(stepHtml);

            // Cierra los pasos existentes y abre el nuevo
            $('.ewm-step-config').removeClass('active');
            $('.ewm-steps-config .ewm-step-config').last().addClass('active');

            // Reinicializar sortable para campos del nuevo paso
            const newStep = $('.ewm-step-config').last();
            if ($.fn.sortable) {
                newStep.find('.ewm-fields-container').sortable({
                    handle: '.ewm-field-handle',
                    placeholder: 'ewm-field-placeholder'
                });
            }

            console.log('EWM Builder v2: Step added', stepNumber);
        },

        /**
         * Eliminar paso
         */
        removeStep: function(e) {
            e.preventDefault();

            if ($('.ewm-step-config').length <= 1) {
                alert('There must be at least one step in the form');
                return;
            }

            if (confirm('Are you sure you want to delete this step?')) {
                $(e.target).closest('.ewm-step-config').remove();
                this.updateStepNumbers();
                console.log('EWM Builder v2: Step removed');
            }
        },

        /**
         * Agregar campo a un paso
         */
        addField: function(e) {
            console.log('🔍 EWM Builder v2: addField called', e);
            e.preventDefault();

            const stepContainer = $(e.target).closest('.ewm-step-config');
            const fieldsContainer = stepContainer.find('.ewm-fields-container');
            const fieldNumber = fieldsContainer.find('.ewm-field-config').length + 1;

            console.log('🔍 EWM Builder v2: stepContainer found:', stepContainer.length);
            console.log('🔍 EWM Builder v2: fieldsContainer found:', fieldsContainer.length);
            console.log('🔍 EWM Builder v2: fieldNumber:', fieldNumber);

            const fieldHtml = this.generateFieldHtml(fieldNumber);
            fieldsContainer.append(fieldHtml);

            console.log('EWM Builder v2: Field added to step');
        },

        /**
         * Eliminar campo
         */
        removeField: function(e) {
            e.preventDefault();

            if (confirm('Are you sure you want to delete this field?')) {
                $(e.target).closest('.ewm-field-config').remove();
                console.log('EWM Builder v2: Field removed');
            }
        },

        /**
         * Duplicar paso
         */
        duplicateStep: function(e) {
            e.preventDefault();

            const stepToDuplicate = $(e.target).closest('.ewm-step-config');
            const clonedStep = stepToDuplicate.clone();

            // Actualizar números y IDs
            const newStepNumber = this.stepCounter++;
            clonedStep.find('.ewm-step-number').text(newStepNumber);
            clonedStep.find('.ewm-step-title').val(clonedStep.find('.ewm-step-title').val() + ' (Copia)');

            stepToDuplicate.after(clonedStep);

            // Reinicializar sortable
            if ($.fn.sortable) {
                clonedStep.find('.ewm-fields-container').sortable({
                    handle: '.ewm-field-handle',
                    placeholder: 'ewm-field-placeholder'
                });
            }

            console.log('EWM Builder v2: Step duplicated');
        },

        /**
         * Manejar cambio de tipo de campo
         */
        onFieldTypeChange: function(e) {
            const fieldType = $(e.target).val();
            const fieldConfig = $(e.target).closest('.ewm-field-config');
            const optionsContainer = fieldConfig.find('.ewm-field-options-container');

            // Mostrar/ocultar opciones según el tipo de campo
            if (['select', 'radio', 'checkbox'].includes(fieldType)) {
                optionsContainer.show();
            } else {
                optionsContainer.hide();
            }

            console.log('EWM Builder v2: Field type changed to', fieldType);
        },

        /**
         * Manejar reordenamiento de pasos
         */
        onStepsReordered: function() {
            this.updateStepNumbers();
            console.log('EWM Builder v2: Steps reordered');
        },

        /**
         * Toggle para expandir/colapsar contenido del paso
         */
        toggleStepContent: function(e) {
            e.preventDefault();
            const $header = $(e.currentTarget);
            const $stepConfig = $header.closest('.ewm-step-config');

            // Acordeón: solo uno abierto a la vez
            const isActive = $stepConfig.hasClass('active');

            // Cierra todos los demás
            $('.ewm-step-config').removeClass('active');

            // Abre el actual si no estaba activo
            if (!isActive) {
                $stepConfig.addClass('active');
            }

            console.log('EWM Builder v2: Step content toggled');
        },

        /**
         * Cargar steps existentes desde el backend
         */
        loadExistingSteps: function() {
            // Esperar a que EWMModalAdmin cargue los datos
            if (typeof EWMModalAdmin !== 'undefined' && EWMModalAdmin.currentModalId) {
                // Escuchar cuando se cargan los datos del modal
                $(document).on('ewm-modal-data-loaded', this.onModalDataLoaded.bind(this));

                console.log('🔍 EWM Builder v2: Waiting for modal data to load...');
            } else {
                // Si no hay modal ID, crear step por defecto
                console.log('🔍 EWM Builder v2: No modal ID, creating default step');
                this.addStep();
            }
        },

        /**
         * Manejar cuando se cargan los datos del modal
         */
        onModalDataLoaded: function(event, modalData) {
            console.log('🔍 EWM Builder v2: Modal data loaded event received:', modalData);

            if (modalData && modalData.steps && modalData.steps.steps && modalData.steps.steps.length > 0) {
                // Limpiar steps existentes
                $('.ewm-steps-config').empty();

                // Cargar cada step existente
                modalData.steps.steps.forEach((stepData, index) => {
                    this.createStepFromData(stepData, index + 1);
                });

                // Abrir el primer step por defecto
                $('.ewm-step-config').first().addClass('active');

                console.log('EWM Builder v2: Loaded', modalData.steps.steps.length, 'existing steps');
            } else {
                // Si no hay steps existentes, crear uno por defecto
                console.log('🔍 EWM Builder v2: No existing steps in data, creating default step');
                this.addStep();
            }
        },

        /**
         * Crear step desde datos del backend
         */
        createStepFromData: function(stepData, stepNumber) {
            const stepHtml = this.generateStepHtml(stepNumber);
            $('.ewm-steps-config').append(stepHtml);

            const $newStep = $('.ewm-step-config').last();

            // Poblar los datos del step
            $newStep.find('.ewm-step-title').val(stepData.title || '');
            $newStep.find('.ewm-step-subtitle').val(stepData.subtitle || '');
            $newStep.find('.ewm-step-description').val(stepData.description || stepData.content || '');

            // Poblar los campos
            if (stepData.fields && stepData.fields.length > 0) {
                const $fieldsContainer = $newStep.find('.ewm-fields-container');

                stepData.fields.forEach((fieldData, fieldIndex) => {
                    const fieldHtml = this.generateFieldHtml(fieldIndex + 1);
                    $fieldsContainer.append(fieldHtml);

                    const $newField = $fieldsContainer.find('.ewm-field-config').last();

                    // Poblar datos del campo
                    $newField.find('.ewm-field-id').val(fieldData.id || '');
                    $newField.find('.ewm-field-type').val(fieldData.type || 'text');
                    $newField.find('.ewm-field-label').val(fieldData.label || '');
                    $newField.find('.ewm-field-placeholder').val(fieldData.placeholder || '');
                    $newField.find('.ewm-field-required').prop('checked', fieldData.required || false);
                    $newField.find('.ewm-field-options').val(fieldData.options || '');

                    // Trigger change event para mostrar opciones si es necesario
                    $newField.find('.ewm-field-type').trigger('change');
                });
            }

            // Reinicializar sortable para campos
            if ($.fn.sortable) {
                $newStep.find('.ewm-fields-container').sortable({
                    handle: '.ewm-field-handle',
                    placeholder: 'ewm-field-placeholder'
                });
            }

            this.stepCounter = Math.max(this.stepCounter, stepNumber + 1);

            console.log('EWM Builder v2: Created step from data:', stepNumber);
        },

        /**
         * Actualizar números de pasos
         */
        updateStepNumbers: function() {
            $('.ewm-step-config').each(function(index) {
                $(this).find('.ewm-step-number').text(index + 1);
            });
        },

        /**
         * Generar HTML para un nuevo paso
         */
        generateStepHtml: function(stepNumber) {
            return `
                <div class="ewm-step-config" data-step="${stepNumber}">
                    <div class="ewm-step-header">
                        <span class="ewm-step-handle">⋮⋮</span>
                        <h4>Step <span class="ewm-step-number">${stepNumber}</span></h4>
                        <div class="ewm-step-actions">
                            <button type="button" class="ewm-btn small ewm-duplicate-step">Duplicate</button>
                            <button type="button" class="ewm-btn small danger ewm-remove-step">Delete</button>
                        </div>
                    </div>

                    <div class="ewm-step-content">
                        <div class="ewm-form-group">
                            <label>Step Title</label>
                            <input type="text" class="ewm-step-title ewm-form-control" placeholder="Step title...">
                        </div>

                        <div class="ewm-form-group">
                            <label>Subtitle (optional)</label>
                            <input type="text" class="ewm-step-subtitle ewm-form-control" placeholder="Step subtitle...">
                        </div>

                        <div class="ewm-form-group">
                            <label>Description (optional)</label>
                            <textarea class="ewm-step-description ewm-form-control" rows="2" placeholder="Step description..."></textarea>
                        </div>

                        <div class="ewm-fields-section">
                            <h5>Step Fields</h5>
                            <div class="ewm-fields-container"></div>
                            <button type="button" class="ewm-btn secondary small ewm-add-field">+ Add Field</button>
                        </div>
                    </div>
                </div>
            `;
        },

        /**
         * Generar HTML para un nuevo campo
         */
        generateFieldHtml: function(fieldNumber) {
            const fieldTypes = ewm_admin_vars.supported_field_types || {
                'text': 'Text',
                'email': 'Email',
                'tel': 'Phone',
                'textarea': 'Text Area',
                'select': 'Dropdown List',
                'radio': 'Radio Buttons',
                'checkbox': 'Checkboxes',
                'number': 'Number',
                'url': 'URL',
                'date': 'Date'
            };

            let optionsHtml = '';
            Object.keys(fieldTypes).forEach(type => {
                optionsHtml += `<option value="${type}">${fieldTypes[type]}</option>`;
            });

            return `
                <div class="ewm-field-config" data-field="${fieldNumber}">
                    <div class="ewm-field-header">
                        <span class="ewm-field-handle">⋮⋮</span>
                        <span class="ewm-field-title">Field ${fieldNumber}</span>
                        <button type="button" class="ewm-btn small danger ewm-remove-field">×</button>
                    </div>

                    <div class="ewm-field-content">
                        <div class="ewm-form-row">
                            <div class="ewm-form-group">
                                <label>Field ID</label>
                                <input type="text" class="ewm-field-id ewm-form-control small" placeholder="field_${fieldNumber}">
                            </div>

                            <div class="ewm-form-group">
                                <label>Field Type</label>
                                <select class="ewm-field-type ewm-form-control small">
                                    ${optionsHtml}
                                </select>
                            </div>
                        </div>

                        <div class="ewm-form-group">
                            <label>Field Label</label>
                            <input type="text" class="ewm-field-label ewm-form-control" placeholder="Etiqueta del campo...">
                        </div>

                        <div class="ewm-form-group">
                            <label>Placeholder (optional)</label>
                            <input type="text" class="ewm-field-placeholder ewm-form-control" placeholder="Help text...">
                        </div>

                        <div class="ewm-form-group">
                            <div class="ewm-checkbox">
                                <input type="checkbox" class="ewm-field-required" id="required_${fieldNumber}">
                                <label for="required_${fieldNumber}">Required field</label>
                            </div>
                        </div>

                        <div class="ewm-field-options-container" style="display: none;">
                            <label>Options (one per line)</label>
                            <textarea class="ewm-field-options ewm-form-control" rows="3" placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
                        </div>
                    </div>
                </div>
            `;
        }
    };

    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        EWMBuilderV2.init();
    });

    // Exponer globalmente para debugging
    window.EWMBuilderV2 = EWMBuilderV2;

})(jQuery);
