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
 * @subpackage      Resource
 * @version         $Id$
 */

class Xoops_Zend_Application_Resource_Frontcontroller extends Zend_Application_Resource_Frontcontroller
{
    const DEFAULT_REGISTRY_KEY = 'frontController';
    /**
     * Initialize Front Controller
     *
     * @return Zend_Controller_Front
     */
    public function init()
    {
        $front = $this->getFrontController();
        $options = $this->getOptions();
        if (isset($options['modulecontrollerdirectoryname'])) {
            $front->setModuleControllerDirectoryName($options['modulecontrollerdirectoryname']);
            unset($options['modulecontrollerdirectoryname']);
        }
        $modules = Xoops::service('module')->getMeta();
        foreach ($modules as $dirname => $data) {
            if (empty($data["active"])) continue;
            if (isset($options['controllerdirectory'][$dirname])) continue;
            if (!isset($options['modulelist'][$dirname])) {
                $options['modulelist'][$dirname] =
                    ((empty($data["type"]) || ("app" == $data["type"])) ? "app" : "module") . "/" . $data["directory"];
            }
        }

        if (isset($options['dispatcher'])) {
            $dispatcher = ucfirst($options['dispatcher']);
            unset($options['dispatcher']);
        } else {
            $dispatcher = "Application";
        }
        $dispatcherClass = "Xoops_Zend_Controller_Dispatcher_" . $dispatcher;
        if (!class_exists($dispatcherClass)) {
            $dispatcherClass = "Zend_Controller_Dispatcher_" . $dispatcher;
        }
        $front->setDispatcher(new $dispatcherClass());

        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'controllerdirectory':
                    if (is_string($value)) {
                        $front->setControllerDirectory(XOOPS::path($value));
                    } elseif (is_array($value)) {
                        foreach ($value as $module => $directory) {
                            $front->addControllerDirectory(XOOPS::path($directory), $module);
                        }
                    }
                    break;

                case 'modulelist':
                    $controllerDirectoryName = $front->getModuleControllerDirectoryName();
                    foreach ($value as $module => $directory) {
                        $front->addControllerDirectory(XOOPS::path($directory) . "/" . $controllerDirectoryName, $module);
                    }
                    break;

                case 'moduledirectory':
                    $values = (array) $value;
                    foreach ($values as $value) {
                        $front->addModuleDirectory($value);
                    }
                    break;

                case 'defaultcontrollername':
                    $front->setDefaultControllerName($value);
                    break;

                case 'defaultaction':
                    $front->setDefaultAction($value);
                    break;

                case 'defaultmodule':
                    $front->setDefaultModule($value);
                    break;

                case 'baseurl':
                    if (!is_null($value)) {
                        $front->setBaseUrl($value);
                    }
                    break;

                case 'params':
                    $front->setParams($value);
                    break;

                case 'plugins':
                    foreach ((array) $value as $key => $plugin) {
                        $stackIndex = null;
                        $options = array();
                        if (is_array($plugin)) {
                            $plugin = array_change_key_case($plugin, CASE_LOWER);
                            if (isset($plugin['class'])) {
                                $pluginClass = $plugin['class'];
                            } elseif (is_string($key)) {
                                $pluginClass = "Zend_Controller_Plugin_" . ucfirst($key);
                            }
                            if (isset($plugin['stackindex'])) {
                                $stackIndex = $plugin['stackindex'];
                            }
                            if (isset($plugin['options'])) {
                                $options = $plugin['options'];
                            }
                        } else {
                            $pluginClass = "Zend_Controller_Plugin_" . ucfirst($plugin);
                        }

                        if (empty($pluginClass)) {
                            continue;
                        }
                        if (class_exists("Xoops_" . $pluginClass)) {
                            $pluginClass = "Xoops_" . $pluginClass;
                        } elseif (!class_exists($pluginClass)) {
                            continue;
                        }
                        $plugin = new $pluginClass($options);
                        $front->registerPlugin($plugin, $stackIndex);
                    }
                    break;

                case 'returnresponse':
                    $front->returnResponse((bool) $value);
                    break;

                case 'throwexceptions':
                    $front->throwExceptions((bool) $value);
                    break;

                case 'actionhelperpaths':
                    if (is_array($value)) {
                        foreach ($value as $helperPrefix => $helperPath) {
                            Zend_Controller_Action_HelperBroker::addPath($helperPath, $helperPrefix);
                        }
                    }
                    break;

                default:
                    $front->setParam($key, $value);
                    break;
            }
        }

        $front->getDispatcher()->setParam('prefixDefaultModule', true);
        if (null !== ($bootstrap = $this->getBootstrap())) {
            $this->getBootstrap()->frontController = $front;
        }

        return $front;
    }

    /**
     * Retrieve front controller instance
     *
     * @return Zend_Controller_Front
     */
    public function getFrontController()
    {
        if (null === $this->_front) {
            $this->_front = Zend_Controller_Front::getInstance();
            $key = (isset($options['registry_key']) && !is_numeric($options['registry_key']))
                ? $options['registry_key']
                : self::DEFAULT_REGISTRY_KEY;
            XOOPS::registry($key, $this->_front);
        }
        return $this->_front;
    }
}
