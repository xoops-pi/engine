<?php
/**
 * Zend Framework for Xoops Engine
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
 * @category        Xoops_Zend
 * @package         Db
 * @version         $Id$
 */

/**
 * XOOPS Row for Nested Set Model
 *
 * @see: {@link Xoops_Zend_Db_Model_Nest}
 */

class Xoops_Zend_Db_Table_Row_Node extends Zend_Db_Table_Row_Abstract
{
    /**
     * Transform a column name from the user-specified form
     * to the physical form used in the database.
     * You can override this method in a custom Row class
     * to implement column name mappings, for example inflection.
     *
     * @param string $columnName Column name given.
     * @return string The column name after transformation applied (none by default).
     * @throws Zend_Db_Table_Row_Exception if the $columnName is not a string.
     */
    protected function _transformColumn($columnName)
    {
        if (!is_string($columnName)) {
            require_once 'Zend/Db/Table/Row/Exception.php';
            throw new Zend_Db_Table_Row_Exception('Specified column is not a string');
        }
        if ($columnName == "id") {
            $columnName = $this->getTable()->id;
        } elseif ($columnName == "depth") {
            $columnName = $this->getTable()->depth;
        } elseif ($columnName == "left") {
            $columnName = $this->getTable()->left;
        } elseif ($columnName == "right") {
            $columnName = $this->getTable()->right;
        }
        /*
        elseif ($columnName == "id") {
            $columnName = array_shift($this->getTable()->info("primary"));
        }
        */
        // Perform no transformation by default
        return $columnName;
    }

    public function isLeaf()
    {
        return $this->right == $this->left + 1;
    }

    public function isRoot()
    {
        return $this->depth == 0;
    }
}
