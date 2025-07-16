INFORMACIÓN RELEVANTE DE WORDPRES BLOCK THEMES (gutenberg).

Existe un bug cuando se trabaja con el paquete de @wordpress

Si tu añades un style.css a tu carpeta del bloque, @wordpress al transpilar creará lo siguiente:

index-style.css (o similar) entonces si tu en el block.json has creido que debes referenciar style.css como sería lo lógico al tenerlo en tu src/bloque esto no funcionará porque no se encontrará el archivo css "index-style.css"

Solucion:
siempre creamos main.css para el index.js y dentro de index.js importamos main.css y en blocktheme elegimos "editorStyle": "file:./index.css", y en este caso si funcionará ya que en lugar de main.css al estar importado en index.js el le crea un index.css y ya no es necesario "adivinar o predecir o calcular" el nombre que se generará.

Es importante también mencionar que para efectos de edición, se debe envolver con un <div> adicional al código que se renderiza en el editor para evitar colisiones en nuestro código y que no se apliquen por estar propagando estilos en el mismo div y los previews se vean correctamente.

Wordpress_coding_standars.md

# Guía de Seguridad de Datos, Interacción con la Base de Datos y Auditoría de Código en WordPress

La seguridad es un pilar fundamental en el desarrollo de WordPress. Proteger la información y las interacciones con la base de datos es crucial para prevenir vulnerabilidades como la inyección SQL y garantizar la integridad de los datos. Esta guía consolida información sobre cómo interactuar de forma segura con la base de datos usando la clase global `$wpdb`, sanear y validar datos de diversas fuentes, y auditar el código utilizando herramientas como PHP_CodeSniffer (PHPCS).

## 1. Interacción Segura con la Base de Datos: La Clase `wpdb`

La clase `wpdb` es la interfaz principal de WordPress para interactuar con la base de datos MySQL. Proporciona métodos seguros para realizar operaciones CRUD (Crear, Leer, Actualizar, Eliminar) y ejecutar consultas generales. Es esencial utilizar sus métodos de forma adecuada para evitar vulnerabilidades.

### Acceso al Objeto Global `$wpdb`

WordPress facilita el acceso a la base de datos a través del objeto global `$wpdb`. Para usarlo, decláralo como global en tus funciones o archivos:

```php
global $wpdb;
// Ahora puedes usar $wpdb para interactuar con la base de datos
```

También puedes usar `$GLOBALS['wpdb']`.

### Protección de Consultas contra Ataques de Inyección SQL

La prevención de la inyección SQL es la preocupación principal al interactuar con la base de datos. **Siempre debes escapar o sanear todos los valores no confiables** (provenientes del usuario, sistemas externos, etc.) antes de incluirlos en una consulta SQL.

#### El Método `prepare()`

El método `$wpdb->prepare()` es la forma recomendada y más segura de escapar datos en consultas SQL. Utiliza una sintaxis similar a `sprintf()`.

**Sintaxis General:**

```php
$sql = $wpdb->prepare( 'consulta_con_marcadores' , valor1 [, valor2, ... ] );
// O pasando argumentos como un array (similar a vsprintf):
$sql = $wpdb->prepare( 'consulta_con_marcadores', array( valor1, valor2, ... ) );
```

*   **`query`**: La cadena de consulta SQL que contiene **marcadores de posición** (placeholders).
*   **`args`**: Variables a sustituir en los marcadores. Pueden ser argumentos individuales o un array.

**Marcadores de Posición:**

*   `%s`: para cadenas (strings).
*   `%d`: para enteros (integers).
*   `%f`: para números de punto flotante (floats).
*   `%i`: para identificadores (nombres de tablas/campos). *Requiere WP 6.2+* y verificación con `wpdb::has_cap('identifier_placeholders')`.

**Reglas Cruciales para `prepare()`:**

*   Los marcadores `%s`, `%d`, `%f` **NO deben llevar comillas** en la cadena `$query`. `prepare()` añade las comillas necesarias para `%s` y escapa los valores.
*   El carácter literal `%` dentro de la consulta (como en los comodines `LIKE` o en cadenas) debe escaparse con `%%` (doble porcentaje).
*   Los valores pasados a `prepare()` **deben ser \"raw\"** (sin escapar previamente).

**Ejemplo de `prepare()` (Insertar datos):**

```php
global $wpdb;
$metakey = 'Funny Phrases';
$metavalue = \"WordPress' database interface is like Sunday Morning: Easy.\";
$wpdb->query(
    $wpdb->prepare(
        \"INSERT INTO $wpdb->postmeta ( post_id, meta_key, meta_value ) VALUES ( %d, %s, %s )\",
        10, // %d
        $metakey, // %s
        $metavalue // %s
    )
);
```

#### Escape de Búsquedas `LIKE` con `esc_like()`

Cuando se utiliza un comodín (`%` o `_`) en una cláusula `LIKE`, estos caracteres deben escaparse si provienen de una fuente no confiable para que se interpreten literalmente y no como comodines. `$wpdb->esc_like()` se usa para esto.

**Ejemplo de `esc_like()`:**

```php
global $wpdb;
$user_search = 'user%'; // Entrada de usuario, podría contener % o _
$wild = '%';
// Escapar la entrada del usuario y añadir comodines para la búsqueda
$like_pattern = $wild . $wpdb->esc_like( $user_search ) . $wild;
$sql = $wpdb->prepare( \"SELECT ID, user_login FROM $wpdb->users WHERE user_login LIKE %s\", $like_pattern );
$users = $wpdb->get_results( $sql );
```

**Importante**: `$wpdb->esc_like()` *no* produce una cadena segura para SQL por sí sola; debe ser usada *antes* de `wpdb::prepare()` o `esc_sql()`.

#### Escape de Identificadores con `quote_identifier()`

Para escapar nombres de tablas o campos de bases de datos MySQL, usa `$wpdb->quote_identifier()`. Desde WordPress 6.2, `prepare()` soporta el marcador `%i` para identificadores, que es la forma preferida. Sin embargo, `quote_identifier()` sigue siendo útil para consultas DDL u otros casos donde `%i` no es viable.

**Ejemplo de `quote_identifier()` (para DDL):**

```php
global $wpdb;
// El nombre de la tabla puede provenir de una fuente no confiable (aunque menos común)
$table_name = 'mi_tabla_personalizada';
$safe_table_name = $wpdb->quote_identifier( $table_name );
// DDL a menudo requiere interpolación directa del nombre de la tabla, justificada con phpcs:ignore
$wpdb->query( \"ALTER TABLE {$safe_table_name} ADD COLUMN nueva_columna VARCHAR(255)\" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Identifier safely escaped with quote_identifier()
```

### Métodos Comunes de Consulta (`SELECT`)

`wpdb` proporciona varios métodos para recuperar datos de la base de datos de forma segura (siempre que la consulta se prepare correctamente):

*   **`get_var( string|null $query = null, int $x = 0, int $y = 0 ): string|null`**: Recupera un único valor escalar (ej. un conteo, una suma).
    *   `$query`: Consulta SQL (usa `prepare()` si incluye variables). Si es `null`, usa el resultado de la consulta anterior.
    *   `$x`: Índice de columna (0-basado).
    *   `$y`: Índice de fila (0-basado).

*   **`get_row( string|null $query = null, string $output = OBJECT, int $y = 0 ): object|array|null|void`**: Recupera una única fila.
    *   `$query`: Consulta SQL.
    *   `$output`: Formato de retorno (`OBJECT`, `ARRAY_A`, `ARRAY_N`).
    *   `$y`: Índice de fila.

*   **`get_col( string|null $query = null, int $x = 0 ): array`**: Recupera una única columna como un array unidimensional.
    *   `$query`: Consulta SQL.
    *   `$x`: Índice de columna.

*   **`get_results( string|null $query = null, string $output = OBJECT ): array|object|null`**: Recupera múltiples filas como un array de objetos o arrays.
    *   `$query`: Consulta SQL.
    *   `$output`: Formato de retorno (`OBJECT`, `OBJECT_K`, `ARRAY_A`, `ARRAY_N`). `OBJECT_K` usa la primera columna como clave.

**Ejemplo: Obtener IDs y títulos de borradores del usuario 5 (usando `get_results`)**

```php
global $wpdb;
$user_id = 5;
$fivesdrafts = $wpdb->get_results(
    $wpdb->prepare(
        \"SELECT ID, post_title FROM $wpdb->posts WHERE post_status = %s AND post_author = %d\",
        'draft',
        $user_id
    )
);
// $fivesdrafts es un array de objetos (por defecto)
foreach ( $fivesdrafts as $draft ) {
    echo $draft->post_title;
}
```

### Métodos Comunes de Manipulación de Datos (CRUD)

Para INSERT, UPDATE, REPLACE y DELETE, `wpdb` ofrece métodos específicos que manejan el escapado de datos automáticamente, *siempre que uses los arrays `$data` y `$where` y especifiques los formatos*.

*   **`insert( string $table, array $data, string[]|string $format = null ): int|false`**: Inserta una fila.
    *   `$table`: Nombre de la tabla.
    *   `$data`: Array asociativo `columna => valor`. Los valores deben ser \"raw\". `null` inserta `NULL`.
    *   `$format`: Formatos (`%s`, `%d`, `%f`) para los valores en `$data`. Array o cadena única. Si se omite, se intenta determinar o se usa `%s`.
    *   Retorna: Filas insertadas (siempre 1 si tiene éxito) o `false` en error. El último ID insertado está en `$wpdb->insert_id`.

*   **`update( string $table, array $data, array $where, string[]|string $format = null, string[]|string $where_format = null ): int|false`**: Actualiza filas.
    *   `$table`: Nombre de la tabla.
    *   `$data`: Array asociativo `columna => nuevo_valor`. Valores \"raw\".
    *   `$where`: Array asociativo `columna => valor_where`. Valores \"raw\". Un `null` crea `IS NULL`. Múltiples pares se unen con `AND`.
    *   `$format`: Formatos para `$data`.
    *   `$where_format`: Formatos para `$where`.
    *   Retorna: Filas actualizadas o `false` en error. 0 si no se encontraron filas o no hubo cambios.

*   **`delete( string $table, array $where, string[]|string $where_format = null ): int|false`**: Elimina filas.
    *   `$table`: Nombre de la tabla.
    *   `$where`: Array asociativo `columna => valor_where`. Valores \"raw\".
    *   `$where_format`: Formatos para `$where`.
    *   Retorna: Filas eliminadas o `false` en error.

*   **`replace( string $table, array $data, string[]|string $format = null ): int|false`**: Reemplaza o inserta una fila basándose en clave primaria/única.
    *   `$table`, `$data`, `$format`: Igual que `insert`.
    *   Retorna: Filas afectadas (eliminadas + insertadas) o `false`. Puede ser > 1 si elimina una fila antes de insertar.

**Ejemplo: Insertar datos (usando `insert`)**

```php
global $wpdb;
$wpdb->insert(
    $wpdb->prefix . 'my_custom_table',
    array(
        'name' => 'John Doe',
        'age'  => 30,
        'city' => 'New York',
    ),
    array(
        '%s', // Formato para 'name' (string)
        '%d', // Formato para 'age' (integer)
        '%s', // Formato para 'city' (string)
    )
);
$last_id = $wpdb->insert_id; // Obtiene el ID de la fila insertada
```

**Ejemplo: Actualizar datos (usando `update`)**

```php
global $wpdb;
$wpdb->update(
    $wpdb->prefix . 'my_custom_table',
    array(
        'age'  => 31, // Nuevo valor para age
        'city' => 'Los Angeles', // Nuevo valor para city
    ),
    array( 'ID' => 123 ), // Cláusula WHERE: donde ID es 123
    array(
        '%d', // Formato para 'age'
        '%s', // Formato para 'city'
    ),
    array( '%d' ) // Formato para 'ID' en WHERE
);
```

### Ejecución de Consultas Generales con `query()`

El método `$wpdb->query()` ejecuta cualquier consulta SQL, pero **NO** maneja el escapado de datos por sí solo.

**Sintaxis:** `query( string $query ): int|bool|false`

*   `$query`: La consulta SQL. **Si incluye variables, DEBE ser preparada con `$wpdb->prepare()` antes de pasarla a `query()`.**
*   Retorna: Número de filas afectadas/seleccionadas (int), `true` para DDL (`CREATE`, `ALTER`, etc.), o `false` en caso de error. Usa `=== false` para verificar errores.

**Ejemplo: Eliminar una meta clave de post (usando `query` y `prepare`)**

```php
global $wpdb;
$post_id = 13;
$meta_key = 'gargle';
$wpdb->query(
    $wpdb->prepare(
        \"DELETE FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s\",
        $post_id,
        $meta_key
    )
);
```

### Estructuras de Tablas de WordPress

`wpdb` proporciona propiedades para acceder fácilmente a los nombres de las tablas estándar, con el prefijo correcto incluido (especialmente importante en Multisite).

**Propiedades de Tablas Comunes:**

*   `$prefix`: Prefijo de tabla del sitio actual.
*   `$base_prefix`: Prefijo base (sin ID de blog en Multisite).
*   `$comments`, `$commentmeta`, `$links`, `$options`, `$posts`, `$postmeta`, `$term_taxonomy`, `$term_relationships`, `$termmeta`, `$terms`, `$users`, `$usermeta`.

**Propiedades de Tablas Multisite:**

*   `$blogid`, `$siteid`, `$blogs`, `$blog_versions`, `$blogmeta`, `$registration_log`, `$signups`, `$site`, `$sitecategories`, `$sitemeta`.

**Método `tables()`:**

`wpdb::tables( string $scope = 'all', bool $prefix = true, int $blog_id = 0 ): string[]` devuelve un array de nombres de tablas basados en el alcance (`all`, `global`, `ms_global`, `blog`, `old`).

### Manejo de Errores y Depuración de `wpdb`

*   **Propiedades de Depuración:**
    *   `$last_error`: Último mensaje de error de la base de datos.
    *   `$num_queries`: Contador de consultas ejecutadas.
    *   `$queries`: Array de consultas ejecutadas si `SAVEQUERIES` es `TRUE`.
    *   `$last_query`: Última consulta ejecutada.
    *   `$num_rows`: Filas devueltas por la última `SELECT`.
    *   `$rows_affected`: Filas afectadas por la última DML.
    *   `$insert_id`: Último ID `AUTO_INCREMENT` generado.
*   **Métodos de Control de Errores:**
    *   `show_errors( bool $show = true ): bool`: Habilita/deshabilita visualización de errores.
    *   `hide_errors(): bool`: Deshabilita la visualización.
    *   `suppress_errors( bool $suppress = true ): int|false`: Habilita/deshabilita la supresión interna de errores.
    *   `print_error( string $str = '' ): void|false`: Imprime un error SQL/DB.
    *   `bail( string $message, string $error_code = '500' ): void|false`: Muestra un mensaje de error y termina la ejecución (si los errores se muestran).
*   **Métodos de Utilidad/Depuración:**
    *   `check_connection( bool $allow_bail = true ): bool|void`: Verifica y reintenta la conexión.
    *   `flush()`: Borra la caché de resultados (`$last_result`, `$col_info`, etc.).
    *   `timer_start()`, `timer_stop()`: Para medir tiempos de consulta (si `SAVEQUERIES` es `TRUE`).
    *   `get_col_info()`: Obtiene metadatos de las columnas del último resultado.

### Glosario Detallado de Métodos y Propiedades de `wpdb`

(Este glosario se basa en la información detallada de `Sanitizacion_WordPress.md`)

**Propiedades Comunes:** `$base_prefix`, `$blogid`, `$col_info`, `$insert_id`, `$last_error`, `$last_query`, `$last_result`, `$num_queries`, `$num_rows`, `$prefix`, `$queries`, `$rows_affected`, `$show_errors`, `$suppress_errors`, `$table_charset`, `$col_meta`, `$charset`, `$collate`, `$ready`, `$dbuser`, `$dbpassword`, `$dbname`, `$dbhost`, `$dbh`, `$time_start`.
**Propiedades de Tablas:** `$comments`, `$commentmeta`, `$links`, `$options`, `$posts`, `$postmeta`, `$term_taxonomy`, `$term_relationships`, `$termmeta`, `$terms`, `$users`, `$usermeta`, `$blogs`, `$blog_versions`, `$blogmeta`, `$registration_log`, `$signups`, `$site`, `$sitecategories`, `$sitemeta`.

**Métodos Principales y CRUD:**
*   `query()`: Ejecuta cualquier consulta SQL (¡requiere `prepare`!).
*   `get_var()`: Obtiene un valor único.
*   `get_row()`: Obtiene una fila.
*   `get_col()`: Obtiene una columna (array).
*   `get_results()`: Obtiene múltiples filas.
*   `insert()`: Inserta una fila (escapa datos).
*   `update()`: Actualiza filas (escapa datos).
*   `delete()`: Elimina filas (escapa datos).
*   `replace()`: Reemplaza o inserta (escapa datos).

**Métodos de Sanitización/Escape:**
*   `prepare()`: Prepara consultas SQL con marcadores de posición.
*   `esc_like()`: Escapa caracteres `%` y `_` para cláusulas `LIKE` (usar antes de `prepare`/`esc_sql`).
*   `quote_identifier()`: Cita identificadores MySQL (nombres de tabla/campo).
*   `_real_escape()`: Escape real usando la extensión de base de datos subyacente.
*   `escape_by_ref()`: Escapa una cadena por referencia.
*   `_escape()`: Escapa datos (puede manejar arrays).
*   `strip_invalid_text_from_query()`, `strip_invalid_text_for_column()`, `strip_invalid_text()`: Eliminan caracteres inválidos basados en charset.
*   `add_placeholder_escape()`, `remove_placeholder_escape()`, `placeholder_escape()`: Utilidades internas para `prepare`.

**Métodos de Conexión y Configuración:**
*   `__construct()`: Constructor, establece conexión.
*   `db_connect()`: Conecta y selecciona DB.
*   `close()`: Cierra conexión.
*   `select()`: Selecciona una DB.
*   `set_prefix()`: Establece prefijo de tabla.
*   `get_blog_prefix()`: Obtiene prefijo de blog (Multisite).
*   `set_blog_id()`: Establece ID de blog (Multisite).
*   `init_charset()`, `set_charset()`: Configuran el juego de caracteres de la conexión.
*   `set_sql_mode()`: Configura el modo SQL.
*   `parse_db_host()`: Analiza la cadena `DB_HOST`.

**Métodos de Error/Depuración:**
*   `show_errors()`, `hide_errors()`, `suppress_errors()`: Controlan la visualización/supresión de errores.
*   `print_error()`: Imprime el último error.
*   `bail()`: Muestra error fatal y detiene ejecución.
*   `timer_start()`, `timer_stop()`, `log_query()`: Para profiling de consultas.

**Métodos de Información DB/Columna:**
*   `db_version()`, `db_server_info()`: Versión del servidor DB.
*   `check_database_version()`: Verifica versión mínima requerida.
*   `tables()`: Devuelve array de nombres de tablas.
*   `get_table_from_query()`: Extrae nombre de tabla de consulta.
*   `get_table_charset()`, `get_col_charset()`: Obtienen charset de tabla/columna.
*   `get_col_length()`: Obtiene longitud de columna.
*   `get_col_info()`, `load_col_info()`: Obtienen metadatos de columnas.
*   `get_charset_collate()`, `determine_charset()`: Configuración de charset/collation.
*   `has_cap()`: Verifica soporte de característica de DB.
*   `check_safe_collation()`: Verifica seguridad de collation.
*   `check_ascii()`: Verifica si una cadena es ASCII.
*   `get_caller()`: Obtiene stack de llamadas.
*   `flush()`: Limpia resultados/caché.

**Métodos Auxiliares Internos:**
*   `_insert_replace_helper()`, `process_fields()`, `process_field_formats()`, `process_field_charsets()`, `process_field_lengths()`: Usados internamente por métodos CRUD.

**Métodos Mágicos:** `__get()`, `__set()`, `__isset()`, `__unset()`: Para compatibilidad con acceso antiguo a propiedades protegidas/privadas.

**Funciones Globales que Interactúan con `wpdb`:** `wp_set_wpdb_vars()`, `is_multisite()`, `wp_load_translations_early()`, `did_action()`, `apply_filters()`, `is_wp_error()`, `_doing_it_wrong()`, `wp_die()`, `dead_db()`, `esc_sql()`, `wp_debug_backtrace_summary()`, `wp_get_db_schema()`, `wp_install_defaults()`.

## 2. Sanitización y Validación General de Datos

Además de proteger las interacciones directas con la base de datos, es vital sanear y validar todos los datos de entrada (POST, GET, etc.) y salida para proteger contra XSS, garantizar formatos correctos y mantener la integridad de la aplicación.

### Conceptos Clave

*   **Importancia:** Validar datos no confiables *lo antes posible* es crucial.
*   **Validación vs. Sanitización:**
    *   **Validación:** Prueba si los datos cumplen un patrón o regla específica (válido/inválido). Enfoque de \"todo o nada\".
    *   **Sanitización:** Limpia los datos, eliminando o modificando partes inseguras para hacerlos seguros de usar. Enfoque de \"hacerlo seguro\".
*   **Filosofías:**
    *   **Safelist (Lista Blanca):** **Preferido**. Solo acepta valores de una lista finita y conocida de valores permitidos. **Requiere verificación estricta de tipo (`===`)**.
    *   **Blocklist (Lista Negra):** **Evitar**. Intenta rechazar valores de una lista conocida de valores *malos*. Es imposible listar todos los valores maliciosos.
    *   **Format Detection:** Prueba si los datos cumplen un formato específico (ej. email válido).
    *   **Format Correction:** Acepta datos y elimina/modifica partes inseguras (ej. `sanitize_title`).

### Glosario de Funciones de Sanitización

Estas funciones \"limpian\" los datos.

*   **`wp_kses_post( string $data ): string`**: Sanitiza contenido HTML para post (`post_content`), permitiendo etiquetas seguras. Usa `wp_kses()` con contexto 'post'. Para imprimir mensajes en admin.
*   **`wp_kses( string $content, array[]|string $allowed_html, string[] $allowed_protocols = array() ): string`**: Filtra contenido HTML, permitiendo solo etiquetas y atributos especificados. \"KSES Strips Evil Scripts\". Útil para filtrado HTML personalizado, incluyendo SVG.
*   **`sanitize_url( string $url, string[] $protocols = null ): string`**: Sanitiza una URL para uso en DB o redirecciones. Llama a `esc_url()`.
*   **`sanitize_user( string $username, bool $strict = false ): string`**: Sanitiza nombre de usuario. Elimina etiquetas, etc. Opción `$strict` para caracteres muy limitados.
*   **`sanitize_title_with_dashes( string $title, string $raw_title = '', string $context = 'display' ): string`**: Sanitiza un título reemplazando espacios/caracteres con guiones. Para slugs de URL o clases HTML. No maneja acentos por defecto. Contexto 'save' para DB.
*   **`sanitize_title_for_query( string $title ): string`**: Sanitiza título para consulta DB (`context='query'`).
*   **`sanitize_title( string $title, string $fallback_title = '', string $context = 'save' ): string`**: Sanitiza a un \"slug\" (alfanumérico, _, -). Convierte acentos (por defecto). Contexto 'save' (DB), 'query' (WHERE). No para títulos legibles.
*   **`sanitize_textarea_field( string $str ): string`**: Sanitiza texto multilinea (textarea), conservando saltos de línea.
*   **`sanitize_text_field( string $str ): string`**: Sanitiza cadena de texto simple. Elimina etiquetas, limpia espacios/saltos de línea/tabulaciones. NO para inyecciones SQL (`wpdb::prepare` es para eso). No recursiva para arrays.
*   **`sanitize_term_field( string $field, string $value, int $term_id, string $taxonomy, string $context ): mixed`**: Sanitiza el valor de un campo específico de un término según el contexto ('raw', 'edit', 'db', 'display', 'rss', 'attribute', 'js').
*   **`sanitize_term( array|object $term, string $taxonomy, string $context = 'display' ): array|object`**: Sanitiza todos los campos de un término (array u objeto). Usa `sanitize_term_field()`.
*   **`sanitize_sql_orderby( string $orderby ): string|false`**: Valida que una cadena sea una cláusula `ORDER BY` válida. Acepta columnas, ASC/DESC, RAND().
*   **`sanitize_option( string $option, mixed $value ): mixed`**: Sanitiza valores de opciones basándose en el nombre de la opción. Usa funciones específicas internamente (`sanitize_email`, `absint`, `sanitize_url`, etc.). Tiene filtros dinámicos.
*   **`sanitize_mime_type( string $mime_type ): string`**: Sanitiza un tipo MIME.
*   **`sanitize_meta( string $meta_key, mixed $meta_value, string $object_type, string $object_subtype = '' ): mixed`**: **NO sanitiza por sí misma**. Es un hook (`sanitize_{$meta_type}_meta_{$meta_key}`) para que los desarrolladores implementen su propia sanitización. Llamada por `add_metadata`/`update_metadata`.
*   **`sanitize_key( string $key ): string`**: Sanitiza una cadena clave interna (alfanumérico minúscula, _, -). Convierte mayúsculas a minúsculas.
*   **`sanitize_html_class( string $classname, string $fallback = '' ): string`**: Sanitiza un nombre de clase HTML (A-Z, a-z, 0-9, _, -). Devuelve fallback si queda vacío. No considera restricción W3C de inicio con número.
*   **`sanitize_hex_color_no_hash( string $color ): string|null`**: Sanitiza color hex sin '#'.
*   **`sanitize_hex_color( string $color ): string|void`**: Sanitiza color hex con '#'.
*   **`sanitize_file_name( string $filename ): string`**: Sanitiza nombre de archivo (reemplaza espacios con guiones, elimina caracteres especiales). No garantiza que sea subible.

### Glosario de Funciones de Validación

Estas funciones \"prueban\" los datos.

*   **`is_email( string $email, bool $deprecated = false ): string|false`**: Verifica si un email tiene formato válido (limitado, no RFC completo, no i18n). Devuelve email si es válido, `false` si no.
*   **`term_exists( int|string $term, string $taxonomy = '', int $parent_term = null ): mixed`**: Verifica si un término existe por ID, slug o nombre.
*   **`username_exists( string $username ): int|false`**: Verifica si un nombre de usuario existe. Devuelve User ID o `false`.
*   **`validate_file( string $file, string[] $allowed_files = array() ): int`**: Valida ruta de archivo contra \"directory traversal\" o rutas de unidad. No si el archivo existe.

### Funciones Auxiliares para Sanitización y Validación

`balanceTags()`, `force_balance_tags()`, `count()`, `in_array()`, `is_array()`, `mb_strlen()`, `strlen()`, `preg_match()`, `strpos()`, `tag_escape()`, `map_deep()` (para aplicar función recursivamente a arrays/objetos, usada por `wp_kses_post_deep`, `urldecode_deep`, `stripslashes_deep`).

## 3. Auditoría de Código y Verificación de Seguridad

Auditar el código es un paso esencial para identificar y corregir posibles vulnerabilidades. PHP_CodeSniffer (PHPCS) con el estándar WordPress es una herramienta poderosa para esto, especialmente para la seguridad SQL.

### Comandos de Auditoría SQL con PHPCS

Puedes usar PHPCS desde la línea de comandos para analizar tus archivos.

*   **Análisis WPCS Completo de un archivo:**
    ```powershell
    phpcs --standard=WordPress --extensions=php path/to/your/file.php
    ```

*   **Auto-corrección de Formato WPCS:**
    ```powershell
    phpcbf --standard=WordPress --extensions=php path/to/your/file.php
    ```
    *(Nota: `phpcbf` corrige principalmente problemas de formato/estilo, no todos los problemas de seguridad SQL)*

*   **Filtrar Solo Errores de SQL No Preparado:**
    ```powershell
    phpcs --standard=WordPress --extensions=php path/to/your/file.php | findstr /C:\"PreparedSQL\"
    ```

*   **Verificar Errores Específicos de Interpolación:**
    ```powershell
    phpcs --standard=WordPress --extensions=php path/to/your/file.php | findstr /C:\"InterpolatedNotPrepared\"
    ```

*   **Resumen de Errores SQL y Advertencias:**
    ```powershell
    phpcs --standard=WordPress --extensions=php path/to/your/file.php | Select-String -Pattern \"PreparedSQL|ERROR|WARNING\" | Select-Object -First 20 # (PowerShell)
    # o en Bash:
    # phpcs --standard=WordPress --extensions=php path/to/your/file.php | grep \"PreparedSQL\\|ERROR\\|WARNING\" | head -n 20
    ```

*   **Búsqueda Básica de Patrones de Código Inseguros en Archivos:**
    ```powershell
    # Buscar interpolación directa en consultas
    findstr /C:\"{$wpdb->\" path/to/your/file.php
    findstr /C:\"{$this->\" path/to/your/file.php

    # Buscar métodos get_* sin preparar (indicativo de posible riesgo)
    findstr /C:\"$wpdb->get_\" path/to/your/file.php
    ```

### Patrones de Código Seguro (Confirmados por Auditoría)

Ejemplos de cómo implementar seguridad al usar `$wpdb`, a menudo acompañados de comentarios `phpcs:ignore` donde la interpolación es necesaria y justificada.

*   **Escape de Nombres de Tabla (usando `esc_sql` o `quote_identifier`)**:
    ```php
    $table_name = esc_sql( $this->table_leads ); // O $wpdb->quote_identifier()
    $query = \"SELECT * FROM {$table_name} WHERE id = %d\"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name safely escaped with esc_sql()
    $wpdb->get_row( $wpdb->prepare( $query, $lead_id ) );
    ```
    *(Nota: Desde WP 6.2, se prefiere `prepare` con `%i` si es posible: `$wpdb->prepare(\"SELECT * FROM %i WHERE id = %d\", $table_name, $lead_id);`)*

*   **Preparación de Consultas SQL (Uso Correcto de `prepare`)**:
    ```php
    // CORRECTO - Consulta completamente preparada
    $wpdb->get_row(
        $wpdb->prepare(
            \"SELECT * FROM `{$wpdb->prefix}my_table` WHERE id = %d\",
            $lead_id
        )
    );
    ```

*   **Escape de Búsquedas LIKE (`esc_like` + `prepare`)**:
    ```php
    $search_term = $args['search'];
    $search = '%' . $wpdb->esc_like( $search_term ) . '%';
    $where[] = $wpdb->prepare( \"campo LIKE %s\", $search );
    ```

*   **Consultas DDL con Escape (justificando `phpcs:ignore`)**:
    ```php
    $table_name = esc_sql( $this->table_leads ); // O $wpdb->quote_identifier()
    // DDL a menudo requiere nombre de tabla literal, justificar ignorando el sniff
    $wpdb->query( \"ALTER TABLE `{$table_name}` ADD COLUMN meta_data longtext DEFAULT NULL\" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name safely escaped with esc_sql(), DDL requires table name interpolation
    ```

### Patrones Inseguros (Identificados en Auditoría)

Ejemplos de código a **EVITAR** por ser vulnerables a inyección SQL u otros problemas de seguridad/calidad.

*   **❌ Interpolación Directa en Consultas:**
    ```php
    // INCORRECTO - VULNERABILIDAD GRAVE
    $wpdb->get_row( \"SELECT * FROM {$this->table_leads} WHERE id = {$lead_id}\" );
    ```
    *(¡Nunca concatenes variables directamente en SQL!)*

*   **❌ Consultas Sin Preparar (con variables):**
    ```php
    // INCORRECTO - SQL NO PREPARADO (si $this->table_leads o la cadena completa pudiera ser manipulada)
    $wpdb->get_var( \"SHOW TABLES LIKE '{$this->table_leads}'\" );
    ```
    *(Aunque `SHOW TABLES` es menos riesgoso, la práctica es peligrosa. Usa `prepare` con `%i` o justifica con ignore si el nombre de la tabla es fijo/confiable pero PHPCS lo marca)*

*   **❌ Uso de Funciones PHP Deprecated/Inseguras (contexto de datos):**
    ```php
    // INCORRECTO - Función deprecated para este uso
    $data['meta_data'] = json_encode( $meta_data );

    // CORRECTO - Función WordPress para codificación JSON segura
    $data['meta_data'] = wp_json_encode( $meta_data );
    ```

### Checklist de Auditoría SQL

Una lista de verificación para revisar el código después de implementar las correcciones.

*   [ ] ✅ Todas las consultas que incluyen variables usan `$wpdb->prepare()`.
*   [ ] ✅ Nombres de tabla o identificadores escapados con `esc_sql()`, `quote_identifier()`, o usando `%i` en `prepare()`.
*   [ ] ✅ Búsquedas LIKE usan `$wpdb->esc_like()` ANTES de `prepare()`.
*   [ ] ✅ Comentarios `phpcs:ignore` están justificados cuando la interpolación directa es necesaria (ej. DDL con nombres de tabla escapados).
*   [ ] ✅ Condiciones Yoda implementadas (Ej: `'draft' === $post_status` en lugar de `$post_status == 'draft'`). *Relacionado con calidad de código más que solo seguridad SQL, pero común en WPCS*.
*   [ ] ✅ Funciones WordPress preferidas sobre funciones PHP nativas cuando existe una alternativa segura (ej. `wp_json_encode` vs `json_encode`, `esc_sql` vs `mysql_real_escape_string` obsoleto/no recomendado).
*   [ ] ✅ Sin interpolación directa de variables NO ESCAPADAS en cadenas SQL (`\"...\" . $variable . \"...\"`).
*   [ ] ✅ Sin consultas SQL construidas únicamente con concatenación a partir de datos variables.

### Comando de Verificación Final de Auditoría SQL

Utiliza este comando para confirmar que no quedan problemas de SQL no preparado según WPCS.

```powershell
# Verificar que no hay errores de SQL no preparado o interpolado sin justificar
phpcs --standard=WordPress --extensions=php path/to/your/file.php | findstr /C:\"PreparedSQL\" /C:\"NotPrepared\" /C:\"InterpolatedNotPrepared\"

# Si el comando no devuelve resultados, el código cumple con los sniffs de seguridad SQL de WPCS.
```

---

## 4. Personalización del Análisis de Código (PHPCS)

Al usar PHPCS para auditar, puede haber casos legítimos en los que necesites ignorar ciertas líneas, bloques o archivos específicos.

### Ignorando Partes de un Archivo

Puedes usar comentarios especiales (anotaciones) para controlar PHPCS.

*   **Ignorar una sola línea:** Añade `// phpcs:ignore <sniff_code> -- [justificación]` al final de la línea a ignorar, o en la línea anterior para ignorar la línea siguiente. Especifica el código del sniff si solo quieres ignorar ese sniff.
    ```php
    $xmlPackage = new XMLPackage; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase -- Ignorar formato de nombre de clase
    ```
    ```php
    // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase -- Ignorar la línea siguiente
    $xmlPackage = new XMLPackage;
    ```

*   **Ignorar múltiples líneas:** Usa `// phpcs:disable <sniff_code(s)> -- [justificación]` antes del bloque y `// phpcs:enable <sniff_code(s)>` (o sin sniffs para habilitar todos los previamente deshabilitados) después.
    ```php
    // phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase -- Deshabilitar sniff para este bloque
    $xmlPackage['error_code'] = get_default_error_code_value();
    $xmlPackage->send();
    // phpcs:enable
    ```
    *(Usar `// phpcs:enable` sin sniffs es una buena práctica para mostrar que todas las deshabilitaciones previas terminan allí).*

### Omitir Análisis PHPCS o PHP Linting para Directorios Específicos

Para excluir directorios completos del análisis automático (como directorios de tests con errores intencionales), puedes crear archivos específicos en la raíz de tu repositorio:

*   **Omitir Análisis PHPCS:** Crea un archivo llamado `.vipgoci_phpcs_skip_folders`. Lista cada directorio a omitir en una nueva línea. No se admiten expresiones regulares.
    ```
    themes/news-site-theme/unit-tests
    plugins/third-party-plugin-v2
    ```
*   **Omitir PHP Linting:** Crea un archivo llamado `.vipgoci_lint_skip_folders`. Lista directorios de la misma manera.

### Omitir PHPCS Scanning para Pull Requests Específicos

Puedes deshabilitar el análisis PHPCS para un Pull Request completo añadiendo una etiqueta específica al PR.

*   Añade la etiqueta `skip-phpcs-scan` al Pull Request. No debe haber otro contenido en la etiqueta.
    *(Nota: El linting PHP básico y la auto-aprobación no se deshabilitan con esta etiqueta).*

---

**Resumen:**

La seguridad en WordPress requiere un enfoque multifacético:

1.  **Base de Datos:** Utiliza SIEMPRE `$wpdb->prepare()` para consultas con variables, `esc_like()` para búsquedas LIKE, y los métodos dedicados `insert()`, `update()`, `delete()`, `replace()` con formatos especificados para operaciones DML.
2.  **Datos Generales:** Saniza y valida toda la entrada de datos utilizando las funciones apropiadas de WordPress (`sanitize_*`, `wp_kses*`, `is_*`, `*_exists`, `validate_*`). Prioriza la filosofía de \"Lista Blanca\" (Safelist) con verificación estricta de tipo.
3.  **Auditoría Continua:** Usa herramientas como PHPCS con el estándar WordPress para escanear tu código, identificando problemas de seguridad y de estilo. Implementa procesos de auditoría, utiliza justificaciones con `phpcs:ignore` cuando sea necesario y documenta tus patrones de código seguro.

Combinar estas técnicas ayudará a construir aplicaciones WordPress más robustas y seguras.

---

Guia Rápida Seguridad.
---

# Guía Definitiva de Seguridad y Calidad de Código en WordPress

## Introducción

Esta guía es un recurso consolidado para desarrolladores de WordPress que buscan escribir código seguro, robusto y compatible con los más altos estándares. Cubriremos los tres pilares fundamentales: la **estrategia de consulta** a la base de datos, la **sanitización y validación** de todos los datos no confiables, y el **cumplimiento de estándares de codificación** para garantizar la calidad y mantenibilidad del código.

---

## Parte I: La Filosofía de la Seguridad en el Desarrollo

Antes de escribir una sola línea de código, es crucial entender los principios que rigen la seguridad de los datos.

### 1. Validación vs. Sanitización

Aunque a menudo se usan indistintamente, tienen propósitos diferentes:

*   **Validación**: Es el proceso de **verificar** si los datos cumplen con un formato o conjunto de reglas específico. El resultado es binario: válido o inválido. Su objetivo es rechazar datos incorrectos.
    *   *Ejemplo*: Comprobar si un campo de correo electrónico contiene un formato de email válido con `is_email()`.

*   **Sanitización**: Es el proceso de **limpiar** o filtrar los datos, eliminando o transformando caracteres y elementos potencialmente peligrosos. Su objetivo es hacer que los datos sean seguros para su uso.
    *   *Ejemplo*: Eliminar etiquetas HTML de un campo de texto con `sanitize_text_field()`.

La validación debe realizarse **lo antes posible**, idealmente en el momento en que se reciben los datos, para evitar que datos malformados o maliciosos se propaguen por la aplicación.

### 2. Filosofías de Validación

*   **Safelist (Lista Blanca) - El Enfoque Preferido**: Solo se aceptan datos que pertenecen a una lista finita de valores conocidos y confiables. Es el método más seguro porque define lo que está permitido, rechazando todo lo demás.
    *   **¡Crucial!**: Usa siempre la **verificación estricta de tipo** (`===` o el tercer parámetro `true` en `in_array()`) para evitar vulnerabilidades de comparación laxa.

*   **Blocklist (Lista Negra) - A Evitar**: Se rechazan datos de una lista de valores no confiables conocidos. **Raramente es una buena idea**, ya que es casi imposible prever y listar todas las posibles entradas maliciosas.

*   **Format Detection (Detección de Formato)**: Se comprueba si los datos tienen el formato correcto (ej., `ctype_alnum()`, `preg_match()`). Si no cumplen, se rechazan.

*   **Format Correction (Corrección de Formato)**: Se aceptan casi todos los datos, pero se eliminan o alteran las partes peligrosas para crear un valor seguro (ej., `sanitize_title()` para generar un slug).

---

## Parte II: La Herramienta Correcta - `WP_Query` vs. `$wpdb`

La decisión más importante al consultar la base de datos de WordPress es elegir la herramienta adecuada. Para la mayoría de las consultas relacionadas con posts, páginas, usuarios o taxonomías, `WP_Query` es la opción superior.

### 1. Cuándo y Por Qué Usar `WP_Query` (El Método Preferido)

`WP_Query` es una clase de alto nivel que abstrae la complejidad de las consultas SQL, ofreciendo seguridad y rendimiento optimizados por el core de WordPress.

**Ventajas Clave:**
1.  **Seguridad Automática**: WordPress se encarga de escapar y sanitizar los parámetros.
2.  **Compatibilidad y Rendimiento**: Aprovecha la caché de objetos de WordPress y garantiza la compatibilidad con otros plugins y futuras versiones de WordPress.
3.  **Mantenibilidad**: El código es más limpio, legible y declarativo.
4.  **Resuelve Problemas Complejos de WPCS**: Evita los errores comunes de PHPCS relacionados con consultas SQL dinámicas.

#### Casos de Uso Comunes donde `WP_Query` es la Solución:

*   **Cláusulas `IN` dinámicas**:
    *   **Problema**: `SELECT * FROM wp_posts WHERE post_status IN ('publish', 'draft')` requiere interpolar una cadena de marcadores de posición, lo que PHPCS prohíbe.
    *   **Solución `WP_Query`**: Pasa un array directamente al argumento `post_status`.

*   **Consultas con `LIMIT` variable**:
    *   **Problema**: Preparar un `LIMIT` con `$wpdb->prepare()` puede ser complicado.
    *   **Solución `WP_Query`**: Usa el argumento `posts_per_page`.

*   **Múltiples condiciones (`JOIN`, `WHERE`)**:
    *   **Problema**: Construir `JOIN`s complejos y múltiples cláusulas `WHERE` manualmente es propenso a errores y difícil de hacer seguro.
    *   **Solución `WP_Query`**: Usa `meta_query` y `tax_query` con relaciones `OR`/`AND`.

#### Patrón de Migración: De `$wpdb` a `WP_Query`

**Antes (Problemático y propenso a errores WPCS):**
```php
global $wpdb;
$stati = array('publish', 'draft');
$placeholders = implode(',', array_fill(0, count($stati), '%s'));
// Esto genera un error WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$query = \"SELECT p.ID FROM {$wpdb->posts} p WHERE p.post_status IN ($placeholders)\";
$results = $wpdb->get_col($wpdb->prepare($query, ...$stati));
```

**Después (Seguro, limpio y compatible):**
```php
$args = array(
    'post_type'      => 'post',
    'post_status'    => array('publish', 'draft'), // Simplemente pasa el array
    'fields'         => 'ids', // Optimizado para obtener solo los IDs
    'posts_per_page' => -1,    // Obtener todos los resultados
);
$query = new WP_Query($args);
$results = $query->posts; // Devuelve un array de IDs
```

### 2. Cuándo Usar la Clase `$wpdb` (Para Casos Específicos)

Usa `$wpdb` solo cuando `WP_Query` (o `WP_User_Query`, `WP_Term_Query`, etc.) no sea una opción viable.

*   Para interactuar con **tablas personalizadas** que no siguen la estructura de WordPress.
*   Para realizar operaciones de **manipulación de datos masiva** (`UPDATE`, `DELETE`) en tablas personalizadas.
*   Para ejecutar consultas muy específicas que no se pueden lograr con las clases de consulta de alto nivel (ej. consultas `DDL` como `ALTER TABLE`).

---

## Parte III: Dominando la Clase `$wpdb` para SQL Seguro

Cuando necesites usar `$wpdb`, es imperativo hacerlo de la manera más segura posible.

### 1. El Pilar de la Seguridad: `$wpdb->prepare()`

El método `$wpdb->prepare()` es tu herramienta principal para prevenir ataques de inyección SQL. Funciona de manera similar a `sprintf()`, reemplazando marcadores de posición con datos escapados de forma segura.

**Sintaxis:**
`$wpdb->prepare( 'consulta', valor_1, valor_2, ... );`

**Marcadores de Posición:**
*   `%s`: Para cadenas (strings).
*   `%d`: Para enteros (integers).
*   `%f`: Para números de punto flotante (floats).
*   `%i`: Para identificadores (nombres de tablas o campos). *Disponible desde WP 6.2.*

**Reglas de Oro para `prepare()`:**
1.  **NUNCA** pongas comillas alrededor de los marcadores de posición en la cadena SQL. `prepare()` lo hará por ti.
2.  Todos los caracteres `%` literales en la consulta (como en una cláusula `LIKE`) **DEBEN** escaparse como `%%`.
3.  Los datos pasados a `prepare()` deben ser \"crudos\" (raw), no escapados previamente.

**Ejemplo:**
```php
global $wpdb;
$user_id = 5;
$status = 'publish';
$posts = $wpdb->get_results(
    $wpdb->prepare(
        \"SELECT post_title, post_content FROM {$wpdb->posts} WHERE post_author = %d AND post_status = %s\",
        $user_id,
        $status
    )
);
```

### 2. Manejo de Cláusulas `LIKE`: `$wpdb->esc_like()`

Para búsquedas con comodines, debes escapar el término de búsqueda *antes* de pasarlo a `prepare()`.
```php
global $wpdb;
$search_term = '43% de los planetas';
$wild = '%';
// Escapa el término para que el '%' en '43%' no actúe como comodín.
$like_term = $wild . $wpdb->esc_like( $search_term ) . $wild;

$sql = $wpdb->prepare( \"SELECT * FROM {$wpdb->posts} WHERE post_content LIKE %s\", $like_term );
```

### 3. Métodos CRUD (Crear, Leer, Actualizar, Eliminar)

`$wpdb` proporciona métodos de alto nivel que simplifican las operaciones comunes y manejan la seguridad internamente.

*   **Insertar Filas: `wpdb::insert()`**
    ```php
    $wpdb->insert(
        $wpdb->posts,
        array(
            'post_author' => 1,
            'post_title'  => 'Mi Título de Prueba',
            'post_status' => 'publish',
        ),
        array( // Formatos
            '%d',
            '%s',
            '%s',
        )
    );
    $new_post_id = $wpdb->insert_id; // Obtener el ID del nuevo registro
    ```

*   **Actualizar Filas: `wpdb::update()`**
    ```php
    $wpdb->update(
        $wpdb->posts,
        array( 'post_status' => 'draft' ), // Datos a actualizar
        array( 'ID' => $new_post_id ),     // Cláusula WHERE
        array( '%s' ),                     // Formato de datos
        array( '%d' )                      // Formato de WHERE
    );
    ```

*   **Eliminar Filas: `wpdb::delete()`**
    ```php
    $wpdb->delete(
        $wpdb->posts,
        array( 'ID' => $new_post_id ), // Cláusula WHERE
        array( '%d' )                  // Formato de WHERE
    );
    ```

*   **Reemplazar Filas: `wpdb::replace()`**
    Funciona como `insert()`, pero si existe una fila con la misma clave primaria/única, la elimina y la reemplaza.

### 4. Consultas Genéricas y DDL con `wpdb::query()`

Para consultas que no devuelven datos (ej. `ALTER TABLE`, `CREATE TABLE`) o son demasiado complejas para otros métodos, usa `query()`. **Siempre úsalo con `prepare()` si hay datos de usuario involucrados.**

Para nombres de tablas o columnas dinámicas (lo cual es raro y debe evitarse si es posible), escápalos por separado.
```php
// Escapar nombres de tablas/columnas es un caso de uso avanzado y debe hacerse con cuidado.
$table_name = $wpdb->prefix . 'mi_tabla_personalizada'; // La forma más segura es construirlo con el prefijo
// Si el nombre de la tabla viene de una fuente no confiable, se requiere un escape riguroso o validación contra una lista blanca.

// Para DDL, la interpolación de nombres de tabla es a veces inevitable.
$wpdb->query(
    \"ALTER TABLE `{$table_name}` ADD COLUMN meta_data longtext DEFAULT NULL\"
); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is controlled and safe. DDL requires table name interpolation.
```

### 5. Propiedades Útiles de `$wpdb`

`$wpdb` te da acceso directo a los nombres de las tablas de WordPress, lo que evita tener que codificarlos.

*   `$wpdb->prefix`: El prefijo de la tabla (ej. `wp_`).
*   `$wpdb->posts`, `$wpdb->users`, `$wpdb->postmeta`, `$wpdb->options`, etc.
*   `$wpdb->last_query`: La última consulta ejecutada (útil para depuración).
*   `$wpdb->last_error`: El último error de la base de datos.
*   `$wpdb->num_rows`: Número de filas devueltas por la última consulta `SELECT`.

---

## Parte IV: El Glosario Definitivo de Funciones de Sanitización y Validación

Esta es una referencia rápida de las funciones más importantes que debes conocer.

### A. Funciones de Sanitización (Limpieza)

*   **`sanitize_text_field( string $str )`**: Para cadenas de texto de una sola línea. Elimina etiquetas, saltos de línea y espacios extra. Es la función de sanitización más común para campos de formulario.
*   **`sanitize_textarea_field( string $str )`**: Similar a la anterior, pero **conserva los saltos de línea**, ideal para `<textarea>`.
*   **`sanitize_email( string $email )`**: Elimina todos los caracteres no permitidos en una dirección de correo electrónico.
*   **`sanitize_url( string $url )`**: Sanitiza una URL para su uso en bases de datos o redirecciones.
*   **`sanitize_key( string $key )`**: Sanitiza una cadena para ser usada como identificador interno (slug, meta key). Convierte a minúsculas y solo permite caracteres alfanuméricos, guiones y guiones bajos.
*   **`sanitize_title( string $title )`**: Convierte una cadena en un \"slug\" amigable para URL.
*   **`sanitize_html_class( string $class )`**: Asegura que un nombre de clase HTML solo contenga caracteres válidos.
*   **`sanitize_file_name( string $filename )`**: Limpia un nombre de archivo, reemplazando espacios con guiones y eliminando caracteres especiales.
*   **`wp_kses_post( string $data )`**: La función más potente para contenido HTML. Sanitiza el contenido permitiendo solo las etiquetas y atributos HTML autorizados para el contenido de una publicación (definidos por WordPress). Úsalo para limpiar cualquier entrada que deba contener HTML seguro (como el contenido de un editor).
*   **`wp_kses( string $content, array $allowed_html )`**: Versión personalizable de `wp_kses_post`, donde puedes definir tu propio array de etiquetas y atributos permitidos.

### B. Funciones de Validación (Verificación)

*   **`is_email( string $email )`**: Verifica si una cadena tiene un formato de correo electrónico válido. Devuelve el email si es válido, `false` si no.
*   **`username_exists( string $username )`**: Comprueba si un nombre de usuario ya existe. Devuelve el ID de usuario si existe, `false` si no.
*   **`term_exists( int|string $term, string $taxonomy )`**: Comprueba si un término (categoría, etiqueta) existe en una taxonomía.
*   **`validate_file( string $file )`**: Valida que una ruta de archivo no contenga \"directory traversal\" (`../`) u otros elementos inseguros.

---

## Parte V: Seguridad Más Allá de SQL

### 1. Verificación de Nonces (Protección CSRF)

Cualquier acción iniciada por un usuario (especialmente a través de formularios o enlaces) debe ser protegida con un \"nonce\" (number used once) para prevenir ataques de Cross-Site Request Forgery (CSRF).

**Paso 1: Crear el Nonce en el formulario o enlace**
```php
// En un enlace
$delete_url = add_query_arg(
    array(
        'action' => 'delete_item',
        'item_id' => 123,
        '_wpnonce' => wp_create_nonce('delete_item_nonce_action')
    ),
    admin_url('admin.php?page=mi-pagina')
);
echo '<a href=\"' . esc_url($delete_url) . '\">Eliminar Item</a>';

// En un formulario
wp_nonce_field('delete_item_nonce_action', '_wpnonce');
```

**Paso 2: Verificar el Nonce antes de procesar la acción**
```php
if (
    isset($_GET['action']) && 'delete_item' === $_GET['action'] &&
    isset($_GET['_wpnonce']) &&
    wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'delete_item_nonce_action')
) {
    // El nonce es válido, el usuario tiene la intención de hacer esto.
    // Procede con la acción...
    $item_id = isset($_GET['item_id']) ? absint($_GET['item_id']) : 0;
    if ($item_id > 0) {
        // ...lógica de eliminación...
    }
}
```

### 2. Sanitización de Superglobales (`$_GET`, `$_POST`, `$_SERVER`)

**Nunca confíes en los datos de estas variables. Sanéalos en el punto de entrada.**

*   **`$_SERVER['REQUEST_URI']`**
    ```php
    // Forma robusta y segura de manejar REQUEST_URI
    $request_uri = '';
    if (isset($_SERVER['REQUEST_URI'])) {
        // wp_unslash() elimina slashes si magic_quotes_gpc está activo (raro hoy en día).
        // esc_url_raw() sanitiza la URL para uso interno (no para atributos HTML).
        $request_uri = esc_url_raw(wp_unslash($_SERVER['REQUEST_URI']));
    }

    if (str_contains($request_uri, '/robots.txt')) {
        // Lógica segura
    }
    ```

*   **`$_GET` y `$_POST` con `filter_input()`**
    Este es el método moderno preferido, ya que comprueba la existencia y sanitiza en un solo paso.

    ```php
    // Obtener un ID de la URL
    $post_id = filter_input(INPUT_GET, 'post_id', FILTER_VALIDATE_INT);
    if ($post_id) {
        // $post_id es un entero válido o false/null si no lo es.
    }

    // Obtener un término de búsqueda
    $search_query = filter_input(INPUT_POST, 's', FILTER_SANITIZE_STRING);
    if ($search_query) {
        // $search_query es una cadena sanitizada.
    }
    ```

---

## Parte VI: Auditoría y Cumplimiento de Código con PHP_CodeSniffer (PHPCS)

PHPCS es una herramienta esencial para mantener la calidad y seguridad del código.

### 1. Comandos Esenciales de Auditoría (Línea de Comandos)

*   **Análisis completo de un archivo:**
    ```shell
    phpcs --standard=WordPress --extensions=php ruta/a/tu/archivo.php
    ```
*   **Auto-corrección de errores de formato (no de lógica):**
    ```shell
    phpcbf --standard=WordPress --extensions=php ruta/a/tu/archivo.php
    ```
*   **Filtrar solo errores de SQL no preparado (muy útil):**
    ```shell
    phpcs --standard=WordPress ruta/a/tu/archivo.php | findstr \"PreparedSQL\"
    ```
*   **Verificación final (no debe devolver resultados):**
    ```shell
    phpcs --standard=WordPress ruta/a/tu/archivo.php | findstr \"PreparedSQL NotPrepared\"
    ```

### 2. Ignorar Advertencias de PHPCS de Forma Justificada

A veces, PHPCS puede marcar falsos positivos o situaciones donde el código es seguro pero no sigue el patrón exacto. En esos casos, puedes usar anotaciones.

*   **Ignorar una sola línea:**
    Úsalo para casos específicos como una DDL donde el nombre de la tabla es seguro pero interpolado.
    ```php
    $wpdb->query( \"ALTER TABLE `{$safe_table_name}` ADD COLUMN new_col VARCHAR(255)\" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is whitelisted and safe.
    ```
*   **Ignorar múltiples líneas:**
    ```php
    // phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase -- Reason for disabling.
    $xmlPackage = new XMLPackage();
    $xmlPackage->send();
    // phpcs:enable
    ```
*   **Ignorar directorios enteros (en VIP Go):**
    Crea un archivo `.vipgoci_phpcs_skip_folders` en la raíz de tu repositorio y lista los directorios a ignorar, uno por línea.
    ```
    themes/mi-tema/node_modules
    plugins/mi-plugin/vendor
    ```

### 3. Checklist Definitivo de Auditoría de Código

Usa esta lista para revisar tu código o cualquier pull request.

-   [ ] **Consultas a la Base de Datos**:
    -   [ ] ¿Se usa `WP_Query` (o similar) para consultas de contenido de WordPress?
    -   [ ] Si se usa `$wpdb`, ¿todas las consultas usan `$wpdb->prepare()`?
    -   [ ] ¿Se evitan las variables interpoladas (`{$variable}`) en las cadenas SQL?
    -   [ ] ¿Las cláusulas `LIKE` usan `$wpdb->esc_like()`?
    -   [ ] ¿Se usan los métodos CRUD (`insert`, `update`, `delete`) cuando es apropiado?
    -   [ ] ¿Los nombres de tabla dinámicos (si son inevitables) se validan contra una lista blanca?

-   [ ] **Manejo de Datos de Usuario**:
    -   [ ] ¿Se verifican los nonces (`wp_verify_nonce()`) para todas las acciones de formulario (POST y GET)?
    -   [ ] ¿Se sanitizan todos los datos de `$_GET`, `$_POST`, `$_REQUEST` y `$_SERVER` en el punto de entrada?
    -   [ ] ¿Se escapa toda la salida para el contexto correcto (`esc_html`, `esc_attr`, `esc_url`, `esc_js`)?

-   [ ] **Buenas Prácticas de WordPress**:
    -   [ ] ¿Se usan funciones de WordPress (ej. `wp_json_encode`) en lugar de funciones nativas de PHP (`json_encode`) cuando existe un equivalente?
    -   [ ] ¿Se implementan condiciones Yoda (`if ( 'valor' === $variable )`)?
    -   [ ] ¿Se han añadido comentarios `phpcs:ignore` solo cuando es estrictamente necesario y con una justificación clara?
    -   [ ] ¿El código pasa el análisis de PHPCS sin errores (o solo con ignorados justificados)?

---
