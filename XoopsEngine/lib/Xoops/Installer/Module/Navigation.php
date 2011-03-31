<?php
/**
 * XOOPS module navigation installer
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

/**
 * Navigation configuration specs
 *
 *  return array(
 *      "navigations" => array(
 *          "name" => array(
 *              "name"      => "uniqueName",
 *              "title"     => "Title",
 *              "section"   => "front",
 *          ),
 *          ...
 *      ),
 *          // front pages
 *          "front"    => array(
 *              "p1" => array(
 *                  "label"         => "Front Page",
 *                  "controller"    => "index",
 *                  "action"        => "test",
 *                  "route"         => "default",
 *                  "param"        => array(
 *                      "a"     => "parama",
 *                      "b"     => 1
 *                  ),
 *              ),
 *              "p2" => array(
 *                  "label"         => "A Feed Page",
 *                  "controller"    => "another",
 *                  "action"        => "index",
 *                  "params"        => "a=parama&b=good"
 *                  "route"         => "feed",
 *              ),
 *              "p3" => array(
 *                  "label"         => "A Static Page",
 *                  // URI relative to webroot
 *                  "uri"           => "/modules/article/read.php?id=5",
 *                  // Or URI relative to current module root
 *                  //"uri"         => "read.php?id=5"
 *                  "resource"      => "section=module&module=mvc&resource=test&item=3&privilege=read",
 *              ),
 *              ...
 *          ),
 *  );
 */

class Xoops_Installer_Module_Navigation extends Xoops_Installer_Abstract
{
    public function install(&$message)
    {
        $module = $this->module->dirname;
        $message = $this->message;
        $status = true;

        $navigations = $this->loadNavigation();
        foreach ($navigations as $navigation => $pageList) {
            $status = $this->insertItems($navigation, $pageList, $message) * $status;
        }
        /*
        if (!isset($navigations["admin"]) || $navigations["admin"] !== false) {
            $status = $this->registerAdmin($message) * $status;
        }
        */

        XOOPS::service('registry')->navigation->flush();
        //XOOPS::service('registry')->admin->flush();
        return $status;
    }

    public function update(&$message)
    {
        XOOPS::service('registry')->navigation->flush();
        //XOOPS::service('registry')->admin->flush();

        if (version_compare($this->version, $this->module->version, ">=")) {
            return true;
        }

        $module = $this->module->dirname;
        $message = $this->message;
        $status = true;

        $navigations = $this->loadNavigation();
        if (!empty($navigations["front-modules"])) {
            $status = $this->verifyFront($navigations["front-modules"], $message) * $status;
        } else {
            $status = $this->unregisterFront($message) * $status;
        }

        $navigation = "admin:" . $module;
        $this->deleteItems($navigation, $message);
        if (!empty($navigations[$navigation])) {
            $status = $this->insertItems($navigation, $navigations[$navigation], $message) * $status;
        }
        /*
        elseif (!isset($navigations["admin"])) {
            $status = $this->registerAdmin($message) * $status;
        } elseif ($navigations["admin"] === false) {
            $status = $this->unregisterAdmin($message) * $status;
        }
        */

        return $status;
    }

    public function uninstall(&$message)
    {
        if (!is_object($this->module)) {
            return;
        }
        $module = $this->module->dirname;
        XOOPS::service('registry')->navigation->flush($module);
        $message = $this->message;

        // remove pages
        $model = XOOPS::getModel('navigation_page');
        $select = $model->select()->where("module = ?", $module)->order("left");
        $row = $model->fetchRow($select);
        $rows = $model->delete(array("module = ?" => $module));
        if (!empty($rows)) {
            $model->trim($row->left);
        }
        XOOPS::service('registry')->navigation->flush();
        //XOOPS::service('registry')->admin->flush();
    }

    public function activate(&$message)
    {
        $module = $this->module->dirname;
        //XOOPS::service('registry')->resource->flush($module);
        $message = $this->message;

        // update role active => 1
        $model = XOOPS::getModel('navigation_page');
        $where = array('module = ?' => $module);
        $model->update(array("active" => 1), $where);
        XOOPS::service('registry')->navigation->flush();
        //XOOPS::service('registry')->admin->flush();
    }

    public function deactivate(&$message)
    {
        $module = $this->module->dirname;
        //XOOPS::service('registry')->resource->flush($module);
        $message = $this->message;

        // update role active => 0
        $model = XOOPS::getModel('navigation_page');
        $where = array('module = ?' => $module);
        $model->update(array("active" => 0), $where);
        XOOPS::service('registry')->navigation->flush();
        //XOOPS::service('registry')->admin->flush();
    }

    protected function verifyFront($items, &$message)
    {
        $module = $this->module->dirname;
        $navigation = "front-modules";
        $modelPage = XOOPS::getModel("navigation_page");
        $select = $modelPage->select()->where("navigation = ?", $navigation)
                                        ->where("name = ?", $module)
                                        ->where("module = ?", $module)
                                        ->where("depth = ?", 0);
        $moduleRoot = $modelPage->fetchRow($select);
        if ($moduleRoot) {
            return true;
        }
        return $this->insertItems($navigation, $items, $message);
    }

    protected function unregisterFront(&$message)
    {
        $module = $this->module->dirname;
        $modelPage = XOOPS::getModel("navigation_page");
        $navigation = "front-modules";
        $depth = $modelPage->quoteIdentifier("depth");
        $where = array(
            "navigation = ?"    => $navigation,
            "name = ?"          => $module,
            "module = ?"        => $module,
            $depth . " = ?"     => 0,
        );
        $row = $modelPage->fetchRow($where);
        if ($row) {
            $modelPage->remove($row, true);
        }

        return true;
    }

    protected function insertItems($navigation, $items, &$message)
    {
        if (empty($items)) {
            return true;
        }
        $modelPage = XOOPS::getModel("navigation_page");
        $select = $modelPage->select()->where("navigation = ?", $navigation)
                                        ->order($modelPage->right . " DESC");
        $lastRoot = $modelPage->fetchRow($select);
        $position = empty($lastRoot) ? "lastOf" : "nextTo";
        foreach ($items as $key => $item) {
            if (!isset($item["name"])) {
                $item["name"] = $key;
            }
            $lastRoot = $this->insertPages($item, $navigation, $lastRoot, $position, $message);
            $position = "nextTo";
            if (empty($lastRoot)) {
                return false;
            }
        }
        return true;
    }

    protected function deleteItems($navigation, &$message)
    {
        $modelPage = XOOPS::getModel("navigation_page");
        $clause = new Xoops_Zend_Db_Clause("navigation = ?", $navigation);
        $clause->order($modelPage->left . " DESC");
        $rowset = $modelPage->getRoots($clause);
        $list = array();
        foreach ($rowset as $row) {
            array_unshift($list, $row);
        }
        foreach ($list as $row) {
            $modelPage->remove($row, true);
        }
        return true;
    }

    private function insertPages($page, $navigation, $objective = null, $position = null, &$message = null)
    {
        $status = true;
        $module = $this->module->dirname;
        //$modelPage = XOOPS::getModel("navigation_page");
        //if (!isset($page["module"])) {
            $page["module"] = $module;
        //}
        if (!isset($page["navigation"])) {
            $page["navigation"] = $navigation;
        }
        $pageId = $this->insertPage($page, $objective, $position, $message);
        if (empty($pageId)) {
            return false;
        }
        $pages = empty($page["pages"]) ? array() : $page["pages"];
        if (!is_array($pages)) {
            //Debug::e($pages);
        }
        foreach ($pages as $key => $page) {
            if (!isset($page["name"])) {
                $page["name"] = $key;
            }
            $status = $this->insertPages($page, $navigation, $pageId, "lastOf", $message);
            if (empty($status)) {
                return false;
            }
        }

        return $pageId;
    }

    private function deletePages($page, &$message)
    {
        $status = true;
        $module = $this->module->dirname;
        $modelPage = XOOPS::getModel("navigation_page");
        $pages = $modelPage->getChildren($page, "id");
        $pageIds = array();
        foreach ($pages as $item) {
            $pageIds[] = $item->id;
        }
        $modelPage->remove($page, true);
        if (empty($pageIds)) {
            return true;
        }
        return true;
    }

    private function insertPage($page, $objective = null, $position = null, &$message = null)
    {
        $module = $this->module->dirname;
        $modelPage = XOOPS::getModel("navigation_page");
        $columnsPage = $modelPage->info("cols");
        $status = true;

        if (isset($page["params"]) && is_array($page["params"])) {
            $page["params"] = http_build_query($page["params"]);
        }
        if (isset($page["resource"]) && is_array($page["resource"])) {
            $page["resource"] = http_build_query($page["resource"]);
        }
        if (isset($page["visible"])) {
            $page["visible"] = intval($page["visible"]);
        }
        // Transform a module relative URI to webroot
        if (!empty($page["uri"]) && "/" != $page["uri"]{0}) {
            $page["uri"] = "/modules/" . $module . "/" . $page["uri"];
        }
        $data = array();
        if (!is_array($page)) {
            //Debug::e($page);
        }
        foreach ($page as $col => $val) {
            if (in_array($col, $columnsPage)) {
                $data[$col] = $val;
            }
        }
        if (!$pageId = $modelPage->add($data, $objective, $position)) {
            $status = false;
        }
        return $status ? $pageId : false;
    }

    protected function loadNavigation()
    {
        $module = $this->module->dirname;
        if (false === ($navigations = $this->config)) {
            return array();
        }

        $dirname = $this->module->parent ? $this->module->parent : $module;
        // Load navigation specs
        $path = Xoops::service('module')->getPath($dirname);
        foreach ($navigations as $navigation => &$data) {
            if (!is_string($data)) continue;
            $file = $path . "/configs/{$data}";
            $data = Xoops_Config::load($file);
        }

        // Translate navigation specs
        $route = (Xoops::service('module')->getType($dirname) == "legacy") ? "legacy" : "default";
        if (!isset($navigations["front"])) {
            $navigations["front-modules"][$module] = array(
                "label" => $this->module->name,
                "name"  => $module,
                "route" => $route,
            );
        } elseif (false !== $navigations["front"]) {
            $navigations["front-modules"] = array(
                $module => array(
                    "label" => $this->module->name,
                    "name"  => $module,
                    "route" => $route,
                    "pages" => $navigations["front"]
                )
            );
        } else {
            $navigations["front-modules"] = array();
        }
        $navigations["front"] = array();

        if (!empty($navigations["admin"])) {
            $navigations["admin:" . $module] = $navigations["admin"];
            $navigations["admin"] = array();
        }

        return $navigations;
    }
}