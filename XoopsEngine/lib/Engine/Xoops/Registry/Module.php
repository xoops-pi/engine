<?php
/**
 * XOOPS module registry
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

class Module extends \Kernel\Registry
{
    protected function loadDynamic($options = array())
    {
        $modules = array();
        $modelModule = \Xoops::getModel("module");
        $rowset = $modelModule->fetchAll();
        foreach ($rowset as $module) {
            $dirname = $module->dirname;
            $modules[$dirname] = array(
                "id"        => $module->id,
                "name"      => $module->name,
                "active"    => $module->active,
                "parent"    => $module->parent,
                "type"      => \Xoops::service('module')->getType($dirname) ?: "app",
                //"path"      => (\Xoops::service('module')->getType($dirname) != 'legacy') ? "app" : "modules",
            );
        }
        return $modules;
    }

    public function read($module = null)
    {
        $data = $this->loadData();
        $ret = empty($module)
                    ? $data
                    : (isset($data[$module])
                        ? $data[$module]
                        : false);
        return $ret;
    }

    public function create($module = null)
    {
        self::delete($module);
        self::read($module);
        return true;
    }

    public function delete($module = null)
    {
        $options = array();
        return $this->cache->clean('matchingTag', self::createTags($options));
    }

    public function flush($module = null)
    {
        return self::delete($module);
    }
}