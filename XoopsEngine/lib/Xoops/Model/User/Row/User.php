<?php
/**
 * XOOPS user model row
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

class Xoops_Model_User_Row_User extends Xoops_Model_User_Row_Account
{
    protected $profile;
    protected static $accountFields;
    protected static $profileFields;

    protected function inAccount($col)
    {
        return in_array($col, $this->getAccountFields());
    }

    protected function inProfile($col)
    {
        return in_array($col, $this->getProfileFields());
    }

    protected function getProfileFields()
    {
        if (!isset(static::$profileFields)) {
            //static::$profileFields = array_keys(XOOPS::service("registry")->user->read());
            static::$profileFields = XOOPS::getModel("user_profile")->info("cols");
        }
        return static::$profileFields;
    }

    protected function getAccountFields()
    {
        if (!isset(static::$accountFields)) {
            static::$accountFields = $this->_getTable()->info("cols");
        }
        return static::$accountFields;
    }

    public function profile()
    {
        if (!isset($this->profile)) {
            //$table = $this->_getTableFromString($this->_getTable()->getProfileTable());
            $table = XOOPS::getModel("user_profile");
            $primaryKey = $this->_getPrimaryKey();
            $this->profile = $table->findRow(array_values($primaryKey));
            if (!$this->profile) {
                $this->profile = $table->createRow($primaryKey);
            }
        }

        return $this->profile;
    }

    public function __get($key)
    {
        if ($this->inAccount($key)) {
            return parent::__get($key);
        }
        if ($this->inProfile($key)) {
            return $this->profile()->$key;
        }
        return null;
    }

    public function __set($key, $value)
    {
        if ($this->inAccount($key)) {
            parent::__set($key, $value);
        } elseif ($this->inProfile($key)) {
            $this->profile()->$key = $value;
        }
    }
}