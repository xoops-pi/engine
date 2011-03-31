<?php
/**
 * XOOPS notification registry
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

class Notification extends \Kernel\Registry
{
    protected function loadDynamic($options = array())
    {
        $model = \Xoops::service('plugin')->getModel('notification_category');
        $select = $model->select();
        $select->where('module = ?', $options['module']);
        $select->where('active = ?', 1);
        $itemList = $model->fetchAll($select);
        $items = array();
        foreach ($itemList as $item) {
            $key = (isset($options["category"])) ? $item["name"] : $item['controller'] .  "-" . $item['action'];
            $items[$key][$item["name"]] = array(
                "id"        => $item["id"],
                //"key"       => $item["key"],
                "param"     => $item["param"],
                //"translate" => $item["translate"],
                "title"     => $item["title"],
            );
        }

        return $items;
    }

    public function read($module, $category = null)
    {
        $options = compact('module', 'category');
        $data = $this->loadData($options);
        //Debug::e($data);
        if (is_null($category)) {
            return $data;
        }
        if (isset($data[$category])) {
            return $data[$category];
        }
        return false;
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