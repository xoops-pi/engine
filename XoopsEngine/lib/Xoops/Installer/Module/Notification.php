<?php
/**
 * XOOPS module notification installer
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
 * @todo            Update module notification configs when comment plugin is installed or activated after a module is installed
 */

/**
 * Notification configuration specs
 *
 *  return array(
 *         "category"   => array(
 *                  "title"         => "Category creation",
 *                  "controller"    => "category",
 *                  "action"        => "index",
 *                  "callback"      => "categoryInfo",
 *              ),
 *         "approve"    => array(
 *                  "title"         => "Article approval",
 *                  "controller"    => "article",
 *                  "action"        => "approve",
 *                  "param"         => "id",
 *                  "content"       => "_PLUGIN_APPROVE_CONTENT",
 *                  "translate"     => "notification",
 *              ),
 *  );
 */

class Xoops_Installer_Module_Notification extends Xoops_Installer_Abstract
{
    protected function pluginAvailable()
    {
        $plugins = XOOPS::service("registry")->plugin->read();
        return isset($plugins["notification"]);
    }

    public function install(&$message)
    {
        if (!$this->pluginAvailable()) {
            return;
        }

        $module = $this->module->dirname;
        $message = $this->message;
        $status = true;

        if (empty($this->config)) {
            return;
        }

        foreach ($this->config as $name => $item) {
            $status = $this->addCategory($item, $name) * $status;
        }
        XOOPS::service('registry')->notification->flush($module);

        return $status;
    }

    public function update(&$message)
    {
        if (!$this->pluginAvailable()) {
            return;
        }

        $module = $this->module->dirname;
        $message = $this->message;
        $status = true;

        if (version_compare($this->version, $this->module->version, ">=")) {
            return true;
        }

        $model = Xoops::service('plugin')->getModel("notification_category");
        $select = $model->select()->where('module = ?', $module);
        $rowset = $model->fetchAll($select);
        $items_exist = array();
        $items_id = array();
        foreach ($rowset as $row) {
            $items_exist[$row->name] = $row;
            $items_id[$row->name] = $row->id;
        }
        foreach ($this->config as $name => $item) {
            if (isset($items_exist[$name])) {
                $row = $items_exist[$name];
                $data = array();
                if (strcmp($row->title, $item_exist["title"])) {
                    $data["title"] = $item["title"];
                }
                if (strcmp($row->param, $item_exist["param"])) {
                    $data["param"] = $item["param"];
                }
                if (strcmp($row->callback, $item_exist["callback"])) {
                    $data["callback"] = $item["callback"];
                }
                if (strcmp($row->content, $item_exist["content"])) {
                    $data["content"] = $item["content"];
                }
                if (strcmp($row->translate, $item_exist["translate"])) {
                    $data["translate"] = $item["translate"];
                }
                if (!empty($data)) {
                    $row->setFromArray($data);
                    $row->save();
                }
                unset($items_id[$name]);
                continue;
            }
            $status = $this->addCategory($item, $name) * $status;
        }

        if (!empty($items_id)) {
            $model->delete(array("id IN (?)", array_values($items_id)));
            $modelNotification = Xoops::service('plugin')->getModel("notification_subscription");
            $modelNotification->delete(array("category IN (?)", array_values($items_id)));
        }
        XOOPS::service('registry')->notification->flush($module);

        return $status;
    }

    ///////////////////////////////////////
    public function uninstall(&$message)
    {
        if (!$this->pluginAvailable()) {
            return;
        }

        if (!is_object($this->module)) {
            return;
        }
        $module = $this->module->dirname;
        $message = $this->message;
        $status = true;

        $modelCategory = Xoops::service('plugin')->getModel("notification_category");
        $modelCategory->delete(array("module = ?" => $module));
        $modelNotification = Xoops::service('plugin')->getModel("notification_subscription");
        $modelNotification->delete(array("module = ?" => $module));

        XOOPS::service('registry')->notification->flush($module);

        return true;
    }

    public function activate(&$message)
    {
        if (!$this->pluginAvailable()) {
            return;
        }

        $module = $this->module->dirname;
        $message = $this->message;

        return true;
    }

    public function deactivate(&$message)
    {
        if (!$this->pluginAvailable()) {
            return;
        }

        $module = $this->module->dirname;
        $message = $this->message;

        return true;
    }

    protected function addCategory($data, $name)
    {
        $module = $this->module->dirname;
        $model = Xoops::service('plugin')->getModel("notification_category");
        $data["name"]   = $name;
        $data["module"] = $module;
        $data["active"] = 1;
        return $model->insert($data);
    }
}