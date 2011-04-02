<?php
/**
 * User profile controller
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

/**
 * User's own page
 */
class User_EditController extends Xoops_Zend_Controller_Action
{
    public function  preDispatch()
    {
        if (!XOOPS::registry("user")->id) {
            $this->_helper->redirector('index', 'login');
        }
    }

    public function indexAction()
    {
        $module = $this->getRequest()->getModuleName();
        $form = $this->getForm(XOOPS::registry("user")->id);
        $this->renderForm($form);
        $this->template->assign("account", $account);
    }

    public function saveAction()
    {
        $module = $this->getRequest()->getModuleName();
        if (!$this->getRequest()->isPost()) {
            return $this->_helper->redirector('index');
        }

        $posts = $this->getRequest()->getPost();
        $form = $this->getForm($posts["id"]);
        if (!$form->isValid($posts)) {
            return $this->renderForm($form);
        }

        $values = $form->getValues();
        $status = \App\User\Gateway::update($values, $message);
        if (!$status) {
            $message[] = XOOPS::_("Profile was not able to save. Please try again or contact webmaster.");
            $form->addErrors($message);
            $form->addDecorators(array(
                'Errors',
            ));
            return $this->renderForm($form);
        }
        XOOPS::service("event")->trigger("update", $values);
        return $this->_helper->redirector('index');
    }

    public function emailAction()
    {
        $module = $this->getRequest()->getModuleName();
        $this->setTemplate("profile_edit.html");
        $id = XOOPS::registry("user")->id;
        $account = array(
            "id"        => $id,
            "identity"  => XOOPS::registry("user")->identity,
            "name"      => XOOPS::registry("user")->name,
        );
        $form = $this->getFormEmail($id);

        if ($this->getRequest()->isPost()) {
            $posts = $this->getRequest()->getPost();
            if ($form->isValid($posts)) {
                $values = $form->getValues();
                $status = \App\User\Gateway::update($values, $message);
                if (!$status) {
                    $message[] = XOOPS::_("Email was not able to save. Please try again or contact webmaster.");
                    $form->addErrors($message);
                    $form->addDecorators(array(
                        'Errors',
                    ));
                } else {
                    return $this->_helper->redirector('index', 'profile');
                }
            }
        }

        $form->assign($this->view);
        $title = XOOPS::_("Change email");
        $this->template->assign("title", $title);
        $this->template->assign("account", $account);
    }

    public function passwordAction()
    {
        $module = $this->getRequest()->getModuleName();
        $this->setTemplate("profile_edit.html");
        $id = XOOPS::registry("user")->id;
        $account = array(
            "id"        => $id,
            "identity"  => XOOPS::registry("user")->identity,
            "name"      => XOOPS::registry("user")->name,
        );
        $form = $this->getFormPassword($id);

        if ($this->getRequest()->isPost()) {
            $posts = $this->getRequest()->getPost();
            if ($form->isValid($posts)) {
                $values = $form->getValues();
                $status = \App\User\Gateway::update($values, $message);
                if (!$status) {
                    $message[] = XOOPS::_("Password was not able to save. Please try again or contact webmaster.");
                    $form->addErrors($message);
                    $form->addDecorators(array(
                        'Errors',
                    ));
                } else {
                    return $this->_helper->redirector('index', 'profile');
                }
            }
        }

        $form->assign($this->view);
        $title = XOOPS::_("Change password");
        $this->template->assign("title", $title);
        $this->template->assign("account", $account);
    }

    protected function renderForm($form)
    {
        $account = array(
            "id"        => XOOPS::registry("user")->id,
            "identity"  => XOOPS::registry("user")->identity,
            "name"      => XOOPS::registry("user")->name,
        );
        $this->setTemplate("profile_edit.html");
        $form->assign($this->view);
        $title = XOOPS::_("Profile edit form");
        $this->template->assign("title", $title);
        $this->template->assign("account", $account);
    }

    // Profile form
    public function getForm($id)
    {
        $userRow = XOOPS::getModel("user")->findRow($id);
        $profileRow = $userRow->profile();
        if ($profileRow) {
            $profile = $profileRow->toArray();
        } else {
            $profile = array();
        }
        $profile["id"] = $id;
        $profile["name"] = $userRow->name;

        $module = $this->getRequest()->getModuleName();
        $this->view->headLink(array(
            "href"  => "form.css",
            "rel"   => "stylesheet",
            "type"  => "text/css"
        ));
        $action = $this->view->url(array(
                "action"        => "save",
                "controller"    => "edit",
                "module"        => $module
            ),
            "default"
        );
        $options = array(
            "name"      => "xoopsProfile",
            "action"    => $action,
            "method"    => "post",
        );
        $form = new Xoops_Zend_Form($options);

        $options = array(
            "label"         => "Full name",
            "required"      => false,
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
            "Description"   => "Username will be used for full name if not set"
        );
        $form->addElement("text", "name", $options);
        $form->addDisplayGroup(array("name"), "account", array("legend" => XOOPS::_("Account")));
        $form->addElement("hidden", "id", array("value" => $id));

        $profileMeta = XOOPS::service("registry")->handler("meta", $module)->read("edit");
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
        $form->setDefaults($profile);
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

    public function getFormEmail($id)
    {
        $module = $this->getRequest()->getModuleName();
        $configs = XOOPS::service("registry")->config->read($module, "account");
        $userRow = XOOPS::getModel("user")->findRow($id);

        $this->view->headLink(array(
            "href"  => "form.css",
            "rel"   => "stylesheet",
            "type"  => "text/css"
        ));
        $action = $this->view->url(array(
                "action"        => "email",
                "controller"    => "edit",
                "module"        => $module
            ),
            "default"
        );
        $options = array(
            "name"      => "xoopsProfile",
            "action"    => $action,
            "method"    => "post",
        );
        $form = new Xoops_Zend_Form($options);

        $options = array(
            "label"         => "Password",
            "required"      => true,
            "prefixPath"    => array(
                "validate"  => array(
                    "prefix"    => "User_Validate",
                    "path"      => Xoops::service('module')->getPath($module) . "/Validate",
                ),
            ),
            "validators"    => array(
                "authenticate"  => array(
                    "validator" => "Authenticate",
                    "options"   => array(
                        "identity"  => $userRow->identity,
                    ),
                ),
            ),
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
        );
        $form->addElement("password", "credential", $options);
        $form->addElement("hidden", "id", array("value" => $id));

        //Debug::e($options);
        $options = array(
            "label"         => "New email",
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
                "backlist"  => array(
                    "validator" => "Backlist",
                    "options"   => array(
                        "list"    => $configs["email_backlist"],
                    ),
                ),
                "duplicate"  => array(
                    "validator" => "EmailDuplicate",
                    "options"   => $id,
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

        $form->setDefaults(array("email" => $userRow->email));

        $options = array(
            "label"     => "Confirm",
            "required"  => false,
            "ignore"    => true,
        );
        $form->addElement("submit", "save", $options);

        $form->setDescription("Change email");

        $form->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'xoops-form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));

        return $form;
    }

    public function getFormPassword($id)
    {
        $module = $this->getRequest()->getModuleName();
        $configs = XOOPS::service("registry")->config->read($module, "account");
        $userRow = XOOPS::getModel("user")->findRow($id);

        $this->view->headLink(array(
            "href"  => "form.css",
            "rel"   => "stylesheet",
            "type"  => "text/css"
        ));
        $action = $this->view->url(array(
                "action"        => "password",
                "controller"    => "edit",
                "module"        => $module
            ),
            "default"
        );
        $options = array(
            "name"      => "xoopsProfile",
            "action"    => $action,
            "method"    => "post",
        );
        $form = new Xoops_Zend_Form($options);

        $options = array(
            "label"         => "Old password",
            "required"      => true,
            "prefixPath"    => array(
                "validate"  => array(
                    "prefix"    => "User_Validate",
                    "path"      => Xoops::service('module')->getPath($module) . "/Validate",
                ),
            ),
            "validators"    => array(
                "authenticate"  => array(
                    "validator" => "Authenticate",
                    "options"   => array(
                        "identity"  => $userRow->identity,
                    ),
                ),
            ),
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
        );
        $form->addElement("password", "credential_old", $options);
        $form->addElement("hidden", "id", array("value" => $id));

        $options = array(
            "label"     => "New password",
            "required"  => true,
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
            "label"     => "Confirm password",
            "required"  => true,
            "validators"    => array(
                "match"    => array(
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

        $options = array(
            "label"     => "Confirm",
            "required"  => false,
            "ignore"    => true,
        );
        $form->addElement("submit", "save", $options);

        $form->setDescription("Change password");

        $form->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'xoops-form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));

        return $form;
    }

}
