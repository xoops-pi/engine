<?php
/**
 * System admin ACL resouce controller
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

class System_ResourceController extends Xoops_Zend_Controller_Action_Admin
{
    // section list
    public function indexAction()
    {
        //$this->setTemplate("system/admin/resource_index.html");
        $module = $this->getRequest()->getModuleName();
        $modules = XOOPS::service("registry")->modulelist->read("active");
        $moduleDeault = array("default" => array("name"  => XOOPS::_("System Application")));
        array_unshift($modules, $moduleDeault);

        $model = XOOPS::getModel("acl_resource");
        $select = $model->select()
                        ->from($model, array("section", "count" => "COUNT(*)"))
                        ->where("section <> ?", "module")
                        ->group("section");
        /*
        $result = $model->fetchAll($select);
        $countList = array();
        foreach ($result as $row) {
            $countList[$row->section] = $row->count;
        }
        */
        $countList = $model->getAdapter()->fetchPairs($select);

        $select = $model->select()
                        ->from($model, array("module", "count" => "COUNT(*)"))
                        ->where("section = ?", "module")
                        ->group("module");
        /*
        $result = $model->fetchAll($select);
        $countModules = array();
        foreach ($result as $row) {
            $countModules[$row->module] = $row->count;
        }
        */
        $countModules = $model->getAdapter()->fetchPairs($select);

        $modelBlock = XOOPS::getModel("block");
        $select = $modelBlock->select()->from($modelBlock, array("count" => "COUNT(*)"));
        $result = $modelBlock->fetchRow($select);
        $countBlock = $result->count;

        $sections = array(
            "front" => array(
                "title" => XOOPS::_("Front"),
                "count" => $countList["front"],
                "url"   => $this->getFrontController()->getRouter()->assemble(
                    array(
                        "module"        => $module,
                        "controller"    => "resource",
                        "action"        => "page",
                        "section"       => "front",
                    ),
                    "admin"
                )
            ),
            "admin" => array(
                "title" => XOOPS::_("Admin"),
                "count" => $countList["admin"],
                "url"   => $this->getFrontController()->getRouter()->assemble(
                    array(
                        "module"        => $module,
                        "controller"    => "resource",
                        "action"        => "page",
                        "section"       => "admin",
                    ),
                    "admin"
                )
            ),
            "block" => array(
                "title" => XOOPS::_("Blocks"),
                "count" => $countBlock,
            ),
        );
        foreach ($countModules as $dirname => $count) {
            if (!isset($modules[$dirname])) continue;
            $sections[$dirname] = array(
                "title" => $modules[$dirname]["name"],
                "count" => isset($countModules[$dirname]) ? $countModules[$dirname] : 0,
                "url"   => $this->getFrontController()->getRouter()->assemble(
                    array(
                        "module"        => $module,
                        "controller"    => "resource",
                        "action"        => "section",
                        "dirname"       => $dirname,
                    ),
                    "admin"
                )
            );
        }

        $this->template->assign("sections", $sections);
    }

    // page resource list
    public function pageAction()
    {
        $this->setTemplate("system/admin/resource_page.html");
        $section = $this->_getParam("section", "front");
        $module = $this->getRequest()->getModuleName();
        $modules = XOOPS::service("registry")->modulelist->read("active");

        /*
        $modelPage = XOOPS::getModel("page");
        $select = $modelPage->select()
                        ->from($modelPage, "id")
                        ->where("section = ?", $section)
                        ->where("controller = ?", "");
        $rowset = $modelPage->fetchAll($select);
        $pages = array();
        foreach ($rowset as $row) {
            $pages[$row->id] = 1;
        }
        $pages[1] = $pages[0];
        */
        $resources = array();
        $modelResource = XOOPS::getModel("acl_resource");
        $resourceList = $modelResource->enumerate(new Xoops_Zend_Db_Clause("section = ?", $section), null, true);
        foreach ($resourceList as $id => $resource) {
            if ($resource["custom"]) {
                $resource["delete"] = 1;
            }
            if ($resource["depth"] == 0) {
                $key = $id;
                $resources[$key] = $resource;
            } else {
                $resources[$key]["resources"][$id] = array(
                    "indent"    => str_pad("", $resource["depth"], "-"),
                    "name"      => $resource["name"],
                    "title"     => !empty($resource["title"]) ? $resource["title"] : $resource["name"]
                );
            }
        }

        $this->template->assign("section", $section);
        $this->template->assign("resources", $resources);
        $this->template->assign("title", sprintf(XOOPS::_("Resources of page %s"), $section));
    }

    // resource list
    public function sectionAction()
    {
        $this->setTemplate("system/admin/resource_section.html");
        $module = $this->getRequest()->getModuleName();
        $section = $this->_getParam("section", "module");
        $dirname = $this->_getParam("dirname", "");
        $modules = XOOPS::service("registry")->modulelist->read("active");

        $modelResource = XOOPS::getModel("acl_resource");
        $clause = new Xoops_Zend_Db_Clause("section = ?", $section);
        if (!empty($dirname)) {
            $clause->add("module = ?", $dirname);
        }
        $resourceList = $modelResource->enumerate($clause, null, true);
        $privileges = array();
        if (!empty($resourceList)) {
            $modulePrivilege = XOOPS::getModel("acl_privilege");
            $select = $modulePrivilege->select()->where("resource IN (?)", array_keys($resourceList));
            $rowset = $modulePrivilege->fetchAll($select);
            foreach ($rowset as $row) {
                $privileges[$row->resource][$row->id] = $row->toArray();
            }
        }
        $resources = array();
        foreach ($resourceList as $id => $resource) {
            //$data = $resource["node"];
            $resource["indent"] = str_pad("", $resource["depth"], "-");
            $resource["privileges"] = isset($privileges[$id]) ? $privileges[$id] : array();
            $resources[$id] = $resource;
        }
        //Debug::e($resources);

        $this->template->assign("resources", $resources);
        $this->template->assign("title", sprintf(XOOPS::_("Resources of %s"), ($section == "module") ? $modules[$dirname]["name"] : $section));
    }

    // add a new page resource
    public function addAction()
    {
        $this->setTemplate("system/admin/resource_add.html");
        $module = $this->getRequest()->getModuleName();
        $parent = $this->_getParam("parent");
        $section = $this->_getParam("section");

        $resource = array(
            "name"          => "",
            "module"        => "",
            "title"         => "",
        );
        if (empty($parent)) {
            $resource["module"] = "default";
        }
        $title = XOOPS::_("Add a new page resource");
        $action = $this->view->url(array("action" => "save", "controller" => "resource", "module" => $module));
        $name = "resource_form_edit";
        $form = $this->getFormResourceAdd($name, $resource, $title, $action);
        $form->addElement(new XoopsFormHidden('parent', $parent));
        $form->addElement(new XoopsFormHidden('section', $section));
        $form->assign($this->template);
    }

    // edit a resource
    public function editAction()
    {
        $this->setTemplate("system/admin/resource_edit.html");
        $module = $this->getRequest()->getModuleName();
        $id = $this->_getParam("id");

        $model = XOOPS::getModel("acl_resource");
        $select = $model->select()
                        ->where("id = ?", $id);
        $row = $model->fetchRow($select);
        $resource = $row->toArray();
        $title = XOOPS::_("Page Resource Edit");
        $action = $this->view->url(array("action" => "save", "controller" => "resource", "module" => $module));
        $name = "resource_form_edit";
        $form = $this->getFormResourceEdit($name, $resource, $title, $action);
        $form->addElement(new XoopsFormHidden('id', $id));
        $form->assign($this->template);
    }

    // Save a page resource information into database
    public function saveAction()
    {
        $module = $this->getRequest()->getModuleName();

        $id = $this->getRequest()->getPost("id", 0);
        $section = $this->getRequest()->getPost("section");
        $parent = $this->getRequest()->getPost("parent", 0);
        $title = $this->getRequest()->getPost("title");
        $name = $this->getRequest()->getPost("name");
        $dirname = $this->getRequest()->getPost("resource_module", "default");
        $model = XOOPS::getModel('acl_resource');

        $success = true;
        if (empty($id)) {
            $parentResource = null;
            if (empty($parent) && empty($section)) {
                $message = XOOPS::_("Parameters are missing.");
                $success = false;
            }
            if ($success && !empty($parent)) {
                if (!$parentResource = $model->findRow($parent)) {
                    $message = XOOPS::_("The parent resource is not found.");
                    $success = false;
                } else {
                    $section = $parentResource->section;
                }
            }
            if ($success) {
                $data = compact("section", "title", "name");
                $data["module"] = $dirname;
                $data["type"] = "custom";
                $model->add($data, $parentResource);
                $message = XOOPS::_("The resource is added successfully.");
            }
        } else {
            if (!$resource = $model->findRow($id)) {
                $message = XOOPS::_("The resource is not found.");
                $success = false;
            } else {
                $data = array("title" => $title);
                $model->update($data, array("id = ?" => $id));
                $message = XOOPS::_("The resource is updated successfully.");
                $section = $resource->section;
                $dirname = $resource->module;
            }
        }
        $options = array("message" => $message, "time" => 3);
        if ($success) {
            XOOPS::service("registry")->page->flush($dirname);
            $redirect = array("action" => "page", "section" => $section);
        } else {
            $redirect = array("action" => "index");
        }
        $this->redirect($redirect, $options);
    }

    // delete a page resource from database
    public function deleteAction()
    {
        $id = $this->_getParam("id", 0);
        $section = "";
        $model = XOOPS::getModel('acl_resource');
        if (!$resource = $model->findRow($id)) {
            $message = XOOPS::_("The resource is not found.");
        } else {
            $section = $resource->section;
            //$modelPage = XOOPS::getModel("page");
            //$page = $modelPage->findRow($resource->name);
            // Module page resource is not allowed to remove
            //if (is_object($page) && ($page->id === $resource->name) && ($page->section != $section || $page->controller == "")) {
            if ("custom" != $resource->type) {
                $message = XOOPS::_("The resource is protected from deletion.");
            } else {
                $model->remove($resource);
                //XOOPS::getModel("acl_rule")->delete(array("resource = ?" => $id, "section = ?" => $resource->section));
                XOOPS::service("registry")->page->flush($resource->module);
                $message = XOOPS::_("The resource is removed successfully.");
            }
        }

        $options = array("message" => $message, "time" => 3);
        $redirect = array("action" => "index");
        $this->redirect($redirect, $options);
    }

    // Resource form
    private function getFormResourceAdd($name, $resource, $title, $action)
    {
        Xoops_Legacy::autoload();

        $form = new XoopsThemeForm($title, $name, $action, 'post', true);
        $form->addElement(new XoopsFormText(XOOPS::_('Name'), 'name', 50, 64, $resource["name"]));
        $form->addElement(new XoopsFormText(XOOPS::_('Module'), 'resource_module', 50, 64, $resource["module"]));
        $form->addElement(new XoopsFormText(XOOPS::_('Title'), 'title', 50, 255, $resource["title"]));

        $form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
        return $form;
    }

    private function getFormResourceEdit($name, $resource, $title, $action)
    {
        Xoops_Legacy::autoload();

        $form = new XoopsThemeForm($title, $name, $action, 'post', true);
        $form->addElement(new XoopsFormText(XOOPS::_('Title'), 'title', 50, 255, $resource["title"]));

        $form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
        return $form;
    }
}