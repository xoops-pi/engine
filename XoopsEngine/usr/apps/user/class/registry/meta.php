<?php
/**
 * User meta-category list registry
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Xoops Engine http://www.xoopsengine.org
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         Xoops_Core
 * @subpackage      Registry
 * @version         $Id$
 */

namespace App\User\Registry;

//class App_User_Registry_Meta extends \Kernel\Registry
class Meta extends \Kernel\Registry
{
    protected function loadDynamic($options)
    {
        $modelCategory = \Xoops::service('module')->getModel("category", "user");
        $select = $modelCategory->select()->order("order ASC")->from($modelCategory, array("key", "id", "title"));
        $categoryList = $modelCategory->getAdapter()->fetchAssoc($select);

        $action = isset($options["action"]) ? $options["action"] : "view";
        $metaList = \XOOPS::service("registry")->user->read($action);
        $modelMetaCategory = \Xoops::service('module')->getModel("meta_category", "user");
        /*
        $query = $modelMeta->getAdapter()->select()
                      ->from(array('m' => $modelMeta->info("name")), array('key', 'title'))
                      ->join(array('c' => $modelMetaCategory->info("name")), 'c.meta = m.key', array('category'))
                      ->order('c.order ASC');
        $rowset = $modelMeta->getAdapter()->fetchAll($query);
        */
        $select = $modelMetaCategory->select()->order(array("order ASC"))->from($modelMetaCategory, array("meta", "category"));
        $rowset = $modelMetaCategory->fetchAll($select);
        foreach ($rowset as $row) {
            $categoryKey = $row->category;
            if ($categoryKey == "-") {
                unset($metaList[$row->meta]);
            }
            if (!isset($categoryList[$categoryKey]) || !isset($metaList[$row->meta])) {
                continue;
            }
            $categoryList[$categoryKey]["meta"][$row->meta] = $metaList[$row->meta];
            unset($metaList[$row->meta]);
        }
        foreach (array_keys($categoryList) as $key) {
            if (empty($categoryList[$key]["meta"])) {
                unset($categoryList[$key]);
            }
        }
        if (!empty($metaList)) {
            $categoryList["misc"] = array(
                "title" => \XOOPS::_("Misc"),
            );
            foreach ($metaList as $key => $meta) {
                $categoryList["misc"]["meta"][$key] = $metaList[$key];
            }
        }
        return $categoryList;
    }

    public function read($action = "view", $meta = null)
    {
        $options = compact("action");
        $data = $this->loadData($options);
        if (isset($meta)) {
            $result = isset($data[$meta]) ? $data[$meta] : false;
        } else {
            $result = $data;
        }

        return $result;
    }

    public function create($action = "view")
    {
        self::delete($action);
        self::read($action);
        return true;
    }

    public function delete($action = null)
    {
        $options = compact("action");
        return $this->cache->clean('matchingTag', self::createTags($options));
    }

    public function flush()
    {
        return self::delete();
    }
}