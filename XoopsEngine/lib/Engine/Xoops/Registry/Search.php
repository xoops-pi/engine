<?php
/**
 * XOOPS search registry
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

class Search extends \Kernel\Registry
{
    //protected $registry_key = "registry_search";

    /**
     * Load raw data
     *
     * @param   array   $options potential values for type: active, inactive, all
     * @return  array   keys: dirname => callback, func, file, active
     */
    protected function loadDynamic($options)
    {
        $model = \Xoops::getModel("search");

        $select = $model->select()->from($model, array("module", "callback", "func", "file"));
        if (!empty($options["active"])) {
            $select->where("active = ?", 1);
        } elseif (!is_null($options["active"])) {
            $select->where("active = ?", 0);
        }
        $modules = $model->getAdapter()->fetchAssoc($select);

        if (\Xoops::registry("user")->role !== "admin" && !empty($options["section"])) {
            $modelPage = \Xoops::getModel("page");
            $select = $modelPage->select()->from($modelPage, array("id", "module"))
                                    ->where("section = ?", "front")
                                    ->where("module IN (?)", array_keys($modules))
                                    ->where("controller = ?", "")
                                    ->where("action = ?", "");
            $moduleSet = $modelPage->fetchAll($select);
            $resources = array();
            foreach ($moduleSet as $row) {
                $resources[$row->id] = $row->module;
            }
            $acl = new \Xoops_Acl("front");
            $modelResource = $acl->getModel("resource");
            $select = $modelResource->select()->from($modelResource, array("id", "name"))->where("name IN (?)", array_keys($resources));
            $select->where("section = ?", $modelResource->getSection());
            $rowset = $modelResource->fetchAll($select);
            if (!$rowset || $rowset->count() == 0) {
                return array();
            }
            $resourceList = array();
            foreach ($rowset as $row) {
                $resourceList[$row->id] = $resources[$row->name];
            }
            $clause = new \Xoops_Zend_Db_Clause("resource IN (?)", array_keys($resourceList));
            $resources = $acl->getResources($clause);
            foreach ($resourceList as $id => $dirname) {
                if (!in_array($id, $resources)) {
                    unset($resourceList[$id]);
                }
            }
            $allowed = array_values($resourceList);
        }

        foreach (array_keys($modules) as $dirname) {
            if (isset($allowed) && !in_array($dirname, $allowed)) {
                unset($modules[$dirname]);
            }
            if (!empty($modules[$dirname]['callback'])) {
                $modules[$dirname]['callback'] = explode("::", $modules[$dirname]['callback']);
                //$module = \XOOPS::service("module")->getDirectory($dirname);
                //$prefix = "app" == \XOOPS::service("module")->getType($module) ? "app" : "module";
                //$modules[$dirname]['callback'][0] = $prefix . '_' . $module . '_' . $modules[$dirname]['callback'][0];
            }
        }

        return $modules;
    }

    protected function loadData(&$options = array())
    {
        if (isset($options['type']) && $options['type'] == 'install') {
            return $this->loadDynamic($options);
        }
        if (false === ($data = $this->loadCache($options))) {
            $data = $this->loadDynamic($options);
            $this->saveCache($data, $options);
        }
        return $data;
    }

    public function read($active = true, $role = null)
    {
        $options = compact('active', 'role');
        return $this->loadData($options);
    }

    public function create($active = true, $role = null)
    {
        self::delete($active, $role);
        self::read($active, $role);
        return true;
    }

    public function delete($active = null, $role = null)
    {
        $options = compact('active');
        if (!empty($role)) {
            $options["role"] = $role;
        }
        return $this->cache->clean('matchingTag', self::createTags($options));
    }

    public function flush()
    {
        return self::delete();
    }
}