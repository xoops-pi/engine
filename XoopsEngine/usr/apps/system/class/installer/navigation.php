<?php
/**
 * XOOPS Module handler
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Xoops Engine http://www.xoopsengine.org
 * @license         BSD Licence
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         Xoops_Core
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
 *                  "uri"           => "modules/article/read.php?id=5",
 *                  "resource"      => "section=module&module=mvc&resource=test&item=3&privilege=read",
 *              ),
 *              ...
 *          ),
 *  );
 */

class App_System_Installer_Navigation extends Xoops_Installer_Module_Navigation
//class System_Installer_Navigation extends Xoops_Installer_Module_Navigation
{
    public function install(&$message)
    {
        $module = $this->module->dirname;
        $message = $this->message;
        $status = true;

        $navigations = $this->loadNavigation();

        foreach ($navigations['navigations'] as $key => $navigation) {
            if (!isset($navigation["module"])) {
                $navigation["module"] = $module;
            }
            if (!isset($navigation["name"])) {
                $navigation["name"] = $key;
            }
            $status = $this->insertNavigation($navigation, $message) * $status;
        }
        unset($navigations['navigations']);

        foreach ($navigations as $navigation => $pageList) {
            $status = $this->insertItems($navigation, $pageList, $message) * $status;
        }

        //$status = $this->registerAdmin($message) * $status;

        return $status;
    }

    public function update(&$message)
    {
        XOOPS::service('registry')->navigation->flush();

        if (version_compare($this->version, $this->module->version, ">=")) {
            return true;
        }

        $module = $this->module->dirname;
        $message = $this->message;
        $status = true;

        $navigations = $this->loadNavigation();

        $navigations_new = $navigations["navigations"];
        $modelNavigation = XOOPS::getModel('navigation');
        $select = $modelNavigation->select();
        $select->where('module = ?', $module);
        $select->from($modelNavigation, array("name", "id", "title"));
        $navigations_exist = $modelNavigation->getAdapter()->fetchAssoc($select);
        $navigation_install = array_diff(array_keys($navigations_new), array_keys($navigations_exist));
        $navigation_delete = array_diff(array_keys($navigations_exist), array_keys($navigations_new));
        $navigation_update = array_intersect(array_keys($navigations_exist), array_keys($navigations_new));
        // Add new navigations
        foreach ($navigation_install as $key) {
            $navigation = $navigations_new[$key];
            if (!isset($navigation["module"])) {
                $navigation["module"] = $module;
            }
            $status = $this->insertNavigation($navigation, $message) * $status;
        }
        // Delete deprecated navigations
        foreach ($navigation_delete as $key) {
            $status = $this->deleteNavigation($navigations_exist[$key]->id, $message) * $status;
        }
        // Update existent navigations
        foreach ($navigation_update as $key) {
            $navigationUpdate = $navigations_new[$key];
            $status = $modelNavigation->update($navigationUpdate, array("id = ?" => $navigations_exist[$key]->id)) * $status;
        }
        unset($navigations['navigations']);

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

        $navigation = "admin";
        $this->deleteItems($navigation, $message);
        if (!empty($navigations["admin"])) {
            $status = $this->insertItems($navigation, $navigations["admin"], $message);
        }

        return $status;
    }

    private function insertNavigation($navigation, &$message)
    {
        $modelNavigation = XOOPS::getModel('navigation');
        $columnsNavigation = $modelNavigation->info("cols");
        $data = array();
        foreach ($navigation as $col => $val) {
            if (in_array($col, $columnsNavigation)) {
                $data[$col] = $val;
            }
        }
        $columnsNavigation['module'] = isset($columnsNavigation['module']) ? $columnsNavigation['module'] : $module;
        if ($id = $modelNavigation->insert($data)) {
            $message[] = "Navigation " . $data['title'] . " created";
        } else {
            $message[] = "Navigation " . $data['title'] . " failed";
            $id = false;
        }

        return $id;
    }

    private function deleteNavigation($navigation, &$message)
    {
        $module = $this->module->dirname;
        $modelNavigation = XOOPS::getModel("navigation");
        $modelPage = XOOPS::getModel("navigation_page");
        //$modelAcl = XOOPS::getModel("navigation_acl");
        //$modelParam = XOOPS::getModel("navigation_Param");

        if (!$navigationRow = $modelNavigation->findRow($navigation)) {
            $message[] = "Navigation " . $navigation . " is not found";
            return false;
        }
        if (!$status = $modelNavigation->delete(array("id = ?" => $navigation))) {
            return false;
        }

        $clause = new Xoops_Zend_Db_Clause("navigation = ?", $navigationRow->name);
        $rowset = $modelPage->getRoots($clause);
        foreach ($rowset as $row) {
            $modelPage->remove($row, true);
        }

        return true;
    }

    protected function loadNavigation()
    {
        $navigations = parent::loadNavigation();
        //$navigations["front-modules"] = isset($navigations["front"]) ? $navigations["front"] : array();
        $navigations["front"] = $navigations["front-template"];
        $navigations["admin"] = $navigations["admin-template"];
        unset($navigations["front-template"]);
        unset($navigations["admin-template"]);

        return $navigations;
    }

}