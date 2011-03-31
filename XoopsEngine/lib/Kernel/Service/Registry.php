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

class Registry extends ServiceAbstract
{
    // Run-time loaded registries
    protected $container;
    protected $cache;
    protected $defaultCache;

    public function handler($name, $module = null)
    {
        $key = empty($module) ? $name : $module . ":" . $name;
        if (isset($this->container[$key])) {
            return $this->container[$key];
        }
        /*
        $this->container[$key] = false;
        if (empty($module)) {
            $class = "Xoops_Core_Registry_" . ucfirst($name);
            $registryKey = $name;
        } else {
            $class = $module . "_registry_" . $name;
            $registryKey = $module . "_" . $name;
        }
        if (!class_exists($class)) {
            trigger_error("Registry class \"{$class}\" was not loaded.", E_USER_ERROR);
            return $this->container[$key];
        }
        $this->container[$key] = new $class();
        */
        $registryKey = empty($module) ? $name : $module . "_" . $name;
        $this->container[$key] = $this->loadHandler($name, $module);
        $this->container[$key]->setCache($this->getCache())->setKey($registryKey);

        return $this->container[$key];
    }

    protected function loadHandler($name, $module = null)
    {
        throw new \Exception('The abstract method can not be accessed directly');
    }

    /**
     * Remove cache data
     *
     * @param array     $options    associative array of options
     * @return boolean
     */
    public function flush($options = array())
    {
        $handler = $this->handler("std");
        return call_user_func(array($handler, "flush"), $options);
    }

    /**
     * Call a registry method as XOOPS::service('registry')->registryName->registryMethod();
     *
     * @param string    $handlerName
     * @return object
     */
    public function __get($handlerName)
    {
        $handler = $this->handler($handlerName);
        return $handler;
    }

    /**
     * Call a registry method as XOOPS::service('registry')->registryMethod('registryName', $arg);
     *
     * @param string    $handlerName
     * @return mixed
     */
    public function __call($handlerName, $args)
    {
        $method = array_pop($args);
        $handler = $this->handler($handlerName);
        if (is_callable(array($handler, $method))) {
            return call_user_func(array($handler, $method), $args);
        }
    }

    /**
     * Load cache engine
     */
    protected function defaultCache()
    {
        if (!isset($this->defaultCache)) {
            $this->defaultCache = \Xoops::persist()->getHandler();
        }

        return $this->defaultCache;
    }

    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    public function getCache()
    {
        if (!isset($this->cache)) {
            $this->cache = $this->defaultCache();
        }
        return $this->cache;
    }
}