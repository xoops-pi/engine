<?php
/**
 * System admin ACL role controller
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

class System_RoleController extends Xoops_Zend_Controller_Action_Admin
{
    public function indexAction()
    {
        $this->setTemplate("system/admin/role_list.html");
        $module = $this->getRequest()->getModuleName();

        $modelRole = XOOPS::getModel("acl_role");
        $modelInherit = XOOPS::getModel("acl_inherit");
        $roleSet = $modelRole->fetchAll();
        $inheritSet = $modelInherit->fetchAll();

        $roles = array();

        foreach ($roleSet as $role) {
            $roles[$role->name]["role"] = $role->toArray();
            foreach (XOOPS::service("registry")->role->read($role->name) as $link) {
                if (!isset($roles[$role->name]["inherit"][$link])) {
                    $roles[$role->name]["inherit"][$link] = -1;
                }
            }
        }
        foreach ($inheritSet as $inherit) {
            $roles[$inherit->child]["inherit"][$inherit->parent] = $inherit->id;
        }
        foreach (array_keys($roles) as $role) {
            foreach (array_keys($roles) as $parent) {
                if (!empty($roles[$parent]["inherit"][$role]) || $role == $parent) {
                    $roles[$role]["inherit"][$parent] = 0;
                }
            }
        }

        $title = XOOPS::_("Role Inheritance");
        $action = $this->view->url(array("action" => "save", "controller" => "role", "module" => $module));
        $form = $this->getFormList("role_form_list", $roles, $title, $action);
        $form->assign($this->template);
    }

    public function addAction()
    {
        $this->setTemplate("system/admin/role_add.html");
        $module = $this->getRequest()->getModuleName();

        $role = array(
            "name"          => "",
            "title"         => "",
            "description"   => "",
        );
        $title = XOOPS::_("Add a new role");
        $action = $this->view->url(array("action" => "create", "controller" => "role", "module" => $module));
        $name = "role_form_edit";
        $form = $this->getFormRole($name, $role, $title, $action);
        $form->assign($this->template);
    }

    public function editAction()
    {
        $this->setTemplate("system/admin/role_edit.html");
        $module = $this->getRequest()->getModuleName();
        $name = $this->_getParam("name");

        $model = XOOPS::getModel("acl_role");
        $select = $model->select()
                        ->where("name = ?", $name);
        $role = $model->fetchRow($select)->toArray();
        $title = XOOPS::_("Role Edit");
        $action = $this->view->url(array("action" => "create", "controller" => "role", "module" => $module));
        $name = "role_form_edit";
        $form = $this->getFormRole($name, $role, $title, $action);
        $form->assign($this->template);
    }

    public function saveAction()
    {
        $module = $this->getRequest()->getModuleName();
        $inherits = $this->getRequest()->getPost("inherits", array());
        //$links = $this->getRequest()->getPost("links", array());
        $adds = $this->getRequest()->getPost("adds", array());

        $modelRole = XOOPS::getModel("acl_role");
        $modelInherit = XOOPS::getModel("acl_inherit");
        //$roleSet = $modelRole->fetchAll();
        //$inheritSet = $modelInherit->fetchAll();

        XOOPS::service("registry")->role->flush();
        $inherits = array_filter($inherits);
        if (!empty($inherits)) {
            $where = array("id NOT IN (?)" => array_keys($inherits));
        } else {
            $where = null;
        }
        $modelInherit->delete($where);

        //$flushes = array();
        $adds = array_filter($adds);
        foreach (array_keys($adds) as $link) {
            list($child, $parent) = explode("-", $link);
            $parents = XOOPS::service("registry")->role->read($parent);
            if (!empty($parents) && in_array($child, $parents)) continue;
            $modelInherit->insert(array("child" => $child, "parent" => $parent));
            XOOPS::service("registry")->role->flush($parent);
            //$flushes[$child] = 1;
            //$flushes[$parent] = 0;
        }
        $options = array("message" => _SYSTEM_AM_DBUPDATED, "time" => 3);
        $redirect = array("action" => "index");
        $this->redirect($redirect, $options);
    }

    public function createAction()
    {
        $name           = $this->getRequest()->getPost("name");
        $title          = $this->getRequest()->getPost("title", "");
        $description    = $this->getRequest()->getPost("description", "");

        $data = compact("name", "title", "description");
        $model = XOOPS::getModel('acl_role');
        if ($model->find($name)->count()) {
            $model->update($data, array("name = ?" => $name));
        } else {
            $model->insert($data);
        }

        $options = array("message" => $message, "time" => 3);
        $redirect = array("action" => "index");
        $this->redirect($redirect, $options);
    }

    private function getFormRole($name, $role, $title, $action)
    {
        //include_once XOOPS::path('www') . '/class/xoopsformloader.php';
        Xoops_Legacy::autoload();

        $form = new XoopsThemeForm($title, $name, $action, 'post', true);
        if (empty($role["name"])) {
            $nameElement = new XoopsFormText(XOOPS::_('Name'), 'name', 50, 64, $role["name"]);
            $nameElement->setDescription(XOOPS::_('Should be unique'));
        } else {
            $nameElement = new XoopsFormHidden('name', $role["name"]);
        }
        $form->addElement($nameElement);
        $form->addElement(new XoopsFormText(XOOPS::_('Title'), 'title', 50, 255, $role["title"]));
        $form->addElement(new XoopsFormText(XOOPS::_('Description'), 'description', 50, 255, $role["description"]));

        $form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
        return $form;
    }

    private function getFormList($name, $roles, $title, $action)
    {
        //include_once XOOPS::path('www') . '/class/xoopsformloader.php';
        Xoops_Legacy::autoload();

        $form = new XoopsFormGrid($title, $name, $action, 'post', true);
        $heads = array(XOOPS::_("Role"));
        foreach (array_keys($roles) as $parent) {
            $heads[] = $roles[$parent]["role"]["title"];
        }
        $heads[] = XOOPS::_("Manage");
        $form->setHead($heads);

        foreach ($roles as $child => $data) {
            $ele = new XoopsFormElementRow($data["role"]["title"]);
            $ele->setDescription($data['role']['name']);

            foreach (array_keys($roles) as $parent) {
                if (!isset($roles[$child]["inherit"][$parent])) {
                    $chkbox = new XoopsFormCheckBox("", "adds[{$child}-{$parent}]", 0);
                    $chkbox->addOption(1, "");
                    if ($child == "admin" || $parent == "admin") {
                        $chkbox->setDisabled();
                    }
                    $ele->addElement($chkbox);
                    unset($chkbox);
                } elseif ($roles[$child]["inherit"][$parent] > 0) {
                    $chkbox = new XoopsFormCheckBox("", "inherits[" . $roles[$child]["inherit"][$parent] . "]", 1);
                    $chkbox->addOption(1, "");
                    $ele->addElement($chkbox);
                    unset($chkbox);
                } elseif ($roles[$child]["inherit"][$parent] < 0) {
                    $label = new XoopsFormLabel("", "V");
                    $ele->addElement($label);
                    unset($label);
                } else {
                    $label = new XoopsFormLabel("", "X");
                    $ele->addElement($label);
                    unset($label);
                }
            }

            $href = $this->view->url(array(
                                        "action"        => "edit",
                                        "controller"    => "role",
                                        "name"          => $child,
                                        ), "admin");
            $editLink = "<a href=\"" . $href. "\" title=\"". $data["role"]["title"] ."\">" . XOOPS::_("Manage") . "</a>";
            $label = new XoopsFormLabel("", $editLink);
            $ele->addElement($label);
            unset($label);

            $form->addElement($ele);
            unset($ele);
        }
        $form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
        return $form;
    }
}