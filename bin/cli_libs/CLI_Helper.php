<?php
/**
 * CLI Helper copied, with modification, from CakePHP's Shell.
 */
class CLI_Helper {
/**
 * stdout Object.
 * @var ConsoleOutput
 */
    public $stdout;

/**
 * stdin object.
 * @var ConsoleOutput
 */
    public $stderr;

/**
 * stdin object.
 * @var ConsoleInput
 */
    public $stdin;

    public function __construct($stdout = 'php://stdout', $stderr = 'php://stderr', $stdin = 'php://stdin') {
        $this->stdout = new ConsoleOutput($stdout);
        $this->stderr = new ConsoleOutput($stderr);
        $this->stdin = new ConsoleInput($stdin);
    }

/**
 * Prompts the user for input, and returns it.
 *
 * @param string $prompt Prompt text.
 * @param string|array $options Array or string of options.
 * @param string $default Default input value.
 * @return mixed Either the default value, or user-provided input.
 */
    public function in($prompt, $options = null, $default = null) {
        $originalOptions = $options;
        $in = $this->_getInput($prompt, $originalOptions, $default);

        if ($options && is_string($options) ) {
            if ( strpos($options, ',') ) {
                $options = explode(',' , $options);
            } else if ( strpos($options, '/') ) {
                $options = explode('/', $options);
            } else {
                $options = array($options);
            }
        }
        if (is_array($options)) {
            $options = array_merge(
               array_map('strtolower', $options),
               array_map('strtoupper', $options),
               $options
            );
            while ($in === '' || !in_array($in, $options)) {
                $this->_getInput($prompt, $originalOptions, $default);
            }
        }
        return $in;
    }

/**
 * Prompts the user for input, and returns it.
 * @param string $prompt Prompt text.
 * @param string|array $options Array or string of options.
 * @param string $default Default input value.
 * @return mixed Either the default value, or the user-provided input.
 */
    protected function _getInput($prompt, $options, $default) {
        if (!is_array($options)) {
            $printOptions = '';
        } else {
            $printOptions = '(' . implode('/', $options) . ')';
        }

        if ($default === null) {
            $this->stdout->write('<question>' . $prompt . '</question>' . " $printOptions \n" . '> ', 0);
        } else {
            $this->stdout->write('<question>' . $prompt . '</question>' . " $printOptions \n" . "[$default] > ", 0);
        }
        $result = $this->stdin->read();

        if ($result === false) {
            $this->_stop(1);
        }
        $result = trim($result);

        if ($default !== null && ($result === '' || $result === null)) {
            return $default;
        }
        return $result;
    }

/**
 * Outputs a single or multiple messages to stdout. If no parameters
 * are passed outputs just a newline.
 *
 * @param string|array $message A string or a an array of strings to output
 * @param integer $newlines Number of newlines to append
 * @return integer|boolean Returns the number of bytes returned from writing to stdout.
 */
    public function out($message = null, $newlines = 1) {
        return $this->stdout->write($message, $newlines);
    }

/**
 * Outputs a single or multiple error messages to stderr. If no parameters
 * are passed outputs just a newline.
 *
 * @param string|array $message A string or a an array of strings to output
 * @param integer $newlines Number of newlines to append
 * @return void
 */
    public function err($message = null, $newlines = 1) {
        $this->stderr->write($message, $newlines);
    }

/**
 * Returns a single or multiple linefeeds sequences.
 *
 * @param integer $multiplier Number of times the linefeed sequence should be repeated
 * @return string
 */
    public function nl($multiplier = 1) {
        return str_repeat(ConsoleOutput::LF, $multiplier);
    }

/**
 * Outputs a series of minus characters to the standard output, acts as a visual separator.
 *
 * @param integer $newlines Number of newlines to pre- and append
 * @param integer $width Width of the line, defaults to 63
 * @return void
 */
    public function hr($newlines = 0, $width = 63) {
        $this->out(null, $newlines);
        $this->out(str_repeat('-', $width));
        $this->out(null, $newlines);
    }

/**
 * Displays a formatted error message
 * and exits the application with status code 1
 *
 * @param string $title Title of the error
 * @param string $message An optional error message
 * @return void
 */
    public function error($title, $message = null) {
        $this->err(sprintf('<error>Error:</error> %s', $title));

        if (!empty($message)) {
            $this->err($message);
        }
        $this->_stop(1);
    }

/**
 * Clear the console
 *
 * @return void
 */
    public function clear() {
        if (DS === '/') {
            passthru('clear');
        } else {
            passthru('cls');
        }
    }

/**
 * Stop execution of the current script.  Wraps exit() making
 * testing easier.
 *
 * @param integer|string $status see http://php.net/exit for values
 * @return void
 */
    protected function _stop($status = 0) {
        exit($status);
    }
}
