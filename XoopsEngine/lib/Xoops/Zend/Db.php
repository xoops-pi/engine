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
 * @copyright       Xoops Engine http://www.xoopsengine.org/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Db
 * @version         $Id$
 */

class Xoops_Zend_Db extends Zend_Db
{
    protected static $prefix = array(
        "core"      => "xo",
        "module"    => "",
        "plugin"    => "plugin",
    );

    /**
     * Factory for Zend_Db_Adapter_Abstract classes.
     *
     * First argument may be a string containing the base of the adapter class
     * name, e.g. 'Mysqli' corresponds to class Zend_Db_Adapter_Mysqli.  This
     * is case-insensitive.
     *
     * First argument may alternatively be an object of type Zend_Config.
     * The adapter class base name is read from the 'adapter' property.
     * The adapter config parameters are read from the 'params' property.
     *
     * Second argument is optional and may be an associative array of key-value
     * pairs.  This is used as the argument to the adapter constructor.
     *
     * If the first argument is of type Zend_Config, it is assumed to contain
     * all parameters, and the second argument is ignored.
     *
     * @param  mixed $adapter String name of base adapter class, or Zend_Config object.
     * @param  mixed $config  OPTIONAL; an array or Zend_Config object with adapter parameters.
     * @return Zend_Db_Adapter_Abstract
     * @throws Zend_Db_Exception
     */
    public static function factory($adapter = 'Pdo_Mysql', $config = array())
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }

        /*
         * Convert Zend_Config argument to plain string
         * adapter name and separate config object.
         */
        if ($adapter instanceof Zend_Config) {
            if (isset($adapter->params)) {
                $config = $adapter->params->toArray();
            }
            if (isset($adapter->adapter)) {
                $adapter = (string) $adapter->adapter;
            } else {
                $adapter = null;
            }
        }

        /*
         * Verify that adapter parameters are in an array.
         */
        if (!is_array($config)) {
            /**
             * @see Zend_Db_Exception
             */
            require_once 'Zend/Db/Exception.php';
            throw new Zend_Db_Exception('Adapter parameters must be in an array or a Zend_Config object');
        }

        /*
         * Verify that an adapter name has been specified.
         */
        if (!is_string($adapter) || empty($adapter)) {
            /**
             * @see Zend_Db_Exception
             */
            require_once 'Zend/Db/Exception.php';
            throw new Zend_Db_Exception('Adapter name must be specified in a string');
        }

        $adapter = str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($adapter))));
        /*
         * Form full adapter class name
         */
        if (isset($config['adapterNamespace'])) {
            if ($config['adapterNamespace'] != '') {
                $adapterNamespace = $config['adapterNamespace'];
            }
            unset($config['adapterNamespace']);
        }

        // Load with specified namespace
        if (!empty($adapterNamespace)) {
            $adapterName = $adapterNamespace . '_' . $adapter;
        } else {
            // Load with xoops namespace
            $adapterName = 'Xoops_Zend_Db_Adapter_' . $adapter;
            if (!class_exists($adapterName)) {
                // Load with Zend namespace
                $adapterName = 'Zend_Db_Adapter_' . $adapter;
            }
        }

        /*
         * Create an instance of the adapter class.
         * Pass the config to the adapter class constructor.
         */
        $dbAdapter = new $adapterName($config);

        /*
         * Verify that the object created is a descendent of the abstract adapter type.
         */
        if (! $dbAdapter instanceof Zend_Db_Adapter_Abstract) {
            /**
             * @see Zend_Db_Exception
             */
            require_once 'Zend/Db/Exception.php';
            throw new Zend_Db_Exception("Adapter class '$adapterName' does not extend Zend_Db_Adapter_Abstract");
        }

        return $dbAdapter;
    }

    public static function setPrefix($key, $value)
    {
        static::$prefix[$key] = $value;
    }

    public static function getPrefix($key)
    {
        return isset(static::$prefix[$key]) ? static::$prefix[$key] : null;
    }
}
