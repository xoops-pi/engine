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
 * @package         Application
 * @subpackage      Bootstrap
 * @version         $Id$
 */

class Xoops_Zend_Application_Bootstrap_Bootstrap extends Zend_Application_Bootstrap_BootstrapAbstract
{
    /**
     * Constructor
     *
     * @param  Zend_Application|Zend_Application_Bootstrap_Bootstrapper $application
     * @return void
     */
    public function __construct($application)
    {
        //parent::__construct($application);
        $this->setApplication($application);
        $options = $application->getOptions();
        $this->setOptions($options);

        if ($application->hasOption('resourceloader')) {
            $this->setOptions(array(
                'resourceloader' => $application->getOption('resourceloader')
            ));
        }
        /*
        $this->getResourceLoader();

        if (!$this->hasPluginResource('FrontController')) {
            $this->registerPluginResource('FrontController');
        }
        */
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
        $front   = $this->getResource('FrontController');
        if (!$front) {
            return;
        }

        $default = $front->getDefaultModule();
        if (null === $front->getControllerDirectory($default)) {
            throw new Zend_Application_Bootstrap_Exception(
                'No default controller directory registered with front controller'
            );
        }

        XOOPS::service("profiler")->start(__METHOD__ . '-dispatch');
        $front->setParam('bootstrap', $this);
        $front->dispatch();
    }

    /**
     * Get class resources (as resource/method pairs)
     *
     * Uses get_class_methods() by default, reflection on prior to 5.2.6,
     * as a bug prevents the usage of get_class_methods() there.
     *
     * @return array
     */
    public function getClassResources()
    {
        if (null === $this->_classResources) {
            $persistKey = "bootstrap.classresources." . get_class($this) . '.' . XOOPS::config("identifier");
            $this->_classResources = XOOPS::persist()->load($persistKey);
            if (!is_array($this->_classResources)) {
                $methodNames = get_class_methods($this);
                $this->_classResources = array();
                foreach ($methodNames as $method) {
                    if (5 < strlen($method) && '_init' === substr($method, 0, 5)) {
                        $this->_classResources[strtolower(substr($method, 5))] = $method;
                    }
                }
                XOOPS::persist()->save($this->_classResources, $persistKey);
            }
        }

        return $this->_classResources;
    }

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

        //$options = $this->parseOptions($options);
        $classResources = $this->getClassResources();
        if (isset($classResources[$resource])) {
            $this->_classResources[$resource] = $options;
        } else {
            $this->_pluginResources[$resource] = $options;
        }
        return $this;
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

        XOOPS::service("profiler")->start(__METHOD__ . "-{$resourceName}");
        $classResources = $this->getClassResources();
        if (array_key_exists($resourceName, $classResources)) {
            $this->_started[$resourceName] = true;
            $method = "_init" . $resourceName;
            if (is_array($classResources[$resourceName])) {
                $options = $classResources[$resourceName];
            } else {
                $options = array();
            }
            $return = $this->$method($options);
            unset($this->_started[$resourceName]);
            $this->_markRun($resourceName);

            if (null !== $return) {
                $this->getContainer()->{$resourceName} = $return;
            }

            if ($log = XOOPS::service()->getService('logger')) {
                $log->info("Class resource '{$resourceName}' is loaded", "resource");
            }
        } elseif ($this->hasPluginResource($resource)) {
            $this->_started[$resourceName] = true;
            $plugin = $this->getPluginResource($resource);
            $return = $plugin->init();
            unset($this->_started[$resourceName]);
            $this->_markRun($resourceName);

            if (null !== $return) {
                $this->getContainer()->{$resourceName} = $return;
            }
            if ($log = XOOPS::service()->getService('logger')) {
                $log->info("Plugin resource '{$resourceName}' is loaded", "resource");
            }
        } elseif ($className = $this->getPluginLoader()->load($resourceName, false)) {
            $this->_started[$resourceName] = true;
            $options = (array) $this->loadOptions($resourceName);
            $options['bootstrap'] = $this;
            $plugin = new $className($options);
            $return = $plugin->init();
            unset($this->_started[$resourceName]);
            $this->_markRun($resourceName);

            if (null !== $return) {
                $this->getContainer()->{$resourceName} = $return;
            }
            if ($log = XOOPS::service()->getService('logger')) {
                $log->info("Runtime resource '{$resourceName}' is loaded", "resource");
            }
        } else {
            throw new Zend_Application_Bootstrap_Exception('Resource matching "' . $resource . '" not found');
        }
        XOOPS::service("profiler")->stop(__METHOD__ . "-{$resourceName}");
        return;
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
                'Xoops_Zend_Application_Resource'   => XOOPS::path('lib') . '/Xoops/Zend/Application/Resource',
            );

            $this->_pluginLoader = new Xoops_Zend_Loader_PluginLoader($options);
        }

        return $this->_pluginLoader;
    }

    public function loadOptions($resource)
    {
        $options = Xoops::loadConfig('resource.' . $resource . '.ini.php');
        return $options ?: array();
    }
}