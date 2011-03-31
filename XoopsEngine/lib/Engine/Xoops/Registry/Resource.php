<?php
/**
 * XOOPS ACL resource registry
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

class Resource extends \Kernel\Registry
{
    //protected $registry_key = "registry_resource";

    protected function loadDynamic($options = array())
    {
        $ancestors = array();
        $model = \Xoops::getModel("acl_resource")->setSection($options['section']);
        $select = $model->select()->where('section = ?', $options['section']);
        if (!is_null($options['module'])) {
            $select->where('module = ?', $options['module']);
        }
        $result = $model->fetchAll($select);
        if (empty($result)) {
            return $ancestors;
        }
        foreach ($result as $row) {
            $ancestors[$row->name] = $model->getAncestors($row, "id");
        }
        return $ancestors;
    }

    public function read($section, $module = null)
    {
        $options = compact('section', 'module');
        return $this->loadData($options);
    }

    public function create($section, $module = null)
    {
        $options = compact('section', 'module');
        self::read($module, $section);
        return true;
    }

    public function delete($section, $module = null)
    {
        $options = compact('section', 'module');
        return $this->cache->clean('matchingTag', self::createTags($options));
    }

    public function flush($module = null)
    {
        return self::delete(null, $module);
    }
}