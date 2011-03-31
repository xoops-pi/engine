<?php
/**
 * XOOPS user profile model row
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
 * @package         Xoops_Model
 * @version         $Id$
 */

class Xoops_Model_User_Row_Profile extends Xoops_Zend_Db_Table_Row
{
    protected static $meta;

    protected function getMeta($key)
    {
        if (!isset(static::$meta)) {
            static::$meta = XOOPS::service("registry")->user->read();
        }
        $meta =& static::$meta[$key];
        if (!empty($meta["method"])) {
            if (is_array($meta["method"])) {
                if (!class_exists($meta["method"][0]) || !is_callable($meta["method"])) {
                    $meta["method"] = null;
                }
            } else {
                $meta["method"] = null;
            }
        }
        return $meta;
    }

    public function display($col = null)
    {
        $result = array();
        if (!isset($col)) {
            foreach (array_keys($this->_data) as $key) {
                $ret = $this->transformMeta($key);
                if (!is_null($ret)) {
                    $result[$key] = $ret;
                }
            }
        } else {
            $result = $this->transformMeta($col);
        }

        return $result;
    }

    protected function transformMeta($key)
    {
        $value = $this->{$key};
        if (!is_null($value)) {
            $meta = static::getMeta($key);
            if (isset($meta["method"])) {
                if (empty($meta["method"])) {
                    $value = null;
                } elseif (is_array($meta["method"])) {
                    $value = call_user_func($meta["method"], $value);
                } elseif (empty($value)) {
                    //$value = null;
                }
            } elseif (isset($meta["options"][$value])) {
                $value = $meta["options"][$value];
            } elseif (empty($value)) {
                //$value = null;
            }
        }
        return $value;
    }
}
