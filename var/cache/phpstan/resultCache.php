<?php declare(strict_types = 1);

return [
	'lastFullAnalysisTime' => 1753775146,
	'meta' => array (
  'cacheVersion' => 'v12-linesToIgnore',
  'phpstanVersion' => '1.12.28',
  'phpVersion' => 80306,
  'projectConfig' => '{parameters: {level: 5, paths: [/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes, /var/www/html/plugins/wp-content/plugins/ewm-modal-cta/ewm-modal-cta.php], excludePaths: {analyseAndScan: [/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/assets, /var/www/html/plugins/wp-content/plugins/ewm-modal-cta/logs, /var/www/html/plugins/wp-content/plugins/ewm-modal-cta/docs, /var/www/html/plugins/wp-content/plugins/ewm-modal-cta/memory, /var/www/html/plugins/wp-content/plugins/ewm-modal-cta/team_memory, /var/www/html/plugins/wp-content/plugins/ewm-modal-cta/tools, /var/www/html/plugins/wp-content/plugins/ewm-modal-cta/automation, /var/www/html/plugins/wp-content/plugins/ewm-modal-cta/memo], analyse: []}, bootstrapFiles: [/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/vendor/php-stubs/wordpress-stubs/wordpress-stubs.php], tmpDir: /var/www/html/plugins/wp-content/plugins/ewm-modal-cta/var/cache/phpstan, treatPhpDocTypesAsCertain: false}}',
  'analysedPaths' => 
  array (
    0 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes',
    1 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/ewm-modal-cta.php',
  ),
  'scannedFiles' => 
  array (
  ),
  'composerLocks' => 
  array (
    '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/composer.lock' => '8bf5f9b9f25065aabc29e836a2556eb98a66f0d4',
  ),
  'composerInstalled' => 
  array (
    '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/vendor/composer/installed.php' => 
    array (
      'versions' => 
      array (
        'php-stubs/wordpress-stubs' => 
        array (
          'pretty_version' => 'v6.8.2',
          'version' => '6.8.2.0',
          'reference' => '9c8e22e437463197c1ec0d5eaa9ddd4a0eb6d7f8',
          'type' => 'library',
          'install_path' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/vendor/composer/../php-stubs/wordpress-stubs',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpstan/phpstan' => 
        array (
          'pretty_version' => '1.12.28',
          'version' => '1.12.28.0',
          'reference' => 'fcf8b71aeab4e1a1131d1783cef97b23a51b87a9',
          'type' => 'library',
          'install_path' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/vendor/composer/../phpstan/phpstan',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
      ),
    ),
  ),
  'executedFilesHashes' => 
  array (
    '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/vendor/php-stubs/wordpress-stubs/wordpress-stubs.php' => '453eb11cbde1730b2f61449094bd9c2d4e208903',
    'phar:///var/www/html/plugins/wp-content/plugins/ewm-modal-cta/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/Attribute.php' => 'eaf9127f074e9c7ebc65043ec4050f9fed60c2bb',
    'phar:///var/www/html/plugins/wp-content/plugins/ewm-modal-cta/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionAttribute.php' => '0b4b78277eb6545955d2ce5e09bff28f1f8052c8',
    'phar:///var/www/html/plugins/wp-content/plugins/ewm-modal-cta/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionIntersectionType.php' => 'a3e6299b87ee5d407dae7651758edfa11a74cb11',
    'phar:///var/www/html/plugins/wp-content/plugins/ewm-modal-cta/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionUnionType.php' => '1b349aa997a834faeafe05fa21bc31cae22bf2e2',
  ),
  'phpExtensions' => 
  array (
    0 => 'Core',
    1 => 'FFI',
    2 => 'PDO',
    3 => 'Phar',
    4 => 'Reflection',
    5 => 'SPL',
    6 => 'SimpleXML',
    7 => 'Zend OPcache',
    8 => 'apcu',
    9 => 'bcmath',
    10 => 'bz2',
    11 => 'calendar',
    12 => 'ctype',
    13 => 'curl',
    14 => 'date',
    15 => 'dom',
    16 => 'exif',
    17 => 'fileinfo',
    18 => 'filter',
    19 => 'ftp',
    20 => 'gd',
    21 => 'gettext',
    22 => 'hash',
    23 => 'iconv',
    24 => 'igbinary',
    25 => 'imagick',
    26 => 'imap',
    27 => 'intl',
    28 => 'json',
    29 => 'ldap',
    30 => 'libxml',
    31 => 'mbstring',
    32 => 'mcrypt',
    33 => 'memcached',
    34 => 'msgpack',
    35 => 'mysqli',
    36 => 'mysqlnd',
    37 => 'openssl',
    38 => 'pcntl',
    39 => 'pcre',
    40 => 'pdo_mysql',
    41 => 'pdo_pgsql',
    42 => 'pdo_sqlite',
    43 => 'pgsql',
    44 => 'posix',
    45 => 'random',
    46 => 'readline',
    47 => 'redis',
    48 => 'session',
    49 => 'shmop',
    50 => 'soap',
    51 => 'sockets',
    52 => 'sodium',
    53 => 'sqlite3',
    54 => 'standard',
    55 => 'sysvmsg',
    56 => 'sysvsem',
    57 => 'sysvshm',
    58 => 'tidy',
    59 => 'tokenizer',
    60 => 'xml',
    61 => 'xmlreader',
    62 => 'xmlrpc',
    63 => 'xmlwriter',
    64 => 'xsl',
    65 => 'yaml',
    66 => 'zip',
    67 => 'zlib',
  ),
  'stubFiles' => 
  array (
  ),
  'level' => '5',
),
	'projectExtensionFiles' => array (
),
	'errorsCallback' => static function (): array { return array (
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method EWM_Admin_Page::get_special_page_id() is unused.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
       'line' => 77,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 18,
       'nodeType' => 'PHPStan\\Node\\ClassMethodsNode',
       'identifier' => 'method.unused',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant EWM_PLUGIN_URL not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
       'line' => 192,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 192,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant EWM_PLUGIN_URL not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
       'line' => 200,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 200,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant EWM_PLUGIN_URL not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
       'line' => 208,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 208,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant EWM_PLUGIN_URL not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
       'line' => 218,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 218,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant EWM_PLUGIN_URL not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
       'line' => 227,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 227,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $text of function esc_attr expects string, int given.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
       'line' => 487,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 487,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $text of function esc_attr expects string, int given.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
       'line' => 530,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 530,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $text of function esc_attr expects string, (float|int) given.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
       'line' => 803,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 803,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Offset \'config\' on array{id: int<min, -1>|int<1, max>, title: mixed, mode: \'formulario\', steps: mixed, design: mixed, triggers: mixed, wc_integration: mixed, display_rules: mixed}|null in isset() does not exist.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
       'line' => 833,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 833,
       'nodeType' => 'PhpParser\\Node\\Expr\\Isset_',
       'identifier' => 'isset.offset',
       'metadata' => 
      array (
      ),
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Offset \'custom_css\' on array{id: int<min, -1>|int<1, max>, title: mixed, mode: \'formulario\', steps: mixed, design: mixed, triggers: mixed, wc_integration: mixed, display_rules: mixed}|null in isset() does not exist.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
       'line' => 859,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 859,
       'nodeType' => 'PhpParser\\Node\\Expr\\Isset_',
       'identifier' => 'isset.offset',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-meta-fields.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method EWM_Meta_Fields::validate_steps_config() is unused.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-meta-fields.php',
       'line' => 83,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-meta-fields.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 18,
       'nodeType' => 'PHPStan\\Node\\ClassMethodsNode',
       'identifier' => 'method.unused',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method EWM_Meta_Fields::validate_design_config() is unused.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-meta-fields.php',
       'line' => 241,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-meta-fields.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 18,
       'nodeType' => 'PHPStan\\Node\\ClassMethodsNode',
       'identifier' => 'method.unused',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method EWM_Meta_Fields::validate_trigger_config() is unused.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-meta-fields.php',
       'line' => 263,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-meta-fields.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 18,
       'nodeType' => 'PHPStan\\Node\\ClassMethodsNode',
       'identifier' => 'method.unused',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method EWM_Meta_Fields::validate_wc_integration() is unused.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-meta-fields.php',
       'line' => 293,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-meta-fields.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 18,
       'nodeType' => 'PHPStan\\Node\\ClassMethodsNode',
       'identifier' => 'method.unused',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method EWM_Meta_Fields::validate_display_rules() is unused.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-meta-fields.php',
       'line' => 305,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-meta-fields.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 18,
       'nodeType' => 'PHPStan\\Node\\ClassMethodsNode',
       'identifier' => 'method.unused',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method EWM_Meta_Fields::validate_field_mapping() is unused.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-meta-fields.php',
       'line' => 341,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-meta-fields.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 18,
       'nodeType' => 'PHPStan\\Node\\ClassMethodsNode',
       'identifier' => 'method.unused',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-performance.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function is_cart not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-performance.php',
       'line' => 179,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-performance.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 179,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function is_checkout not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-performance.php',
       'line' => 179,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-performance.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 179,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant EWM_PLUGIN_URL not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-performance.php',
       'line' => 215,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-performance.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 215,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant EWM_PLUGIN_URL not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-performance.php',
       'line' => 216,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-performance.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 216,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant HOUR_IN_SECONDS not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-performance.php',
       'line' => 330,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-performance.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 330,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-render-core.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Offset \'mode\' on array{steps: mixed, design: mixed, triggers: mixed, wc_integration: mixed, display_rules: mixed, modal_id: float|int<1, max>|numeric-string, title: mixed} in isset() does not exist.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-render-core.php',
       'line' => 166,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-render-core.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 166,
       'nodeType' => 'PhpParser\\Node\\Expr\\Isset_',
       'identifier' => 'isset.offset',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Result of || is always true.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-render-core.php',
       'line' => 166,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-render-core.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 166,
       'nodeType' => 'PHPStan\\Node\\BooleanOrNode',
       'identifier' => 'booleanOr.alwaysTrue',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Offset \'custom_css\' on array{steps: mixed, design: mixed, triggers: mixed, wc_integration: mixed, display_rules: mixed, modal_id: float|int<1, max>|numeric-string, title: mixed, mode: \'formulario\'} in isset() does not exist.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-render-core.php',
       'line' => 171,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-render-core.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 171,
       'nodeType' => 'PhpParser\\Node\\Expr\\Isset_',
       'identifier' => 'isset.offset',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Result of || is always true.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-render-core.php',
       'line' => 171,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-render-core.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 171,
       'nodeType' => 'PHPStan\\Node\\BooleanOrNode',
       'identifier' => 'booleanOr.alwaysTrue',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Offset \'pattern\' on array{id: mixed, name: mixed, class: \'ewm-field-input\', placeholder: mixed, required?: \'required\'} in isset() does not exist.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-render-core.php',
       'line' => 540,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-render-core.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 540,
       'nodeType' => 'PhpParser\\Node\\Expr\\Isset_',
       'identifier' => 'isset.offset',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant EWM_PLUGIN_URL not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-render-core.php',
       'line' => 813,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-render-core.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 813,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant EWM_PLUGIN_URL not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-render-core.php',
       'line' => 820,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-render-core.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 820,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant EWM_PLUGIN_URL not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-render-core.php',
       'line' => 828,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-render-core.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 828,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant WP_PLUGIN_DIR not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'line' => 64,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 64,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant WP_PLUGIN_DIR not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'line' => 65,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 65,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Variable $config in empty() always exists and is not falsy.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'line' => 694,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 694,
       'nodeType' => 'PhpParser\\Node\\Expr\\Empty_',
       'identifier' => 'empty.variable',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Instantiated class WC_Coupon not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'line' => 913,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 913,
       'nodeType' => 'PhpParser\\Node\\Expr\\New_',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method get_discount_type() on an unknown class WC_Coupon.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'line' => 918,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 918,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method get_amount() on an unknown class WC_Coupon.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'line' => 919,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 919,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method get_usage_count() on an unknown class WC_Coupon.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'line' => 920,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 920,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method get_usage_limit() on an unknown class WC_Coupon.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'line' => 921,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 921,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Variable $context in empty() always exists and is not falsy.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'line' => 957,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 957,
       'nodeType' => 'PhpParser\\Node\\Expr\\Empty_',
       'identifier' => 'empty.variable',
       'metadata' => 
      array (
      ),
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant WP_PLUGIN_DIR not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'line' => 1235,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 1235,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant WP_PLUGIN_DIR not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'line' => 1236,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 1236,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    11 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Instantiated class WC_Coupon not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'line' => 1241,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 1241,
       'nodeType' => 'PhpParser\\Node\\Expr\\New_',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    12 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method EWM_REST_API::is_valid_wc_integration_config() is unused.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'line' => 1331,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 17,
       'nodeType' => 'PHPStan\\Node\\ClassMethodsNode',
       'identifier' => 'method.unused',
       'metadata' => 
      array (
      ),
    )),
    13 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method EWM_REST_API::is_valid_display_rules_config() is unused.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'line' => 1363,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 17,
       'nodeType' => 'PHPStan\\Node\\ClassMethodsNode',
       'identifier' => 'method.unused',
       'metadata' => 
      array (
      ),
    )),
    14 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method EWM_REST_API::is_valid_design_config() is unused.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'line' => 1407,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 17,
       'nodeType' => 'PHPStan\\Node\\ClassMethodsNode',
       'identifier' => 'method.unused',
       'metadata' => 
      array (
      ),
    )),
    15 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method EWM_REST_API::is_valid_triggers_config() is unused.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'line' => 1446,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 17,
       'nodeType' => 'PHPStan\\Node\\ClassMethodsNode',
       'identifier' => 'method.unused',
       'metadata' => 
      array (
      ),
    )),
    16 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method EWM_REST_API::get_modal_schema() is unused.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'line' => 1638,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 17,
       'nodeType' => 'PHPStan\\Node\\ClassMethodsNode',
       'identifier' => 'method.unused',
       'metadata' => 
      array (
      ),
    )),
    17 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method EWM_REST_API::get_form_submission_schema() is unused.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'line' => 1655,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 17,
       'nodeType' => 'PHPStan\\Node\\ClassMethodsNode',
       'identifier' => 'method.unused',
       'metadata' => 
      array (
      ),
    )),
    18 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function WC not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'line' => 1696,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 1696,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    19 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $timestamp of function date expects int|null, string given.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'line' => 2008,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 2008,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    20 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $timestamp of function date expects int|null, string given.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'line' => 2009,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 2009,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    21 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant WP_PLUGIN_DIR not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'line' => 2277,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 2277,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    22 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant WP_PLUGIN_DIR not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'line' => 2278,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 2278,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-shortcodes.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method EWM_Shortcodes::check_frequency_limit() is unused.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-shortcodes.php',
       'line' => 344,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-shortcodes.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 17,
       'nodeType' => 'PHPStan\\Node\\ClassMethodsNode',
       'identifier' => 'method.unused',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant DAY_IN_SECONDS not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-shortcodes.php',
       'line' => 379,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-shortcodes.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 379,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant WEEK_IN_SECONDS not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-shortcodes.php',
       'line' => 381,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-shortcodes.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 381,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant DAY_IN_SECONDS not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-shortcodes.php',
       'line' => 699,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-shortcodes.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 699,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant WEEK_IN_SECONDS not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-shortcodes.php',
       'line' => 701,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-shortcodes.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 701,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant MINUTE_IN_SECONDS not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-shortcodes.php',
       'line' => 703,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-shortcodes.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 703,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-submission-cpt.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant EWM_PLUGIN_URL not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-submission-cpt.php',
       'line' => 565,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-submission-cpt.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 565,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant OBJECT not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-submission-cpt.php',
       'line' => 784,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-submission-cpt.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 784,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-auto-injection.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant EWM_PLUGIN_URL not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-auto-injection.php',
       'line' => 359,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-auto-injection.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 359,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant EWM_PLUGIN_URL not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-auto-injection.php',
       'line' => 366,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-auto-injection.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 366,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Class WooCommerce not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'line' => 72,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 72,
       'nodeType' => 'PhpParser\\Node\\Expr\\Instanceof_',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function is_woocommerce not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'line' => 120,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 120,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function is_product not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'line' => 124,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 124,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function is_product_category not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'line' => 124,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 124,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function is_product_tag not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'line' => 124,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 124,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function is_shop not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'line' => 124,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 124,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function is_product not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'line' => 140,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 140,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function get_woocommerce_currency not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'line' => 172,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 172,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function WC not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'line' => 188,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 188,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function WC not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'line' => 215,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 215,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function wc_get_product not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'line' => 249,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 249,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    11 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Class WooCommerce not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'line' => 285,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 285,
       'nodeType' => 'PhpParser\\Node\\Expr\\Instanceof_',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function is_cart not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 88,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 88,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function is_checkout not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 88,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 88,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function is_woocommerce not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 88,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 88,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function wc_get_cart_url not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 107,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 107,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function wc_get_checkout_url not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 108,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 108,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Instantiated class WC_Coupon not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 181,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 181,
       'nodeType' => 'PhpParser\\Node\\Expr\\New_',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method get_code() on an unknown class WC_Coupon.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 184,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 184,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method get_description() on an unknown class WC_Coupon.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 185,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 185,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method get_discount_type() on an unknown class WC_Coupon.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 186,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 186,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method get_amount() on an unknown class WC_Coupon.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 187,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 187,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method get_usage_count() on an unknown class WC_Coupon.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 188,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 188,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    11 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method get_usage_limit() on an unknown class WC_Coupon.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 189,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 189,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    12 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function wc_get_product not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 221,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 221,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    13 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function WC not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 243,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 243,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    14 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function WC not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 254,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 254,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    15 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function WC not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 269,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 269,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    16 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function WC not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 270,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 270,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    17 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function WC not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 271,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 271,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    18 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function WC not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 272,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 272,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    19 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function WC not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 299,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 299,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    20 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function WC not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 332,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 332,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    21 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Anonymous function has an unused use $modal_id.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 411,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 411,
       'nodeType' => 'PhpParser\\Node\\Expr\\Closure',
       'identifier' => 'closure.unusedUse',
       'metadata' => 
      array (
      ),
    )),
    22 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function WC not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 437,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 437,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    23 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function WC not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 441,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 441,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    24 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function WC not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 447,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 447,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    25 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function WC not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 469,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 469,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    26 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function WC not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 475,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 475,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    27 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function WC not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 476,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 476,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    28 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function is_cart not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 514,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 514,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    29 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function is_checkout not found.',
       'file' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'line' => 514,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 514,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
); },
	'locallyIgnoredErrorsCallback' => static function (): array { return array (
); },
	'linesToIgnore' => array (
),
	'unmatchedLineIgnores' => array (
),
	'collectedDataCallback' => static function (): array { return array (
); },
	'dependencies' => array (
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/ewm-modal-cta.php' => 
  array (
    'fileHash' => '00bfbdcdd54e73b3590c0ff68bc44457c7d27d9f',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-general-auto-injection.php',
    ),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php' => 
  array (
    'fileHash' => 'd49aead0ed1a1d7351125a25aaa5af6212dfecb4',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/ewm-modal-cta.php',
    ),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-auto-injection.php' => 
  array (
    'fileHash' => 'da39a3ee5e6b4b0d3255bfef95601890afd80709',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-capabilities.php' => 
  array (
    'fileHash' => '0284ee3f4e061068d89c6f35191d5848213c6804',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/ewm-modal-cta.php',
      1 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
      2 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-shortcodes.php',
    ),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-general-auto-injection.php' => 
  array (
    'fileHash' => 'fe23ba5a6e47f508f0e73ede032dde111b2a8e97',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-meta-fields.php' => 
  array (
    'fileHash' => '1c2d24dcde381a72cbf322a2d7550e5d48b2935d',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/ewm-modal-cta.php',
      1 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php',
      2 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php',
    ),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-modal-cpt.php' => 
  array (
    'fileHash' => '556766f8ab321a864bf95d9d4ccbda589f5d3312',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/ewm-modal-cta.php',
      1 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
      2 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-submission-cpt.php',
    ),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-performance.php' => 
  array (
    'fileHash' => 'af784124d8f2946989f662f3ca4d2e0389a71295',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-render-core.php' => 
  array (
    'fileHash' => 'a28a44b71d998e2ea58a32e7e24008c0c0b67f46',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/ewm-modal-cta.php',
      1 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-general-auto-injection.php',
      2 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-performance.php',
      3 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-shortcodes.php',
      4 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-auto-injection.php',
    ),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php' => 
  array (
    'fileHash' => '67119a35338e39519a2a0a260ce268785ae8b754',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/ewm-modal-cta.php',
    ),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-shortcodes.php' => 
  array (
    'fileHash' => '81722847c098b1b32773922c4b2e99826673c4a6',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/ewm-modal-cta.php',
      1 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-performance.php',
    ),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-submission-cpt.php' => 
  array (
    'fileHash' => '50913d24d0f1072b0020a00e61556d41d18ea4d6',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/ewm-modal-cta.php',
      1 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php',
    ),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-auto-injection.php' => 
  array (
    'fileHash' => 'f2b4dd67adb1380df2234b3e6330107a8994c0a2',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php' => 
  array (
    'fileHash' => '278838fccadd8e2d464f42ab759244c39ce1006d',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce-endpoints.php' => 
  array (
    'fileHash' => 'b99e5611cc4000e2ccd449f5a88965dd57b9a66a',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/ewm-modal-cta.php',
    ),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php' => 
  array (
    'fileHash' => 'd172a10f2abeab487ec1fb4e3f955412bc6f8bdf',
    'dependentFiles' => 
    array (
    ),
  ),
),
	'exportedNodesCallback' => static function (): array { return array (
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/ewm-modal-cta.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ewm_init_core_components',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Initialize core components
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
      ),
       'attributes' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ewm_init_rest_api',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Initialize REST API endpoints
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
      ),
       'attributes' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ewm_modal_cta_activate',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Plugin activation hook
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
      ),
       'attributes' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ewm_modal_cta_deactivate',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Plugin deactivation hook
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
      ),
       'attributes' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ewm_modal_cta_load_textdomain',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Load plugin textdomain for translations
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
      ),
       'attributes' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ewm_modal_cta_enqueue_frontend_assets',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Enqueue frontend assets
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
      ),
       'attributes' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ewm_modal_cta_enqueue_admin_devpipe',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Enqueue DevPipe for admin development logging
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
      ),
       'attributes' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ewm_has_modal_shortcode',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Check if page has modal shortcode OR is a WooCommerce product page with applicable modals
 * CORREGIDO: Detecta shortcodes en contenido raw y procesado + páginas WC
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
      ),
       'attributes' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ewm_has_auto_injectable_modals',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Verificar si hay modales configurados para auto-inyección en la página actual
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
      ),
       'attributes' => 
      array (
      ),
    )),
    9 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ewm_simulate_modal_detection',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Simular detección de modales para la página actual (para encolado de assets)
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
      ),
       'attributes' => 
      array (
      ),
    )),
    10 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ewm_get_current_page_type',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Obtener tipo de página actual (helper function)
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
      ),
       'attributes' => 
      array (
      ),
    )),
    11 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ewm_should_modal_show_on_page',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Verificar si un modal debe mostrarse en la página actual (helper function)
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'config',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'page_type',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    12 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ewm_build_config_from_separate_fields',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Construir configuración desde campos separados (para compatibilidad)
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'modal_id',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-admin-page.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'EWM_Admin_Page',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Class for the Modal Builder admin page
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'map_special_page_value_to_id',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Mapea slugs especiales (\'home\', \'blog\', \'none\', \'all\') a su ID o valor lógico.
	 * Si es numérico, lo retorna como int. Si no es especial, retorna null.
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'value',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_instance',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener instancia singleton
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'add_admin_menu',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Agregar menú de administración
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'enqueue_admin_scripts',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Encolar scripts de administración
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'hook',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'render_modal_builder_page',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Renderizar página del Modal Builder
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'render_settings_page',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Renderizar página de configuraciones
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'render_analytics_page',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Renderizar página de analytics
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'save_modal_builder',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Guardar configuración del modal builder
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'save_global_settings',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Guardar configuraciones globales (modo de depuración de frecuencia)
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'load_modal_builder',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Cargar configuración del modal builder
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'preview_modal',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Generar vista previa del modal
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-capabilities.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'EWM_Capabilities',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Class to manage custom plugin capabilities
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_instance',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Get singleton instance
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'setup_capabilities',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Setup capabilities on activation
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'remove_capabilities_from_roles',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Remove capabilities from roles (for deactivation)
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'map_meta_capabilities',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Mapear meta capabilities
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'caps',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'cap',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user_id',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'args',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'filter_user_capabilities',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Filtrar capabilities del usuario
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'allcaps',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'caps',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'args',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'current_user_can_manage_modals',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Verificar si el usuario actual puede gestionar modales
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'current_user_can_view_submissions',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Verificar si el usuario actual puede ver envíos
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'current_user_can_manage_settings',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Verificar si el usuario actual puede gestionar configuraciones
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'current_user_can_view_analytics',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Verificar si el usuario actual puede ver analytics
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'current_user_can_edit_modal',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Verificar si el usuario puede editar un modal específico
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'modal_id',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'current_user_can_view_submission',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Verificar si el usuario puede ver un envío específico
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'submission_id',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        11 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_plugin_capabilities',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener capabilities del plugin
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        12 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_role_capabilities',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener capabilities por rol
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'role',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        13 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'is_plugin_capability',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Verificar si un capability es del plugin
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'capability',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        14 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'add_custom_capability',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Agregar capability personalizado
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'capability',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'roles',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        15 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'remove_custom_capability',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Remover capability personalizado
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'capability',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        16 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_capabilities_info',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener información de capabilities para debugging
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-general-auto-injection.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'EWM_General_Auto_Injection',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Clase para		if ( ! isset( $config[\'display_rules\'][\'user_roles\'] ) || empty( $conf	private function render_general_modal( $modal_data ) {
		$modal_id = $modal_data[\'id\'];
		$config = $modal_data[\'config\'];

		// Usar el sistema de renderizado existente pero con configuración generalplay_rules\'][\'user_roles\'] ) ) {
			return true; // Sin restricciones = permitir todos
		}

		$allowed_roles = $config[\'display_rules\'][\'user_roles\'];

		// Si \'all\' está en los roles permitidos, permitir a todosyección de modales en páginas generales
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_instance',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener instancia singleton
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'init',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Inicializar la clase
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'detect_current_page',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Detectar tipo de página actual y buscar modales aplicables
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register_shortcode_modal',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Registrar modal renderizado via shortcode (para evitar duplicados)
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'modal_id',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'inject_general_modals',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Inyectar modales generales en el footer
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_detected_modals',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener modales detectados (para debugging)
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_current_page_type',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener tipo de página actual (para debugging)
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-meta-fields.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'EWM_Meta_Fields',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Class to manage flexible meta fields with JSON and serialized support
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'resolve_to_id',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	* Resolve a special page/category slug or numeric value to its ID or logical value.
	* Returns int for numeric values, or null if not found.
	*/',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'value',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_instance',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_supported_field_types',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener tipos de campo soportados
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_meta',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener meta field con fallback
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'post_id',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'meta_key',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'default',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-modal-cpt.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'EWM_Modal_CPT',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Clase para manejar el Custom Post Type de modales
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::__set_state(array(
           'constants' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::__set_state(array(
               'name' => 'POST_TYPE',
               'value' => '\'ew_modal\'',
               'attributes' => 
              array (
              ),
            )),
          ),
           'public' => true,
           'private' => false,
           'final' => false,
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Post type name
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_instance',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener instancia singleton
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register_post_type',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Registrar el Custom Post Type
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register_meta_fields',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Registrar meta fields
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'add_meta_boxes',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Agregar meta boxes
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'render_config_meta_box',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Renderizar meta box de configuración
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'post',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'render_shortcode_meta_box',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Renderizar meta box de shortcode
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'post',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'save_meta_fields',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Guardar meta fields
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'post_id',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'add_custom_columns',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Agregar columnas personalizadas
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'columns',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'custom_column_content',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Contenido de columnas personalizadas
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'column',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'post_id',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_modal_config',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener configuración de modal con flexibilidad de almacenamiento
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'modal_id',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        11 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'save_modal_config',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Guardar configuración de modal con flexibilidad de almacenamiento
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'modal_id',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'config',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-performance.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'EWM_Performance',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Clase para optimizaciones de performance
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_instance',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener instancia singleton
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'setup_caching',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Configurar sistema de cache
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'conditional_asset_loading',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Carga condicional de assets
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'lazy_load_modals',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Lazy loading de modales
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'add_async_defer_attributes',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Agregar atributos async/defer a scripts
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'tag',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'handle',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'add_preload_hints',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Agregar hints de precarga
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'cache_modal_config',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Cache de configuración de modal
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'config',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'modal_id',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'clear_modal_cache',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Limpiar cache de modal
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'post_id',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'post',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'optimize_queries',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Optimizar consultas de base de datos
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'add_version_to_assets',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Agregar versión a assets
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'src',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'handle',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_performance_stats',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener estadísticas de performance
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        11 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'clear_all_cache',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Limpiar todo el cache del plugin
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        12 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_performance_config',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener configuración de performance
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-render-core.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'EWM_Render_Core',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Clase para el motor de renderizado universal
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_instance',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener instancia singleton
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'render_modal',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Función principal de renderizado (usada por shortcodes)
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'modal_id',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'config',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'render_modal_scripts',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Renderizar scripts del modal en el footer
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'add_modal_styles',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Agregar estilos del modal en el head
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_rendered_modals',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener modales renderizados (solo IDs)
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_rendered_modals_info',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener información completa de modales renderizados
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'ewm_render_modal_core',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Función global para renderizado universal
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'modal_id',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'config',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => true,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-rest-api.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'EWM_REST_API',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Clase para manejar los endpoints REST API del plugin
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register_test_modal_visibility_route',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Endpoint de testing: Verificar si un modal específico se mostraría para un producto dado
	 * GET /ewm/v1/test-modal-visibility/(?P<modal_id>\\d+)/(?P<product_id>\\d+)
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'test_modal_visibility',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Callback para test_modal_visibility
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register_user_profile_route',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Endpoint: /user-profile
	 * Devuelve datos básicos del usuario autenticado
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_user_profile',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Callback para /user-profile
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::__set_state(array(
           'constants' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::__set_state(array(
               'name' => 'NAMESPACE',
               'value' => '\'ewm/v1\'',
               'attributes' => 
              array (
              ),
            )),
          ),
           'public' => true,
           'private' => false,
           'final' => false,
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Namespace de la API
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_instance',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener instancia singleton
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register_routes',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Registrar todas las rutas REST
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_modals',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener lista de modales
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_modal',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener modal específico
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'create_modal',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Crear nuevo modal
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'submit_form',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Enviar formulario
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        11 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'update_modal',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Actualizar modal existente
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        12 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'preview_modal',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Generar vista previa del modal
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        13 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_wc_coupons',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener cupones de WooCommerce
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        14 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_active_modals_endpoint',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Endpoint para obtener modales activos (Modal Injection System)
	 *
	 * @param WP_REST_Request $request La petición REST.
	 * @return WP_REST_Response|WP_Error Respuesta con modales activos o error.
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        15 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'check_admin_permissions',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Verificar permisos para administración
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        16 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'check_permissions',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Verificar permisos
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        17 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'debug_cart_status',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Endpoint para debugging del estado del carrito
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        18 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'check_coupon_applied',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Endpoint para verificar si un cupón específico está aplicado
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        19 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_cart_info_endpoint',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Endpoint SOLO LECTURA para información del carrito
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        20 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'try_cart_access_strategies',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Endpoint para probar diferentes estrategias de acceso al carrito
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        21 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'test_coupon_detection',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Endpoint simple para probar detección de cupones
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-shortcodes.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'EWM_Shortcodes',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Clase para manejar shortcodes del plugin
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_instance',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener instancia singleton
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register_shortcodes',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Registrar todos los shortcodes
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'render_modal_shortcode',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Renderizar shortcode principal [ew_modal]
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'atts',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'content',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'render_trigger_shortcode',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Renderizar shortcode de trigger [ew_modal_trigger]
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'atts',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'content',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'render_stats_shortcode',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Renderizar shortcode de estadísticas [ew_modal_stats]
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'atts',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'content',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'has_modal_shortcode',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Verificar si hay shortcodes de modal en el contenido
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'content',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_modal_ids_from_content',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener IDs de modales desde shortcodes en el contenido
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'content',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'render_debug_shortcode',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Shortcode de debug temporal [ew_debug]
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'atts',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'content',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_shortcodes_info',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener información de shortcodes para debugging
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register_modal_view',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * AJAX: Registrar visualización del modal
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'clear_modal_transients',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * AJAX: Limpiar transients del modal
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        11 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'check_modal_frequency',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * AJAX: Verificar si el modal puede mostrarse según la configuración de frecuencia
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-submission-cpt.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'EWM_Submission_CPT',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Clase para manejar el Custom Post Type de leads generados por formularios
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::__set_state(array(
           'constants' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::__set_state(array(
               'name' => 'POST_TYPE',
               'value' => '\'ewm_submission\'',
               'attributes' => 
              array (
              ),
            )),
          ),
           'public' => true,
           'private' => false,
           'final' => false,
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Post type name
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_instance',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener instancia singleton
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register_post_type',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Registrar el Custom Post Type
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register_meta_fields',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Registrar meta fields
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'add_meta_boxes',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Agregar meta boxes
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'render_details_meta_box',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Renderizar meta box de detalles
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'post',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'render_data_meta_box',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Renderizar meta box de datos del formulario
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'post',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'render_meta_box',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Renderizar meta box de información técnica
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'post',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'save_meta_fields',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Guardar meta fields
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'post_id',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'add_custom_columns',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Agregar columnas personalizadas
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'columns',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'custom_column_content',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Contenido de columnas personalizadas
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'column',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'post_id',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        11 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'modify_row_actions',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Modificar acciones de fila
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'actions',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'post',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        12 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'enqueue_admin_styles',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Encolar estilos de administración
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'hook',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        13 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'add_bulk_actions',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Agregar acciones masivas personalizadas
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'bulk_actions',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        14 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handle_bulk_actions',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Manejar acciones masivas personalizadas
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'redirect_to',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'doaction',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'post_ids',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        15 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'show_bulk_action_notices',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Mostrar mensajes de acciones masivas
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        16 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'create_submission',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Crear nuevo lead de formulario
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'modal_id',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'form_data',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'step_data',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        17 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'update_existing_submission_titles',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Actualizar títulos de envíos existentes que están sin título
	 * Función utilitaria para migrar envíos antiguos al nuevo formato
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        18 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'maybe_trigger_title_update',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Hook para ejecutar actualización de títulos en admin
	 * Se puede llamar desde una página de admin o vía wp-cli
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-auto-injection.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'EWM_WC_Auto_Injection',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Clase para auto-inyección de modales WooCommerce
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_instance',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener instancia singleton
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'init',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Inicializar la clase
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'detect_product_page',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Detectar si estamos en una página de producto y obtener modales aplicables
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'inject_wc_modals',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Inyectar modales WooCommerce en el footer
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'enqueue_scripts',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Encolar scripts necesarios
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_detected_modals',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener modales detectados (para debugging)
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_current_product_id',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener ID del producto actual (para debugging)
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-wc-compatibility-manager.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'EWM_WC_Compatibility_Manager',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Clase para manejar la compatibilidad con WooCommerce
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_instance',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Obtener instancia singleton
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'is_woocommerce_active',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Verificar si WooCommerce está activo y disponible
     * 
     * @return bool True si WooCommerce está disponible
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'is_wc_function_available',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Verificar si una función específica de WooCommerce está disponible
     * 
     * @param string $function_name Nombre de la función
     * @return bool True si la función está disponible
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'function_name',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'is_wc_page',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Verificar si estamos en una página de WooCommerce
     * 
     * @return bool True si estamos en una página WC
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'is_product_page',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Verificar si estamos en una página de producto
     * 
     * @return bool True si estamos en una página de producto
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_current_product_id',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Obtener ID del producto actual de forma segura
     * 
     * @return int|false ID del producto o false si no está disponible
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_currency',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Obtener moneda de WooCommerce de forma segura
     * 
     * @return string Código de moneda o fallback
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'is_cart_available',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Verificar si el carrito está disponible
     * 
     * @return bool True si el carrito está disponible
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'apply_coupon_safe',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Aplicar cupón de forma segura
     * 
     * @param string $coupon_code Código del cupón
     * @return array Resultado de la operación
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'coupon_code',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_product_info_safe',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Obtener información de un producto de forma segura
     * 
     * @param int $product_id ID del producto
     * @return array|false Información del producto o false
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'product_id',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'refresh_cache',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Refrescar cache (llamado en plugins_loaded)
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        11 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_compatibility_status',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Obtener estado de compatibilidad para debugging
     * 
     * @return array Estado completo de compatibilidad
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        12 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'clear_cache',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Limpiar cache manualmente
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        13 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'show_wc_compatibility_notices',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Mostrar notificaciones de compatibilidad en el admin
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce-endpoints.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'EWM_WooCommerce_Endpoints',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register_routes',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_coupons',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/plugins/wp-content/plugins/ewm-modal-cta/includes/class-ewm-woocommerce.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'EWM_WooCommerce',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Clase para integración con WooCommerce
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_instance',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener instancia singleton
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'setup_hooks',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Configurar hooks
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'enqueue_wc_scripts',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Encolar scripts de WooCommerce
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register_rest_routes',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Registrar rutas REST API
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_coupons',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener cupones de WooCommerce
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_products',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener productos de WooCommerce
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_cart_data',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener datos del carrito
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handle_cart_updated',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Manejar actualización del carrito
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handle_add_to_cart',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Manejar agregar al carrito
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'cart_item_key',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'product_id',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'quantity',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'variation_id',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            4 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'variation',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            5 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'cart_item_data',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'apply_coupon',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Aplicar cupón via AJAX
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'ajax_add_to_cart',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Agregar al carrito via AJAX
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        11 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'maybe_show_checkout_modal',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Mostrar modal en checkout si está configurado
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        12 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'add_cart_abandonment_script',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Agregar script de abandono de carrito
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        13 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'modal_has_wc_integration',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Verificar si un modal tiene integración WC habilitada
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'modal_id',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        14 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get_modal_wc_config',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
	 * Obtener configuración WC de un modal
	 */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'modal_id',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
); },
];
