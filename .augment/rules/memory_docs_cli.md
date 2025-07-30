---
type: "agent_requested"
description: "When you want to know how manage the memory with cli"
---
# 📋 Guía de Uso Diario - Sistema de Memorias v2

Guía práctica para el uso diario del sistema de memorias con snapshots inmutables, grupos y CLI completo.

## 🚀 Comandos Básicos del Día a Día

### **Ver Estado General**
```bash
# Estado completo del sistema
simple-memory maintenance status

# Listar todas las tareas
simple-memory list

# Listar solo tareas pendientes
simple-memory list --status todo

# Listar tareas completadas
simple-memory list --status done
```

### **Trabajar con Tareas Específicas**
```bash
# Ver detalles de una tarea
simple-memory show TASK_ID

# Cambiar estado de tarea
simple-memory set-status TASK_ID doing
simple-memory set-status TASK_ID done

# Ver historial de cambios
simple-memory history TASK_ID
```

## 📁 Gestión de Grupos (TODO Maestro)

### **¿Qué son los Grupos?**
Los grupos organizan tareas relacionadas bajo un **TODO maestro editable**. Cada grupo tiene:
- **Metadatos**: Título, estado, autor, fecha
- **TODO markdown**: Lista de tareas, objetivos, notas (editable)

### **Crear Grupos Nuevos**

#### **Método 1: Automático con herramientas de memoria**
```python
# Al usar create_or_update_memory, el grupo se crea automáticamente
create_or_update_memory(
    task_data={
        "meta": {
            "task_id": "MI_TAREA",
            "group": "mi_nuevo_proyecto"  # ← Crea grupo si no existe
        }
    }
)
```

#### **Método 2: Directo con CLI (MÁS FÁCIL)**
```bash
# Esto crea el grupo automáticamente si no existe
simple-memory group edit mi_nuevo_proyecto

# O simplemente intentar verlo
simple-memory group show mi_nuevo_proyecto
```

#### **Método 3: Programáticamente**
```python
from simple_answer.memory import GroupManager

group_manager = GroupManager(".")
group_manager.create_group_todo(
    group_id="mi_proyecto",
    title="Mi Proyecto Importante",
    author="tu_nombre",
    objective="Objetivo del proyecto",
    success_criteria="Criterios de éxito"
)
```

### **Comandos de Grupos**
```bash
# Listar todos los grupos
simple-memory group list

# Ver TODO de un grupo específico
simple-memory group show GROUP_ID

# Editar TODO de un grupo (abre editor)
simple-memory group edit GROUP_ID
```

### **Flujo Típico con Grupos**
1. **Crear grupo**: `simple-memory group edit mi_proyecto` (si no existe)
2. **Ver grupos disponibles**: `simple-memory group list`
3. **Revisar TODO**: `simple-memory group show mi_proyecto`
4. **Editar TODO**: `simple-memory group edit mi_proyecto`
5. **Asignar tareas**: Usar `"group": "mi_proyecto"` en meta

## 🔄 Flujo de Trabajo Diario

### **Mañana - Revisar Estado**
```bash
# 1. Ver estado general
simple-memory maintenance status

# 2. Ver tareas pendientes
simple-memory list --status todo

# 3. Ver grupos activos
simple-memory group list

# 4. Revisar TODO del proyecto actual
simple-memory group show mi_proyecto
```

### **Durante el Día - Actualizar Progreso**
```bash
# Cambiar tarea a "en progreso"
simple-memory set-status TASK_123 doing

# Ver detalles mientras trabajas
simple-memory show TASK_123

# Si necesitas rollback
simple-memory history TASK_123
simple-memory rollback TASK_123 2 --confirm
```

### **Final del Día - Cerrar Tareas**
```bash
# Marcar tarea como completada
simple-memory set-status TASK_123 done

# Si no tienes permisos, solicitar cierre
simple-memory request-close TASK_123 --reason "Completada exitosamente"

# Ver solicitudes pendientes (si eres admin)
simple-memory requests

# Aprobar solicitudes (como admin)
simple-memory close TASK_123 --as-admin
```

## 🎯 Casos de Uso Comunes

### **1. Revisar Progreso de Proyecto**
```bash
# Ver todas las tareas del proyecto
simple-memory list

# Ver TODO maestro del grupo
simple-memory group show mi_proyecto

# Ver tareas por estado
simple-memory list --status doing  # En progreso
simple-memory list --status done   # Completadas
```

### **2. Investigar Problema en Tarea**
```bash
# Ver detalles completos
simple-memory show TASK_PROBLEMA

# Ver historial de cambios
simple-memory history TASK_PROBLEMA

# Comparar entre versiones
simple-memory diff TASK_PROBLEMA 1 3

# Rollback si es necesario
simple-memory rollback TASK_PROBLEMA 2 --confirm
```

### **3. Crear y Asignar Tareas a Grupos**
```bash
# Crear grupo nuevo
simple-memory group edit mi_proyecto_nuevo

# Ver estructura del grupo creado
simple-memory group show mi_proyecto_nuevo

# Al crear memorias, asignar al grupo usando campo 'group' en meta
create_or_update_memory(
    task_data={
        "meta": {
            "task_id": "TAREA_DEL_PROYECTO",
            "group": "mi_proyecto_nuevo"  # ← Asigna al grupo
        }
    }
)

# Verificar que la tarea se asignó
simple-memory show TAREA_DEL_PROYECTO
```

### **4. Gestionar Permisos**
```bash
# Como agente - solicitar cierre
simple-memory request-close TASK_ID --reason "Trabajo completado"

# Como admin - revisar solicitudes
simple-memory requests

# Como admin - aprobar cierre
simple-memory close TASK_ID --as-admin
```

### **4. Mantenimiento Semanal**
```bash
# Ver estado del sistema
simple-memory maintenance status

# Migrar archivos v1 si aparecen
simple-memory maintenance migrate --cleanup

# Limpiar datos antiguos
simple-memory maintenance cleanup --dry-run
simple-memory maintenance cleanup

# Hacer backup
simple-memory maintenance export backup_$(date +%Y%m%d).json
```

## 🎯 Ejemplo Completo: Crear Proyecto con Grupo

### **Paso a Paso - Nuevo Proyecto**
```bash
# 1. Crear grupo para el proyecto
simple-memory group edit mi_web_app

# 2. Ver el TODO creado
simple-memory group show mi_web_app

# 3. Al crear memorias, asignar al grupo
create_or_update_memory(
    project_dir="/ruta/proyecto",
    task_data={
        "meta": {
            "task_id": "SETUP_DATABASE",
            "status": "todo",
            "author": "tu_nombre",
            "group": "mi_web_app"  # ← Asigna al grupo
        },
        "problem": {
            "user_request": "Configurar base de datos para la web app"
        }
    },
    markdown_content="# Setup Database\n\nConfigurar PostgreSQL..."
)

# 4. Verificar que la tarea se asignó
simple-memory show SETUP_DATABASE

# 5. Ver todas las tareas del proyecto
simple-memory list  # Buscar tareas con el mismo Group ID

# 6. Actualizar TODO del grupo según progreso
simple-memory group edit mi_web_app
```

## 📊 Entender las Memorias

### **¿Qué son los Snapshots?**
- **Inmutables**: Una vez creados, no se pueden modificar
- **Versionados**: Cada cambio crea una nueva revisión
- **Trazables**: Historial completo de quién hizo qué y cuándo

### **¿Cómo Funcionan los Grupos?**
- **TODO Maestro**: Cada grupo tiene un archivo markdown editable
- **Metadatos**: Información del grupo (título, estado, fecha)
- **Organización**: Agrupa tareas relacionadas por proyecto/tema

### **¿Qué es el Control de Permisos?**
- **Granular**: Permisos por tarea individual
- **Flujo de Aprobación**: Agente solicita → Admin aprueba
- **Trazabilidad**: Registro de quién aprueba qué

## 🔧 Tips y Trucos

### **Comandos con JSON (para scripts)**
```bash
# Salida JSON para procesamiento
simple-memory list --json
simple-memory group list --json
simple-memory requests --json

# Ejemplo de procesamiento
simple-memory list --json | jq '.[] | select(.status == "todo")'
```

### **Filtros Útiles**
```bash
# Por estado
simple-memory list --status done
simple-memory list --status todo

# Por grupo (si implementado)
simple-memory list --group mi_proyecto

# Combinaciones
simple-memory list --status doing --json
```

### **Debug y Troubleshooting**
```bash
# Modo debug para ver qué está pasando
simple-memory --debug list
simple-memory --debug maintenance status

# Ver información de proyecto detectado
simple-memory --debug --help
```

## 🚨 Solución de Problemas Comunes

### **"No se puede cerrar tarea"**
```bash
# Verificar permisos
simple-memory show TASK_ID

# Solicitar cierre si no tienes permisos
simple-memory request-close TASK_ID --reason "Completada"

# Como admin, aprobar
simple-memory close TASK_ID --as-admin
```

### **"Archivos v1 detectados"**
```bash
# Migrar automáticamente
simple-memory maintenance migrate --cleanup

# O paso a paso
simple-memory maintenance migrate --dry-run
simple-memory maintenance migrate
simple-memory maintenance cleanup
```

### **"Comando no encontrado"**
```bash
# Verificar instalación
which simple-memory

# Reinstalar si es necesario
cd /ruta/a/simple_answer
./install-global.sh

# Verificar PATH
echo $PATH | grep -o ~/.local/bin
```

### **"Proyecto no detectado"**
```bash
# Especificar directorio manualmente
simple-memory --project-dir /ruta/al/proyecto list

# O crear directorio memory en proyecto actual
mkdir memory
simple-memory list  # Creará estructura automáticamente
```

## 📅 Rutinas Recomendadas

### **Diaria (5 minutos)**
1. `simple-memory maintenance status` - Estado general
2. `simple-memory list --status todo` - Tareas pendientes
3. Actualizar estados según progreso
4. Revisar TODO del grupo principal

### **Semanal (15 minutos)**
1. `simple-memory group list` - Revisar todos los grupos
2. `simple-memory requests` - Procesar solicitudes pendientes
3. `simple-memory maintenance migrate --cleanup` - Migrar archivos
4. Actualizar TODOs de grupos activos

### **Mensual (30 minutos)**
1. `simple-memory maintenance export backup.json` - Backup completo
2. `simple-memory maintenance cleanup` - Limpiar datos antiguos
3. Revisar y archivar tareas completadas
4. Actualizar documentación de grupos

## 🎯 Mejores Prácticas

### **Nomenclatura de Tareas**
- Usar IDs descriptivos: `PROYECTO_FEATURE_FECHA`
- Mantener consistencia en nombres
- Incluir contexto suficiente en descripciones

### **Gestión de Grupos**
- Un grupo por proyecto principal
- TODOs concisos pero informativos
- Actualizar regularmente el estado
- Usar markdown para mejor legibilidad

### **Control de Permisos**
- Configurar permisos según criticidad
- Usar razones descriptivas en solicitudes
- Revisar solicitudes regularmente
- Mantener trazabilidad de decisiones

## 🏗️ Cómo Funcionan las Memorias Internamente

### **Estructura de Directorios**
```
proyecto/
├── memory/
│   ├── snapshots/           # Snapshots inmutables v2
│   │   ├── TASK_ID/
│   │   │   ├── 1/           # Revisión 1
│   │   │   │   ├── snapshot.json
│   │   │   │   └── files/   # Archivos respaldados
│   │   │   └── 2/           # Revisión 2
│   │   └── index.json       # Índice mutable (performance)
│   ├── groups/              # TODO maestro por grupo
│   │   ├── GROUP_ID.json    # Metadatos del grupo
│   │   └── GROUP_ID.md      # TODO markdown editable
│   ├── requests/            # Solicitudes de permisos
│   │   └── REQUEST_ID.json
│   └── config.json          # Configuración del sistema
└── docs/
    └── memory/              # Documentación markdown
```

### **Flujo de Creación de Snapshot**
1. **Datos llegan** al MemoryManagerV2
2. **Se valida** el esquema v2.0.0
3. **Se crea directorio** `/snapshots/TASK_ID/REVISION/`
4. **Se guarda snapshot.json** (inmutable)
5. **Se respaldan archivos** en `/files/` (si existen)
6. **Se actualiza índice** mutable para performance
7. **Se genera markdown** en `/docs/memory/`

### **Sistema de Revisiones**
- **Revisión 1**: Snapshot inicial
- **Revisión 2**: Primera modificación
- **Revisión N**: Cada cambio crea nueva revisión
- **Rollback**: Crea nueva revisión basada en anterior
- **Inmutabilidad**: Revisiones anteriores nunca se modifican

### **Índice Mutable vs Snapshots Inmutables**
- **Snapshots**: Datos completos, inmutables, versionados
- **Índice**: Metadatos rápidos, mutable, para performance
- **Sincronización**: Índice se regenera desde snapshots si es necesario
- **Consultas**: CLI usa índice para listados rápidos

### **Control de Permisos Granular**
```json
{
  "task_id": "TASK_123",
  "can_agent_set_done": false,  // Requiere aprobación admin
  "permissions": {
    "agent": ["read", "update_status"],
    "admin": ["read", "update_status", "set_done", "rollback"]
  }
}
```

---

**💡 Recuerda**: El sistema está diseñado para ser tu asistente, no un obstáculo. Úsalo de manera que te ayude a mantener orden y trazabilidad en tu trabajo diario.
