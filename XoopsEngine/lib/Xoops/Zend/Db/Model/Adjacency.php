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
 * XOOPS Adjacency List Model
 *
 * Managing Hierarchical Data with adjacency list model
 */

abstract Xoops_Zend_Db_Model_Adjacency extends Xoops_Zend_Db_Model
{
    protected $_primary = "id";
    public $parent   = "parent";

    /**
     * setOptions()
     *
     * @param array $options
     * @return Zend_Db_Table_Abstract
     */
    public function setOptions(Array $options)
    {
        if (isset($options['parent'])) {
            $this->parent = (string) $options['parent'];
            unset($options['parent']);
        }
        parent::setOptions($options);

        return $this;
    }

    /**
     * Initializes metadata.
     *
     * If metadata cannot be loaded from cache, adapter's describeTable() method is called to discover metadata
     * information. Returns true if and only if the metadata are loaded from cache.
     *
     * @return boolean
     * @throws Zend_Db_Table_Exception
     */
    protected function _setupMetadata()
    {
        if (!$this->parent) {
            require_once "Zend/Db/Table/Exception.php";
            throw new Zend_Db_Table_Exception('"parent" column name must be supplied.');
        }

        parent::_setupMetadata();

        if (!in_array($this->parent, array_keys($this->_metadata))) {
            require_once "Zend/Db/Table/Exception.php";
            throw new Zend_Db_Table_Exception('Supplied "parent" was not found.');
        }
    }

}
