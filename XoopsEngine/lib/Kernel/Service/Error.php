<?php
/**
 * Kernel service
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Xoops Engine http://www.xoopsengine.org/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         Kernel/Service
 * @version         $Id$
 */

namespace Kernel\Service;

/**
 * Error Handler main class file
 *
 * The error handler catches PHP errors and exceptions and optionally sends
 * information about them to the logger service.
 * Physical paths that would be part of the error messages are converted to prevent
 * path disclosure on production servers.
 */

define('ERROR_REPORTING_PRODUCTION', 0);    // Production mode, no error display
define('ERROR_REPORTING_DEVELOPMENT', -1);  // Development mode, all possible
define('ERROR_REPORTING_DEBUG', E_ALL & ~ (E_DEPRECATED | E_USER_DEPRECATED | E_NOTICE));   // Debug/test mode, all errors except deprecated/notice messages

class Error extends ServiceAbstract
{
    protected $options = array(
        // If this handler should catch errors or not
        'catchErrors'       => false,
        //If this handler should catch exceptions or not
        'catchExceptions'   => false,
        //Which PHP errors are reported
        'errorReporting'    => false
    );

    /**
     * The reporting level that was set before the handler has been activated
     * @var integer
     * @access private
     */
    protected $oldErrorReporting = false;

    /**
     * How errors are reported in the message
     * @var string[]
     */
    protected $errorNames = array(
        E_ERROR             => 'Error',
        E_USER_ERROR        => 'Error',
        E_PARSE             => 'Parse error',
        E_WARNING           => 'Warning',
        E_USER_WARNING      => 'Warning',
        E_NOTICE            => 'Notice',
        E_USER_NOTICE       => 'Notice',
        E_DEPRECATED        => 'Deprecated',
        E_USER_DEPRECATED   => 'Deprecated',
        E_STRICT            => 'Suggestion',
        E_RECOVERABLE_ERROR => 'Fatal',
        -1 => 'Exception',
        //-2 => 'Smarty'
    );

    protected $epMap = array(
        "production"    => ERROR_REPORTING_PRODUCTION,
        "qa"            => ERROR_REPORTING_DEVELOPMENT,
        "debug"         => ERROR_REPORTING_DEBUG,
        "development"   => ERROR_REPORTING_DEVELOPMENT
    );

    protected $errors = array();

    protected $fatalTag;

    /**
     * Initializes this instance
     */
    public function __construct($options = array())
    {
        parent::__construct($options);
        if (!isset($this->options["errorReporting"]) || $this->options["errorReporting"] === false) {
            $this->options["errorReporting"] = isset($this->epMap[\XOOPS::config('environment')])
                                                ? $this->epMap[\XOOPS::config('environment')]
                                                : $this->epMap['development'];
        }
        $this->activateErrorHandling();
        \XOOPS::registry("error", $this);
        return true;
    }

    public function error_reporting($errorReporting = null)
    {
        if ($errorReporting !== null) {
            $this->options["errorReporting"] = $errorReporting;
            $this->oldErrorReporting = error_reporting($this->options["errorReporting"]);
        }
        return $this->options["errorReporting"];
    }

    /**
     * Enable/disable the error handling functionality.
     *
     * When set to active, the error handler set the php error reporting level to E_ALL and uses its own
     * $errorReporting property to mask the errors to report to ensure @ operator still works :-).
     * @param bool    $enable        Whether to enable or disable the error handler
     */
    public function activateErrorHandling($enable = null)
    {
        $enable = (null === $enable) ? $this->active : $enable;
        if ($enable) {
            if (!$this->options['catchErrors'] ) {
                set_error_handler(array(&$this, 'handleError'));
                $this->options['catchErrors'] = true;
            }
            if (!$this->options['catchExceptions']) {
                // Set exception handler for uncaught exceptions before any real execution
                set_exception_handler(array(&$this, 'handleException'));
                $this->options['catchExceptions'] = true;
            }
            $this->oldErrorReporting = error_reporting($this->options["errorReporting"]);
            // Catch fatal error
            $this->catchFatal();
        } else {
            if ($this->options['catchErrors']) {
                restore_error_handler();
                $this->options['catchErrors'] = false;
            }
            if ($this->options['catchExceptions']) {
                restore_exception_handler();
                $this->options['catchExceptions'] = false;
            }
            error_reporting($this->oldErrorReporting);
        }
        return $this->options['catchErrors'];
    }

    /**
     * Error handler (called by PHP on error)
     */
    public function handleError($errno, $errstr, $errfile, $errline)
    {
        if ($this->options['catchErrors'] && ($errno & $this->options["errorReporting"])) {
            $name = isset($this->errorNames[$errno])
                    ? $this->errorNames[$errno]
                    : 'Unknown';
            $this->errors[] = array(
                "time"      => microtime(true),
                "message"   => "{$name}: " . $this->sanitizePaths($errstr) . " in " . $this->sanitizePaths($errfile) . " on line {$errline}",
                "code"      => $errno);
        }
    }

    /**
     * Exception handler (called by PHP on exception triggered)
     */
    public function handleException(\Exception $exception)
    {
        if ($this->options['catchExceptions']) {
            $file = $this->sanitizePaths($exception->getFile());
            $line = $exception->getLine();
            $errstr = $exception->getMessage();
            $trace = $this->sanitizePaths($exception->getTraceAsString());
            $message = "Exception: {$errstr} in {$file} on line {$line}";
            $message .= "<pre>{$trace}</pre>";
            $this->errors[] = array(
                "time"      => microtime(true),
                "message"   => $message,
                "code"      => E_ERROR
            );
        }
    }

    public function shutdown()
    {
        if ($logger = \XOOPS::registry("logger")) {
            $logger->flushError();
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    private function sanitizePaths($str)
    {
        static $paths;

        if (!isset($paths)) {
            // Loads all path settings from host data
            $paths = \XOOPS::host()->get("paths");
            $lens = array();
            foreach ($paths as $root => $v) {
                $lens[] = strlen($v[0]);
            }
            // Sort the paths by their lengths in reverse
            array_multisort($lens, SORT_NUMERIC, SORT_DESC, $paths);
        }
        if (DIRECTORY_SEPARATOR != '/') {
            $str = str_replace(DIRECTORY_SEPARATOR, '/', $str);
        }
        foreach ($paths as $root => $v) {
            $str  = str_replace($v[0] . '/', "{$root}/", $str);
        }
        return $str;
    }

    /**
     * Catch fatal error
     *
     * Fatal errors are not able to catch via custom error handler, thus ob functions are used to collect and parse fatal errors
     */
    protected function catchFatal()
    {
        // It is impossible to catch fatal errors in legacy architecture since parse errors are disclosed directly prior to error handler service
        // i.e. THIS method is not called if a parse error occurs in legacy module
        // Before a good solution is found, we disable it
        return;

        if (isset($this->fatalTag)) {
            return;
        }

        $this->fatalTag = \XOOPS::config("identifier") . "FatalError";
        ini_set('error_prepend_string', "<" . $this->fatalTag . ">");
        ini_set('error_append_string', "</" . $this->fatalTag . ">");

        //Start an output buffer with a filter callback
        ob_start(array($this, "parseFatal"));
    }

    public function parseFatal($buffer)
    {
        //$buffer = "<" . $this->fatalTag . ">" . "Error 1" . "</" . $this->fatalTag . ">";
        if (preg_match('|<' . $this->fatalTag . '>(.*)</' . $this->fatalTag . '>|s', $buffer, $matches)) {
            $errors = "<h2>Fatal error!</h2>";
            /*
            if ($error = error_get_last()) {
                $errors .= "<p>" . $this->errorNames[$error["type"]] . ": " . $error["message"] . "</p>";
            } else {
                $errors .= "<p>" . $this->sanitizePaths($matches[1]) . "</p>";
            }
            */
            $errors .= "<p>" . $this->sanitizePaths($matches[1]) . "</p>";
            return $errors;
        }

        return false;
    }
}