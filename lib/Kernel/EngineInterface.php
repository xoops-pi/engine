<?php
/**
 * Kernel engine interface
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

interface EngineInterface
{
    /**
     * Bootstrap
     */
    public function boot($bootstrap = null);

    /**
     * Get host handler/container
     */
    public function host();

    /**
     * Get a configuration data
     */
    public function config($key = null);

    /**
     * Set configuration data
     *
     * @param array $configs
     */
    public function setConfigs($configs);

    /**
     * Generic storage class helps to manage global data
     *
     * @param string $index The location to store the value, if value is not set, to load the value.
     * @param mixed $value The object to store.
     * @return mixed
     */
    public function registry($key, $value = null);


    /**
     * Loads configuration data from an ini file, allows for multi-level key
     *
     * @param string $config    configuration name
     * @param string $section
     * @return associative array
     */
    public function loadConfig($config, $section = null);

    /**
     * Translates a given string
     *
     * @param  string             $message Translation string
     * @param  string|Zend_Locale $locale    (optional) Locale/Language to use, identical with locale
     *                                       identifier, @see Zend_Locale for more information
     * @return string
     */
    public function _($message, $locale = null);

    /**
     * Translates a given string and displays it
     *
     * @param  string             $message Translation string
     * @param  string|Zend_Locale $locale    (optional) Locale/Language to use, identical with locale
     *                                       identifier, @see Zend_Locale for more information
     * @return string
     */
    public function _e($message, $locale = null);

    /**
     * Loads legacy kernel ORM handler
     *
     * @param  string   $name     object name
     * @param  bool     $optional whether or not generate errors if handler not loaded
     * @return string
     */
    //public function getHandler($name, $optional = false);

    /**
     * Load a core model
     *
     * @param string $name
     * @param array $options
     * @return object {@link xoops_Zend_Db_Model}
     */
    //public function getModel($name, $options = array());
}
