<?php
/**
 * User admin index (users) controller
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
 * @package         User
 * @version         $Id$
 */

class User_IndexController extends Xoops_Zend_Controller_Action
{
    public function indexAction()
    {
        $this->setTemplate("user_list.html");
        $module = $this->getRequest()->getModuleName();
        $page = $this->getRequest()->getParam("page", 1);
        $configs = XOOPS::service("registry")->config->read("admin", $module);
        $itemCountPerPage = !empty($configs["items_per_page"]) ? $configs["items_per_page"] : 10;
        $userModel = XOOPS::getModel("user");

        $select = $userModel->select()->where("active = ?", 1)->order("id DESC");
        $paginator = Xoops_Zend_Paginator::factory($select);
        $paginator->setItemCountPerPage($itemCountPerPage);
        $paginator->setCurrentPageNumber($page);
        //$paginator->setPageParam("cp");
        $rowset = $paginator->getCurrentItems();

        $view = $this->view;
        $editLink = function ($id, $action) use ($view)
        {
            $action = ($action == "delete") ? "delete" : "edit";
            $link = $view->url(
                array(
                    "action"    => $action,
                    "id"        => $id,
                ),
                "admin",
                false
            );
            return $link;
        };

        $viewLink = function ($id) use ($view)
        {
            $link = $view->url(
                array(
                    "user"      => $id,
                ),
                "user"
            );
            return $link;
        };

        $users = array();
        foreach ($rowset as $row) {
            $users[$row->id] = $row->toArray();
            $users[$row->id]["edit"] = $editLink($row->id, "edit");
            $users[$row->id]["view"] = $viewLink($row->id);
            $users[$row->id]["delete"] = $editLink($row->id, "delete");
        }

        $roleModel = XOOPS::getModel("acl_role");
        $select = $roleModel->select();
        $roleList = $roleModel->getAdapter()->fetchPairs($select);

        if (!empty($users)) {
            $userRoleModel = XOOPS::getModel("acl_user");
            $select = $userRoleModel->select()->where("user IN (?)", array_keys($users));
            $rowset = $userRoleModel->fetchAll($select);
            foreach ($rowset as $row) {
                $users[$row->user]["role"] = $roleList[$row->role];
            }
        }

        $addLink = $this->view->url(array("action" => "add", "controller" => "index", "module" => "user"));
        $this->template->assign("url_adduser", $addLink);
        $this->template->assign("users", $users);
        $this->template->assign("paginator", $paginator);
    }

    public function batchAction()
    {
        $module = $this->getRequest()->getModuleName();
        $prefix = $this->getRequest()->getParam("prefix", "user");
        $count = $this->getRequest()->getParam("count", 100);

        $roleModel = XOOPS::getModel("acl_user");
        for ($i = 1; $i <= $count; $i++) {
            $data = array(
                "identity"      => $prefix . $i,
                "credential"    => "password" . $i,
                "email"         => $prefix . $i . "@example.org",
                "name"          => ucfirst($prefix) . " " . $i,
                "active"        => 1,
                "create_time"   => time(),
                "create_ip"     => $this->getRequest()->getClientIp(),
            );
            $id = \App\User\Gateway::create($data);
            $roleModel->insert(array("user" => $id, "role" => Xoops_Acl::MEMBER));
        }

        return $this->redirect(array('action' => 'index'));
    }

    public function addAction()
    {
        $this->editAction();
    }

    public function editAction()
    {
        $module = $this->getRequest()->getModuleName();
        $id = $this->getRequest()->getParam("id", 0);
        $form = $this->getForm($id);
        $this->renderForm($form);
    }

    public function saveAction()
    {
        $module = $this->getRequest()->getModuleName();
        if (!$this->getRequest()->isPost()) {
            return $this->_helper->redirector('index');
        }

        $posts = $this->getRequest()->getPost();
        $id = $posts["id"];
        $form = $this->getForm($id);
        if (!$form->isValid($posts)) {
            return $this->renderForm($form);
        }

        $values = $form->getValues();
        if (!empty($id)) {
            $status = \App\User\Gateway::update($values, $message);
        } else {
            unset($values["id"]);
            $status = \App\User\Gateway::create($values, $message);
        }
        if (!$status) {
            $message[] = XOOPS::_("Profile was not able to save.");
            $form->addErrors($message);
            $form->addDecorators(array(
                'Errors',
            ));
            return $this->renderForm($form);
        }
        if (!empty($id)) {
            XOOPS::service("event")->trigger("update", $values);
        }
        return $this->_helper->redirector('index');
    }

    public function deleteAction()
    {
        $module = $this->getRequest()->getModuleName();
        $id = $this->getRequest()->getParam("id", 0);
        if (!$this->getRequest()->isPost()) {
            $this->confirm(
                array(
                    "id"    => $id,
                ),
                null,
                array(
                    'action'        => 'index',
                    'controller'    => 'index',
                    'module'        => 'user',
                    'route'         => 'admin',
                    'reset'         => true,
                )
            );
            return;
        }

        $status = \App\User\Gateway::delete($id, $message);
        if (!$status) {
            $message = XOOPS::_("User deletion is not performed completely.") . '<br />' . implode("<br />", $message);
        } else {
            XOOPS::service("event")->trigger("delete", $id);
            $message = XOOPS::_("User is deleted.");
        }

        $urlOptions = array(
            'action'        => 'index',
            'controller'    => 'index',
            'module'        => 'user',
            'route'         => 'admin',
            'reset'         => true,
        );
        $options = array("time" => 3, "message" => $message);
        $this->redirect($urlOptions, $options);
    }

    protected function renderForm($form)
    {
        $this->setTemplate("user_edit.html");
        $form->assign($this->view);
        if ($form->id->getValue()) {
            $title = XOOPS::_("User edit");
        } else {
            $title = XOOPS::_("Add user");
        }
        $this->template->assign("title", $title);
    }

    public function getForm($id)
    {
        $module = $this->getRequest()->getModuleName();
        $configs = XOOPS::service("registry")->config->read($module, "account");
        if (!empty($id)) {
            $userRow = XOOPS::getModel("user")->findRow($id);
        } else {
            $userRow = XOOPS::getModel("user")->createRow();
        }
        $profileRow = $userRow->profile();
        if ($profileRow) {
            $profile = $profileRow->toArray();
        } else {
            $profile = array();
        }
        $profile = array_merge($profile, $userRow->toArray());

        $this->view->headLink(array(
            "href"  => "form.css",
            "rel"   => "stylesheet",
            "type"  => "text/css"
        ));
        $action = $this->view->url(array(
                "action"        => "save",
                "controller"    => "index",
                "module"        => $module
            ),
            "admin"
        );
        $options = array(
            "name"      => "xoopsProfile",
            "action"    => $action,
            "method"    => "post",
        );
        $form = new Xoops_Zend_Form($options);
        $form->addElement("hidden", "id", array("value" => $id));

        $options = array(
            "label"         => "Username",
            "required"      => true,
            "prefixPath"    => array(
                "validate"  => array(
                    "User_Validate"      => Xoops::service('module')->getPath($module) . "/Validate",
                ),
            ),
            "validators"    => array(
                "username"  => array(
                    "validator" => "Username",
                    "options"   => array(
                        "format"    => $configs["uname_format"],
                    ),
                ),
                "duplicate"  => array(
                    "validator" => "UserDuplicate",
                    "options"   => array(
                        "id"    => $id,
                    ),
                ),
            ),
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
        );
        $form->addElement("Text", "identity", $options);

        $options = array(
            "label"         => "Full name",
            "required"      => false,
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
        );
        $form->addElement("Text", "name", $options);

        $options = array(
            "label"         => "Email",
            "required"      => true,
            "prefixPath"    => array(
                "validate"  => array(
                    "prefix"    => "User_Validate",
                    "path"      => Xoops::service('module')->getPath($module) . "/Validate",
                ),
            ),
            "validators"    => array(
                "email"     => array(
                    "validator" => "EmailAddress",
                    "options"   => array(
                        "domain"    => false,
                    ),
                ),
                "duplicate"  => array(
                    "validator" => "EmailDuplicate",
                    "options"   => array(
                        "id"    => $id,
                    ),
                ),
            ),
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
                "lowercase" => array(
                    "filter"    => "StringToLower",
                ),
            ),
        );
        $form->addElement("Text", "email", $options);

        $options = array(
            "label"         => "Password",
            "required"      => empty($id) ? true : false,
            "validators"    => array(
                "strlen"    => array(
                    "validator" => "StringLength",
                    "options"   => array(
                        "min"   => $configs["password_min"],
                    ),
                ),
            ),
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
        );
        $form->addElement("Password", "credential", $options);

        $options = array(
            "label"         => "Confirm password",
            "required"      => empty($id) ? true : false,
            "validators"    => array(
                "match"     => array(
                    "validator" => "PasswordConfirmation",
                    "options"   => array(
                        "variable"  => "credential",
                    ),
                ),
            ),
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
        );
        $form->addElement("Password", "credential_confirm", $options);
        //$form->addDisplayGroup(array("credential", "credential_confirm"), "password", array("legend" => XOOPS::_("Password")));

        $options = array(
            "label"         => "Active",
            "value"         => 0
        );
        $form->addElement("Yesno", "active", $options);

        // Role selection
        $options = array(
            "label"         => "Role",
            "value"         => "member"
        );
        $form->addElement("Role", "role", $options);

        $form->addDisplayGroup(array("identity", "name", "email", "credential", "credential_confirm", "active", "role"), "account", array("legend" => XOOPS::_("Account")));

        $profileMeta = XOOPS::service("registry")->handler("meta", $module)->read("admin");
        foreach ($profileMeta as $keyCategory => $category) {
            foreach ($category["meta"] as $keyMeta => $meta) {
                $type = empty($meta["type"]) ? "text" : $meta["type"];
                $options = isset($meta["options"]) ? $meta["options"] : null;
                if (!empty($meta["module"])) {
                    $class = $meta["module"] . "_form_element_" . $type;
                    if (class_exists($class)) {
                        $element = new $class($keyMeta, $options);
                    } else {
                        $element = $form->createElement("text", $keyMeta);
                    }
                } else {
                    $element = $form->createElement($type, $keyMeta, $options);
                }
                $form->addElement($element);
            }
            if (!empty($category["meta"])) {
                $form->addDisplayGroup(array_keys($category["meta"]), $keyCategory, array("legend" => XOOPS::_($category["title"])));
            }
        }
        if (!empty($id)) {
            $form->setDefaults($profile);
        }
        $options = array(
            "label"     => "Confirm",
            "required"  => false,
            "ignore"    => true,
        );
        $form->addElement("submit", "save", $options);

        $form->setDescription("Edit profile");

        $form->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'xoops-form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));

        return $form;
    }
}
