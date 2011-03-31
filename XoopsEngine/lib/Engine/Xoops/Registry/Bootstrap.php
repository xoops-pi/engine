<?php
/**
 * XOOPS module bootstrap registry
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

class Bootstrap extends \Kernel\Registry
{
    protected function loadDynamic($options = array())
    {
        //$modules = \Xoops::service('registry')->module->read();
        $modules = \Xoops::service('module')->getMeta();
        $bootstraps = array();
        foreach ($modules as $dirname => $module) {
            if (empty($module['active'])) continue;
            $class = ("app" == $module['type'] ? "app" : "module" ) . '_' . $module['directory'] . "_bootstrap";
            if (class_exists($class)) {
                $bootstraps[] = $class;
            }
        }
        $bootstraps = array_unique($bootstraps);
        return $bootstraps;
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
?>