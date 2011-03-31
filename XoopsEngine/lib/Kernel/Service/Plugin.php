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

class Plugin extends ServiceAbstract
{
    protected $container = array(
        // plugins
        "plugin"    => array(),
        'list'      => null,
    );

    public function load($key, $options = array())
    {
        if (!isset($this->container['plugin'][$key])) {
            $this->container['plugin'][$key] = false;
            $plugins = static::getList();
            $plugin = false;
            if (isset($plugins[$key])) {
                $class = "Plugin\\" . ucfirst($key) . "\\Helper";
                if (!class_exists($class)) {
                    $class = "Plugin_" . ucfirst($key) . "_Helper";
                    if (!class_exists($class)) {
                        trigger_error("Plugin class \"{$class}\" for \"{$key}\" was not loaded.", E_USER_ERROR);
                        return false;
                    }
                }
                $plugin = new $class($options);
            }
            $this->container['plugin'][$key] = $plugin;
        } elseif (!empty($options)) {
            $this->container['plugin'][$key]->setOptions($options);
        }

        return $this->container['plugin'][$key];
    }

    /**
     * Check if a plugin is loaded
     */
    public function hasPlugin($name)
    {
        return isset($this->container['plugin'][$name]);
    }

    /**
     * Get loaded plugin helper
     */
    public function getPlugin($name = null)
    {
        if (is_null($name)) {
            return $this->container['plugin'];
        } elseif (isset($this->container['plugin'][$name])) {
            return $this->container['plugin'][$name];
        }

        return null;
    }

    public function getList()
    {
        throw new \Exception('The abstract method can not be accessed directly');
        return $this->container['list'];
    }

    public function loadConfig($plugin)
    {
        throw new \Exception('The abstract method can not be accessed directly');
    }

    public function loadInfo($plugin, $translate = false)
    {
        return include \Xoops::path('plugin') . '/' . $plugin . '/info.php';
    }

    /**
     * Load a model for a plugin
     *
     * Model class file is located in /apps/app/model/example.php
     * with class name app_model_example
     *
     * @param string $name
     * @param string|null $plugin
     * @param array $options
     * @return object {@link Xoops_Zend_Db_Model}
     */
    public function getModel($name, $plugin = null, $options = array())
    {
        throw new \Exception('The abstract method can not be accessed directly');
    }
}