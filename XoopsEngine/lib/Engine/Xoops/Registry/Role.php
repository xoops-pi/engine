<?php
/**
 * XOOPS ACL role registry
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

class Role extends \Kernel\Registry
{
    //protected $registry_key = "registry_role";

    protected function loadDynamic($options = array())
    {
        $model = \Xoops::getModel("acl_role");
        $ancestors = $model->getAncestors($options['role']);
        return $ancestors;
    }

    public function read($role)
    {
        $options = compact('role');
        return $this->loadData($options);
    }

    public function create($role)
    {
        self::delete($role);
        self::read($role);
        return true;
    }

    public function delete($role = null)
    {
        if (!empty($role)) {
            $options = compact('role');
        } else {
            $options = array();
        }
        return $this->cache->clean('matchingTag', self::createTags($options));
    }

    public function flush($role = null)
    {
        return self::delete($role);
    }
}