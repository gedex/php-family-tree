<?php

$default_callback = function(&$val, $key) {
    // Uppercase defined as global constant
    if (preg_match('/^[A-Z0-9_]+$/', $key)) {
        define($key, $val);
    }
    // Write config via Configure::write
    else {
        Configure::write($key, $val);
    }
};

return array(
    'core' => array(
        'debug' => 0,
        'Error' => array(
            'handler' => 'ErrorHandler::handleError',
            'level' => E_ALL & ~E_DEPRECATED,
            'trace' => true,
        ),
        'Exception' => array(
            'handler' => 'ErrorHandler::handleException',
            'renderer' => 'ExceptionRenderer',
            'log' => true,
        ),
        'App.encoding' => 'UTF-8',
        'LOG_ERROR' => LOG_ERR,
        'Session' => array(
            'defaults' => 'php',
        ),

        'Security.level' => 'medium',
        'Security.salt' => 'DYhG93b0qyJfIxfs2guVoUubWwvniR2G0FgaC9mi',
        'Security.cipherSeed' => '76859309657453542496749683645',

        'Acl.classname' => 'DbAcl',
        'Acl.database' => 'default',

        // Specialized key-name, in form '__key_name___', for callback in which the configs will be extracted
        // in Gedex_Env_Config->extract. Please keep in mind the __callback__ will be overiden by sub-env configs
        // if one is defined.
        '__callback__' => $default_callback,
    ),

    'database' => array(
        'datasource' => 'Database/Mysql',
        'persistent' => false,
        'host' => 'localhost',
        'login' => 'root',
        'password' => '',
        'database' => 'php_family_tree',
        'prefix' => '',
        'encoding' => 'utf8',
    ),

    /**
     * App-specific configuration
     */
    'app' => array(
        'MAX_ROWS' => 10,
        'APP_NAME' => 'Family Tree',
        'BASE_URL' => '/',
        'APP_LOCALE' => 'Family Tree',
        '__callback__' => $default_callback,
    ),
);
