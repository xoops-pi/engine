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
 * Legacy (XOOPS 2.x) databse driver class definition
 */

class Xoops_Zend_Db_Legacy //extends Xoops_Zend_Db_Adapter_Pdo_Mysql
{
    public $conn = null;

    protected $options = array();
    /**
     * Prefix for tables in the database
     * @var string
     */
    public $prefix = '';
    /**
     * If true, attempt to connect to the database upon instanciation
     * @var boolean
     */
    public $autoConnect = true;
    /**
     * If statements that modify the database are selected (see forceExec() to override this)
     * @var boolean
     */
    public $allowWebChanges = true;

    /**
     * reference to a {@link XoopsLogger} object
     * @see XoopsLogger
     * @var object XoopsLogger
     */
    protected $__logger = false;

    public function __construct(Zend_Db_Adapter_Abstract $engine, $options = array())
    {
        $this->conn = $engine;
        $this->prefix = isset($options['prefix']) ? $options['prefix'] : $this->prefix;

        return true;
    }

    /**
     * connect to the database
     *
     * @param bool $selectdb select the database now ?
     * @return bool successful?
     */
    public function connect($selectdb = true)
    {
        return true;
    }

    /**
     * close connection to the database
     *
     * @return bool successful?
     */
    public function close()
    {
        $this->conn = null;
        return true;
    }

    /**
     * Set database for current active connection
     *
     * @param   string  $dbname     database name
     * @return  bool successful?
     */
    public function selectDb($dbname = null)
    {
        return true;
    }

    /**
     * Quotes a string for use in a query.
     *
     */
    public function quote($string)
    {
        return $this->conn->quote($string);
    }

    /**
     * generate an ID for a new row
     *
     * This is for compatibility only. Will always return 0, because MySQL supports
     * autoincrement for primary keys.
     *
     * @param string $sequence name of the sequence from which to get the next ID
     * @return int always 0, because mysql has support for autoincrement
     */
    public function genId($sequence)
    {
        return 0; // will use auto_increment
    }

    /**
     * Get a result row as an enumerated array
     *
     * @param resource $result
     * @return array
     */
    public function fetchRow($result)
    {
        return $result->fetch(PDO::FETCH_NUM);
    }

    /**
     * Fetch a result row as an associative array
     *
     * @return array
     */
    public function fetchArray($result)
    {
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch a result row as an associative array
     *
     * @return array
     */
    public function fetchBoth($result)
    {
        return $result->fetch(PDO::FETCH_BOTH);
    }

    /**
     * Get the ID generated from the previous INSERT operation
     *
     * @return int
     */
    public function getInsertId()
    {
        return $this->conn->lastInsertId();
    }

    /**
     * Get number of rows in result
     *
     * @param resource query result
     * @return int
     */
    public function getRowsNum($result)
    {
        // Not guaranteed
        return $result->rowCount();
    }

    /**
     * Get number of affected rows
     *
     * @return int
     */
    public function getAffectedRows()
    {
        trigger_error(__METHOD__ . " is deprecated.", E_USER_DEPRECATED);
        //return mysql_affected_rows($this->conn->getConnection());
        if (is_object($this->conn->currentResult)) {
            $count = $this->conn->currentResult->rowCount();
        } else {
            $count = $this->conn->currentResult;
        }
        return $count;
    }

    /**
     * will free all memory associated with the result identifier result.
     *
     * @param resource query result
     * @return bool TRUE on success or FALSE on failure.
     */
    public function freeRecordSet($result)
    {
        return true;
    }

    /**
     * Returns the text of the error message from previous MySQL operation
     *
     * @return bool Returns the error text from the last MySQL function, or '' (the empty string) if no error occurred.
     */
    public function error()
    {
        $e = $this->conn->errorInfo();
        return $e[2];
    }

    /**
     * Returns the numerical value of the error message from previous MySQL operation
     *
     * @return int Returns the error number from the last MySQL function, or 0 (zero) if no error occurred.
     */
    public function errno()
    {
        return $this->conn->errorCode();
    }
    /**
     * Fetch extended error information associated with the last operation on the database handle
     *
     * <p>errorInfo() returns an array of error information about the last operation performed
     * by this database handle. The array consists of the following fields:<br />
     * 0: SQLSTATE error code (a five-character alphanumeric identifier defined in the ANSI SQL standard).<br />
     * 1: Driver-specific error code
     * 2: Driver-specific error message</p>
     *
     * <p class="note">This implementation doesn't support SQLSTATE (it will always be HY000)</p>
     */
    public function errorInfo()
    {
        return $this->conn->errorInfo();
    }

    /**
     * Fetch the SQLSTATE associated with the last operation on the database handle
     */
    public function errorCode()
    {
        return $this->conn->errorCode();
    }

    /**
     * Returns escaped string text with single quotes around it to be safely stored in database
     *
     * @param string $str unescaped string text
     * @return string escaped string text with single quotes around
     */
    public function quoteString($str)
    {
         return $this->quote($str);
    }

    /**
     * Get field name
     *
     * @param resource $result query result
     * @param int numerical field index
     * @return string
     */
    public function getFieldName($result, $offset)
    {
        //return mysql_field_name($result, $offset);
    }

    /**
     * Get field type
     *
     * @param resource $result query result
     * @param int $offset numerical field index
     * @return string
     */
    public function getFieldType($result, $offset)
    {
        //return mysql_field_type($result, $offset);
    }

    /**
     * Get number of fields in result
     *
     * @param resource $result query result
     * @return int
     */
    public function getFieldsNum($result)
    {
        return $result->columnCount();
    }

    /**
     * perform a query on the database
     *
     * @param string $sql a valid MySQL query
     * @param int $limit number of records to return
     * @param int $start offset of first record to return
     * @return resource query result or FALSE if successful
     * or TRUE if successful and no result
     */
    public function query($sql, $limit = 0, $start = 0)
    {
        $sql = ltrim($sql);
        if (!$this->allowWebChanges && strtolower(substr($sql, 0, 6)) != 'select')  {
            trigger_error('Database updates are not allowed during processing of a GET request', E_USER_WARNING);
            return false;
        }
        return $this->queryF($sql, $limit, $start);
    }

    public function queryF($sql, $limit = 0, $start = 0)
    {
        if (!empty($limit)) {
            $sql = $sql . ' LIMIT ' . (int)$start . ', ' . (int)$limit;
        }
        if ($result = $this->conn->query($sql)) {
            //$this->logEvent($sql);
        } else {
            //$this->logError($sql);
        }
        return $result;
    }

    /**
     * perform queries from SQL dump file in a batch
     *
     * @param string $file file path to an SQL dump file
     *
     * @return bool FALSE if failed reading SQL file or TRUE if the file has been read and queries executed
     */
    public function queryFromFile($file, &$logs)
    {
        Xoops_Zend_Db_File_Mysql::setDb($this);
        $status = Xoops_Zend_Db_File_Mysql::queryFile($file, $logs);
        Xoops_Zend_Db_File_Mysql::reset();
        return $status;

        if (false !== ($fp = fopen($file, 'r'))) {
            $sql_queries = trim(fread($fp, filesize($file)));
            Xoops_Zend_Db_File_Mysql::split($pieces, $sql_queries);
            foreach ($pieces as $query) {
                // [0] contains the prefixed query
                // [4] contains unprefixed table name
                $prefixed_query = Xoops_Zend_Db_File_Mysql::prefixQuery(trim($query), $this->prefix());
                if ($prefixed_query != false) {
                    $this->query($prefixed_query[0]);
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Send information about an event to the attached logger
     */
    public function __logEvent($msg)
    {
        if ($this->logger) {
            $this->logger->log($msg, 'info', 'queries');
        }
    }

    /**
     * Send information about an error to the attached logger
     */
    public function __logError($msg = null)
    {
        if ($this->logger) {
            $error = $this->errorInfo();
            $msg .= "<br />Error {$error[1]} ({$error[0]}): {$error[2]}";
            $this->logger->log($msg, 'error', 'queries');
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
    public function prefix($table = '', $dirname = null)
    {
        if ($dirname && $prefix = Xoops_Zend_Db::getPrefix($dirname)) {
            $dirname = $prefix;
        }
        return $this->conn->prefix($table, $dirname);

        if (empty($table)) {
            return $this->prefix;
        }
        return empty($this->prefix) ? $table : ($this->prefix . '_' . $table);
    }

    /**
     * assign a {@link XoopsLogger} object to the database
     * @deprecated (assign the logger public property)
     */
    public function __setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * set the prefix for tables in the database
     * @deprecated (assign the prefix public property)
     */
    public function setPrefix($value)
    {
        $this->prefix = $value;
    }
}