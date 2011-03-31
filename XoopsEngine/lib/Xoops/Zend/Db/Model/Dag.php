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
 * XOOPS Directed Acyclic Graph or Partilly Ordered Set Model
 *
 * Managing Partially Ordered Data with DAG
 * @see http://en.wikipedia.org/wiki/Directed_acyclic_graph
 * @see http://www.codeproject.com/KB/database/Modeling_DAGs_on_SQL_DBs.aspx
 */

abstract Xoops_Zend_Db_Model_Dag extends Xoops_Zend_Db_Model
{
    protected $_primary = "id";
    public $properties = array(
        // Start vertex column name
        "start"     => "start",
        // End vertex column name
        "end"       => "end",
        // Entry edge column name
        "entry"     => "entry",
        // Direct edge column name
        "direct"    => "direct",
        // Exit edge column name
        "exit"      => "exit",
        // Number of hops from start to end
        "hops"      => "hops",
    );

    /**
     * Classname for rowset
     *
     * @var string
     */
    protected $_rowsetClass = 'Xoops_Zend_Db_Table_Rowset_Vertex';

    /**
     * setOptions()
     *
     * @param array $options
     * @return Zend_Db_Table_Abstract
     */
    public function setOptions(Array $options)
    {
        foreach ($this->properties as $property => &$value) {
            if (isset($options[$property])) {
                $value = (string) $options[$property];
                unset($options[$property]);
            }
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
        foreach ($this->properties as $property => $value) {
            if (empty($value)) {
                require_once "Zend/Db/Table/Exception.php";
                throw new Zend_Db_Table_Exception('Column name " . $property . " must be supplied.');
            }
        }

        parent::_setupMetadata();

        if (count(array_intersect(array_keys($this->properties), array_keys($this->_metadata))) < count(array_keys($this->properties))) {
            require_once "Zend/Db/Table/Exception.php";
            throw new Zend_Db_Table_Exception('Supplied columns were not found.');
        }
    }

}
