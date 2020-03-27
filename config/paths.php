<?php
/**
 * Use the DS to separate the directories in other defines
 */
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * These defines should only be edited if you have cake installed in
 * a directory layout other than the way it is distributed.
 * When using custom settings be sure to use the DS and do not add a trailing DS.
 */

/**
 * The full path to the directory which holds "src", WITHOUT a trailing DS.
 */
define('ROOT', dirname(__DIR__));

/**
 * The actual directory name for the application directory. Normally
 * named 'src'.
 */
define('APP_DIR', 'src');

/**
 * Path to the application's directory.
 */
define('APP', ROOT . DS . APP_DIR . DS);

/**
 * Path to the config directory.
 */
define('CONFIG', ROOT . DS . 'config' . DS);

/**
 * Path to the temporary files directory.
 */
define('TMP', ROOT . DS . 'tmp' . DS);

/**
 * Path to the logs directory.
 */
define('LOGS', ROOT . DS . 'logs' . DS);

/**
 * Path to the Web directory.
 */
define('WEB', ROOT . DS . 'web' . DS);

/**
 * The actual directory name for the application directory. Normally
 * named 'src'.
 */
define('APP_WEB', 'web');

define('DATABASE', 'domain');

define('CAMOO_CORE_INCLUDE_PATH', ROOT . DS . 'vendor' . DS . 'camoo' . DS . 'framework');

/**
 * Path to the cake directory.
 */
define('CORE_PATH', CAMOO_CORE_INCLUDE_PATH . DS);
define('CAMOO', CORE_PATH . 'src' . DS);

/**
 * Path to the cache files directory.
 */
define('CACHE', ROOT . DS . 'tmp' . DS. 'cache' .DS);
