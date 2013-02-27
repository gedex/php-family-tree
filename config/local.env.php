<?php
$_env = array(
    'core' => array(
        'debug' => 2,
    ),
);

// Allow these default dev settings to be overridden locally by a config that is not committed
if( file_exists(dirname(__FILE__) . '/local-mine.env.php') ){
    $_mine = require(dirname(__FILE__) . '/local-mine.env.php');
    foreach ($_env as $key => $val) {
        $_env[$key] = array_merge(
            $_env[$key],
            isset($_mine[$key]) ? $_mine[$key] : array()
        );
    }
}

return $_env;
