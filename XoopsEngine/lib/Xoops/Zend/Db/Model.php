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

abstract class Xoops_Zend_Db_Model extends Xoops_Zend_Db_Table
{
    public function setupMetadata()
    {
        $status = true;
        try {
            $this->_setupMetadata();
            if (empty($this->_metadata)) {
                $status = false;
            }
        } catch (Zend_Db_Table_Exception $e) {
            $status = false;
        }

        return $status;
    }

    /**
     * Initialize table and schema names.
     *
     * If the table name is not set in the class definition,
     * it is translated from class name: app_model_postfix => app_postfix
     *
     * A schema name provided with the table name (e.g., "schema.table") overrides
     * any existing value for $this->_schema.
     *
     * @return void
     */
    protected function _setupTableName()
    {
        if (!$this->_name) {
            list($app, $name) = explode('_model_', strtolower(get_class($this)), 2);
            //$this->_name = (($app == 'xoops') ? "" : $app . "_") . $name;
            $this->_name = $name;
        } elseif (strpos($this->_name, '.')) {
            list($this->_schema, $this->_name) = explode('.', $this->_name);
        }
        if ($this->_name) {
            //$this->_name = $this->getAdapter()->prefix($this->_name);
            $this->_name = $this->prefix($this->_name);
        }
    }

    /**
     * Returns an instance of a Xoops_Zend_Db_Table_Select object.
     *
     * @param bool $withFromPart Whether or not to include the from part of the select based on the table
     * @return Zend_Db_Table_Select
     */
    public function select($withFromPart = self::SELECT_WITHOUT_FROM_PART)
    {
        $select = new Xoops_Zend_Db_Table_Select($this);
        if ($withFromPart == self::SELECT_WITH_FROM_PART) {
            $select->from($this, Zend_Db_Table_Select::SQL_WILDCARD, $this->info(self::SCHEMA));
        }
        return $select;
    }

    /**
     * Get entries matching given criteria which are defined by {@link Xoops_Zend_Db_Clause}
     *
     * @param   mixed  $clause     where string or {@link Xoops_Zend_Db_Clause}
     * @param   array   $columns    columns to fetch
     * @return  array
     */
    public function get($clause, $columns = array())
    {
        $select = $this->select()->where($clause);
        if (!empty($columns)) {
            $select->from($this, $columns);
        }
        if ($clause instanceof Xoops_Zend_Db_Clause) {
            foreach ($clause->getProperties() as $property => $args) {
                if (empty($args)) continue;
                if (!is_callable(array($select, $property))) continue;
                call_user_func_array(array($select, $property), $args);
            }
        }
        $rows = $select->query()->fetchAll();
        return $rows;
    }

    /**
     * Fetches row by primary key.  The argument specifies one or more primary
     * key value(s).  To find multiple rows by primary key, the argument must
     * be an array.
     *
     * This method accepts a variable number of arguments.  If the table has a
     * multi-column primary key, the number of arguments must be the same as
     * the number of columns in the primary key.  To find multiple rows in a
     * table with a multi-column primary key, each argument must be an array
     * with the same number of elements.
     *
     * The find() method always returns a Row object, even if multiple rows
     * were found.
     *
     * @param  mixed $key The value(s) of the primary keys.
     * @return Zend_Db_Table_Row_Abstract Row(s) matching the criteria.
     * @throws Zend_Db_Table_Exception
     */
    public function findRow()
    {
        $this->_setupPrimaryKey();
        $args = func_get_args();
        $keyNames = array_values((array) $this->_primary);

        if (count($args) < count($keyNames)) {
            //require_once 'Zend/Db/Table/Exception.php';
            throw new Zend_Db_Table_Exception("Too few columns for the primary key");
        }

        if (count($args) > count($keyNames)) {
            //require_once 'Zend/Db/Table/Exception.php';
            throw new Zend_Db_Table_Exception("Too many columns for the primary key");
        }

        $whereList = array();
        $numberTerms = 0;
        foreach ($args as $keyPosition => $keyValues) {
            $keyValuesCount = count($keyValues);
            // Coerce the values to an array.
            // Don't simply typecast to array, because the values
            // might be Zend_Db_Expr objects.
            if (!is_array($keyValues)) {
                $keyValues = array($keyValues);
            }
            if ($numberTerms == 0) {
                $numberTerms = $keyValuesCount;
            } else if ($keyValuesCount != $numberTerms) {
                require_once 'Zend/Db/Table/Exception.php';
                throw new Zend_Db_Table_Exception("Missing value(s) for the primary key");
            }
            $keyValues = array_values($keyValues);
            for ($i = 0; $i < $keyValuesCount; ++$i) {
                if (!isset($whereList[$i])) {
                    $whereList[$i] = array();
                }
                $whereList[$i][$keyPosition] = $keyValues[$i];
            }
        }

        $whereClause = null;
        if (count($whereList)) {
            $whereOrTerms = array();
            $tableName = $this->_db->quoteTableAs($this->_name, null, true);
            foreach ($whereList as $keyValueSets) {
                $whereAndTerms = array();
                foreach ($keyValueSets as $keyPosition => $keyValue) {
                    $type = $this->_metadata[$keyNames[$keyPosition]]['DATA_TYPE'];
                    $columnName = $this->_db->quoteIdentifier($keyNames[$keyPosition], true);
                    $whereAndTerms[] = $this->_db->quoteInto(
                        $tableName . '.' . $columnName . ' = ?',
                        $keyValue, $type);
                }
                $whereOrTerms[] = '(' . implode(' AND ', $whereAndTerms) . ')';
            }
            $whereClause = '(' . implode(' OR ', $whereOrTerms) . ')';
        }

        // empty where clause should return null
        if ($whereClause == null) {
            return null;
        }

        return $this->fetchRow($whereClause);
    }
}