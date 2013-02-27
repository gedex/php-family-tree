<?php

$_env = array(
    'core' => array(
        'debug' => 0,
        'Security.salt' => 'st491n93nv',
        'Security.cipherSeed' => '12312313',
    ),
    'app' => array(
        'DOMAIN_CURRENT_SITE' => 'staging.registration.utschool.sch.id',
    ),
    'database' => array(
        'login' => '__DB_USER__',
        'password' => '__DB_PASSWORD__',
    ),
);

// Allow these default dev settings to be overridden locally by a config that is not committed
if( file_exists(dirname(__FILE__) . '/staging-mine.env.php') ){
    $_mine = require(dirname(__FILE__) . '/staging-mine.env.php');
    foreach ($_env as $key => $val) {
        $_env[$key] = array_merge(
            $_env[$key],
            isset($_mine[$key]) ? $_mine[$key] : array()
        );
    }
}

return $_env;
