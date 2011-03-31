<?php
/**
 * XOOPS module list registry
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

class Modulelist extends \Kernel\Registry
{
    //protected $registry_key = "registry_modulelist";

    /**
     * Load raw data
     *
     * @param   array   $options potential values for type: installed, active, inactive, install
     * @return  array    keys: dirname, name, (mid), weight, image, author, version
     */
    protected function loadDynamic($options)
    {
        $type = isset($options['type']) ? $options['type'] : 'installed';
        $modules = array();
        if ($type == 'install') {
            $moduleList = array();
            $skipList = array_keys(self::read('installed'));
            $skipList[] = \Xoops::registry('frontController')->getDefaultModule();

            $iterators[] = new \DirectoryIterator(\Xoops::path('app'));
            $iterators[] = new \DirectoryIterator(\Xoops::path('module'));
            foreach ($iterators as $iterator) {
                foreach ($iterator as $fileinfo) {
                    if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                        continue;
                    }
                    $module = $fileinfo->getFilename();
                    if (in_array($module, $skipList) || preg_match("/[^a-z0-9_]/i", $module)) {
                        continue;
                    }
                    $skipList[] = $module;
                    $info = \Xoops::service('module')->loadInfo($module);
                    if (empty($info)) {
                        continue;
                    }
                    $modules[$module] = array(
                        'name'      => $info['name'],
                        'version'   => $info['version'],
                        'parent'    => isset($info['parent']) ? $info['parent'] : "",
                        'email'     => isset($info['email']) ? $info['email'] : "",
                        "type"      => \Xoops::service('module')->getType($module),
                        'logo'      => $info['logo']
                    );
                }
            }
            return $modules;
        }

        $modelModule = \Xoops::getModel("module");
        $select = $modelModule->select();
        if ($type == 'active') {
            $select->where('active = ?', 1);
        } elseif ($type == 'inactive') {
            $select->where('active = ?', 0);
        }
        $rowset = $modelModule->fetchAll($select);
        foreach ($rowset as $module) {
            $dirname = $module->dirname;
            $info = \Xoops::service('module')->loadInfo($dirname);
            $modules[$dirname] = array(
                "id"        => $module->id,
                "name"      => $module->name,
                "active"    => $module->active,
                "version"   => $module->version,
                "parent"    => $module->parent,
                "email"     => isset($info['email']) ? $info['email'] : "",
                //"update"    => formatTimestamp($module->update, 'm'),
                "update"    => $module->update,
                "type"      => \Xoops::service('module')->getType($dirname),
                "logo"      => $info['logo'],
            );
        }

        if (\Xoops::registry("user")->role == "admin" || empty($options["section"])) {
            return $modules;
        }
        $modelPage = \Xoops::getModel("page");
        $select = $modelPage->select()->from($modelPage, array("id", "module"))
                                ->where("section = ?", $options["section"])
                                ->where("module IN (?)", array_keys($modules))
                                ->where("controller = ?", "")
                                ->where("action = ?", "");
        $moduleSet = $modelPage->fetchAll($select);
        $resources = array();
        foreach ($moduleSet as $row) {
            $resources[$row->id] = $row->module;
        }
        $acl = new \Xoops_Acl($options["section"]);
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
        foreach (array_keys($modules) as $dirname) {
            if (!in_array($dirname, $allowed)) {
                unset($modules[$dirname]);
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

    public function read($type = 'installed', $section = null, $role = null)
    {
        $options = compact('type', 'section', 'role');
        return $this->loadData($options);
    }

    public function create($type = 'installed', $section = null, $role = null)
    {
        self::delete($type, $section, $role);
        self::read($type, $section, $role);
        return true;
    }

    public function delete($type = null, $section = null, $role = null)
    {
        //$options = compact('type', 'section', 'role');
        $options = compact('type', 'section');
        if (!empty($role)) {
            $options["role"] = $role;
        }
        return $this->cache->clean('matchingTag', self::createTags($options));
    }

    public function flush($type = null)
    {
        return self::delete($type);
    }
}