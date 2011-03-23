<?php
/**
 * System admin plugin controller
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

class System_PluginController extends Xoops_Zend_Controller_Action_Admin
{
    public function init()
    {
        $this->skipCache();
    }

    public function indexAction()
    {
        $model = XOOPS::getModel("plugin");
        $select = $model->select()->order(array("order", "id"));
        $rowset = $model->fetchAll($select);
        $plugins = array();
        foreach ($rowset as $row) {
            $info = $this->loadInfo($row->dirname);
            $info["version"] = $row->version;
            $info["order"] = $row->order;
            $info["update"] = $row->update;
            $plugins[$row->active ? "active" : "inactive"][$row->dirname] = $info;
        }

        //Debug::e($plugins);
        $action = $this->view->url(array("action" => "order", "controller" => "plugin"));
        $form = array("action" => $action, "method" => "post", "param" => "orders");
        $this->template->assign("form", $form);
        $this->template->assign("plugins", $plugins);
        $this->setTemplate("plugin_list.html");
    }

    public function availableAction()
    {
        $model = XOOPS::getModel("plugin");
        $select = $model->select()->from($model, array("dirname", "id"));
        $pluginList = $model->getAdapter()->fetchPairs($select);

        $pluginPath = XOOPS::path("plugin");
        $iterator = new DirectoryIterator($pluginPath);
        $plugins = array();
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                continue;
            }
            $name = $fileinfo->getFilename();
            if (isset($pluginList[$name])) {
                continue;
            }
            $plugins[$name] = $this->loadInfo($name);
        }
        $this->template->assign("plugins", $plugins);

        $this->setTemplate("plugin_available.html");
    }

    public function installAction()
    {
        $dirname = $this->getRequest()->getParam("dirname");
        $data = $this->loadInfo($dirname);
        $data["dirname"] = $dirname;
        $data["active"] = 1;
        $data["update"] = time();
        $model = XOOPS::getModel("plugin");
        $columns = $model->info("cols");
        foreach ($data as $col => $val) {
            if (!in_array($col, $columns)) {
                unset($data[$col]);
            }
        }

        $model->insert($data);
        XOOPS::service('registry')->plugin->flush();
        $this->installOption($dirname);
        XOOPS::service('registry')->option->flush();

        // Query from sql file
        $sqlFile = XOOPS::path("plugin/{$dirname}/mysql.sql");
        if (file_exists($sqlFile)) {
            Xoops_Zend_Db_File_Mysql::reset();
            Xoops_Zend_Db_File_Mysql::setPrefix(Xoops_Zend_Db::getPrefix("plugin"));
            $message = null;
            $status = Xoops_Zend_Db_File_Mysql::queryFile($sqlFile, $message);

            // Record created tables
            $createdTables = Xoops_Zend_Db_File_Mysql::getLogs("create");
            Xoops_Zend_Db_File_Mysql::reset();
            if (!empty($createdTables)) {
                $model = XOOPS::getModel("table");
                foreach ($createdTables as $table) {
                    $model->insert(array("name" => $table, "module" => "plugin:" . $dirname));
                }
            }
        }

        $message = sprintf(XOOPS::_("The plugin '%s' is installed."), $dirname);
        $options = array("time" => 5, "message" => $message);
        $url = array("action" => "index", "route" => "admin", "reset" => true);
        $this->redirect($url, $options);
    }

    public function updateAction()
    {
        $dirname = $this->getRequest()->getParam("dirname");
        $model = XOOPS::getModel("plugin");
        $select = $model->select()->where("dirname = ?", $dirname);
        $row = $model->fetchRow($select);

        $data = $this->loadInfo($dirname);
        if (version_compare($row->version, $data["version"])) {
            $data["update"] = time();
            $columns = $model->info("cols");
            foreach ($columns as $col) {
                if (isset($data[$col]) && $data[$col] != $row->$col) {
                    $row->$col = $data[$col];
                }
            }
            if ($row->save()) {
                $this->updateOption($dirname);
            }
            XOOPS::service('registry')->option->flush();
        }
        $message = sprintf(XOOPS::_("The plugin '%s' is updated."), $dirname);

        XOOPS::service('registry')->plugin->flush();

        $url = array("action" => "index", "route" => "admin", "reset" => true);
        $options = array("time" => 5, "message" => $message);
        $this->redirect($url, $options);
    }

    public function activateAction()
    {
        $dirname = $this->getRequest()->getParam("dirname");

        $model = XOOPS::getModel("plugin");
        $model->update(array("active" => 1), array("dirname = ?" => $dirname));
        XOOPS::service('registry')->plugin->flush();

        $message = sprintf(XOOPS::_("The plugin '%s' is activated."), $dirname);
        $url = array("action" => "index", "route" => "admin", "reset" => true);
        $options = array("time" => 5, "message" => $message);
        $this->redirect($url, $options);
    }

    public function deactivateAction()
    {
        $dirname = $this->getRequest()->getParam("dirname");
        $model = XOOPS::getModel("plugin");
        $model->update(array("active" => 0), array("dirname = ?" => $dirname));
        XOOPS::service('registry')->plugin->flush();

        $message = sprintf(XOOPS::_("The theme '%s' is deactivated."), $dirname);
        $url = array("action" => "index", "route" => "admin", "reset" => true);
        $options = array("time" => 5, "message" => $message);
        $this->redirect($url, $options);
    }

    public function uninstallAction()
    {
        $dirname = $this->getRequest()->getParam("dirname");
        $model = XOOPS::getModel("plugin");
        $model->delete(array("dirname = ?" => $dirname));
        XOOPS::service('registry')->plugin->flush();

        $this->uninstallOption($dirname);
        XOOPS::service('registry')->option->flush();

        $modelTable = XOOPS::getModel("table");
        $select = $modelTable->select()->where("module = ?", "plugin:" . $dirname)->from($modelTable, "name");
        $createdTables = (array) $modelTable->getAdapter()->fetchCol($select);
        $droppedTables = array();
        foreach ($createdTables as $table) {
            $result = XOOPS::registry("db")->query("DROP TABLE IF EXISTS " . XOOPS::registry("db")->prefix($table));
            $errorInfo = $result->errorInfo();
            if (empty($errorInfo[1])) {
                $droppedTables[] = $table;
                $message[] = "Table " . $table . " dropped";
            } else {
                $message[] = "Table " . $table . " not dropped: " . $errorInfo[2];
            }
        }
        XOOPS::registry("cache")->clean('matchingTag', array("model"));
        if (!empty($droppedTables) && $modelTable = XOOPS::getModel("table")) {
            $modelTable->delete(array("name IN (?)" => $droppedTables, "module = ?" => "plugin:" . $dirname));
        }

        $message = sprintf(XOOPS::_("The plugin '$s' is uninstalled."), $dirname);
        $url = array("action" => "index", "route" => "admin", "reset" => true);
        $options = array("time" => 5, "message" => $message);
        $this->redirect($url, $options);
    }

    public function downloadAction()
    {
        $description = XOOPS::_("You can find plugins from below links:");
        $list = array(
            "http://sourceforge.net/projects/xoops/files/" => XOOPS::_("XOOPS Project Repository"),
            "http://www.xoops.org/modules/repository/" => XOOPS::_("XOOPS Community Repository"),
            "http://www.xoops.org/modules/xoopspartners/" => XOOPS::_("XOOPS Local Support Communities"),
        );
        $this->template->assign("description", $description);
        $this->template->assign("list", $list);
    }

    public function readmeAction()
    {
        $title = XOOPS::_("A plugin provides system level services for all module");
        $this->template->assign("title", $title);
    }

    public function orderAction()
    {
        $orders = $this->getRequest()->getPost("orders");
        $posts = $this->getRequest()->getPost();
        //Debug::e($_POST);
        //Debug::e($posts);
        //Debug::e($orders);
        $model = XOOPS::getModel("plugin");
        $select = $model->select()->where("active = ?", 1);
        $rowset = $model->fetchAll($select);
        foreach ($rowset as $row) {
            $order = empty($orders[$row->dirname]) ? 0 : intval($orders[$row->dirname]);
            if ($order != $row->order) {
                $row->order = $order;
                $row->save();
            }
        }
        XOOPS::service('registry')->plugin->flush();

        $message = XOOPS::_("Plugin orders are updated.");
        $url = array("action" => "index", "route" => "admin", "reset" => true);
        $options = array("time" => 5, "message" => $message);
        $this->redirect($url, $options);
    }

    public function optionAction()
    {
        $plugin = $this->_getParam("dirname");
        if (empty($plugin)) {
            $this->forward("index");
            return;
        }

        $loaded = XOOPS::service("translate")->loadTranslation("admin", "plugin:" . $plugin);
        $title = sprintf(XOOPS::_("Options for Plugin %s"), $plugin);

        $modelConfig = XOOPS::getModel("config");
        $select = $modelConfig->select()->where("module = ?", "plugin")->where("category = ?", $plugin)->order("order ASC");
        $configs = $modelConfig->fetchAll($select);

        $action = $this->view->url(array("action" => "optionsave", "controller" => "plugin"));
        $hidden = array("dirname" => $plugin);
        $form = $this->getForm($configs, $action, $hidden);
        $this->template->assign("title", $title);
        $this->setTemplate("system/admin/preference.html");
    }

    public function optionsaveAction()
    {
        $modelConfig = XOOPS::getModel("config");
        $ids = $this->_getParam("__ids");
        foreach ($ids as $name => $id) {
            $config = $modelConfig->findRow($id);
            $new_value = $this->_getParam($name);
            if (is_array($new_value) || $new_value != $config->value) {
                $config->value = $new_value;
                $config->save();
            }
        }
        XOOPS::service("registry")->option->flush();

        $message = XOOPS::_("Option data are updated.");
        $url = array("action" => "index", "route" => "admin", "reset" => true);
        $options = array("time" => 5, "message" => $message);
        $this->redirect($url, $options);
    }

    public function __call($method, $args)
    {
        $this->forward("index");
    }

    public function installOption($dirname)
    {
        $configFile = XOOPS::path("plugin/" . $dirname . "/option.php");
        if (!file_exists($configFile)) {
            return;
        }
        $configs = include $configFile;
        if (empty($configs)) {
            return;
        }
        $order = 0;
        $status = 1;
        $message = array();
        foreach ($configs as $config) {
            if (!isset($config["order"])) {
                $config["order"] = ++$order;
            }
            $config["category"] = $dirname;
            $status = $this->addConfig($config, $message) * $status;
        }
    }

    public function updateOption($dirname)
    {
        $configFile = XOOPS::path("plugin/" . $dirname . "/option.php");
        if (!file_exists($configFile)) {
            $configs = array();
        } else {
            $configs = include $configFile;
        }
        $configList = array();
        foreach ($configs as $config) {
            $config["category"] = $dirname;
            $configList[$config["name"]] = $config;
        }
        $select = $modelConfig->select()->where("module = ?", "plugin")->where("category = ?", $dirname);
        $configSet = $modelConfig->fetchAll($select);
        $configs_update = array();
        foreach ($configSet as $row) {
            if (isset($configList[$row->name])) {
                $this->updateConfig($row, $configList[$row->name], $message);
                unset($configList[$row->name]);
                continue;
            }
            if (!$modelConfig->delete(array("id = ?" => $row->id))) {
                $message[] = 'Config ' . $row->name . ' failed to delete';
            } else {
                $message[] = 'Config ' . $row->name . ' deleted';
                $modelOption->delete(array("config = ?" => $row->id));
            }
        }
        foreach ($configList as $name => $config) {
            $this->addConfig($config, $message);
        }
    }

    public function uninstallOption($dirname)
    {
        $modelConfig = XOOPS::getModel("config");
        $modelOption = XOOPS::getModel("config_option");

        $select = $modelConfig->select()->where("module = ?", "plugin")->where("category = ?", $dirname)->from($modelConfig, array("id"));
        $configIds = $modelConfig->getAdapter()->fetchCol($select);

        $modelConfig->delete(array("module = ?" => "plugin", "category = ?" => $dirname));

        if (!empty($configIds)) {
            $modelOption->delete(array("config IN (?)" => $configIds));
        }
        return;
    }

    protected function loadInfo($plugin)
    {
        $infoFile = XOOPS::path("plugin/" . $plugin . "/info.php");
        $info = include $infoFile;
        $loaded = XOOPS::service("translate")->loadTranslation("admin", "plugin:" . $plugin);
        if ($loaded) {
            $info["name"] = XOOPS::_($info["name"]);
            $info["description"] = XOOPS::_($info["description"]);
        }
        return $info;
    }

    private function addConfig($config, &$message)
    {
        $module = "plugin";
        $modelConfig = XOOPS::getModel("config");
        $modelOption = XOOPS::getModel("config_option");

        $options = array();
        if (isset($config["options"])) {
            $options = $config["options"];
            unset($config["options"]);
        }

        if (!isset($config["module"])) {
            $config["module"] = $module;
        }
        if (isset($config["default"])) {
            $config["value"] = $config["default"];
            unset($config["default"]);
        }

        $configRow = $modelConfig->createRow($config);
        $configId = $configRow->save();
        if (empty($configId)) {
            $message[] = "Config " . $config["name"] . " insert failed";
            return false;
        }
        if (!empty($options)) {
            foreach ($options as $name => $value) {
                $data = array(
                    "name"      => $name,
                    "value"     => $value,
                    "config"    => $configId
                );
                $status = $modelOption->insert($data);
                if (empty($status)) {
                    $message[] = "Options for config " . $config["name"] . " insert failed";
                    return false;
                }
            }
        }
        $message[] = "Config " . $config["name"] . " inserted";

        return $configId;
    }

    private function updateConfig($row, $config, &$message)
    {
        $module = "plugin";
        $modelConfig = XOOPS::getModel("config");
        $modelOption = XOOPS::getModel("config_option");

        $options = array();
        if (isset($config["options"])) {
            $options = $config["options"];
            unset($config["options"]);
        }

        $config["module"] = $module;
        if (isset($config["default"])) {
            unset($config["default"]);
        }

        $row->setFromArray($config);
        $status = $row->save();
        if (empty($status)) {
            $message[] = "Config " . $config["name"] . " update failed";
            return false;
        }
        $modelOption->delete(array("config = ?" => $row->id));
        if (!empty($options)) {
            foreach ($options as $name => $value) {
                $data = array(
                    "name"      => $name,
                    "value"     => $value,
                    "config"    => $row->id
                );
                $status = $modelOption->insert($data);
                if (empty($status)) {
                    $message[] = "Options for config " . $config["name"] . " insert failed";
                    return false;
                }
            }
        }
        $message[] = "Config " . $config["name"] . " updated";

        return $configId;
    }

    private function getForm($configs, $action, $hidden = array())
    {
        $this->view->headLink(array(
            "href"  => "form.css",
            "rel"   => "stylesheet",
            "type"  => "text/css"
        ));
        $options = array(
            "action"    => $action,
        );
        //$form = new App_System_Form_Preference($options);
        $form = new System_Form_Preference($options);
        $form->addConfigs($configs);
        $options = array(
            "label"     => "Confirm",
            "required"  => false,
            "ignore"    => true,
        );
        $form->addElement("submit", "save", $options);

        if (!empty($hidden)) {
            foreach ($hidden as $key => $value) {
                $form->addElement("hidden", $key, array("value" => $value));
            }
        }
        $form->assign($this->view);
        return $form;
    }
}