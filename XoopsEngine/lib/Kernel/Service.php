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
 * @package         Kernel
 * @since           3.0
 * @version         $Id$
 */

namespace Kernel;

class Service
{
    protected static $services = array();
    protected static $engine;

    public function __construct($engineName = 'xoops')
    {
        static::$engine = ucfirst($engineName);
    }

    public function load($name, $options = array())
    {
        $key = strtolower($name);
        if (!isset(static::$services[$key])) {
            static::$services[$key] = false;
            // Loads custom service
            $class = "Engine\\" . static::$engine . "\\Service\\" . ucfirst($name);
            if (!class_exists($class)) {
                // If custom service not defined, loads kernel service
                $class = "Kernel\\Service\\" . ucfirst($name);
                if (!class_exists($class)) {
                    trigger_error("Service class \"{$class}\" was not loaded.", E_USER_ERROR);
                    return static::$services[$key];
                }
            }

            static::$services[$key] = new $class($options);
            if (!(static::$services[$key] instanceof Service\ServiceAbstract)) {
                throw new \Exception("Invalid service instantiation '{$name}'!");
            }
            if ($log = $this->getService('logger')) {
                $log->info("Service '{$name}' is loaded", "service");
            }
        } elseif (!empty($options)) {
            static::$services[$key]->setOptions($options);
        }

        return static::$services[$key];
    }

    /**
     * Check if a services is loaded
     */
    public function hasService($name)
    {
        $name = strtolower($name);
        return isset(static::$services[$name]);
    }

    /**
     * Get loaded service
     */
    public function getService($name = null)
    {
        if (null === $name) {
            return static::$services;
        }
        $name = strtolower($name);
        if (isset(static::$services[$name])) {
            return static::$services[$name];
        }

        return null;
    }
}

namespace Kernel\Service;

abstract class ServiceAbstract
{
    /**
     * Config file in var/etc/
     * @var string
     */
    protected $configFile = "";

    /**
     * options
     * @var array
     */
    protected $options = array();

    /**
     * Whether or not to the service is active
     * @var boolean
     */
     protected $active = true;

    /**
     * Constructor
     *
     * @param array     $options    Parameters to send to the service during instanciation
     */
    public function __construct($options = array())
    {
        $this->setOptions($options);
    }

    /**
     * Loads options
     *
     * @param array|string    $options
     */
    public function setOptions($options = array())
    {
        if (!is_array($options)) {
            $configFile = (is_string($options) && !is_numeric($options)) ? $options : $this->configFile;
            if (!empty($configFile)) {
                $options = \Xoops::loadConfig('service.' . $configFile . '.ini.php');

            } else {
                $options = array();
            }
        }

        if (array_key_exists('active', $options)) {
            $this->active = $options['active'];
            unset($options['active']);
        }
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
    }
}