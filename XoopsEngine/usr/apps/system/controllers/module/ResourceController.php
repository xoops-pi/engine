<?php
/**
 * Generic module admin resource controller
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

class Module_ResourceController extends Xoops_Zend_Controller_Action_Admin
{
    // resource list
    public function indexAction()
    {
        $this->setTemplate("system/admin/resource_list_module.html");
        //$section = $this->_getParam("section", "front");
        $module = $this->getRequest()->getModuleName();
        //$modules = XOOPS::service("registry")->modulelist->read("active");

        $sections = array(
            "front"     => XOOPS::_("Front page resources"),
            "admin"     => XOOPS::_("Admin page resources"),
            "module"    => XOOPS::_("Modules resources"),
            "block"     => XOOPS::_("Blocks"),
        );
        $modelResource = XOOPS::getModel("acl_resource");
        $select = $modelResource->select()->distinct()
                                            ->from($modelResource, "section")
                                            ->where("module = ?", $module)
                                            ->where("section <> ?", "feed");
        $rowset = $modelResource->fetchAll($select);
        $resources = array();
        foreach ($rowset as $row) {
            $section = $row->section;
            $resources[$section] = array(
                "section"   => isset($sections[$section]) ? $sections[$section] : $row->section,
            );
            if ($section != "admin") {
                $resources[$section]["url"] = $this->getFrontController()->getRouter()->assemble(
                    array(
                        "module"        => $module,
                        "controller"    => "rule",
                        "action"        => $section,
                    ),
                    "admin"
                );
            }
            $clause = new Xoops_Zend_Db_Clause("section = ?", $section);
            $clause->add("module = ?", $module);
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

            foreach ($resourceList as $id => $resource) {
                $resources[$section]["resources"][$id] = array(
                    "indent"    => str_pad("", $resource["depth"], "-"),
                    "name"      => $resource["name"],
                    "title"     => !empty($resource["title"]) ? $resource["title"] : $resource["name"],
                    /*
                    "ruleUrl"   => $this->getFrontController()->getRouter()->assemble(
                            array(
                                "module"        => $module,
                                "controller"    => "resource",
                                "action"        => "rule",
                                "section"       => $section,
                                "resource"      => $id,
                            ),
                            "admin"
                    ),
                    */
                    "privileges"    => isset($privileges[$id]) ? $privileges[$id] : array(),
                );
                if ($section != "admin") {
                    $resources[$section]["resources"][$id]["url"] = $this->getFrontController()->getRouter()->assemble(
                        array(
                            "module"        => $module,
                            "controller"    => "rule",
                            "action"        => $section,
                            "resource"      => $id,
                        ),
                        "admin"
                    );
                }
            }
        }
        $modelBlock = XOOPS::getModel("block");
        $select = $modelBlock->select()->from($modelBlock, array("id", "name", "title"))->where("module = ?", $module);
        $resourceList = $modelBlock->fetchAll($select);
        if ($resourceList->count()) {
            $section = "block";
            $resources[$section] = array(
                "section"   => isset($sections[$section]) ? $sections[$section] : $row->section,
            );
            foreach ($resourceList as $id => $resource) {
                $resources[$section]["resources"][$resource["id"]] = array(
                    "indent"    => "",
                    "name"      => !empty($resource["name"]) ? $resource["name"] : $resource["id"],
                    "title"     => !empty($resource["title"]) ? $resource["title"] : $resource["name"],
                    "ruleUrl"   => $this->getFrontController()->getRouter()->assemble(
                            array(
                                "module"        => $module,
                                "controller"    => "rule",
                                "action"        => $section,
                                "resource"      => $resource["id"],
                            ),
                            "admin"
                    ),
                );
            }
        }

        $this->template->assign("resources", $resources);
        $this->template->assign("title", XOOPS::_("Module resources"));
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
        //Debug::_e($resources);

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
        //include_once XOOPS::path('www') . '/class/xoopsformloader.php';
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
        //include_once XOOPS::path('www') . '/class/xoopsformloader.php';
        Xoops_Legacy::autoload();

        $form = new XoopsThemeForm($title, $name, $action, 'post', true);
        //$form->addElement(new XoopsFormText(XOOPS::_('Controller'), 'resource_controller', 50, 64, $resource["controller"]));
        $form->addElement(new XoopsFormText(XOOPS::_('Title'), 'title', 50, 255, $resource["title"]));

        $form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
        return $form;
    }
}