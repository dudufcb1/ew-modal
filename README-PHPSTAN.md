# PHPStan - Análisis Estático de Código

Este proyecto está configurado con PHPStan para análisis estático de código PHP.

## ¿Qué es PHPStan?

PHPStan es una herramienta de análisis estático que encuentra errores en tu código PHP sin ejecutarlo. Ayuda a detectar:

- Errores de tipos
- Funciones no definidas
- Variables no definidas
- Código muerto
- Y muchos otros problemas potenciales

## Configuración

### Archivos de configuración:

- `phpstan.neon` - Configuración principal de PHPStan
- `phpstan-baseline.neon` - Baseline con errores existentes ignorados
- `composer.json` - Scripts de Composer para ejecutar PHPStan

### Dependencias instaladas:

- `phpstan/phpstan` - Herramienta principal
- `php-stubs/wordpress-stubs` - Stubs de WordPress para reconocer funciones WP

## Comandos disponibles

### Ejecutar análisis completo
```bash
composer phpstan
```

### Regenerar baseline (ignorar errores existentes)
```bash
composer phpstan-baseline
```

### Limpiar cache de PHPStan
```bash
composer phpstan-clear
```

### Ejecutar directamente con vendor/bin
```bash
vendor/bin/phpstan analyse
vendor/bin/phpstan analyse --no-progress
vendor/bin/phpstan analyse --level=6
```

## Configuración actual

- **Nivel de análisis**: 5 (de 0 a 9, donde 9 es el más estricto)
- **Archivos analizados**: 
  - `includes/`
  - `admin/`
  - `ewm-modal-cta.php`
  - `add-caps.php`
  - `fix-capabilities.php`
- **Archivos excluidos**: assets, logs, docs, memory, etc.
- **Stubs incluidos**: WordPress stubs para reconocer funciones WP

## Flujo de trabajo recomendado

1. **Antes de hacer cambios**: Ejecuta `composer phpstan` para asegurar que no hay errores nuevos
2. **Después de hacer cambios**: Ejecuta `composer phpstan` para verificar que no introdujiste errores
3. **Si aparecen errores nuevos**: Corrígelos antes de hacer commit
4. **Si necesitas ignorar errores temporalmente**: Regenera el baseline con `composer phpstan-baseline`

## Integración con IDE

Puedes configurar tu IDE para ejecutar PHPStan automáticamente:

- **VS Code**: Instala la extensión "PHPStan"
- **PhpStorm**: Configurar PHPStan en Settings > PHP > Quality Tools

## Niveles de análisis

- **Nivel 0**: Errores básicos de sintaxis
- **Nivel 1**: Variables no definidas, llamadas a métodos no definidos
- **Nivel 2**: Métodos no definidos en todas las expresiones
- **Nivel 3**: Tipos de retorno, tipos de propiedades
- **Nivel 4**: Verificación básica de tipos de variables
- **Nivel 5**: Verificación de argumentos pasados a métodos (ACTUAL)
- **Nivel 6**: Verificación de tipos de variables faltantes
- **Nivel 7**: Verificación de tipos de variables parcialmente incorrectos
- **Nivel 8**: Verificación de llamadas a métodos y acceso a propiedades en tipos mixtos
- **Nivel 9**: Verificación estricta de tipos mixtos

## Próximos pasos

1. Revisar y corregir gradualmente los errores en el baseline
2. Considerar subir el nivel de análisis a 6 o 7
3. Agregar PHPStan a CI/CD pipeline
4. Configurar pre-commit hooks para ejecutar PHPStan automáticamente
