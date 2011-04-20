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

class Module extends ServiceAbstract
{
    const FILE_META = "modules.ini.php";
    protected $container = array(
        // modules
        // Associative array of: type, directory, active, path
        "module"  => array(),
    );

    public function getMetaFile()
    {
        return static::FILE_META;
    }

    public function init($force = false)
    {
        if ($force || empty($this->container["module"])) {
            $persistKey = "module.directory.list." . \XOOPS::config("identifier");
            if (!$list = \XOOPS::persist()->load($persistKey)) {
                $list = parse_ini_file(\XOOPS::path("var") . "/etc/" . $this->getMetaFile(), true);
                if (!isset($list['default'])) {
                    $list['default'] = array(
                        "type"      => "app",
                        "directory" => "default",
                        "active"    => 1,
                    );
                }
                \XOOPS::persist()->save($list, $persistKey);
            }
            $this->container["module"] = $list;
        }
        return true;
    }

    public function getMeta($module = null)
    {
        $this->init();
        if (is_null($module)) {
            $return = $this->container["module"];
        } elseif (isset($this->container["module"][$module])) {
            $return = $this->container["module"][$module];
        } else {
            $return = false;
        }

        return $return;
    }

    public function loadConfig($module)
    {
        throw new \Exception('The abstract method can not be accessed directly');
    }

    public function loadInfo($module, $category = false)
    {
        throw new \Exception('The abstract method can not be accessed directly');
    }


    /**
     * Gets a module's type
     *
     * Valid types:
     *  app - the regular type, MVC implemented, located in Xoops::path('app');
     *  module - procedural or no MVC implemented, located in Xoops::path('module');
     *  legacy - legacy modules, procedural, located in Xoops::path('module');
     *  "" or empty - not available
     *
     * @param string $module a module's dirname or key name
     */
    public function getType($module)
    {
        if (!isset($this->container["module"][$module]['type'])) {
            if (is_dir($file = \XOOPS::path('app') . '/' . $module)) {
                $type = "app";
            } elseif (is_dir($file = \XOOPS::path('module') . '/' . $module)) {
                $type = is_file($file . "/xoops_version.php") ? "legacy" : "module";
            } else {
                $type = false;
            }
        } else {
            $type = $this->container["module"][$module]['type'];
        }
        return $type;
    }

    public function getPath($module)
    {
        if (isset($this->container["module"][$module])) {
            $module = $this->container["module"][$module]["directory"];
        }
        $type = $this->getType($module);
        if ($type == "app") {
            $path = \XOOPS::path('app') . '/' . $module;
        } elseif ($type == "legacy" || $type == "module") {
            $path = \XOOPS::path('module') . '/' . $module;
        } else {
            $path = false;
        }
        return $path;
    }

    /**
     * Gets a module's physical directory name.
     *
     * Usually a module's directory is equal to its folder name.
     * However, when module clone happends, which is implemented in Xoops Engine or X3,
     * a module's directory is its parent or root module's folder name while folder or 'dirname' by tradition is its key name.
     *
     * @param string $module a module's dirname or key name
     */
    public function getDirectory($module)
    {
        $directory = false;
        if (isset($this->container["module"][$module])) {
            $directory = $this->container["module"][$module]["directory"];
        } elseif ($this->getType($module)) {
            $directory = $module;
        }
        return $directory;
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
        throw new \Exception('The abstract method can not be accessed directly');
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
        throw new \Exception('The abstract method can not be accessed directly');
    }
}