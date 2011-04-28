<?php
/**
 * XOOPS kernel engine factory
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Xoops Engine http://www.xoopsengine.org
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         Xoops_Kernel
 * @version         $Id$
 */

/**
 * Xoops Engine Factory
 *
 * @author      Taiwen Jiang <phppp@users.sourceforge.net>
 */
class XOOPS
{
    /**
     * Lib root path
     * @var string
     * @access public
     */
    const ROOT = XOOPS_PATH;

    /**
     * Reference to default engine
     * @var {@Kernel\EngineInterface}
     * @access private
     */
    private static $engine;

    /**
     * Reference to persist handler
     * @var {@Kernel\Persist\PersistInterface}
     * @access private
     */
    private static $persist;

    /**
     * Reference to service handler
     * @var {@Kernel\Service}
     * @access private
     */
    private static $service;

    /**
     * Reference to autoloader handler
     * @var {@Kernel\Loader\Autoloader}
     * @access private
     */
    private static $autoloader;

    /**
     * Reference to the already instantiated engines
     * @var array of {@Kernel\EngineInterface}
     * @access private
     */
    private static $instances = array();

    /**
     * Engine factory
     *
     * @param string    $engine engine name or identifier, default as "xoops"
     * @param array     $options
     * @return {@Kernel\EngineInterface}
     */
    public static function factory($engine = "xoops", $options = array())
    {
        // Normalize default engine
        $engine = strtolower($engine ?: "xoops");

        if (!isset(self::$instances[$engine])) {
            // Set configs
            if (!isset($options["configs"])) {
                $options["configs"] = __DIR__ . "/boot/engine." . $engine . ".ini.php";
            }

            // Set hosts
            if (!isset($options["hosts"])) {
                $options["hosts"] = __DIR__ . "/boot/hosts." . $engine . ".ini.php";
            }

            // Namespaced class
            $classEngine = "Engine\\" . ucfirst($engine) . '\\Engine';
            // Instatiate engine with loaded options
            $instance = new $classEngine($options);
            // Register to container
            self::$instances[$engine] = $instance;
        }
        return self::$instances[$engine];
    }

    /**
     * Loads engine through factory
     */
    public static function engine($engine = "", $options = array())
    {
        // Normalize default engine
        if (empty($engine)) {
            if (!isset(self::$engine)) {
                self::$engine = self::factory($engine, $options);
            }
            return self::$engine;
        }

        return self::factory($engine, $options);
    }

    /**
     * Binds an engine as default engine
     */
    public static function bind($engine)
    {
        self::$engine = $engine;
        return self::$engine;
    }

    /**
     * If bound with an engine, i.e. booted
     */
    public static function bound()
    {
        return null !== self::$engine;
    }

    /**
     * Perform the boot sequence
     *
     * The following operations are done in order during the boot-sequence:
     * - Load system bootstrap config file
     * - Load primary services
     * - Application bootstrap
     *
     * @param string|{@Xoops_Engine_Interface}    $engine     Engine name or object
     * @param array|string     $options    options for the engine
     * @return boolean
     */
    public static function boot($engine = "xoops", $options = array())
    {
        //static::autoloader();
        // Set bootstrap file
        if (is_string($options)) {
            // Bootstrap is specified directly
            $bootstrap = $options;
            $options = array();
        } elseif (isset($options["bootstrap"])) {
            // Bootstrap set in options
            $bootstrap = $options["bootstrap"];
            unset($options["bootstrap"]);
        //} elseif (isset($GLOBALS['xoopsOption']["bootstrap"])) {
            // Bootstrap set in global options
            //$bootstrap = $GLOBALS['xoopsOption']["bootstrap"];
        } elseif (defined('XOOPS_BOOTSTRAP')) {
            // Bootstrap set in global options
            $bootstrap = constant('XOOPS_BOOTSTRAP');
        } else {
            // If not set, load regular bootstrap
            $bootstrap = "application";
        }
        // Load engine
        if (is_string($engine)) {
            $engine = self::factory($engine, $options);
        }
        // Bing the engine
        self::bind($engine);

        // Skip bootstrap if set empty
        if (empty($bootstrap)) {
            return true;
        }
        // Performe bootstrap
        return $engine->boot($bootstrap);
    }

    /**
     * Perform the system-wide shutdown sequence
     *
     * During kernel shutdown, instanciated services are checked for an 'shutdown'
     * method and if they provide one it will be called.
     *
     * @access public
     * @return bool
     */
    public static function shutdown()
    {
        $services = self::service()->getService();
        $serviceKeys = array_reverse(array_keys($services));
        foreach ($serviceKeys as $srv) {
            if (method_exists($services[$srv], 'shutdown')) {
                $services[$srv]->shutdown();
            }
        }
    }

    /**
     * Loads persistent data handler
     *
     */
    public static function persist()
    {
        if (!isset(self::$persist)) {
            $type = defined("XOOPS_PERSIST_TYPE") ? constant("XOOPS_PERSIST_TYPE") : null;
            self::$persist = new Kernel\Persist($type);
        }
        return self::$persist;
    }

    /**
     * Load a service by name or return service handler if name is not specified
     *
     * If service is not loaded with specified name, a service placeholder will be returned
     *
     * @param string    $name
     * @param array     $options
     * @return Kernel\Service|Kernel\Service\ServiceAbstract
     */
    public static function service($name = null, $options = array())
    {
        // Singleton
        if (!isset(self::$service)) {
            self::$service = new Kernel\Service();
        }
        // Return service handler
        if (null === $name) {
            return self::$service;
        }
        // Load a service
        if (!$service = self::$service->load($name, $options)) {
            // Load foo service if specified service is not loaded
            $service = self::$service->load("std");
        }
        return $service;
    }

    /**
     * Loads autoloader handler
     *
     * @return Kernel\Loader\Autoloader
     */
    public static function autoloader()
    {
        if (!isset(self::$autoloader)) {
            if (!class_exists("Kernel\\Loader\\Autoloader", false)) {
                require self::ROOT . '/Kernel/Loader/Autoloader.php';
            }
            self::$autoloader = new Kernel\Loader\Autoloader();
        }
        return self::$autoloader;
    }


    /**#@+
     * Global APIs, proxy to {@Kernel\Engine}
     */

    /**
     * Loads host data handler, proxy to engine host handler
     *
     */
    public static function host()
    {
        return self::engine()->host();
    }

    /**
     * Convert a path to a physical one, proxy to engine host handler
     *
     * @param string    $url        path: with leading slash "/" - absolute path, do not convert; w/o "/" - relative path, will be translated
     * @param bool      $virtual    wether convert to full URI
     */
    public static function path($url, $virtual = false)
    {
        return self::host()->path($url, $virtual);
    }

    /**
     * Generic storage class helps to manage global data, proxy to engine
     *
     * @param string $index The location to store the value, if value is not set, to load the value.
     * @param mixed $value The object to store.
     * @return mixed
     */
    public static function registry($index, $value = null)
    {
        return self::engine()->registry($index, $value);
    }

    /**
     * Gets configuration data, proxy to engine
     *
     * @param string|null   $key
     * @param mixed         $value
     * @return mixed    configuration data
     */
    public static function config($key = null, $value = null)
    {
        return self::engine()->config($key, $value);
    }

    /**
     * Loads configuration data from an ini file and caches the data, proxy to engine
     *
     * @param string $config    configuration name
     * @param string $section
     * @return associative array
     */
    public static function loadConfig($config, $section = null)
    {
        return self::engine()->loadConfig($config, $section);
    }

    /**
     * Convert a path to an URL, proxy to engine host handler
     *
     * @param string    $url        url to be converted: with leading slash "/" - absolute path, do not convert; w/o "/" - relative path, will be translated
     * @param bool      $absolute   whether convert to full URI; relative URI is used by default, i.e. no hostname
     */
    public static function url($url, $absolute = false)
    {
        return self::host()->url($url, $absolute);
    }

    /**
     * Assemble a generic application URL, proxy to engine host handler
     *
     * @param   array   $params
     * @param   string  $route  route name
     * @param   bool    $reset  Whether or not to reset the route defaults with those provided
     * @return  string  assembled URI
     */
    public static function assembleUrl($params = array(), $route = 'legacy', $reset = true, $encode = true)
    {
        return self::host()->assembleUrl($params, $route, $reset, $encode);
    }

    /**
     * Build application URL, proxy to engine host handler
     *
     * @param   array   $params
     * @param   string  $route  route name
     * @param   bool    $reset  Whether or not to reset the route defaults with those provided
     * @return  string  assembled URI
     */
    public static function appUrl($params = array(), $route = 'default', $reset = true, $encode = true)
    {
        return self::host()->appUrl($params, $route, $reset, $encode);
    }

    /**
     * Build URL mapping a locale resource, proxy to engine host handler
     *
     * @param   string  $domain     domain name, potential values: "", moduleName, theme:default, etc.
     * @param   string  $path       path to locale resource
     * @return  string  assembled URI
     */
    public static function localeUrl($domain = "", $path = "")
    {
        return self::host()->localeUrl($domain, $path);
    }

    /**
     * Translates the given string, proxy engine translator
     *
     * @param  string             $message Translation string
     * @param  string|Zend_Locale $locale    (optional) Locale/Language to use, identical with locale
     *                                       identifier, @see Zend_Locale for more information
     * @return string
     */
    public static function _($message, $locale = null)
    {
        return self::engine()->_($message, $locale);
    }

    /**
     * Translates the given string and echo it
     *
     * @param  string             $message Translation string
     * @param  string|Zend_Locale $locale    (optional) Locale/Language to use, identical with locale
     *                                       identifier, @see Zend_Locale for more information
     * @return string
     */
    public static function _e($message, $locale = null)
    {
        echo self::_($message, $locale);
    }

    /**
     * Load a core model, proxy to engine
     *
     * @param string $name
     * @param array $options
     * @return object {@link xoops_Zend_Db_Model}
     */
    public static function getModel($name, $options = array())
    {
        $model = self::engine()->getModel($name, $options);
        return $model;
    }

    /**
     * Loads legacy kernel ORM handler, proxy to engine
     *
     * @param  string   $name     object name
     * @param  bool     $optional whether or not generate errors if handler not loaded
     * @return string
     */
    public static function getHandler($name, $optional = false)
    {
        $handler = self::engine()->getHandler($name, $optional);
        return $handler;
    }

    /**
     * Overloading
     */
    public static function __callStatic($name, $args)
    {
        return call_user_func_array(array(self::engine(), $name), $args);
    }
    /**#@-*/
}

/**#@+
 * Register autoloader
 */
spl_autoload_register(array(XOOPS::autoloader(), 'dispatch'));
/*#@-*/


// Register shutdown functions
register_shutdown_function(array('XOOPS', 'shutdown'));
