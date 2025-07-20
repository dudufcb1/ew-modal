/**
 * EWM Logging Admin JavaScript
 * 
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

(function($, window, document) {
    'use strict';
    
    /**
     * Clase principal para la administración de logging
     */
    class EWMLoggingAdmin {
        constructor() {
            this.config = window.ewmLoggingAdmin || {};
            this.init();
        }
        
        /**
         * Inicializar la clase
         */
        init() {
            this.bindEvents();
            this.loadRecentLogs();
            this.startAutoRefresh();
        }
        
        /**
         * Vincular eventos
         */
        bindEvents() {
            // Test logging
            $('#ewm-test-logging').on('click', (e) => {
                e.preventDefault();
                this.testLogging();
            });
            
            // Clear logs
            $('#ewm-clear-logs').on('click', (e) => {
                e.preventDefault();
                this.clearLogs();
            });
            
            // Refresh logs
            $('#ewm-refresh-logs').on('click', (e) => {
                e.preventDefault();
                this.loadRecentLogs();
            });
            
            // Auto-save settings
            $('input[name*="ewm_logging_config"], select[name*="ewm_logging_config"]').on('change', () => {
                this.showMessage('Settings will be saved when you click "Save Changes"', 'info');
            });
            
            // Enable/disable dependent fields
            $('input[name="ewm_logging_config[enabled]"]').on('change', (e) => {
                this.toggleDependentFields($(e.target).is(':checked'));
            });
            
            // Initialize dependent fields state
            const isEnabled = $('input[name="ewm_logging_config[enabled]"]').is(':checked');
            this.toggleDependentFields(isEnabled);
        }
        
        /**
         * Alternar campos dependientes
         */
        toggleDependentFields(enabled) {
            const dependentFields = [
                'input[name="ewm_logging_config[frontend_enabled]"]',
                'select[name="ewm_logging_config[level]"]',
                'input[name="ewm_logging_config[api_logging]"]',
                'input[name="ewm_logging_config[form_logging]"]',
                'input[name="ewm_logging_config[performance_logging]"]'
            ];
            
            dependentFields.forEach(selector => {
                $(selector).prop('disabled', !enabled);
                $(selector).closest('tr').toggleClass('disabled', !enabled);
            });
            
            // Toggle quick actions
            $('#ewm-test-logging, #ewm-clear-logs').prop('disabled', !enabled);
        }
        
        /**
         * Probar logging
         */
        testLogging() {
            const $button = $('#ewm-test-logging');
            const originalText = $button.text();
            
            $button.prop('disabled', true).text('Testing...');
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ewm_test_logging',
                    nonce: this.config.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.showMessage(this.config.strings.testSuccess, 'success');
                        this.loadRecentLogs();
                    } else {
                        this.showMessage(response.data || this.config.strings.error, 'error');
                    }
                },
                error: () => {
                    this.showMessage(this.config.strings.error, 'error');
                },
                complete: () => {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        }
        
        /**
         * Limpiar logs
         */
        clearLogs() {
            if (!confirm(this.config.strings.confirm)) {
                return;
            }
            
            const $button = $('#ewm-clear-logs');
            const originalText = $button.text();
            
            $button.prop('disabled', true).text('Clearing...');
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ewm_clear_logs',
                    nonce: this.config.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.showMessage(this.config.strings.clearSuccess, 'success');
                        this.loadRecentLogs();
                        this.updateLogStats();
                    } else {
                        this.showMessage(response.data || this.config.strings.error, 'error');
                    }
                },
                error: () => {
                    this.showMessage(this.config.strings.error, 'error');
                },
                complete: () => {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        }
        
        /**
         * Cargar logs recientes
         */
        loadRecentLogs() {
            const $container = $('#ewm-recent-logs');
            $container.html('<p>Loading...</p>');
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ewm_get_recent_logs',
                    nonce: this.config.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.renderRecentLogs(response.data.logs);
                    } else {
                        $container.html('<p>Error loading logs.</p>');
                    }
                },
                error: () => {
                    $container.html('<p>Error loading logs.</p>');
                }
            });
        }
        
        /**
         * Renderizar logs recientes
         */
        renderRecentLogs(logs) {
            const $container = $('#ewm-recent-logs');
            
            if (!logs || logs.length === 0) {
                $container.html('<p>No recent logs found.</p>');
                return;
            }
            
            let html = '';
            logs.forEach(log => {
                const parsed = this.parseLogLine(log);
                if (parsed) {
                    html += this.formatLogEntry(parsed);
                }
            });
            
            $container.html(html || '<p>No valid logs found.</p>');
        }
        
        /**
         * Parsear línea de log
         */
        parseLogLine(line) {
            // Formato: [2025-01-11 10:30:45] EWM-INFO: Message | Context: {...}
            const regex = /^\[([^\]]+)\]\s+EWM-(\w+):\s+(.+?)(?:\s+\|\s+Context:\s+(.+))?$/;
            const match = line.match(regex);
            
            if (!match) {
                return null;
            }
            
            return {
                timestamp: match[1],
                level: match[2].toLowerCase(),
                message: match[3],
                context: match[4] || null
            };
        }
        
        /**
         * Formatear entrada de log
         */
        formatLogEntry(parsed) {
            const levelClass = `level-${parsed.level}`;
            const levelIndicator = `<span class="level-indicator ${parsed.level}"></span>`;
            
            let html = `<div class="log-entry ${levelClass}">`;
            html += `<span class="log-timestamp">${parsed.timestamp}</span> `;
            html += `${levelIndicator}`;
            html += `<span class="log-level">${parsed.level}</span>: `;
            html += `<span class="log-message">${this.escapeHtml(parsed.message)}</span>`;
            
            if (parsed.context) {
                html += `<details style="margin-top: 5px;">`;
                html += `<summary style="cursor: pointer; color: #666;">Context</summary>`;
                html += `<pre style="margin: 5px 0; font-size: 11px; color: #333;">${this.escapeHtml(parsed.context)}</pre>`;
                html += `</details>`;
            }
            
            html += `</div>`;
            
            return html;
        }
        
        /**
         * Escapar HTML
         */
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        /**
         * Mostrar mensaje
         */
        showMessage(message, type = 'info') {
            // Remover mensajes existentes
            $('.ewm-message').remove();
            
            const $message = $(`<div class="ewm-message ${type}">${message}</div>`);
            // Insertar DENTRO de la columna principal para no afectar el layout flexbox
            $('.ewm-logging-main').prepend($message);
            
            // Auto-ocultar después de 5 segundos
            setTimeout(() => {
                $message.fadeOut(() => $message.remove());
            }, 5000);
        }
        
        /**
         * Actualizar estadísticas de logs
         */
        updateLogStats() {
            // Esta función se puede expandir para obtener estadísticas actualizadas
            // Por ahora, simplemente recarga la página para actualizar las estadísticas
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
        
        /**
         * Iniciar auto-refresh de logs
         */
        startAutoRefresh() {
            // Refrescar logs cada 30 segundos si la página está visible
            setInterval(() => {
                if (!document.hidden) {
                    this.loadRecentLogs();
                }
            }, 30000);
        }
        
        /**
         * Exportar logs (funcionalidad futura)
         */
        exportLogs() {
            // Implementar exportación de logs
            this.showMessage('Export functionality coming soon!', 'info');
        }
        
        /**
         * Filtrar logs por nivel (funcionalidad futura)
         */
        filterLogsByLevel(level) {
            const $entries = $('.log-entry');
            
            if (level === 'all') {
                $entries.show();
            } else {
                $entries.hide();
                $(`.log-entry.level-${level}`).show();
            }
        }
    }
    
    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        new EWMLoggingAdmin();
    });
    
})(jQuery, window, document);
