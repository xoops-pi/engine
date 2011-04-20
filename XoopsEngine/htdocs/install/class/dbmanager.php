<?php
/**
 * Database manager for XOOPS installer
 *
 * See the enclosed file license.txt for licensing information.
 * If you did not receive this file, get it at http://www.fsf.org/copyleft/gpl.html
 *
 * @copyright   The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license     BSD License
 * @package     installer
 * @since       3.0
 * @author      Haruki Setoyama  <haruki@planewave.org>
 * @author      Skalpa Keo <skalpa@xoops.org>
 * @author      Taiwen Jiang <phppp@users.sourceforge.net>
 * @version     $Id$
 */
class db_manager
{
    private $successStrings = array(
        'create'    => TABLE_CREATED,
        'insert'    => ROWS_INSERTED,
        'alter'     => TABLE_ALTERED,
        'drop'      => TABLE_DROPPED,
       );
    private $failureStrings = array(
        'create'    => TABLE_NOT_CREATED,
        'insert'    => ROWS_FAILED,
        'alter'     => TABLE_NOT_ALTERED,
        'drop'      => TABLE_NOT_DROPPED,
       );
    private $logs = array();
    //private $s_tables = array();
    //private $f_tables = array();
    public $db;
    //public $db_options = array();

    public function __construct()
    {
        $options = XOOPS::loadConfig("resource.db.ini.php");
        $this->db = new Xoops_Zend_Db_Legacy(XOOPS::registry('db'), $options);
        return;

        if (empty($GLOBALS['xoopsDB'])) {
            $options = XOOPS::loadConfig("resource.db.ini.php");

            $db = Xoops_Zend_Db::factory($options['adapter'], $options);
            Zend_Db_Table_Abstract::setDefaultAdapter($db);
            XOOPS::registry('db', $db);
            $GLOBALS['xoopsDB'] = new Xoops_Zend_Db_Legacy($db, $options);
            //$this->db = XOOPS::loadService("database", $this->db_options)->getConnection();
        }
        $this->db = $GLOBALS['xoopsDB'];
    }

    public function isConnectable()
    {
        return ($this->db->connect(false) != false) ? true : false;
    }

    public function dbExists()
    {
        return ($this->db->connect() != false) ? true : false;
    }

    public function queryFromFile($sql_file_path)
    {
        if (!file_exists($sql_file_path)) {
            return false;
        }
        Xoops_Zend_Db_File_Mysql::setDb($this->db);
        $status = Xoops_Zend_Db_File_Mysql::queryFile($sql_file_path, $logs);
        $this->logs = array_merge($this->logs, $logs);
        return $status;
    }

    public function report()
    {
        $commands = array('create', 'insert', 'alter', 'drop');
        $content = '<ul class="log">';
        foreach ($commands as $cmd) {
            if (empty($this->logs[$cmd])) continue;
            foreach ($this->logs[$cmd] as $table => $logs) {
                foreach ($logs as $status => $items) {
                    //$status = empty($status) ? 'failure' : 'success';
                    list($status, $string) = empty($status) ? array('failure', $this->failureStrings[$cmd]) : array('success', $this->successStrings[$cmd]);
                    $content .= "<li class='{$status}'>";
                    $content .= ($cmd != 'insert') ? sprintf($string, $table) : sprintf($string, count($items), $table);
                    $content .= "</li>\n";
                }
            }
        }
        $content .= '</ul>';
        $this->logs = array();
        return $content;
    }

    public function query($sql)
    {
        $this->db->connect();
        return $this->db->query($sql);
    }

    public function prefix($table)
    {
        $this->db->connect();
        return $this->db->prefix($table);
    }

    public function fetchArray($ret)
    {
        $this->db->connect();
        return $this->db->fetchArray($ret);
    }

    public function insert($table, $query)
    {
        $this->db->connect();
        $query = 'INSERT INTO ' . $this->db->prefix($table) . ' ' . $query;
        $result = $this->db->queryF($query);
        $status = empty($result) ? 0 : 1;
        $this->logs["insert"][$table][$status][] = microtime(true);
        return $status ? $this->db->getInsertId() : 0;
    }

    public function tableExists($table, $type = "core")
    {
        $ret = false;
        if ($table != '') {
            $this->db->connect();
            $sql = "SHOW TABLES LIKE '" . $this->db->prefix(Xoops_Zend_Db::getPrefix($type) . "_" . $table) . "'";
            $result = $this->db->queryF($sql);
            $ret = $this->db->getRowsNum($result) ? true : false;
        }
        return $ret;
    }

    public function rowCount($table)
    {
        $count = 0;
        if ($table != '') {
            $this->db->connect();
            $sql = 'SELECT COUNT(*) FROM ' . $this->db->prefix($table);
            if ($ret = $this->db->query($sql)) {
                list ($count) = $this->db->fetchRow($ret);
            }
        }
        return $count;
    }
}