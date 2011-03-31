<?php
/**
 * XOOPS plugin list registry
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

class Plugin extends \Kernel\Registry
{
    /**
     * Load installed themes, indexed by dirname, sorted by order
     *
     * @param array $options No use
     * @return array    keys: dirname, official
     */
    protected function loadDynamic($options = array())
    {
        $model = \Xoops::getModel("plugin");
        $select = $model->select()->from($model, array("dirname", "name", "official", "autoload"))
            ->where("active = ?", 1)
            ->order(array("order", "id"));
        $rowset = $model->fetchAll($select);
        $plugins = array();
        foreach ($rowset as $row) {
            $plugins[$row->dirname] = array(
                "name"          => $row->name,
                "official"      => $row->official,
                "autoload"      => $row->autoload,
            );
        }

        return $plugins;
    }

    public function read()
    {
        $options = array();
        return $this->loadData($options);
    }

    public function create()
    {
        self::delete();
        self::read();
        return true;
    }

    public function delete()
    {
        $options = array();
        return $this->cache->clean('matchingTag', self::createTags($options));
    }

    public function flush()
    {
        return self::delete();
    }
}