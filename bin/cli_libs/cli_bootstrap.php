<?php
/**
 * Bootstrap before any cli commands.
 */

define('APP_DIR', 'app');
define('DS', DIRECTORY_SEPARATOR);
define('PROJECT', rtrim(realpath(dirname(__FILE__) . '/../..'), '/')); // Refers to the top project/repo root directory.
define('ROOT', PROJECT . DS . 'docroot'); // Refers to root of CakePHP directory, which is docroot
define('WEBROOT_DIR', 'webroot');
define('WWW_ROOT', ROOT . DS . APP_DIR . DS . WEBROOT_DIR . DS);

/**
 * This only needs to be changed if the "cake" directory is located
 * outside of the distributed structure.
 * Full path to the directory containing "cake". Do not add trailing directory separator
 */
if (!defined('CAKE_CORE_INCLUDE_PATH')) {
    define('CAKE_CORE_INCLUDE_PATH', ROOT . DS . 'lib');
}

require PROJECT . '/bin/cli_libs/ConsoleOutput.php';
require PROJECT . '/bin/cli_libs/ConsoleInput.php';
require PROJECT . '/bin/cli_libs/CLI_Helper.php';
require PROJECT . '/bin/cli_libs/cli_utilities.php';
require PROJECT . '/config/Gedex_Env_Config.php';

return new CLI_Helper();