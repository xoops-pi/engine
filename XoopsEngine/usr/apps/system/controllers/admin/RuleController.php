<?php
/**
 * System admin ACL rule controller
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

class System_RuleController extends Xoops_Zend_Controller_Action_Admin
{
    // section list
    // @TODO: section
    public function indexAction()
    {
        $module = $this->getRequest()->getModuleName();
        $modules = XOOPS::service("registry")->modulelist->read("active");
        $moduleDeault = array("default" => array("name"  => XOOPS::_("System Application")));
        $modules = array_merge($moduleDeault, $modules);

        //Debug::e($modules);
        $modelResource = XOOPS::getModel("acl_resource");
        $select = $modelResource->select()
                                    ->from($modelResource, array("section", "module", "count" => "COUNT(*)"))
                                    ->group(array("section", "module"));
        $rowSet = $modelResource->fetchAll($select);
        $list = array();
        foreach ($rowSet as $row) {
            $list[$row->section][$row->module] = $row->count ? $row->count : 0;
        }
        $modelBlock = XOOPS::getModel("block");
        $select = $modelBlock->select()
                                ->from($modelBlock, array("module", "count" => "COUNT(*)"))
                                ->group("module");
        $rowSet = $modelBlock->fetchAll($select);
        foreach ($rowSet as $row) {
            $list["block"][$row->module] = $row->count ? $row->count : 0;
        }

        $sections = array(
            "front" => array(
                "title" => XOOPS::_("Front page resources"),
            ),
            "admin" => array(
                "title" => XOOPS::_("Admin page resources"),
            ),
            "block" => array(
                "title" => XOOPS::_("Blocks"),
                "resources" => array(
                    // custom blocks
                    ""     => array(
                        "title" => XOOPS::_("Custom blocks"),
                        "url"   => $this->getFrontController()->getRouter()->assemble(
                                array(
                                    "module"        => $module,
                                    "controller"    => "rule",
                                    "action"        => "block",
                                    "custom"        => 1
                                ),
                                "admin"
                        ),
                    ),
                ),
            ),
            "module" => array(
                "title" => XOOPS::_("Modules"),
            ),
        );
        //foreach (array_keys($modules) as $dirname) {
        foreach (array_keys($sections) as $section) {
            if (!isset($list[$section])) {
                unset($sections[$section]);
                continue;
            }
            foreach ($list[$section] as $dirname => $count) {
                if (isset($sections[$section]["resources"][$dirname])) {
                    $sections[$section]["resources"][$dirname]["count"] = $count;
                    continue;
                }
                if (!isset($modules[$dirname])) {
                    continue;
                }
                $sections[$section]["resources"][$dirname] = array(
                    "title" => $modules[$dirname]["name"],
                    "count" => $count,
                    "url"   => $this->getFrontController()->getRouter()->assemble(
                            array(
                                "module"        => $module,
                                "controller"    => "rule",
                                "action"        => $section,
                                "dirname"       => $dirname
                            ),
                            "admin"
                    ),
                );
            }
            if (empty($list["block"][""])) {
                unset($sections["block"]["resources"][""]);
            }
        }

        //Debug::e($list);
        //Debug::e($sections);

        $this->template->assign("sections", $sections);
    }

    // front page resource list
    public function frontAction()
    {
        $this->setTemplate("system/admin/rule_section.html");
        $dirname = $this->_getParam("dirname", "default");
        $module = $this->getRequest()->getModuleName();
        $section = "front";

        //$modules = XOOPS::service("registry")->modulelist->read("active");
        $roles = $this->getRoleList();

        $acl = new Xoops_Acl($section);
        $modelResource = XOOPS::getModel("acl_resource");
        $clause = new Xoops_Zend_Db_Clause("section = ?", $section);
        $clause->add("module = ?", $dirname);
        $resourceList = $modelResource->enumerate($clause, null, true);
        if (empty($resourceList)) {
            $this->template->assign("rule_form_list", XOOPS::_("No resources available"));
            return;
        }
        $resources = array();
        $access = array();
        foreach ($resourceList as $id => $resource) {
            $data = $resource; //["node"];
            $data["indent"] = str_pad("", $resource["depth"] - 1, "-");
            $resources[$id] = $data;
            foreach (array_keys($roles) as $role) {
                $access[$id][$role] = $acl->isAllowed($role, $data["name"]);
            }
        }
        //Debug::e($resources);
        //Debug::e($access);

        $modelRule = XOOPS::getModel("acl_rule");
        $select = $modelRule->select()->where("section = ?", $section)
                                        ->where("resource IN (?)", array_keys($resources));
        $rowSet = $modelRule->fetchAll($select);
        $rules = array();
        foreach ($rowSet as $row) {
            $rules[$row->resource][$row->role] = $row->deny;
        }
        $data = compact("roles", "resources", "rules", "access");
        $title = XOOPS::_("Permissions for front pages");
        $action = $this->view->url(array("action" => "save", "controller" => "rule", "module" => $module));
        $form = $this->getFormList("rule_form_list", $data, $title, $action);
        $form->addElement(new xoopsFormHidden("section", $section));
        $form->addElement(new xoopsFormHidden("dirname", $dirname));
        $form->assign($this->template);
    }

    // admin page resource list
    public function adminAction()
    {
        $this->setTemplate("system/admin/rule_section.html");
        $dirname = $this->_getParam("dirname", "default");
        $module = $this->getRequest()->getModuleName();
        $section = "admin";

        //$modules = XOOPS::service("registry")->modulelist->read("active");
        $roles = $this->getRoleList();

        $acl = new Xoops_Acl($section);
        $modelResource = XOOPS::getModel("acl_resource");
        $clause = new Xoops_Zend_Db_Clause("section = ?", $section);
        $clause->add("module = ?", $dirname);
        $resourceList = $modelResource->enumerate($clause, null, true);
        if (empty($resourceList)) {
            $this->template->assign("rule_form_list", XOOPS::_("No resources available"));
            return;
        }
        $resources = array();
        $access = array();
        foreach ($resourceList as $id => $resource) {
            $data = $resource; //["node"];
            $data["indent"] = str_pad("", $resource["depth"] - 1, "-");
            $resources[$id] = $data;
            foreach (array_keys($roles) as $role) {
                $access[$id][$role] = $acl->isAllowed($role, $data["name"]);
            }
        }
        //Debug::e($resources);
        //Debug::e($access);

        $modelRule = XOOPS::getModel("acl_rule");
        $select = $modelRule->select()->where("section = ?", $section)
                                        ->where("resource IN (?)", array_keys($resources));
        $rowSet = $modelRule->fetchAll($select);
        $rules = array();
        foreach ($rowSet as $row) {
            $rules[$row->resource][$row->role] = $row->deny;
        }
        $data = compact("roles", "resources", "rules", "access");
        $title = XOOPS::_("Permissions for admin pages");
        $action = $this->view->url(array("action" => "save", "controller" => "rule", "module" => $module));
        $form = $this->getFormList("rule_form_list", $data, $title, $action);
        $form->addElement(new xoopsFormHidden("section", $section));
        $form->addElement(new xoopsFormHidden("dirname", $dirname));
        $form->assign($this->template);
    }


    // block resource list
    public function blockAction()
    {
        $this->setTemplate("system/admin/rule_section.html");
        $dirname = $this->_getParam("dirname", "");
        $custom = $this->_getParam("custom", 0);
        $module = $this->getRequest()->getModuleName();
        $section = "block";

        //$modules = XOOPS::service("registry")->modulelist->read("active");
        $roles = $this->getRoleList();

        $modelBlock = XOOPS::getModel("block");
        $select = $modelBlock->select()->from($modelBlock, array("id", "name", "title"))->where("module = ?", $dirname);
        $resourceList = $modelBlock->fetchAll($select);
        if (!$resourceList->count()) {
            $this->template->assign("rule_form_list", XOOPS::_("No resources available"));
            return;
        }

        $resources = array();
        foreach ($resourceList as $row) {
            $data = $row->toArray();
            $resources[$data["id"]] = $data;
        }
        $access = array();
        $clause = new Xoops_Zend_Db_Clause("resource IN (?)", array_keys($resources));
        $acl = new Xoops_Acl($section);
        foreach (array_keys($roles) as $role) {
            $acl->setRole($role);
            $blockIds = $acl->getResources($clause);

            foreach ($blockIds as $id) {
                $access[$id][$role] = 1;
            }
        }

        $modelRule = XOOPS::getModel("acl_rule");
        $select = $modelRule->select()->where("section = ?", $section)
                                        ->where("resource IN (?)", array_keys($resources));
        $rowSet = $modelRule->fetchAll($select);
        $rules = array();
        foreach ($rowSet as $row) {
            $rules[$row->resource][$row->role] = $row->deny;
        }
        $data = compact("roles", "resources", "rules", "access");
        $title = sprintf(XOOPS::_("Permissions for blocks of %s"), empty($dirname) ? XOOPS::_("custom") : $dirname);
        $action = $this->view->url(array("action" => "save", "controller" => "rule", "module" => $module));
        $form = $this->getFormList("rule_form_list", $data, $title, $action);
        $form->addElement(new xoopsFormHidden("section", $section));
        $form->addElement(new xoopsFormHidden("dirname", $dirname));
        $form->assign($this->template);
    }

    // module resource list
    public function moduleAction()
    {
        $this->setTemplate("system/admin/rule_section.html");
        $dirname = $this->_getParam("dirname", "");
        $module = $this->getRequest()->getModuleName();
        $section = "module";

        //$modules = XOOPS::service("registry")->modulelist->read("active");
        $roles = $this->getRoleList();

        $modelResource = XOOPS::getModel("acl_resource");
        $clause = new Xoops_Zend_Db_Clause("section = ?", $section);
        $clause->add("module = ?", $dirname);
        $resourceList = $modelResource->enumerate($clause, null, true);
        if (empty($resourceList)) {
            $this->template->assign("rule_form_list", XOOPS::_("No resources available"));
            return;
        }
        $privileges = array();
        $modulePrivilege = XOOPS::getModel("acl_privilege");
        $select = $modulePrivilege->select()->where("resource IN (?)", array_keys($resourceList));
        $rowset = $modulePrivilege->fetchAll($select);
        //$resourceSet = array();
        foreach ($rowset as $row) {
            $privileges[$row->resource][$row->name] = $row->toArray();
            //$resourceSet[$row->resource] = $row;
        }
        $resources = array();
        $access = array();
        $acl = new Xoops_Acl($section);
        $acl->setModule($dirname);
        foreach ($resourceList as $id => $resource) {
            $data = $resource; //["node"];
            $data["indent"] = str_pad("", $resource["depth"] - 1, "-");
            //$data["privileges"] = isset($privileges[$id]) ? $privileges[$id] : array();
            $resources[$id] = $data;
            $resourceRow = $modelResource->findRow($id);
            foreach (array_keys($roles) as $role) {
                if (empty($privileges[$id])) {
                    $access[$id][$role] = $acl->isAllowed($role, $resourceRow);
                } else {
                    foreach (array_keys($privileges[$id]) as $privilege) {
                        $access[$id][$privilege][$role] = $acl->isAllowed($role, $resourceRow, $privilege);
                    }
                }
            }
        }
        //Debug::e($resources);
        //Debug::e($privileges);
        //Debug::e($access);

        $modelRule = XOOPS::getModel("acl_rule");
        $select = $modelRule->select()->where("section = ?", $section)
                                        ->where("resource IN (?)", array_keys($resources));
        $rowSet = $modelRule->fetchAll($select);
        $rules = array();
        foreach ($rowSet as $row) {
            $rules[$row->resource][$row->privilege][$row->role] = $row->deny;
        }
        //Debug::e($resources);
        $data = compact("roles", "resources", "rules", "access", "privileges");
        $title = sprintf(XOOPS::_("Permissions for module of %s"), $dirname);
        $action = $this->view->url(array("action" => "save", "controller" => "rule", "module" => $module));
        $form = $this->getFormList("rule_form_list", $data, $title, $action);
        $form->addElement(new xoopsFormHidden("section", $section));
        $form->addElement(new xoopsFormHidden("dirname", $dirname));
        $form->assign($this->template);
    }

    // Save resource rule information into database
    public function saveAction()
    {
        $module = $this->getRequest()->getModuleName();

        $section = $this->getRequest()->getPost("section");
        $dirname = $this->getRequest()->getPost("dirname");
        $rules = $this->getRequest()->getPost("rules", array());

        //Debug::e($rules);
        $modelRule = XOOPS::getModel('acl_rule');
        $select = $modelRule->select()->where("section = ?", $section)->where("module = ?", $dirname);
        $rowset = $modelRule->fetchAll($select);
        $ruleList = array();
        foreach ($rowset as $row) {
            $ruleList[$row->resource][$row->role][$row->privilege] = $row;
        }
        $count = 0;
        foreach ($rules as $key => $deny) {
            list($resource, $role, $privilege) = explode("-", $key);
            $ruleRow =  isset($ruleList[$resource][$role][$privilege]) ? $ruleList[$resource][$role][$privilege] : null;
            if ($deny < 0) {
                if (is_null($ruleRow)) {
                    continue;
                }
                $modelRule->delete(array("id = ?" => $ruleRow->id));
                $count++;
                continue;
            }

            if (is_null($ruleRow)) {
                $data = compact("section", "resource", "privilege", "deny", "role");
                $data["module"] = $dirname;
                $modelRule->insert($data);
                $count++;
            } elseif ($deny == $ruleRow->deny) {
                continue;
            } else {
                $modelRule->update(array("deny" => $deny), array("id = ?" => $ruleRow->id));
                $count++;
            }
        }
        $message = sprintf(XOOPS::_("%d rules upated"), $count);
        $options = array("message" => $message, "time" => 3);
        $redirect = array("action" => "index", "dirname" => $dirname);
        $this->redirect($redirect, $options);
    }

    private function getRoleList()
    {
        $modelRole = XOOPS::getModel("acl_role");
        $roleSet = $modelRole->fetchAll();
        $roles = array();
        $rolesSkip = array("admin", "inactive", "banned");
        foreach ($roleSet as $role) {
            if (in_array($role->name, $rolesSkip)) continue;
            $roles[$role->name] = $role->title;
        }

        return $roles;
    }

    private function getFormList($name, $data, $title, $action)
    {
        //include_once XOOPS::path('www') . '/class/xoopsformloader.php';
        Xoops_Legacy::autoload();

        $form = new XoopsFormAcl($title, $name, $action, 'post', true);
        $form->roles = $data["roles"];
        $form->resources = $data["resources"];
        $form->rules = $data["rules"];
        $form->access = $data["access"];
        if (isset($data["privileges"])) {
            $form->privileges = $data["privileges"];
        }
        return $form;
    }
}