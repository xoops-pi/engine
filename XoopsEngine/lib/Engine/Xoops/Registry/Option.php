<?php
/**
 * XOOPS plugin config registry
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

class Option extends \Kernel\Registry
{
    protected static $configs;

    protected function loadDynamic($options = array())
    {
        $configs = array();
        $plugins = \Xoops::service('registry')->plugin->read();
        if (empty($plugins)) {
            return $configs;
        }

        $modelConfig = \Xoops::getModel("config");
        $select = $modelConfig->select()->from($modelConfig, array("name", "category", "value", "filter"))->where("module = ?", "plugin");
        $select->where("category IN (?)", array_keys($plugins));
        $rowset = $modelConfig->fetchAll($select);
        foreach ($rowset as $row) {
            $configs[$row->category][$row->name] = $row->value;
        }
        return $configs;
    }

    public function read($plugin = null)
    {
        if (!isset(static::$configs)) {
            $options = array();
            static::$configs = $this->loadData($options);
        }
        if (is_null(static::$configs)) {
            $return = static::$configs;
        } elseif (isset(static::$configs[$plugin])) {
            $return = static::$configs[$plugin];
        } else {
            $return = array();
        }
        return $return;
    }

    public function create($plugin = null)
    {
        self::delete($plugin);
        self::read($plugin);
        return true;
    }

    public function delete($plugin = null)
    {
        static::$configs = null;
        $options = array();
        return $this->cache->clean('matchingTag', self::createTags($options));
    }

    public function flush($plugin = null)
    {
        return self::delete($plugin);
    }
}