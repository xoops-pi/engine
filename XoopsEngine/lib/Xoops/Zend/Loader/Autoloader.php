<?php
/**
 * Zend Framework for Xoops Engine
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
 * @category        Xoops_Zend
 * @package         Loader
 * @version         $Id$
 */

/** Zend_Loader */
require_once XOOPS::path("lib") . '/Zend/Loader/Autoloader.php';
require_once XOOPS::path("lib") . '/Xoops/Zend/Loader.php';

class Xoops_Zend_Loader_Autoloader extends Zend_Loader_Autoloader
{
    protected $coreClasses = array();
    protected $coreAutoloader = array('Xoops_Zend_Loader', 'core');
    protected $moduleAutoloader = array('Xoops_Zend_Loader', 'module');
    protected $pluginAutoloader = array('Xoops_Zend_Loader', 'plugin');

    /**
     * @var array Default autoloader callback
     */
    protected $_defaultAutoloader = array('Xoops_Zend_Loader', 'loadClass');

    /**
     * @var Zend_Loader_Autoloader Singleton instance
     */
    protected static $_instance;

    /**
     * @var array Supported namespaces 'Zend' and 'ZendX' by default.
     */
    protected $_namespaces = array(
        'Xoops_'        => true,
        'Zend_'         => true,
        //'ZendX_'        => true,
        //'Xoops_Zend_'   => true,
    );

    /**
     * Retrieve singleton instance
     *
     * @return Zend_Loader_Autoloader
     */
    public static function getInstance()
    {
        if (null === static::$_instance) {
            static::$_instance = new self();
        }
        return static::$_instance;
    }

    /**
     * Reset the singleton instance
     *
     * @return void
     */
    public static function resetInstance()
    {
        static::$_instance = null;
    }

    /**
     * Autoload a class
     *
     * @param  string $class
     * @return bool
     */
    public static function autoload($class)
    {
        $self = static::getInstance();

        // Caches
        if (null !== ($result = Xoops_Zend_Loader::loadFromPersist($class))) {
            return $result;
        }

        // Core classes
        if (array_key_exists($class, $self->getCoreClasses())) {
            $callback = $self->getCoreAutoloader();
            return call_user_func($callback, $class);
        }

        if ($pos = strpos($class, "_")) {
            $prefix = substr($class, 0, $pos);
            if (isset($self->_namespaces[$prefix . "_"])) {
            // If is a plugin class
            } elseif ("plugin" == strtolower($prefix)) {
                if ($callback = $self->getPluginAutoloader()) {
                    return call_user_func($callback, $class);
                }
            // If is a module class
            } elseif ($callback = $self->getModuleAutoloader($class)) {
                return call_user_func($callback, $class);
            }
        } else {
            return false;
        }

        foreach ($self->getClassAutoloaders($class) as $autoloader) {
            if ($autoloader instanceof Zend_Loader_Autoloader_Interface) {
                if ($autoloader->autoload($class)) {
                    return true;
                }
            } elseif (is_string($autoloader)) {
                if ($autoloader($class)) {
                    return true;
                }
            } elseif (is_array($autoloader)) {
                $object = array_shift($autoloader);
                $method = array_shift($autoloader);

               if (call_user_func(array($object, $method), $class)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getCoreClasses()
    {
        return $this->coreClasses;
    }

    public function setCoreClasses($classes)
    {
        $this->coreClasses = $classes;
        return $this;
    }

    /**
     * Set the core autoloader implementation
     *
     * @param  string|array $callback PHP callback
     * @return void
     */
    public function setCoreAutoloader($callback)
    {
        if (!is_callable($callback)) {
            throw new Zend_Loader_Exception('Invalid callback specified for core autoloader');
        }

        $this->coreAutoloader = $callback;
        return $this;
    }

    /**
     * Retrieve the core autoloader callback
     *
     * @return string|array PHP Callback
     */
    public function getCoreAutoloader()
    {
        return $this->coreAutoloader;
    }

    /**
     * Retrieve module class autoloader callback
     *
     * @return string|array PHP Callback
     */
    public function getModuleAutoloader($class)
    {
        /*
        if (!$pos = strpos($class, "_")) {
            return null;
        }
        $prefix = substr($class, 0, $pos);
        if ($prefix == "Xoops" || $prefix == "Zend") {
            return null;
        }
        */
        return $this->moduleAutoloader;
    }

    /**
     * Retrieve plugin class autoloader callback
     *
     * @return string|array PHP Callback
     */
    public function getPluginAutoloader($class)
    {
        return $this->pluginAutoloader;
    }

    /**
     * Constructor
     *
     * Registers instance with spl_autoload stack
     *
     * @return void
     */
    protected function __construct()
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
        $this->_internalAutoloader = array($this, '_autoload');
        $this->coreClasses = array(
            "Xoops_Module"      => true,
            "Xoops_Plugin"      => true,
            "Xoops_Service"     => true,
            "Xoops_Installer"   => true,
            "Xoops_Api"         => true,
            "Xoops_Path"        => true,
            "Xoops_Registry"    => true,
            "Xoops_User"        => true,
            "Xoops_Loader"      => true,
            "Xoops_Version"     => true,
        );
    }
}