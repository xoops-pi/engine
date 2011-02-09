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
 * @copyright       The Xoops Engine http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Application
 * @subpackage      Bootstrap
 * @version         $Id$
 */

class Lite_Zend_Application_Bootstrap_Cli
    extends Zend_Application_Bootstrap_BootstrapAbstract
{
    /**
     * Register a new resource plugin
     *
     * @param  string|Zend_Application_Resource_Resource $resource
     * @param  mixed  $options
     * @return Zend_Application_Bootstrap_BootstrapAbstract
     * @throws Zend_Application_Bootstrap_Exception When invalid resource is provided
     */
    public function registerPluginResource($resource, $options = null)
    {
        if ($resource instanceof Zend_Application_Resource_Resource) {
            $resource->setBootstrap($this);
            $pluginName = $this->_resolvePluginResourceName($resource);
            $this->_pluginResources[$pluginName] = $resource;
            return $this;
        }

        if (!is_string($resource)) {
            throw new Zend_Application_Bootstrap_Exception('Invalid resource provided to ' . __METHOD__);
        }

        $options = $this->parseOptions($options);
        $classResources = $this->getClassResources();
        if (isset($classResources[$resource])) {
            $this->_classResources[$resource] = $options;
        } else {
            $this->_pluginResources[$resource] = $options;
        }
        return $this;
    }

    /**
     * Run the application
     *
     * Checks to see that we have a default controller directory. If not, an
     * exception is thrown.
     *
     * If so, it registers the bootstrap with the 'bootstrap' parameter of
     * the front controller, and dispatches the front controller.
     *
     * @return void
     * @throws Zend_Application_Bootstrap_Exception
     */
    public function run()
    {
        return;
    }

    /**
     * Execute a resource
     *
     * Checks to see if the resource has already been run. If not, it searches
     * first to see if a local method matches the resource, and executes that.
     * If not, it checks to see if a plugin resource matches, and executes that
     * if found.
     *
     * Finally, if not found, it throws an exception.
     *
     * @param  string $resource
     * @return void
     * @throws Zend_Application_Bootstrap_Exception When resource not found
     */
    protected function _executeResource($resource)
    {
        $resourceName = strtolower($resource);

        if (in_array($resourceName, $this->_run)) {
            return;
        }

        if (isset($this->_started[$resourceName]) && $this->_started[$resourceName]) {
            throw new Zend_Application_Bootstrap_Exception('Circular resource dependency detected');
        }

        $classResources = $this->getClassResources();
        if (array_key_exists($resourceName, $classResources)) {
            $this->_started[$resourceName] = true;
            if (is_string($classResources[$resourceName])) {
                $method = $classResources[$resourceName];
                $options = null;
            } else {
                $method = "_init" . $resourceName;
                $options = $classResources[$resourceName];
            }
            $return = $this->$method($options);
            unset($this->_started[$resourceName]);
            $this->_markRun($resourceName);

            if (null !== $return) {
                $this->getContainer()->{$resourceName} = $return;
            }

            if (XOOPS::service()->hasService('logger')) {
                XOOPS::service('logger')->info("Class resource '{$resourceName}' is loaded", "resource");
            }

            return;
        }

        if ($this->hasPluginResource($resource)) {
            $this->_started[$resourceName] = true;
            $plugin = $this->getPluginResource($resource);
            $return = $plugin->init();
            unset($this->_started[$resourceName]);
            $this->_markRun($resourceName);

            if (null !== $return) {
                $this->getContainer()->{$resourceName} = $return;
            }

            if (XOOPS::service()->hasService('logger')) {
                XOOPS::service('logger')->info("Plugin resource '{$resourceName}' is loaded", "resource");
            }

            return;
        }

        throw new Zend_Application_Bootstrap_Exception('Resource matching "' . $resource . '" not found');
    }

    /**
     * Get the plugin loader for resources
     *
     * @return Zend_Loader_PluginLoader_Interface
     */
    public function getPluginLoader()
    {
        if ($this->_pluginLoader === null) {
            $options = array(
                'Zend_Application_Resource'         => XOOPS::path('lib') . '/Zend/Application/Resource',
                'Lite_Zend_Application_Resource' => XOOPS::path('lib') . '/Lite/Zend/Application/Resource',
            );

            $this->_pluginLoader = new Xoops_Zend_Loader_PluginLoader($options);
        }

        return $this->_pluginLoader;
    }

    /**
     * Load a plugin resource
     *
     * @param  string $resource
     * @param  array|object|null $options
     * @return string|false
     */
    protected function _loadPluginResource($resource, $options)
    {
        $options['bootstrap'] = $this;
        $className = $this->getPluginLoader()->load(strtolower($resource), false);

        if (!$className) {
            return false;
        }

        $instance = new $className($options);

        unset($this->_pluginResources[$resource]);

        if (isset($instance->_explicitType)) {
            $resource = $instance->_explicitType;
        }
        $resource = strtolower($resource);
        $this->_pluginResources[$resource] = $instance;

        return $resource;
    }

    protected function parseOptions($options)
    {
        $options = (array) $options;
        if (!empty($options['config'])) {
            if (file_exists($file = Xoops::path("var/etc/resource." . $options['config'] . ".ini"))) {
                unset($options['config']);
                $configs = new Zend_Config_Ini($file);
                $options = $this->mergeOptions($options, $configs->toArray());
            }
        }
        return $options;
    }

    /**
     * Load XOOPS cache handler
     *
     */
    public function _initCache()
    {
        $cache = Xoops_Zend_Cache::factory();
        XOOPS::registry('cache', $cache);
    }
}