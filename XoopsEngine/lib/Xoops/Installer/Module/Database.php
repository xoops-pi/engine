<?php
/**
 * XOOPS module database table/sql installer
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
 * @package         Xoops_Installer
 * @subpackage      Installer
 * @version         $Id$
 */

class Xoops_Installer_Module_Database extends Xoops_Installer_Abstract
{
    public function install(&$message)
    {
        $module = $this->module->dirname;

        $message = $this->message;
        $options = $this->config;

        if (empty($options['sqlfile'])) {
            return true;
        }
        $sqlfile = $options['sqlfile'];
        if (!is_array($sqlfile) || empty($sqlfile['mysql'])) {
            return true;
        }

        $sql_file_path = XOOPS::path($sqlfile['mysql']);
        if (!file_exists($sql_file_path)) {
            //var_dump($this->module->toArray());
            $dirname = $this->module->parent ?: $module;
            $sql_file_path = Xoops::service('module')->getPath($dirname) . "/" . $sqlfile['mysql'];
            if (!file_exists($sql_file_path)) {
                $message[] = "SQL file is not found";
                trigger_error("SQL file {$sql_file_path} is not found", E_USER_ERROR);
                return false;
            }
        }

        // Keep legacy module tables as no module specific prefix
        if (Xoops::service('module')->getType($module) == 'legacy') {
            Xoops_Zend_Db_File_Mysql::setPrefix('');
        // Set app tables as with app specific prefix
        } else {
            Xoops_Zend_Db_File_Mysql::setPrefix($module);
        }
        $status = Xoops_Zend_Db_File_Mysql::queryFile($sql_file_path, $message);
        if (!$status) {
            $createdTables = Xoops_Zend_Db_File_Mysql::getLogs("create");
            foreach ($createdTables as $ct => $type) {
                XOOPS::registry("db")->query("DROP " . $type . " IF EXISTS " . XOOPS::registry("db")->prefix($ct));
            }
            Xoops_Zend_Db_File_Mysql::reset();
            return false;
        }

        // Record created tables
        $createdTables = Xoops_Zend_Db_File_Mysql::getLogs("create");
        if (!empty($createdTables)) {
            $model = XOOPS::getModel("table");
            foreach ($createdTables as $table => $type) {
                $model->insert(array("name" => $table, "module" => $module, "type" => $type));
            }
        }

        return true;
    }

    /**
     * Module database table list is supposed to be updated during module upgrade,
     * however we don't have a feasible solution yet. Thus module developers are encouraged to use $config["extensions"]["database"]["tables"]
     */
    public function update(&$message)
    {
    }

    public function uninstall(&$message)
    {
        $module = $this->module->dirname;
        $message = $this->message;
        $options = $this->config;

        $modelTable = XOOPS::getModel("table");
        $select = $modelTable->select()->where("module = ?", $module)->from($modelTable, array("name", "type"));
        $createdTables = $modelTable->getAdapter()->fetchPairs($select);
        if (!empty($options['tables']) && is_array($options['tables'])) {
            $recordedTables = array_diff($options['tables'], array_keys($createdTables));
            foreach ($recordedTables as $table) {
                $createdTables[$table] = 'table';
            }
        }
        $droppedTables = array();
        foreach ($createdTables as $table => $type) {
            $result = XOOPS::registry("db")->query("DROP " . $type . " IF EXISTS " . XOOPS::registry("db")->prefix($table));
            $errorInfo = $result->errorInfo();
            if (empty($errorInfo[1])) {
                $droppedTables[] = $table;
                $message[] = "Table " . $table . " dropped";
            } else {
                $message[] = "Table " . $table . " not dropped: " . $errorInfo[2];
            }
        }
        //XOOPS::registry("cache")->clean('matchingTag', array("model"));
        if (!empty($droppedTables) && $modelTable = XOOPS::getModel("table")) {
            $modelTable->delete(array("name IN (?)" => $droppedTables, "module = ?" => $module));
        }

        return true;
    }
}