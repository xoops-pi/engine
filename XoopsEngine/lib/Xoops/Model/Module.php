<?PHP
/**
 * XOOPS module model
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

class Xoops_Model_Module extends Xoops_Zend_Db_Model
{
    protected $_primary = "id";

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
        $tableName = $this->getAdapter()->foldCase($this->_name);
        $i = 1;
        $this->_metadata = array(
            "id"    => array(
                'SCHEMA_NAME'      => null,
                'TABLE_NAME'       => $tableName,
                'COLUMN_NAME'      => $this->getAdapter()->foldCase("id"),
                'COLUMN_POSITION'  => $i++,
                'DATA_TYPE'        => "smallint(5)",
                'DEFAULT'          => null,
                'NULLABLE'         => false,
                'LENGTH'           => null,
                'SCALE'            => null,
                'PRECISION'        => null,
                'UNSIGNED'         => true,
                'PRIMARY'          => true,
                'PRIMARY_POSITION' => 1,
                'IDENTITY'         => true
            ),
            "name"  => array(
                'SCHEMA_NAME'      => null,
                'TABLE_NAME'       => $tableName,
                'COLUMN_NAME'      => $this->getAdapter()->foldCase("name"),
                'COLUMN_POSITION'  => $i++,
                'DATA_TYPE'        => "varchar",
                'DEFAULT'          => '',
                'NULLABLE'         => false,
                'LENGTH'           => 64,
                'SCALE'            => null,
                'PRECISION'        => null,
                'UNSIGNED'         => true,
                'PRIMARY'          => false,
                'PRIMARY_POSITION' => null,
                'IDENTITY'         => false
            ),
            "version"   => array(
                'SCHEMA_NAME'      => null,
                'TABLE_NAME'       => $tableName,
                'COLUMN_NAME'      => $this->getAdapter()->foldCase("version"),
                'COLUMN_POSITION'  => $i++,
                'DATA_TYPE'        => "varchar",
                'DEFAULT'          => '',
                'NULLABLE'         => false,
                'LENGTH'           => 64,
                'SCALE'            => null,
                'PRECISION'        => null,
                'UNSIGNED'         => true,
                'PRIMARY'          => false,
                'PRIMARY_POSITION' => null,
                'IDENTITY'         => false
            ),
            "update"    => array(
                'SCHEMA_NAME'      => null,
                'TABLE_NAME'       => $tableName,
                'COLUMN_NAME'      => $this->getAdapter()->foldCase("update"),
                'COLUMN_POSITION'  => $i++,
                'DATA_TYPE'        => "int(10)",
                'DEFAULT'          => '0',
                'NULLABLE'         => false,
                'LENGTH'           => null,
                'SCALE'            => null,
                'PRECISION'        => null,
                'UNSIGNED'         => true,
                'PRIMARY'          => false,
                'PRIMARY_POSITION' => null,
                'IDENTITY'         => false
            ),
            "active"    => array(
                'SCHEMA_NAME'      => null,
                'TABLE_NAME'       => $tableName,
                'COLUMN_NAME'      => $this->getAdapter()->foldCase("active"),
                'COLUMN_POSITION'  => $i++,
                'DATA_TYPE'        => "tinyint(1)",
                'DEFAULT'          => '1',
                'NULLABLE'         => false,
                'LENGTH'           => null,
                'SCALE'            => null,
                'PRECISION'        => null,
                'UNSIGNED'         => true,
                'PRIMARY'          => false,
                'PRIMARY_POSITION' => null,
                'IDENTITY'         => false
            ),
            "dirname"   => array(
                'SCHEMA_NAME'      => null,
                'TABLE_NAME'       => $tableName,
                'COLUMN_NAME'      => $this->getAdapter()->foldCase("dirname"),
                'COLUMN_POSITION'  => $i++,
                'DATA_TYPE'        => "varchar",
                'DEFAULT'          => '',
                'NULLABLE'         => false,
                'LENGTH'           => 64,
                'SCALE'            => null,
                'PRECISION'        => null,
                'UNSIGNED'         => true,
                'PRIMARY'          => false,
                'PRIMARY_POSITION' => null,
                'IDENTITY'         => false
            ),
            "parent"    => array(
                'SCHEMA_NAME'      => null,
                'TABLE_NAME'       => $tableName,
                'COLUMN_NAME'      => $this->getAdapter()->foldCase("dirname"),
                'COLUMN_POSITION'  => $i++,
                'DATA_TYPE'        => "varchar",
                'DEFAULT'          => '',
                'NULLABLE'         => false,
                'LENGTH'           => 64,
                'SCALE'            => null,
                'PRECISION'        => null,
                'UNSIGNED'         => true,
                'PRIMARY'          => false,
                'PRIMARY_POSITION' => null,
                'IDENTITY'         => false
            ),
        );

        return true;
    }

    public function load($dirname)
    {
        $select = $this->select()->where("dirname = ?", $dirname);
        $row = $this->fetchRow($select);
        return $row;
    }
}