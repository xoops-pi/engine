<?php
/**
 * XOOPS plugin service class
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

class Plugin extends \Kernel\Service\Plugin
{
    protected $container = array(
        "plugin"    => array(),
        'list'      => null,
        "model"     => array(),
    );

    public function loadConfig($plugin)
    {
        return \XOOPS::service('registry')->option->read($plugin);
    }

    public function getList()
    {
        if (!isset($this->container['list'])) {
            $this->container['list'] = \XOOPS::service("registry")->plugin->read();
        }

        return $this->container['list'];
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
        $name = strtolower($name);
        if (empty($plugin)) {
            list($plugin, $name) = explode("_", $name, 2);
            if (empty($name)) {
                return null;
            }
        } else {
            $plugin = strtolower($plugin);
        }
        if (!isset($this->container['model'][$plugin][$name])) {
            $className = "plugin_" . $plugin . "_model_" . $name;
            if (!class_exists($className)) {
                $className = "Xoops_Model_Model";
                if (!isset($options["name"])) {
                    $options["name"] = $plugin . "_" . $name;
                }
            }
            /*
            if (!isset($options["name"])) {
                $options["name"] = $module . "_" . $name;
            } else {
                $options["name"] = $module . "_" . $options["name"];
            }
            */
            $options["prefix"] = \Xoops_Zend_Db::getPrefix("plugin");
            $model = new $className($options);
            if (!$model->setupMetadata()) {
                $model = false;
            }

            $this->container['model'][$plugin][$name] = $model;
        }
        return $this->container['model'][$plugin][$name];
    }
}