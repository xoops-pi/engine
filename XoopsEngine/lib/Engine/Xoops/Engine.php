<?php
/**
 * Engine class
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
 * @package         Kernel
 * @since           3.0
 * @version         $Id$
 */

namespace Engine\Xoops;

/**
 * XOOPS kernel engine
 *
 * Tasks: boot, shutdown; load hosts, configs; set paths, loadClass, loadService, loadConfig
 *
 * @author      Taiwen Jiang <phppp@users.sourceforge.net>
 */

class Engine implements \Kernel\EngineInterface
{
    /**
     * @var string
     *
     * Versioning schema: uses GNU version numbering scheme: major.minor.revision[state]
     * Version number: segments separated by "."
     *  Major       - increment in first segment, 3.0.0
     *  Minor       - increment in second segment, 3.1.0
     *  Revision    - increment in third segment, 3.1.1
     * Development state:
     *  Production  - "final" or not specified
     *  RC          - "rc" or "rc" + {number}: rc2
     *  Beta        - "beta" or "beta" + {number}: beta2
     *  Alpha       - "alpha" or "alpha" + {number}: alpha3
     *  Dev         - "dev" or "dev" + {number}: dev
     *  Preview     - Informal release, "preview" or "preview" + {number}: preview5
     * A full version number looks like: 3.0.0rc2
     */
    const VERSION = 'Xoops Engine 3.0 alpha2';

    protected $container = array(
        // Registries
        "registry"  => array(),
        // Core models
        "model"     => array(),
        // Legacy core handlers
        "handler"   => array(),
    );

    /**
     * Loaded system configs
     * @var assoaciative array
     */
    protected $configs = array(
        // Identifier of system engine, set on installation
        "identifier"    => "xoops",
        // Salt for encryption, created on installation
        "salt"          => "xo441c889f6e25003dba02caf7b0bec764",
        // Run environment
        "environment"   => "debug"
    );

    /**
     * Host and path container
     * @var {@Xoops_Host}
     */
    protected $host;

    /**
     * Constructor
     *
     * Can only be instantiated via method of instance
     *
     * @param  array $options
     * @return void
     */
    public function __construct($options = null)
    {
        $this->registerAutoloader();
        if (isset($options["hosts"]) && $options["hosts"] !== false) {
            $this->loadHosts($options["hosts"]);
        }
        if (isset($options["configs"])) {
            $this->setConfigs($options["configs"]);
        }
    }

    public function version()
    {
        return static::VERSION;
    }

    protected function registerAutoloader()
    {
        /*
        $persistKey = "autoloader.classmap.core." . $this->config('identifier');;
        if (!$map = \Xoops::persist()->load($persistKey)) {
            $map = array(
                "Application\\Controller"    => __DIR__ . '/Application/Controller.php',
                "Application\\Plugin"        => __DIR__ . '/Application/Plugin.php',
                "Application\\Applet"        => __DIR__ . '/Application/Applet.php',
            );
            $rootPath = \Xoops::ROOT . '/Xoops/Core';
            $iterator = new \DirectoryIterator(\Xoops::ROOT . '/Xoops/Core');
            foreach ($iterator as $fileinfo) {
                if (!$fileinfo->isFile() || $fileinfo->isDot()) {
                    continue;
                }
                $baseName = $fileinfo->getFileInfo()->getBasename(".php");
                $map["Xoops_" . $baseName] = $fileinfo->getRealPath();
            }
            \Xoops::persist()->save($map, $persistKey);
        }
        */
        $map = array(
            "Application\\Controller"   => __DIR__ . '/Application/Controller.php',
            "Application\\Plugin"       => __DIR__ . '/Application/Plugin.php',
            "Application\\Applet"       => __DIR__ . '/Application/Applet.php',
        );
        \Xoops::autoloader()->registerMap($map);
        return $this;
    }

    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    public function host()
    {
        return $this->host;
    }

    /**
     * Perform the boot sequence
     *
     * The following operations are done in order during the boot-sequence:
     * - Load system bootstrap config file
     * - Load primary services
     * - Application bootstrap
     *
     * @param string|array $bootstrap  bootstrap name or bootstrap options
     * @return string   path to boot file
     */
    public function boot($bootstrap = null)
    {
        // Set run environment
        // Defined in configuration
        if (defined('XOOPS_ENV')) {
            $this->configs['environment'] = XOOPS_ENV;
        // Defined via system variable
        } elseif (getenv('XOOPS_ENV')) {
            $this->configs['environment'] = getenv('XOOPS_ENV');
        }

        try {
            // Load prerequisite basic services
            $services = isset($this->configs['services']) ? $this->configs['services'] : array();
            foreach ($services as $name => $options) {
                \Xoops::service()->load($name, $options);
            }
        } catch (\Exception $e) {
            echo "Exception in basic service: <pre>" . $e->getMessage() . "</pre>";
            if (\Xoops::service()->hasService('error')) {
                \Xoops::service('error')->handleException($e);
            }
        }

        // profiling
        \Xoops::service("profiler")->start('boot')->start('Application');

        try {
            try {
                $options = array(
                    "autoloader"    => \Xoops::autoloader(),
                    "bootoption"    => $bootstrap,
                    "engine"        => $this,
                );
                $application = new \Xoops_Zend_Application($this->configs["environment"], $options);
            } catch (\Exception $e) {
                echo "Exception: <pre>" . $e->getMessage() . "</pre>";
                if (\Xoops::service()->hasService('error')) {
                    \Xoops::service('error')->handleException($e);
                }
            }
            $this->registry('application', $application);
            \Xoops::service("profiler")->stop('Application')->start('Bootstrap');
            $application->bootstrap();
            \Xoops::service("profiler")->stop('Bootstrap')->start('run');
            $application->run();
        } catch (Exception $e) {
            echo "Exception: <pre>" . $e->getMessage() . "</pre>";
            if (\Xoops::service()->hasService('error')) {
                \Xoops::service('error')->handleException($e);
            }
        }

        // Register shutdown functions for this application
        register_shutdown_function(array(&$this, 'shutdown'));
    }

    public function setConfigs($configs = array())
    {
        if (is_string($configs)) {
            $persistKey = "engine.config." . md5($configs);
            if (!$cfgs = \Xoops::persist()->load($persistKey)) {
                $cfgs = parse_ini_file($configs, true);
                if (isset($cfgs['services'])) {
                    $cfgs['engine']['services'] = $cfgs['services'];
                }
                $cfgs = $cfgs['engine'];
                \Xoops::persist()->save($cfgs, $persistKey);
            }
            $configs = $cfgs;
        }
        $this->configs = array_merge($this->configs, $configs);
    }

    /**
     * Load host data
     *
     * @param  array|string $hostVars   configurations for virtual hosts or section name in host configuration file: null - to load from configuration file and look up automatically; string - to load rom configuration file with specified section; empty string - to skip host configuration
     * @return void
     */
    public function loadHosts($hostVars = null)
    {
        if (is_string($hostVars)) {
            $persistKey = "engine.hosts." . md5($hostVars);
            if (!$cfgs = \Xoops::persist()->load($persistKey)) {
                $cfgs = parse_ini_file($hostVars, true);
                \Xoops::persist()->save($cfgs, $persistKey);
            }
            $hostVars = $cfgs;
        }
        /*
        if (!interface_exists("Kernel\\HostInterface", false)) {
            require \Xoops::ROOT . "/Kernel/HostInterface.php";
        }
        if (!class_exists("Xoops_Host", false)) {
            require \Xoops::ROOT . "/Xoops/Core/Host.php";
        }
        */
        // Set host handler with path configurations
        $this->setHost(new Host($hostVars));
        return true;
    }

    /**
     * Convert a XOOPS path to a physical one, proxy to path handler
     *
     * @param string    $url        XOOPS path: with leading slash "/" - absolute path, do not convert; w/o "/" - relative path, relative to XOOPS root path
     * @param bool      $virtual    whether convert to full URI
     */
    public function path($url, $virtual = false)
    {
        return $this->host->path($url, $virtual);
    }

    /**
     * Convert a XOOPS path to an URL, proxy to path handler
     */
    public function url($url, $absolute = false)
    {
        return $this->host->url($url, $absolute);
    }

    /**
     * Build an URL with the specified request params, proxy to path handler
     */
    public function buildUrl($url, $params = array())
    {
        return $this->host->buildUrl($url, $params);
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
    public function shutdown()
    {
        /*
        $services = Xoops_Service::getService();
        $serviceKeys = array_reverse(array_keys($services));
        foreach ($serviceKeys as $srv) {
            if (method_exists($services[$srv], 'shutdown')) {
                $services[$srv]->shutdown();
            }
        }
        */
    }

    /**
     * Gets a configuration parameter or all config data if $name is not specified
     *
     * @param string|null   $name
     * @param mixed         $value
     * @return mixed    configuration data
     */
    public function config($name = null, $value = null)
    {
        if (is_null($name)) {
            return $this->configs;
        }
        if (null !== $value) {
            $this->configs[$name] = $value;
            return $this->configs[$name];
        } else {
            return isset($this->configs[$name]) ? $this->configs[$name] : null;
        }
    }

    /**
     * Loads configuration data from an ini file.
     *
     * If the $section is null, then all sections in the ini file are loaded.
     * If $config file is not found, it will attempt to load the file from var/configs/
     *
     * @param string $configFile    configuration name
     * @param string $section
     * @return associative array
     */
    public function loadConfig($configFile, $section = null)
    {
        $persistKey = "config." . $this->config("identifier") . "." . md5($configFile . ($section ? "." . $section : ""));
        if ($config = \Xoops::persist()->load($persistKey)) {
            return $config;
        }
        $config = \Xoops_Config::load($configFile, $section);
        \Xoops::persist()->save($config, $persistKey);
        return $config;
    }

    /**
     * Generic storage class helps to manage global data.
     *
     * @param string $index The location to store the value, if value is not set, to load the value.
     * @param mixed $value The object to store.
     * @return mixed
     */
    public function registry($index, $value = null)
    {
        $index = strtolower($index);
        if (isset($value)) {
            $this->container["registry"][$index] = $value;
            return $this;
        } else {
            return isset($this->container["registry"][$index]) ? $this->container["registry"][$index] : null;
        }
    }

    /**
     * Translates the given string
     * returns the translation
     *
     * @param  string             $message Translation string
     * @param  string|Zend_Locale $locale    (optional) Locale/Language to use, identical with locale
     *                                       identifier, @see Zend_Locale for more information
     * @return string
     */
    public function _($message, $locale = null)
    {
        /*
        if (!$this->registry('translate')) {
            return $message;
        }
        */
        return \Xoops::service('translate')->_($message, $locale);
    }

    public function _e($message, $locale = null)
    {
        echo $this->_($message, $locale);
    }

    /**
     * Call a service method as $xoops->serviceName mapping to \Xoops::service('serviceName');
     *
     * @param string    $serviceName
     * @return mixed
     */
    public function __get($serviceName)
    {
        switch ($serviceName) {
            case "paths":
            case "baseUrl":
            case "baseLocation":
                return $this->host->get($serviceName);
            case "path":
                return $this->host;
            case "persist":
                return \Xoops::persist();
            default:
                return \Xoops::service($serviceName);
        }
    }

    /**
     * Loads legacy kernel ORM handler
     *
     * @param  string   $name     object name
     * @param  bool     $optional whether or not generate errors if handler not loaded
     * @return string
     */
    public function getHandler($name, $optional = false)
    {
        $name = strtolower($name);
        if (!isset($this->container["handler"][$name])) {
            if (file_exists($hnd_file = $this->host->path('www') . '/kernel/' . $name . '.php')) {
                require_once $hnd_file;
            }
            $class = 'Xoops' . $name . 'Handler';
            if (class_exists($class)) {
                $this->container["handler"][$name] = new $class($GLOBALS['xoopsDB']);
            } else {
                $this->container["handler"][$name] = null;
                trigger_error("Class {$class} does not exist: Handler Name - {$name}", $optional ? E_USER_WARNING : E_USER_ERROR);
            }
        }
        return $this->container["handler"][$name];
    }

    /**
     * Load a core model
     *
     * Model class file is located in lib/Model/Example.php
     * with class name Xoops_Model_Example
     *
     * @param string $name
     * @param array $options
     * @return object {@link Xoops_Zend_Db_Model}
     */
    public function getModel($name, $options = array())
    {
        $key = strtolower($name);
        if (!isset($this->container["model"][$key])) {
            if (!isset($options["prefix"])) {
                $options["prefix"] = \XOOPS_Zend_Db::getPrefix("core");
            }
            $name = str_replace(" ", "_", ucwords(str_replace("_", " ", $key)));
            $className = "Xoops_Model_" . $name;
            if (!class_exists($className)) {
                $className = "Xoops_Model_Model";
                if (!isset($options["name"])) {
                    $options["name"] = $key;
                }
            }
            /*
            if (!isset($options["name"])) {
                $options["name"] = "xo_" . strtolower($name);
            } else {
                $options["name"] = "xo_" . $options["name"];
            }
            */
            $model = new $className($options);
            if (!$model->setupMetadata()) {
                $model = false;
            }

            $this->container["model"][$key] = $model;
        }
        return $this->container["model"][$key];
    }
}