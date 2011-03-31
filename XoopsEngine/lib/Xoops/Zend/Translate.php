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
 * @package         Translate
 * @version         $Id$
 */

class Xoops_Zend_Translate extends Zend_Translate
{
    const DEFAULT_ADAPTER = 'Legacy';
    //protected static $loaded;
    protected $adapter;

    /**
     * Adapter
     *
     * @var Zend_Translate_Adapter
     */
    //protected $_adapter;
    //protected $_currentAdapter;
    //protected static $adapters = array();
    //protected static $_cache = null;
    //protected $options = array();
    //protected $locale = null;
    //protected static $loaded = array();

    /**
     * Generates the standard translation object
     *
     * @param  array|Zend_Config $options Options to use
     */
    public function ______construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            $options = array('adapter' => $options);
        }

        $this->setAdapter($options);
    }

    /**
     * Sets a new adapter
     *
     * @param  array|Zend_Config $options Options to use
     */
    public function setAdapter($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (func_num_args() > 1) {
            $args               = func_get_args();
            $options            = array();
            $options['adapter'] = array_shift($args);
            if (!empty($args)) {
                $options['content'] = array_shift($args);
            }

            if (!empty($args)) {
                $options['locale'] = array_shift($args);
            }

            if (!empty($args)) {
                $opt     = array_shift($args);
                $options = array_merge($opt, $options);
            }
        } else if (!is_array($options)) {
            $options = array('adapter' => $options);
        }

        if (array_key_exists('cache', $options)) {
            Zend_Translate_Adapter::setCache($options['cache']);
        }

        $adapter = !empty($options['adapter']) ? $options['adapter'] : static::DEFAULT_ADAPTER;
        unset($options['adapter']);
        $adapter = 'Zend_Translate_Adapter_' . ucfirst($adapter);
        if (class_exists('Xoops_' . $adapter)) {
            $adapter = 'Xoops_' . $adapter;
        }
        $this->adapter = new $adapter($options);
    }

    /**
     * Returns the adapters name and it's options
     *
     * @return Zend_Translate_Adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Calls all methods from the adapter
     */
    public function __call($method, array $options)
    {
        if (method_exists($this->adapter, $method)) {
            return call_user_func_array(array($this->adapter, $method), $options);
        }
        throw new Zend_Translate_Exception("Unknown method '" . $method . "' called!");
    }

    /**
     * Load translation data
     *
     * @param  string       $data    Translation data
     * @param  string       $domain  global, module, theme, plugin, etc.
     * @param  string|Zend_Locale $locale  (optional) Locale/Language to add data for, identical
     *                                        with locale identifier, see Zend_Locale for more information
     * @param  array              $options (optional) Option for this Adapter
     * @return Xoops_Zend_Translate Provides fluent interface
     */
    public function loadTranslation($data, $domain = "", $locale = null, $options = array())
    {
        //$key = md5("{$data}.{$domain}.{$locale}." . get_class($this->adapter) . "." . serialize($options));
        //if (!isset(self::$loaded[$key])) {
            //self::$loaded[$key] = 1;
            //$path = $this->adapter->getPath($domain, $locale);
            //if ($path) {
                //$data = $path . "/" . $data;
                try {
                    $options['content'] = $data;
                    $options['domain']  = $domain;
                    $options['locale']  = $locale;
                    //Debug::e($options);
                    $this->adapter->addTranslation($options);
                } catch (Zend_Translate_Exception $e) {
                   trigger_error("Translation data '{$data}' is failed: " . $e->getMessage());
                }
                //XOOPS::service('logger')->info("Translation data '{$data}' is loaded", "resource");
            //} else {
            //    XOOPS::service('logger')->info("Translation data '{$data}' is failed: path not found", "resource");
            //}
        //}

        return $this;
    }

    /**
     * Get translation data path
     *
     *
     */
    public function getPath($domain = "", $locale = null)
    {
        return $this->adapter->getPath($domain, $locale);
    }
}
