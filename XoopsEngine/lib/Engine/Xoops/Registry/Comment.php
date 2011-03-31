<?php
/**
 * XOOPS comment registry
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

class Comment extends \Kernel\Registry
{
    protected function loadDynamic($options = array())
    {
        $model = \Xoops::service('plugin')->getModel('comment_category');
        $select = $model->select();
        $select->where('module = ?', $options['module']);
        $select->where('active = ?', 1);
        $itemList = $model->fetchAll($select);
        $items = array();
        foreach ($itemList as $item) {
            $key = $item['controller'] .  "-" . $item['action'];
            $items[$key] = array(
                "id"            => $item["id"],
                "key"           => $item["key"],
                "param_item"    => $item["param_item"],
                "param_page"    => $item["param_page"],
                "template"      => $item["template"],
                "expire"        => $item["expire"],
            );
        }

        return $items;
    }

    public function read($module)
    {
        $options = compact('module');
        return $this->loadData($options);
    }

    public function create($module)
    {
        $options = compact('module');
        self::read($module);
        return true;
    }

    public function delete($module = null)
    {
        $options = compact('module');
        return $this->cache->clean('matchingTag', self::createTags($options));
    }

    public function flush($module = null)
    {
        return self::delete($module);
    }
}