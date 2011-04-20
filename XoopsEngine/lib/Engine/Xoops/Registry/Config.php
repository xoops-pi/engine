<?php
/**
 * XOOPS config registry
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
 * @package         Xoops_Core
 * @subpackage      Registry
 * @version         $Id$
 */

namespace Engine\Xoops\Registry;

class Config extends \Kernel\Registry
{
    protected function loadDynamic($options = array())
    {
        $moduleConfig = array();
        $module = "";
        $category = null;
        if (!empty($options['module'])) {
            if (!\Xoops::service('registry')->module->read($options['module'])) {
                return false;
            }
            $module = $options['module'];
        }
        if (isset($options['category'])) {
            if (is_numeric($options['category'])) {
                $modelCategory = \Xoops::getModel("config_category");
                if (!$row = $modelCategory->findRow($options['category'])) {
                    return false;
                }
                $category = $row->key;
            } else {
                $category = $options['category'];
            }
        }

        $modelConfig = \Xoops::getModel("config");
        $select = $modelConfig->select()->from($modelConfig, array("name", "value", "filter"))->where("module = ?", $module);
        if (isset($category)) {
            $select->where("category = ?", $category);
        }
        $rowset = $modelConfig->fetchAll($select);
        $configs = array();
        foreach ($rowset as $row) {
            $configs[$row->name] = $row->value;
        }
        /*
        if (empty($category)) {
            if (empty($module)) {
                $config_ini = \Xoops::path("var") . '/etc/config.custom.ini.php';
            } else {
                $config_ini = \Xoops::service('module')->getPath($module). '/configs/config.custom.ini.php';
            }
            if (file_exists($config_ini)) {
                $customConfig = \Xoops::loadConfig($config_ini);
                $configs = array_merge($customConfig, $configs);
            }
        }
        */
        return $configs;
    }

    public function read($module, $category = null)
    {
        //if (empty($module)) return false;
        $options = compact('module', 'category');
        return $this->loadData($options);
    }

    public function create($module, $category = null)
    {
        self::delete($module, $category);
        self::read($module, $category);
        return true;
    }

    public function delete($module, $category = null)
    {
        $options = compact('module', 'category');
        return $this->cache->clean('matchingTag', self::createTags($options));
    }

    public function flush($module = null)
    {
        return self::delete($module);
    }
}