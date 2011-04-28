<?php
/**
 * XOOPS module installer
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
 * @category        Xoops_Module
 * @package         System
 * @version         $Id$
 */

class App_System_Installer extends Xoops_Installer_Abstract
//class System_Installer extends Xoops_Installer_Abstract
{
    public function preInstall(&$message)
    {
        $message = $this->message;
        // Query from sql file
        $sqlSystem = XOOPS::path("app/system/sql/mysql.system.sql");
        Xoops_Zend_Db_File_Mysql::reset();
        Xoops_Zend_Db_File_Mysql::setPrefix(Xoops_Zend_Db::getPrefix("core"));
        $status = Xoops_Zend_Db_File_Mysql::queryFile($sqlSystem, $message);

        // Record created tables
        $createdTables = Xoops_Zend_Db_File_Mysql::getLogs("create");
        Xoops_Zend_Db_File_Mysql::reset();
        if (!empty($createdTables)) {
            $model = XOOPS::getModel("table");
            foreach ($createdTables as $table => $type) {
                $model->insert(array("name" => $table, "module" => "", "type" => $type));
            }
        }

        /*
        // Create groups
        $group_handler = XOOPS::getHandler("group");
        $group = $group_handler->create();
        $group->setVar("groupid", XOOPS_GROUP_ADMIN);
        $group->setVar("name", _INSTALL_WEBMASTER);
        $group->setVar("description", _INSTALL_WEBMASTERD);
        $group->setVar("group_type", "Admin");
        $group_handler->insert($group);

        $group = $group_handler->create();
        $group->setVar("groupid", XOOPS_GROUP_USERS);
        $group->setVar("name", _INSTALL_REGUSERS);
        $group->setVar("description", _INSTALL_REGUSERSD);
        $group->setVar("group_type", "User");
        $group_handler->insert($group);

        $group = $group_handler->create();
        $group->setVar("groupid", XOOPS_GROUP_ANONYMOUS);
        $group->setVar("name", _INSTALL_ANONUSERS);
        $group->setVar("description", _INSTALL_ANONUSERSD);
        $group->setVar("group_type", "Anonymous");
        $group_handler->insert($group);

        // Set group permissions
        $groupperm_handler = XOOPS::getHandler("groupperm");
        for ($i = 1; $i < 15; $i ++) {
            $groupperm_handler->addRight('system_admin', $i, XOOPS_GROUP_ADMIN);
        }
        */

        // Insert system configurations        ;
        $module = clone $this->module;
        $module->id = 0;
        $module->dirname = "";
        $configs = Xoops_Config::load(XOOPS::path("app/system/configs/system.config.php"));
        //$installer = new Xoops_Installer_Config($configs, $module);
        //$status = $status && $installer->install($message);
        $status = $status && Xoops_Installer::instance()->loadExtension("config", $module, $configs)->install($message);
        unset($module);

        /*
        global $xoopsDB;
        // User ranks
        $i = 1;
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("ranks") . " VALUES (" . ($i++) . ", " . $xoopsDB->quote(_INSTALL_RANKS_1) . ", 0, 20, 0, 'rank3e632f95e81ca.gif')");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("ranks") . " VALUES (" . ($i++) . ", " . $xoopsDB->quote(_INSTALL_RANKS_2) . ", 21, 40, 0, 'rank3dbf8e94a6f72.gif')");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("ranks") . " VALUES (" . ($i++) . ", " . $xoopsDB->quote(_INSTALL_RANKS_3) . ", 41, 70, 0, 'rank3dbf8e9e7d88d.gif')");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("ranks") . " VALUES (" . ($i++) . ", " . $xoopsDB->quote(_INSTALL_RANKS_4) . ", 71, 150, 0, 'rank3dbf8ea81e642.gif')");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("ranks") . " VALUES (" . ($i++) . ", " . $xoopsDB->quote(_INSTALL_RANKS_5) . ", 151, 10000, 0, 'rank3dbf8eb1a72e7.gif')");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("ranks") . " VALUES (" . ($i++) . ", " . $xoopsDB->quote(_INSTALL_RANKS_6) . ", 0, 0, 1, 'rank3dbf8edf15093.gif')");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("ranks") . " VALUES (" . ($i++) . ", " . $xoopsDB->quote(_INSTALL_RANKS_7) . ", 0, 0, 1, 'rank3dbf8ee8681cd.gif')");

        // Smileys
        $i = 1;
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("smiles") . " VALUES (" . ($i++) . ", ':-D', 'smil3dbd4d4e4c4f2.gif', " . $xoopsDB->quote(_INSTALL_SMILES_1) . ", 1)");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("smiles") . " VALUES (" . ($i++) . ", ':-)', 'smil3dbd4d6422f04.gif', " . $xoopsDB->quote(_INSTALL_SMILES_2) . ", 1)");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("smiles") . " VALUES (" . ($i++) . ", ':-(', 'smil3dbd4d75edb5e.gif', " . $xoopsDB->quote(_INSTALL_SMILES_3) . ", 1)");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("smiles") . " VALUES (" . ($i++) . ", ':-o', 'smil3dbd4d8676346.gif', " . $xoopsDB->quote(_INSTALL_SMILES_4) . ", 1)");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("smiles") . " VALUES (" . ($i++) . ", ':-?', 'smil3dbd4d99c6eaa.gif', " . $xoopsDB->quote(_INSTALL_SMILES_5) . ", 1)");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("smiles") . " VALUES (" . ($i++) . ", '8-)', 'smil3dbd4daabd491.gif', " . $xoopsDB->quote(_INSTALL_SMILES_6) . ", 1)");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("smiles") . " VALUES (" . ($i++) . ", ':lol:', 'smil3dbd4dbc14f3f.gif', " . $xoopsDB->quote(_INSTALL_SMILES_7) . ", 1)");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("smiles") . " VALUES (" . ($i++) . ", ':-x', 'smil3dbd4dcd7b9f4.gif', " . $xoopsDB->quote(_INSTALL_SMILES_8) . ", 1)");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("smiles") . " VALUES (" . ($i++) . ", ':-P', 'smil3dbd4ddd6835f.gif', " . $xoopsDB->quote(_INSTALL_SMILES_9) . ", 1)");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("smiles") . " VALUES (" . ($i++) . ", ':oops:', 'smil3dbd4df1944ee.gif', " . $xoopsDB->quote(_INSTALL_SMILES_10) . ", 0)");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("smiles") . " VALUES (" . ($i++) . ", ':cry:', 'smil3dbd4e02c5440.gif', " . $xoopsDB->quote(_INSTALL_SMILES_11) . ", 0)");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("smiles") . " VALUES (" . ($i++) . ", ':evil:', 'smil3dbd4e1748cc9.gif', " . $xoopsDB->quote(_INSTALL_SMILES_12) . ", 0)");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("smiles") . " VALUES (" . ($i++) . ", ':roll:', 'smil3dbd4e29bbcc7.gif', " . $xoopsDB->quote(_INSTALL_SMILES_13) . ", 0)");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("smiles") . " VALUES (" . ($i++) . ", ';-)', 'smil3dbd4e398ff7b.gif', " . $xoopsDB->quote(_INSTALL_SMILES_14) . ", 0)");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("smiles") . " VALUES (" . ($i++) . ", ':pint:', 'smil3dbd4e4c2e742.gif', " . $xoopsDB->quote(_INSTALL_SMILES_15) . ", 0)");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("smiles") . " VALUES (" . ($i++) . ", ':hammer:', 'smil3dbd4e5e7563a.gif', " . $xoopsDB->quote(_INSTALL_SMILES_16) . ", 0)");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("smiles") . " VALUES (" . ($i++) . ", ':idea:', 'smil3dbd4e7853679.gif', " . $xoopsDB->quote(_INSTALL_SMILES_17) . ", 0)");
        */

        /*
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("banner") . " (bid, cid, imptotal, impmade, clicks, imageurl, clickurl, date) VALUES (1, 1, 0, 1, 0, '" . XOOPS::url("www") . "/images/banners/xoops_flashbanner2.swf', 'http://www.xoops.org/', " . time() . ")");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("banner") . " (bid, cid, imptotal, impmade, clicks, imageurl, clickurl, date) VALUES (2, 1, 0, 1, 0, '" . XOOPS::url("www") . "/images/banners/xoops_banner_2.gif', 'http://www.xoops.org/', " . time() . ")");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("banner") . " (bid, cid, imptotal, impmade, clicks, imageurl, clickurl, date) VALUES (3, 1, 0, 1, 0, '" . XOOPS::url("www") . "/images/banners/banner.swf', 'http://www.xoops.org/', " . time() . ")");
        $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("bannerclient") . " (`cid`, `name`, `contact`, `email`) VALUES (1, 'XOOPS', 'XOOPS Dev Team', 'webmaster@xoops.org')");
        */

        return $status;
    }

    public function postInstall(&$message)
    {
        $module = $this->module->dirname;
        $message = $this->message;
        $model = XOOPS::getModel("page");
        $where = array(
            "module = ?" => "default",
            "controller = ?" => "",
            "action = ?" => ""
        );
        $model->update(array("id" => 0), $where);

        $model = XOOPS::getModel("block");
        $select = $model->select()->where("`module` = ?", $module)
                                    ->where("`key` IN (?)", array("user", "login"));
        $blockList = $model->fetchAll($select)->toArray();
        $blocks = array();
        $i = 0;
        $model = XOOPS::getModel("page_block");
        foreach ($blockList as $key => $block) {
            $blocks[$block["key"]] = $block["id"];
            $data = array(
                "page"      => 0, //$page->id,
                "block"     => $block["id"],
                "position"  => 0,
                "order"     => ++$i
            );
            $model->insert($data);
        }
        /*
        $model = XOOPS::getModel("acl_rule");
        $select = $model->select()
                        ->where("role = ?", "guest")
                        ->where("resource = ?", $blocks["user"])
                        ->where("privilege = ?", "block");
        if ($item = $model->fetchRow($select)) {
            $model->update(array("deny" => 1), array("id = ?" => $item->id));
        } else {
            $data = array(
                "role"      => "guest",
                "privilege" => "block",
                "resource"  => $blocks["user"],
                "module"    => "system",
                "allow"     => 0,
            );
            $model->insert($data);
        }
        $select = $model->select()
                        ->where("role = ?", "member")
                        ->where("resource = ?", $blocks["login"])
                        ->where("privilege = ?", "block");
        if ($item = $model->fetchRow($select)) {
            $model->update(array("deny" => 1), array("id = ?" => $item->id));
        } else {
            $data = array(
                "role"      => "member",
                "privilege" => "block",
                "resource"  => $blocks["login"],
                "module"    => "system",
                "deny"      => 1,
            );
            $model->insert($data);
        }
        */
        $model = Xoops::service('module')->getModel('task', $module);
        if ($model->insert(array('memo' => _SYSTEM_MI_TASK_MODULE_INSTALLATION))) {
            $message[] = 'Task created: ' . _SYSTEM_MI_TASK_MODULE_INSTALLATION;
        }
        if ($model->insert(array('memo' => _SYSTEM_MI_TASK_THEME_CONFIGURATION))) {
            $message[] = 'Task created: ' . _SYSTEM_MI_TASK_THEME_CONFIGURATION;
        }

        $model = Xoops::service('module')->getModel('shortcut', $module);
        $data = array('link' => 'http://www.xoopsengine.org', 'title' => 'Xoops Engine Demo');
        if ($model->insert($data)) {
            $message[] = 'Shortcut created: ' . $data['title'];
        }
        $data = array('link' => 'http://sf.net/projects/xoops', 'title' => 'XOOPS Project');
        if ($model->insert($data)) {
            $message[] = 'Shortcut created: ' . $data['title'];
        }
        $data = array('link' => 'http://www.xoops.org', 'title' => 'XOOPS Community');
        if ($model->insert($data)) {
            $message[] = 'Shortcut created: ' . $data['title'];
        }

        $model = Xoops::service('module')->getModel('update', $module);
        $data = array(
            "title"     => XOOPS::_("System installed"),
            "content"   => XOOPS::_("The system is installed successfully."),
            "uri"       => XOOPS::url("www", true),
            "time"      => time(),
        );
        $model->insert($data);
    }

    public function preUninstall(&$message)
    {
        $module = $this->module->dirname;
        $message = $this->message;
        $model = XOOPS::getModel("module");
        $rowset = $model->fetchAll(array("dirname <> ?" => $module));
        if ($rowset->count() > 0) {
            $message[] = "Module is not empty.";
            return false;
        }
        return true;
    }

    public function postUninstall(&$message)
    {
        //global $xoops;
        $message = $this->message;
        $modelTable = XOOPS::getModel("table");
        $select = $modelTable->select()->where("module = ?", "")->from($modelTable, "name");
        $createdTables = (array) $modelTable->getAdapter()->fetchCol($select);
        foreach ($createdTables as $table) {
            $result = XOOPS::registry("db")->query("DROP TABLE IF EXISTS " . XOOPS::registry("db")->prefix($table, ''));
            $errorInfo = $result->errorInfo();
            if (empty($errorInfo[1])) {
                $message[] = "Table " . $table . " dropped";
            } else {
                $message[] = "Table " . $table . " not dropped: " . $errorInfo[2];
            }
        }
        //XOOPS::registry("cache")->clean('matchingTag', array("model"));
        return;
    }

    public function preUpdate(&$message)
    {
        $message = $this->message;
        $message[] = 'Called from ' . __METHOD__;
    }

    public function postUpdate(&$message)
    {
        //global $xoops;

        $message = $this->message;
        $module = clone $this->module;
        $module->id = 0;
        $module->dirname = "";
        //$module->setVar("mid", 0);
        //$module->setVar("dirname", "");
        // Insert system configurations        ;
        $configs = Xoops_Config::load(XOOPS::path("app/system/configs/system.config.php"));
        //$installer = new Xoops_Installer_Config($configs, $module, $this->version);
        //$status = $installer->update($message);
        $status = $status && Xoops_Installer::instance()->loadExtension("config", $module, $configs)->update($message);
        unset($module);

        $model = XOOPS::getModel('update');
        $data = array(
            "title"     => XOOPS::_("System updated"),
            "content"   => XOOPS::_("The system is updated successfully."),
            "uri"       => XOOPS::url("www", true)
        );
        $model->insert($data);

        return $status;
    }
}