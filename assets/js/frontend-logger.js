/**
 * EWM Frontend Logger - Sistema de logging para JavaScript
 * 
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

(function(window, document) {
    'use strict';
    
    /**
     * Clase principal para logging frontend
     */
    class EWMFrontendLogger {
        constructor(config = {}) {
            this.config = {
                enabled: config.enabled || false,
                level: config.level || 'info',
                ajaxUrl: config.ajaxUrl || '',
                nonce: config.nonce || '',
                sendToServer: config.sendToServer !== false,
                bufferSize: config.bufferSize || 10,
                flushInterval: config.flushInterval || 5000
            };
            
            this.levels = {
                debug: 0,
                info: 1,
                warning: 2,
                error: 3
            };
            
            this.buffer = [];
            this.originalConsole = {};
            
            this.init();
        }
        
        /**
         * Inicializar el logger
         */
        init() {
            if (!this.config.enabled) {
                return;
            }
            
            this.backupOriginalConsole();
            this.wrapConsoleMethods();
            this.startBufferFlush();
            this.setupErrorHandling();
            
            this.log('info', 'EWM Frontend Logger initialized', {
                level: this.config.level,
                sendToServer: this.config.sendToServer
            });
        }
        
        /**
         * Respaldar métodos originales de console
         */
        backupOriginalConsole() {
            this.originalConsole = {
                // log: console.log.bind(console),
                info: console.info.bind(console),
                warn: console.warn.bind(console),
                error: console.error.bind(console),
                debug: console.debug.bind(console)
            };
        }
        
        /**
         * Envolver métodos de console para interceptar logs
         */
        wrapConsoleMethods() {
            const self = this;
            
            // console.log = function(...args) {
            //     self.originalConsole.log(...args);
            //     self.log('info', self.formatConsoleArgs(args), { source: 'console.log' });
            // };
            
            console.info = function(...args) {
                self.originalConsole.info(...args);
                self.log('info', self.formatConsoleArgs(args), { source: 'console.info' });
            };
            
            console.warn = function(...args) {
                self.originalConsole.warn(...args);
                self.log('warning', self.formatConsoleArgs(args), { source: 'console.warn' });
            };
            
            console.error = function(...args) {
                self.originalConsole.error(...args);
                self.log('error', self.formatConsoleArgs(args), { source: 'console.error' });
            };
            
            console.debug = function(...args) {
                self.originalConsole.debug(...args);
                self.log('debug', self.formatConsoleArgs(args), { source: 'console.debug' });
            };
        }
        
        /**
         * Formatear argumentos de console para logging
         */
        formatConsoleArgs(args) {
            return args.map(arg => {
                if (typeof arg === 'object') {
                    try {
                        return JSON.stringify(arg, null, 2);
                    } catch (e) {
                        return '[Object]';
                    }
                }
                return String(arg);
            }).join(' ');
        }
        
        /**
         * Configurar manejo de errores globales
         */
        setupErrorHandling() {
            const self = this;
            
            // Errores JavaScript no capturados
            window.addEventListener('error', function(event) {
                self.log('error', 'Uncaught JavaScript error', {
                    message: event.message,
                    filename: event.filename,
                    lineno: event.lineno,
                    colno: event.colno,
                    stack: event.error ? event.error.stack : null,
                    source: 'window.error'
                });
            });
            
            // Promesas rechazadas no manejadas
            window.addEventListener('unhandledrejection', function(event) {
                self.log('error', 'Unhandled promise rejection', {
                    reason: event.reason,
                    promise: event.promise,
                    source: 'unhandledrejection'
                });
            });
        }
        
        /**
         * Verificar si un nivel debe ser loggeado
         */
        shouldLog(level) {
            if (!this.config.enabled) {
                return false;
            }
            
            const currentLevel = this.levels[this.config.level] || 1;
            const messageLevel = this.levels[level] || 1;
            
            return messageLevel >= currentLevel;
        }
        
        /**
         * Método principal de logging
         */
        log(level, message, context = {}) {
            if (!this.shouldLog(level)) {
                return;
            }
            
            const logEntry = {
                timestamp: new Date().toISOString(),
                level: level,
                message: message,
                context: {
                    ...context,
                    url: window.location.href,
                    userAgent: navigator.userAgent,
                    viewport: {
                        width: window.innerWidth,
                        height: window.innerHeight
                    }
                }
            };
            
            // Añadir al buffer para envío al servidor
            if (this.config.sendToServer) {
                this.buffer.push(logEntry);
                
                // Flush inmediato para errores críticos
                if (level === 'error') {
                    this.flushBuffer();
                }
            }
            
            // Log local para desarrollo
            this.logToLocalStorage(logEntry);
        }
        
        /**
         * Guardar log en localStorage para debugging local
         */
        logToLocalStorage(logEntry) {
            try {
                const storageKey = 'ewm_frontend_logs';
                let logs = JSON.parse(localStorage.getItem(storageKey) || '[]');
                
                logs.push(logEntry);
                
                // Mantener solo los últimos 50 logs
                if (logs.length > 50) {
                    logs = logs.slice(-50);
                }
                
                localStorage.setItem(storageKey, JSON.stringify(logs));
            } catch (e) {
                // Ignorar errores de localStorage
            }
        }
        
        /**
         * Iniciar flush automático del buffer
         */
        startBufferFlush() {
            setInterval(() => {
                this.flushBuffer();
            }, this.config.flushInterval);
        }
        
        /**
         * Enviar buffer de logs al servidor
         */
        flushBuffer() {
            if (this.buffer.length === 0 || !this.config.ajaxUrl) {
                return;
            }
            
            const logsToSend = [...this.buffer];
            this.buffer = [];
            
            const formData = new FormData();
            formData.append('action', 'ewm_log_frontend');
            formData.append('nonce', this.config.nonce);
            formData.append('logs', JSON.stringify(logsToSend));
            formData.append('url', window.location.href);
            
            fetch(this.config.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            }).catch(error => {
                // Silenciosamente manejar errores de envío
                // para evitar loops infinitos
                this.originalConsole.error('Failed to send logs to server:', error);
            });
        }
        
        /**
         * Métodos de conveniencia
         */
        debug(message, context = {}) {
            this.log('debug', message, context);
        }
        
        info(message, context = {}) {
            this.log('info', message, context);
        }
        
        warning(message, context = {}) {
            this.log('warning', message, context);
        }
        
        error(message, context = {}) {
            this.log('error', message, context);
        }
        
        /**
         * Logging específico para modales
         */
        logModalEvent(eventType, modalId, data = {}) {
            this.info(`Modal Event: ${eventType}`, {
                modalId: modalId,
                eventType: eventType,
                data: data,
                source: 'modal'
            });
        }
        
        /**
         * Logging específico para formularios
         */
        logFormEvent(eventType, formData = {}) {
            this.info(`Form Event: ${eventType}`, {
                eventType: eventType,
                formData: formData,
                source: 'form'
            });
        }
        
        /**
         * Logging de performance
         */
        logPerformance(metric, value, context = {}) {
            this.info(`Performance: ${metric}`, {
                metric: metric,
                value: value,
                context: context,
                source: 'performance'
            });
        }
        
        /**
         * Obtener logs del localStorage
         */
        getLocalLogs() {
            try {
                return JSON.parse(localStorage.getItem('ewm_frontend_logs') || '[]');
            } catch (e) {
                return [];
            }
        }
        
        /**
         * Limpiar logs del localStorage
         */
        clearLocalLogs() {
            try {
                localStorage.removeItem('ewm_frontend_logs');
            } catch (e) {
                // Ignorar errores
            }
        }
        
        /**
         * Restaurar console original
         */
        restore() {
            if (this.originalConsole.log) {
                // console.log = this.originalConsole.log;
                console.info = this.originalConsole.info;
                console.warn = this.originalConsole.warn;
                console.error = this.originalConsole.error;
                console.debug = this.originalConsole.debug;
            }
        }
        
        /**
         * Destructor
         */
        destroy() {
            this.restore();
            this.flushBuffer();
            this.config.enabled = false;
        }
    }
    
    // Inicializar logger cuando esté disponible la configuración
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof ewmLogger !== 'undefined') {
            window.EWMLogger = new EWMFrontendLogger(ewmLogger);
            
            // Exponer métodos globales para facilidad de uso
            window.ewmLog = {
                debug: (msg, ctx) => window.EWMLogger.debug(msg, ctx),
                info: (msg, ctx) => window.EWMLogger.info(msg, ctx),
                warning: (msg, ctx) => window.EWMLogger.warning(msg, ctx),
                error: (msg, ctx) => window.EWMLogger.error(msg, ctx),
                modal: (event, id, data) => window.EWMLogger.logModalEvent(event, id, data),
                form: (event, data) => window.EWMLogger.logFormEvent(event, data),
                performance: (metric, value, ctx) => window.EWMLogger.logPerformance(metric, value, ctx)
            };
        }
    });
    
})(window, document);
