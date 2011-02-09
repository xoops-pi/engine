<?php
/**
 * XOOPS theme list registry
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       The Xoops Engine http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         Xoops_Core
 * @subpackage      Registry
 * @version         $Id$
 */

namespace Engine\Xoops\Registry;

class Theme extends \Kernel\Registry
{
    /**
     * Load installed themes, indexed by dirname, sorted by order
     *
     * @param array $options No use
     * @return array    keys: dirname, name, screenshot, author
     */
    protected function loadDynamic($options = array())
    {
        $model = \Xoops::getModel("theme");
        //Debug::backtrace();
        $select = $model->select()->from($model, array("dirname", "name", "screenshot"))
            ->where("active = ?", 1)
            ->order(array("order", "parent"));
        $rowset = $model->fetchAll($select);
        $themes = array();
        foreach ($rowset as $row) {
            $themes[$row->dirname] = array(
                "name"          => $row->name,
                "screenshot"    => $row->screenshot,
            );
        }
        if (!isset($themes["default"])) {
            $themes["default"] = array(
                "name"          => "Default",
                "screenshot"    => "screenshot.png",
            );
        }

        return $themes;
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