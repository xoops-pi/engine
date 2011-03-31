<?php
/**
 * XOOPS module comment installer
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
 * @todo            Update module comment configs when comment plugin is installed or activated after a module is installed
 */

/**
 * Comment configuration specs
 *
 *  return array(
 *         "article"    => array(
 *                  "title"         => "Article",
 *                  "controller"    => "article",
 *                  "action"        => "read",
 *                  "param_item"    => "id",
 *                  "param_page"    => "cp",
 *                  "template"      => "article.html",
 *              ),
 *         "image"      => array(
 *                  "title"         => "Image",
 *                  "controller"    => "image",
 *                  "action"        => "view",
 *                  "param_item"    => "image_id",
 *              ),
 *  );
 */

class Xoops_Installer_Module_Comment extends Xoops_Installer_Abstract
{
    protected function pluginAvailable()
    {
        $plugins = XOOPS::service("registry")->plugin->read();
        return isset($plugins["comment"]);
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
        foreach ($this->config as $key => $item) {
            $status = $this->addCategory($item, $key) * $status;
        }
        XOOPS::service('registry')->comment->flush($module);

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

        $plugins = XOOPS::service("registry")->read();
        if (!isset($plugins["comment"])) {
            return;
        }


        $model = Xoops::service('plugin')->getModel("comment_category");
        $select = $model->select()->where('module = ?', $module);
        $rowset = $model->fetchAll($select);
        $items_exist = array();
        $items_id = array();
        foreach ($rowset as $row) {
            $items_exist[$row->key] = $row;
            $items_id[$key] = $row->id;
        }
        foreach ($this->config as $key => $item) {
            if (isset($items_exist[$key])) {
                $row = $items_exist[$key];
                $data = array();
                if (strcmp($row->title, $item_exist["title"])) {
                    $data["title"] = $item["title"];
                }
                if (strcmp($row->param, $item_exist["param"])) {
                    $data["param"] = $item["param"];
                }
                if (strcmp($row->template, $item_exist["template"])) {
                    $data["template"] = $item["template"];
                }
                if (!empty($data)) {
                    $row->setFromArray($data);
                    $row->save();
                }
                unset($items_id[$key]);
                continue;
            }
            $status = $this->addCategory($item, $key) * $status;
        }

        if (!empty($items_id)) {
            $model->delete(array("id IN (?)", array_values($items_id)));
            $modelItem = Xoops::service('plugin')->getModel("comment_item");
            $modelItem->delete(array("category IN (?)", array_values($items_id)));
        }
        XOOPS::service('registry')->comment->flush($module);

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

        $modelCategory = Xoops::service('plugin')->getModel("comment_category");
        $modelCategory->delete(array("module = ?" => $module));
        $modelItem = Xoops::service('plugin')->getModel("comment_item");
        $modelItem->delete(array("module = ?" => $module));
        $modelComment = Xoops::service('plugin')->getModel("comment_post");
        $modelComment->delete(array("module = ?" => $module));

        XOOPS::service('registry')->comment->flush($module);

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

    protected function addCategory($data, $key)
    {
        $module = $this->module->dirname;
        $model = Xoops::service('plugin')->getModel("comment_category");
        $data["key"]    = $key;
        $data["module"] = $module;
        $data["active"] = 1;
        return $model->insert($data);
    }
}