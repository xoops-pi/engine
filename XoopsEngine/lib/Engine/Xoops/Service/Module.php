<?php
/**
 * XOOPS Module service class
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
 * @package         Xoops_Service
 * @version         $Id$
 */

namespace Engine\Xoops\Service;

class Module extends \Kernel\Service\Module
{
    protected $container = array(
        // modules
        "module"  => array(),
        // module models
        "model"     => array(),
        // Legacy module handlers
        "handler"   => array(),
    );

    public function loadConfig($module)
    {
        return \XOOPS::service('registry')->config->read($module);
    }

    /**
     * Loads module information data from config file
     *
     * @param string        $module     dirname of module
     * @param bool|string   $category   category name of extension
     * @return array
     */
    public function loadInfo($module, $category = false)
    {
        return \Xoops_Module_Info::load($module, $category);
        /*
        if ($directory = $this->getDirectory($module)) {
            $module = $directory;
        }

        \XOOPS::service('translate')->loadTranslation('modinfo', $module);
        if (!$path = $this->getPath($module)) {
            return false;
        }
        $file = $path . "/configs/module.php";
        if (!file_exists($file)) {
            return false;
        }
        $info = \Xoops_Config::load($file);

        if (!empty($category)) {
            // Loads a single category extension data
            if (is_string($category)) {
                if (!empty($info['extensions'][$category])) {
                    if (is_string($info['extensions'][$category])) {
                        $file = \XOOPS::path("app") . "/{$module}/configs/" . $info['extensions'][$category];
                        $info['extensions'][$category] = \Xoops_Config::load($file);
                    }
                    $info = $info['extensions'][$category];
                } else {
                    $info = array();
                }
            // Loads all extension data
            } else {
                if (!empty($info['extensions'])) {
                    foreach ($info['extensions'] as $extension => $options) {
                        if (!is_string($options)) continue;
                        $file = \XOOPS::path("app") . "/{$module}/configs/{$options}";
                        $info['extensions'][$extension] = \Xoops_Config::load($file);
                    }
                } else {
                    $info = array();
                }
            }
        } else {
            $info['logo'] = "app/{$module}/" . $info['logo'];
        }

        return $info;
        */
    }

    /**
     * Loads legacy module ORM handler
     *
     * @param  string   $name     object name
     * @param  string   $module   module dirname
     * @param  bool     $optional whether or not generate errors if handler not loaded
     * @return string
     */
    public function getHandler($name = null, $module = null, $optional = false)
    {
        // if $module is not specified
        if (!isset($module)) {
            //if a module is loaded
            if (\XOOPS::registry("module")) {
                $module = \XOOPS::registry("module")->dirname;
            } else {
                trigger_error('No Module is loaded', E_USER_ERROR);
                return null;
            }
        }
        $name = strtolower($name);
        if (!isset($this->container["handler"][$module][$name])) {
            $hnd_file = $this->getPath($module) . "/class/{$name}.php";
            if (file_exists($hnd_file)) {
                include $hnd_file;
            }
            $class = $module . $name . 'Handler';
            if (class_exists($class)) {
                $this->container["handler"][$module][$name] = new $class($GLOBALS['xoopsDB']);
            } else {
                $this->container["handler"][$module][$name] = null;
                trigger_error("Handler does not exist: Module - {$module}; Name - {$name}", $optional ? E_USER_WARNING : E_USER_ERROR);
            }
        }
        return $this->container["handler"][$module][$name];
    }

    /**
     * Load a model for an application
     *
     * Model class file is located in /apps/app/model/example.php
     * with class name app_model_example
     *
     * @param string $name
     * @param string|null $module
     * @param array $options
     * @return object {@link Xoops_Zend_Db_Model}
     */
    public function getModel($name, $module = null, $options = array())
    {
        if (!isset($module)) {
            //if a module is loaded
            if (\XOOPS::registry("module")) {
                $module = \XOOPS::registry("module")->dirname;
            } else {
                trigger_error('No Module is loaded', E_USER_ERROR);
                return null;
            }
        }
        $name = strtolower($name);
        if (!isset($this->container["model"][$module][$name])) {
            if (!$dir = $this->getDirectory($module)) {
                $this->container["model"][$module][$name] = false;
                trigger_error('No Module is available', E_USER_ERROR);
                return null;
            }
            //$className = "app_" . $module . "_model_" . $name;
            $className = $module . "_model_" . $name;
            if (!class_exists($className, false)) {
                $classFile = $this->getPath($dir) . DIRECTORY_SEPARATOR . "models" . DIRECTORY_SEPARATOR . str_replace("_", DIRECTORY_SEPARATOR, $name) . ".php";
                if (file_exists($classFile)) {
                    include $classFile;
                }
            }
            if (!class_exists($className, false)) {
                $className = "Xoops_Model_Model";
                if (!isset($options["name"])) {
                    $options["name"] = $name;
                }
            }
            /*
            if (!isset($options["name"])) {
                $options["name"] = $module . "_" . $name;
            } else {
                $options["name"] = $module . "_" . $options["name"];
            }
            */
            $options["prefix"] = $module;
            $model = new $className($options);
            if (!$model->setupMetadata()) {
                $model = false;
            }

            $this->container["model"][$module][$name] = $model;
        }
        return $this->container["model"][$module][$name];
    }
}