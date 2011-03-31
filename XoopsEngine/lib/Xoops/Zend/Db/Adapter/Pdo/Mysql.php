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

class Xoops_Zend_Db_Adapter_Pdo_Mysql extends Zend_Db_Adapter_Pdo_Mysql
{
    public $currentResult;

    /**
     * Default class name for a DB statement.
     *
     * @var string
     */
    protected $_defaultStmtClass = 'Xoops_Zend_Db_Statement_Pdo';

    /**
     * Prefix for tables in the database
     * @var string
     */
    public $prefix = '';

    public function __construct($config)
    {
        if (array_key_exists('driver_options', $config)) {
            if (!empty($config['driver_options'])) {
                $options = array();
                // can't use array_merge() because keys might be integers
                foreach ((array) $config['driver_options'] as $key => $value) {
                    if (is_int($key)) {
                        $options[$key] = $value;
                    }
                    if (is_string($key)) {
                        $attribute = 'PDO::ATTR_' . strtoupper($key);
                        if (defined($attribute)) {
                            $options[$attribute] = $value;
                        }
                    }
                }
                $config['driver_options'] = $options;
            }
        }
        parent::__construct($config);
        // Is dealt with in Zend_Db_Adapter_Pdo_Mysql::_connect
        /*
        if (!empty($config['charset']) && !defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
            $this->query("SET NAMES '" . $config['charset'] . "'");
        }
        */
        if (isset($this->_config['prefix'])) {
            $this->prefix = $this->_config['prefix'];
            unset($this->_config['prefix']);
        }
    }

    /**
     * Prefix a table name
     *
     * if tablename is empty, only prefix will be returned
     *
     * @param string $table tablename
     * @param string $module module dirname
     * @return string prefixed tablename, just prefix if tablename is empty
     */
    public function prefix($table = '', $dirname = '')
    {
        $segs = array($this->prefix);
        if (!empty($dirname)) {
            $segs[] = $dirname;
        }
        if (!empty($table)) {
            $segs[] = $table;
        }
        return implode("_", $segs);
    }

    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * Special handling for PDO query().
     * All bind parameter names must begin with ':'
     *
     * @param string|Zend_Db_Select $sql The SQL statement with placeholders.
     * @param array $bind An array of data to bind to the placeholders.
     * @return Zend_Db_Statement_Pdo
     * @throws Zend_Db_Adapter_Exception To re-throw PDOException.
     */
    public function query($sql, $bind = array())
    {
        $this->currentResult = parent::query($sql, $bind);
        return $this->currentResult;
    }

    /**
     * Executes an SQL statement and return the number of affected rows
     *
     * @param  mixed  $sql  The SQL statement with placeholders.
     *                      May be a string or Zend_Db_Select.
     * @return integer      Number of rows that were modified
     *                      or deleted by the SQL statement
     */
    public function exec($sql)
    {
        $this->currentResult = parent::exec($sql);
        return $this->currentResult;
    }

    /**
     * Convert an array, string, or Zend_Db_Expr object
     * into a string to put in a WHERE clause.
     *
     * @param mixed $where
     * @return string
     */
    protected function _whereExpr($where)
    {
        if ($where instanceof Xoops_Zend_Db_Clause) {
            $where = $where->render($this);
        }
        if (is_array($where) && isset($where["order"])) {
            $order = $where["order"];
            unset($where["order"]);
        }
        $return = parent::_whereExpr($where);
        if (!empty($order)) {
            $return .= " ORDER BY " . $order;
        }

        return $return;
    }

}
