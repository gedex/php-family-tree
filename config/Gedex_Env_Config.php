<?php
/**
 * Cake App Env-specific Configuration Loader.
 * Copied from Wpized_Env_Config, with modifications, provided in WPized framework (x-team) which
 * is courtesy of Weston Ruter <weston@x-team.com>.
 *
 * @author Akeda Bagus <admin@gedex.web.id>
 * @version 0.1
 *
 * This configuration loader iterates over all *.env.php files listed in
 * the provided config directory; these files each must take the form of
 * "<?php return array(...) ?>". There should be one default.env.php which
 * has options that are common to all environments, but then there can be
 * environment-specific configurations which are activated by specifying
 * environment's name in config/active-env file. The environment-specific
 * configurations are merged on top of default.env.php.
 */

class Gedex_Env_Config {
/**
 * Object
 * @var object
 */
    static $instance = null;

/**
 * Configuration directory containing *.env.php.
 * @var string
 */
    public $dir;

/**
 * Configurations for all defined-environments.
 * @var array
 */
    public $configs = array();

    public $active_env;

    public $active_config;

/**
 * @param string $dir configuration dir.
 */
    public function __construct($dir = null) {
        if ( !is_null(self::$instance) ) {
            throw new Exception("Gedex_Env_Config is a singleton and may only be instantiated once");
        }

        if ($dir) {
            $this->dir = $dir;
        } else {
            if ( !defined('PROJECT') ) {
                define('PROJECT', rtrim(realpath(dirname(__FILE__) . '/..'), '/'));
            }
            $this->dir = PROJECT . '/config';
        }

        // Get active environment.
        if ( !file_exists("{$this->dir}/active-env") ) {
            throw new Exception("Missing required {$this->dir}/active-env file. This file must contain the name of the currently active environment");
        }
        $this->active_env = trim(file_get_contents("{$this->dir}/active-env"));
        if ( !file_exists("{$this->dir}/{$this->active_env}.env.php") ) {
            throw new Exception("Missing {$this->dir}/{$this->active_env}.env.php.");
        }

        // Load default configuration.
        $default_config = array();
        if ( file_exists("{$this->dir}/default.env.php") ) {
            $default_config = require("{$this->dir}/default.env.php");
        }

        // Get all environment configs
        foreach ( glob("{$this->dir}/*.env.php") as $config_file ) {
            $env_name = basename($config_file, '.env.php');

            // Skip default env.
            if ($env_name === 'default') continue;

            // Skip any sub-environment overrides (like local-mine or production-overrides)
            if ( strpos($env_name, '-') !== false ) continue;

            // Save the config after merging it with default.env.php
            $config = require($config_file);
            $this->configs[$env_name] = array();
            foreach ($default_config as $key => $val) {
                $this->configs[$env_name][$key] = array_merge(
                    $val,
                    isset($config[$key]) ? $config[$key] : array()
                );
            }

            // Validates configurations after merged with default env.
            if ( !$this->_isValidConfig($this->configs[$env_name]) ) {
                throw new Exception("Invalid configuration for `{$env_name}` environment.");
            }
        }

        $this->active_config = &$this->configs[$this->active_env];
    }

/**
 * Get Gedex_Env_Config's instance.
 * @return object
 */
    static function getInstance() {
        if ( is_null(self::$instance) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

/**
 * Get configuration from given environment's name.
 * @param string $env_name Environment's name.
 * @return array Array of environment's configurations.
 */
    public function __get($env_name) {
        return $this->configs[$env_name];
    }

/**
 * Validates given $config.
 * @param array $config
 * @return bool
 */
    protected function _isValidConfig($config) {
        if (!is_array($config)) return false;

        $mustKeys = array('core', 'database');
        foreach ($mustKeys as $key) {
            if ( !isset($config[$key]) ) return false;
        }

        return true;
    }

/**
 * Load the configuration constants and variables into the environment.
 *
 * @param string $section_name Configuration section such as core, database, etc.
 */
    public function extract($section_name = null) {
        foreach ($this->active_config as $config_section => $config) {
            if ($section_name && $config_section !== $section_name) {
                continue;
            } else {
                // Apply the callback for loading the config into environment.
                if ( isset($config['__callback__']) ) {
                    $callback = $config['__callback__'];
                    unset($config['__callback__']);
                    array_walk($config, $callback);
                }

                if ($section_name) return $config;
            }
        }
        return $this->active_config;
    }
}