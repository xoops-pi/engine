<?php
/**
 * System admin menu controller
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

class System_MenuController extends Xoops_Zend_Controller_Action_Admin
{
    public function indexAction()
    {
        $module = $this->getRequest()->getModuleName();

        $modelNavigation = XOOPS::getModel("navigation");
        $select = $modelNavigation->select()
                        ->from($modelNavigation, array("name", "title", "module"));
        $navigationList = $modelNavigation->getAdapter()->fetchAssoc($select);
        $navigations = array();
        $navigations["front"] = array(
            "title" => $navigationList["front"]["title"],
            "url"   => $this->getFrontController()->getRouter()->assemble(
                array(
                    "module"        => "system",
                    "controller"    => "menu",
                    "action"        => "front",
                ),
                "admin"
            )
        );
        unset($navigationList["front"]);
        $navigations["front-modules"] = array(
            "title" => XOOPS::_("Front module menus"),
            "url"   => $this->getFrontController()->getRouter()->assemble(
                array(
                    "module"        => "system",
                    "controller"    => "menu",
                    "action"        => "modules",
                ),
                "admin"
            )
        );
        $navigations["admin"] = array(
            "title" => $navigationList["admin"]["title"],
            "url"   => $this->getFrontController()->getRouter()->assemble(
                array(
                    "module"        => "system",
                    "controller"    => "menu",
                    "action"        => "admin",
                ),
                "admin"
            )
        );
        unset($navigationList["admin"]);
        foreach ($navigationList as $name => $data) {
            if ($data["module"]) continue;
            $navigations[$name] = array(
                "title"     => $data["title"],
                "url"       => $this->getFrontController()->getRouter()->assemble(
                    array(
                        "module"        => "system",
                        "controller"    => "menu",
                        "action"        => "item",
                        "name"          => $name,
                    ),
                    "admin"
                ),
            );
        }

        $modules = XOOPS::service("registry")->modulelist->read("active");
        $modelPage = XOOPS::getModel("navigation_page");
        $select = $modelPage->select()
                        ->distinct()
                        ->from($modelPage, "navigation")
                        ->where("navigation NOT IN (?)", array_keys($navigationList));
        $rowset = $modelPage->fetchAll($select);
        foreach ($rowset as $row) {
            if (substr($row->navigation, 0, 6) != "admin:") continue;
            $navigations[$row->navigation] = array(
                "title" => $modules[substr($row->navigation, 6)]["name"],
                "url"   => $this->getFrontController()->getRouter()->assemble(
                    array(
                        "module"        => "system",
                        "controller"    => "menu",
                        "action"        => "module",
                        "name"          => substr($row->navigation, 6)
                    ),
                    "admin"
                )
            );
        }

        $this->template->assign("menus", $navigations);
    }

    // Front navigation
    public function frontAction()
    {
        $module = $this->getRequest()->getModuleName();

        $modelPage = XOOPS::getModel("navigation_page");
        $clause = new Xoops_Zend_Db_Clause("navigation = ?", "front");
        $pages = $modelPage->enumerate($clause);
        foreach ($pages as $key => &$page) {
            if ($page["name"] == "modules" && $page["module"] == "system") {
                $page["type"] = "protected";
                break;
            }
        }
        //Debug::e($pages);
        $tree = $this->renderTree($pages);
        $this->setCallbacks();

        $title = XOOPS::_("Front Menu");
        $this->setTemplate("system/admin/menu_item.html");
        $this->template->assign("title", $title);
        $this->template->assign("navigation", "front");
    }

    // Admin navigation
    public function adminAction()
    {
        $module = $this->getRequest()->getModuleName();
        $modules = XOOPS::service("registry")->modulelist->read("active");

        $modelPage = XOOPS::getModel("navigation_page");
        $clause = new Xoops_Zend_Db_Clause("navigation = ?", "admin");
        $pages = $modelPage->enumerate($clause);
        foreach ($pages as $key => &$page) {
            if (($page["name"] == "modules" || $page["name"] == "module") && $page["module"] == "system") {
                $page["type"] = "protected";
            }
        }

        $tree = $this->renderTree($pages);
        $this->setCallbacks();

        $title = XOOPS::_("Admin Menu");
        $this->setTemplate("system/admin/menu_item.html");
        //$this->template->assign("pages", $pages);
        $this->template->assign("title", $title);
        $this->template->assign("navigation", "admin");
    }

    // Module front menus
    public function modulesAction()
    {
        $module = $this->getRequest()->getModuleName();
        $modules = XOOPS::service("registry")->modulelist->read("active");

        $modelPage = XOOPS::getModel("Navigation_Page");
        $clause = new Xoops_Zend_Db_Clause("navigation = ?", "front-modules");
        $pages = $modelPage->enumerate($clause);

        $tree = $this->renderTree($pages);
        $this->setCallbacks();

        $title = XOOPS::_("Module front menu");
        $this->setTemplate("system/admin/menu_item.html");
       // $this->template->assign("pages", $pages);
        $this->template->assign("title", $title);
        $this->template->assign("navigation", "front-modules");
    }

    // Module admin navigation
    public function moduleAction()
    {
        $module = $this->getRequest()->getModuleName();
        $name = $this->_getParam("name");
        $modules = XOOPS::service("registry")->modulelist->read("active");

        $modelPage = XOOPS::getModel("navigation_page");
        $clause = new Xoops_Zend_Db_Clause("navigation = ?", "admin:" . $name);
        $pages = $modelPage->enumerate($clause);

        $tree = $this->renderTree($pages);
        $this->setCallbacks();

        $title = sprintf(XOOPS::_("Admin Module Menu of %s"), $modules[$module]["name"]);
        $this->setTemplate("system/admin/menu_item.html");
       // $this->template->assign("pages", $pages);
        $this->template->assign("title", $title);
        $this->template->assign("navigation", "admin:" . $name);
    }

    // Generic navigation
    public function itemAction()
    {
        $module = $this->getRequest()->getModuleName();
        $name = $this->_getParam("name");
        $modules = XOOPS::service("registry")->modulelist->read("active");

        $modelPage = XOOPS::getModel("navigation_page");
        $clause = new Xoops_Zend_Db_Clause("navigation = ?", $name);
        $pages = $modelPage->enumerate($clause);

        $tree = $this->renderTree($pages);
        $this->setCallbacks();

        $title = XOOPS::_("Menu pages");
        $this->setTemplate("system/admin/menu_item.html");
        //$this->template->assign("pages", $pages);
        $this->template->assign("title", $title);
        $this->template->assign("navigation", $name);
    }

    protected function setCallbacks()
    {
        $actions = array(
            "read",
            "add",
            "edit",
            "rename",
            "move",
            "delete",
        );
        $callback = array();
        foreach ($actions as $key) {
            $url = $this->getFrontController()->getRouter()->assemble(
                array(
                    "module"        => "system",
                    "controller"    => "menuaction",
                    "action"        => $key,
                ),
                "admin"
            );
            $callback[] = $key . 'Call: "' . $url . '"';
        };
        $callbacks = "{" . implode(", ", $callback) . "}";

        //return $callbacks;
        $this->template->assign("callbacks", $callbacks);
    }

    protected function renderTree($data)
    {
        $tree = ""; //"<ul>";
        foreach ($data as $key => $element) {
            $tree .= $this->renderElement($element) . PHP_EOL;
        }
        //$tree .= "</ul>";
        //return $tree;
        $this->template->assign("tree", $tree);
    }

    protected function renderElement($element)
    {
        $tree = "<li " . (empty($element["child"]) ? "" : "class='open' ") . (empty($element["type"]) ? "" : "rel='" . $element["type"] . "' ") . "id='" . $element["id"] . "'>" . PHP_EOL;
        $tree .= "<a href='#'><ins>&nbsp;</ins>" . $element["label"] . "</a>" . PHP_EOL;
        if (!empty($element["child"])) {
            $tree .= "<ul>";
            foreach ($element["child"] as $ele) {
                $tree .= $this->renderElement($ele) . PHP_EOL;
            }
            $tree .= "</ul>";
        }
        $tree .= "</li>";
        return $tree;
    }
}