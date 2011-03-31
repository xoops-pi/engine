<?php
/**
 * XOOPS Config Model Row
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

class Xoops_Model_Config_Row extends Xoops_Zend_Db_Table_Row
{
    /**
     * Retrieve row field value
     *
     * @param  string $columnName The user-specified column name.
     * @return string             The corresponding column value.
     * @throws Zend_Db_Table_Row_Exception if the $columnName is not a column in the row.
     */
    public function __get($columnName)
    {
        $value = parent::__get($columnName);
        if ($columnName == "edit" && !empty($value) && is_string($value)) {
            $value = unserialize($value);
            return $value;
        }
        if ($columnName != "value") {
            return $value;
        }
        $filter = $this->_data["filter"];
        //switch($this->_data["valuetype"]) {
        switch($filter) {
            case 'int':
                $filter = "number_int";
                break;
            case 'array':
                //$value = unserialize($value);
                $filter = "unserialize";
                break;
            case 'float':
                $filter = "number_float";
                break;
            case 'textarea':
                $filter = "special_chars";
                break;
            //case 'string':
            case 'text':
                $filter = "string";
                break;
            /*
            case 'email':
                $filter = "email";
                break;
            case 'url':
                $filter = "url";
                break;
            */
            default:
                break;
        }
        if (empty($filter)) {
        } elseif ($filter_id = filter_id($filter)) {
            $value = filter_var($value, $filter_id);
        } elseif (function_exists($filter)) {
            $value = filter_var($value, FILTER_CALLBACK, array("options" => $filter));
        }
        return $value;
    }

    /**
     * Set row field value
     *
     * @param  string $columnName The column key.
     * @param  mixed  $value      The value for the property.
     * @return void
     * @throws Zend_Db_Table_Row_Exception
     */
    public function __set($columnName, $value)
    {
        $columnName = $this->_transformColumn($columnName);
        if (!array_key_exists($columnName, $this->_data)) {
            require_once 'Zend/Db/Table/Row/Exception.php';
            throw new Zend_Db_Table_Row_Exception("Specified column \"{$columnName}\" is not in the row");
        }
        $this->_data[$columnName] = $value;
        $this->_modifiedFields[$columnName] = true;
    }

    /**
     * Allows pre-insert logic to be applied to row.
     * Subclasses may override this method.
     *
     * @return void
     */
    protected function _insert()
    {
        //if (isset($this->_modifiedFields["value"])) {
            $this->filterInput();
        //}
    }

    /**
     * Allows pre-update logic to be applied to row.
     * Subclasses may override this method.
     *
     * @return void
     */
    protected function _update()
    {
        //if (isset($this->_modifiedFields["value"])) {
            $this->filterInput();
        //}
    }

    protected function filterInput()
    {
        if (isset($this->_modifiedFields["value"])) {
            switch($this->_data["filter"]) {
                case 'array':
                    $value = $this->_data["value"];
                    if (!is_array($value)) {
                        $value = explode('|', $value);
                    }
                    $this->_data["value"] = serialize($value);
                    break;
                default:
                    break;
            }
        }
        if (isset($this->_modifiedFields["edit"]) && !empty($this->_data["edit"])) {
            $this->_data["edit"] = serialize($this->_data["edit"]);
        }
    }
}
