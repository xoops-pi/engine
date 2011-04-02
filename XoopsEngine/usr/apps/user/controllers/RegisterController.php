<?php
/**
 * User register
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

class User_RegisterController extends Xoops_Zend_Controller_Action
{
    const   FORM_NAME = "xoopsRegister";

    public function  preDispatch()
    {
        $module = $this->getRequest()->getModuleName();
        $configs = XOOPS::service("registry")->config->read($module, "register");
        if (!$configs["enable_register"]) {
            $this->_helper->redirector('index', 'index');
        }

        if (Xoops::service("auth")->hasIdentity()) {
            // Register should be disabled for logged-in users;
            //$this->_helper->redirector('index', 'index');
        }
    }

    public function indexAction()
    {
        $form = $this->getForm();
        $this->renderForm($form);
    }

    protected function renderForm($form)
    {
        $this->setTemplate("register.html");
        $form->assign($this->view);
        $title = XOOPS::_("Register form");
        $this->template->assign("title", $title);
    }

    public function processAction()
    {
        $module = $this->getRequest()->getModuleName();
        if (!$this->getRequest()->isPost()) {
            return $this->_helper->redirector('index');
        }

        $posts = $this->getRequest()->getPost();
        $form = $this->getForm();
        if (!$form->isValid($posts)) {
            return $this->renderForm($form);
        }

        $this->setTemplate("registered.html");
        $values = $form->getValues();
        $redirect = isset($values["redirect"]) ? $values["redirect"] : "";
        $status = false;
        /*
        if ($uid = $this->createLegacyUser($values)) {
            $values["id"] = $uid;
            $status = $this->createUser($values);
        }
        */
        $id = $this->createUser($values);

        if (!$id) {
            $message = XOOPS::_("The account was not able to save. Please try again or contact webmaster.");
            $form->addError($message);
            $form->addDecorators(array(
                'Errors',
            ));
            return $this->renderForm($form);
        }
        $values["id"] = $id;
        XOOPS::service("event")->trigger("register", $values);

        $message = XOOPS::_("The account is created successfully. Please login");
        if ($redirect) {
            $urlOptions = urldecode($redirect);
        } else {
            $urlOptions = XOOPS::url("www");
        }
        $options = array("time" => 3, "message" => $message);
        $this->redirect($urlOptions, $options);
    }

    /*
    public function imageAction()
    {
        $captcha = $this->createCaptcha()->getCaptcha();
        $captcha->generate();
        $result = array(
            "image"     => $captcha->getImgUrl() . $captcha->getId() . $captcha->getSuffix(),
            "id"        => $captcha->getId(),
        );
        if ($this->getRequest()->isXmlHttpRequest()) {
            echo json_encode($result);
        }
        return;
    }
    */

    protected function createCaptcha($form = null)
    {
        $module = $this->getRequest()->getModuleName();
        $captchaName = "captcha";
        $options = array(
            "label"     => "Please type following characters",
            //"captcha"   => array(
                "captcha"           => "Image",
            //),
            "description"   => "Click the above image to refresh",
        );
        if ($form instanceof Xoops_Zend_Form) {
        } else {
            $form = new Xoops_Zend_Form();
        }
        $captcha = $form->createElement("Captcha", $captchaName, $options);

        /*
        $callback = $this->getFrontController()->getRouter()->assemble(
            array(
                "module"        => $module,
                "controller"    => "register",
                "action"        => "image",
            ),
            "default"
        );
        $captchaData  = array(
            "elementId" => $captcha->getId(),
            "callback"  => $callback
        );
        // For Image CAPTCHA
        $this->template->assign("captcha", $captchaData);
        */

        return $captcha;
    }

    // login form
    public function getForm()
    {
        $module = $this->getRequest()->getModuleName();
        $this->view->headLink(array(
            "href"  => "form.css",
            "rel"   => "stylesheet",
            "type"  => "text/css"
        ));
        $action = $this->view->url(array(
                "action"        => "process",
                "controller"    => "register",
                "module"        => $module
            ),
            "default"
        );
        $options = array(
            "name"      => self::FORM_NAME,
            "action"    => $action,
            "method"    => "post",
        );
        $form = new Xoops_Zend_Form($options);

        $configs = XOOPS::service("registry")->config->read($module, "register");
        $configs = array_merge($configs, XOOPS::service("registry")->config->read($module, "account"));

        $options = array(
            "label"         => "Username",
            "required"      => true,
            "prefixPath"    => array(
                "validate"  => array(
                    //"App_User_Validate"      => Xoops::service('module')->getPath($module) . "/Validate",
                    "User_Validate"      => Xoops::service('module')->getPath($module) . "/Validate",
                ),
            ),
            "validators"    => array(
                "strlen"    => array(
                    "validator" => "StringLength",
                    "options"   => array(
                        "min"   => $configs["uname_min"],
                        "max"   => $configs["uname_max"],
                    ),
                ),
                "username"  => array(
                    "validator" => "Username",
                    "options"   => array(
                        "format"    => $configs["uname_format"],
                    ),
                ),
                "backlist"  => array(
                    "validator" => "Backlist",
                    "options"   => array(
                        "list"    => $configs["uname_backlist"],
                    ),
                ),
                "duplicate"  => array(
                    "validator" => "UserDuplicate",
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
            "Description"   => "Username will be used for full name if not set"
        );
        $form->addElement("Text", "name", $options);

        $options = array(
            "label"         => "Email",
            "required"      => true,
            "prefixPath"    => array(
                "validate"  => array(
                    //"prefix"    => "App_User_Validate",
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
            "label"     => "Password",
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
        $form->addDisplayGroup(array("credential", "credential_confirm"), "password", array("legend" => XOOPS::_("Password")));

        if (extension_loaded("gd") && !empty($configs["captcha"])) {
            $captcha = $this->createCaptcha($form);
            $form->addElement($captcha);
        }

        if (!empty($configs["disclaim_enable"])) {
            $options = array(
                "label"         => "Disclaim",
                "value"         => 1,
                "required"      => true,
                "multioptions"  => array(
                    "1"     => "I have read and accept the disclaim.",
                ),
            );
            $form->addElement("MultiCheckbox", "disclaim", $options);
        }

        $redirect = $this->getRequest()->getParam("redirect", "");
        if (!empty($redirect)) {
            $form->addElement("hidden", "redirect", array("value" => $redirect));
        }

        $options = array(
            "label"     => "Register",
            "required"  => false,
            "ignore"    => true,
        );
        $form->addElement("submit", "register", $options);

        $form->setDescription("Register an account");

        $form->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'xoops-form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));

        return $form;
    }

    protected function ____createLegacyUser($data)
    {
        $module = $this->getRequest()->getModuleName();
        $configs = XOOPS::service("registry")->config->read($module, "register");

        // create member account
        $member_handler = XOOPS::getHandler('member');
        $account = $member_handler->createUser();
        $account->setVar('user_viewemail', 0, true);
        $account->setVar('uname', $data["username"], true);
        $account->setVar('email', $data["email"], true);
        $account->setVar('url', Xoops::url("www", true), true);
        $account->setVar('user_avatar', 'blank.gif', true);
        $account->setVar('pass', md5($data['password']), true);
        $account->setVar('user_regdate', time(), true);
        $account->setVar('user_mailok', 1, true);

        $actkey = substr(md5(uniqid(mt_rand(), 1)), 0, 8);
        $account->setVar('actkey', $actkey, true);

        if ($configs["activate_type"] == "auto") {
            $level = 1;
        } else {
            $level = 0;
        }
        $account->setVar('level', $level, true);
        if (!$member_handler->insertUser($account)) {
            return false;
        }
        $uid = $account->getVar('uid');
        $member_handler->addUserToGroup(XOOPS_GROUP_USERS, $uid);

        return $uid;
    }

    protected function createUser($data)
    {
        //global $xoops;
        $module = $this->getRequest()->getModuleName();
        $configs = XOOPS::service("registry")->config->read($module, "register");
        if ($configs["activate_type"] == "auto") {
            $data["active"] = 1;
        } else {
            $data["active"] = 0;
        }
        $data["create_time"] = time();
        $data["create_ip"] = $this->getRequest()->getClientIp();

        $id = \App\User\Gateway::create($data, $message);
        $status = true;

        // Requires activation by user
        if ($configs["activate_type"] == "user") {
            $role = Xoops_Acl::INACTIVE;
            $hashModel = $this->getModel("hash");
            $hash = substr(md5(uniqid(mt_rand(), 1)), 0, 8);
            $user = array(
                "user"      => $id,
                "hash"      => $hash,
                "expire"    => empty($configs["activate_expire"]) ? 0 : $configs["activate_expire"] * 24 * 3600 + time(),
            );
            $status = $hashModel->insert($user);

            $activation_url = $this->getFrontController()->getRouter()->assemble(
                array(
                    "module"        => $module,
                    "controller"    => "activate",
                    "action"        => "index",
                    "key"           => $hash,
                ),
                "default"
            );
            $mail = new Xoops_Mail();
            $mail->addTo($data["email"], $data["name"])
                ->setSubject(XOOPS::_("Account activation"))
                ->setTemplate("activate.txt", "module:user")
                ->assign(array(
                    "user_name" => empty($data["name"]) ? $data["identity"] : $data["name"],
                    "username"  => $data["identity"],
                    "activation_url"    => XOOPS::url($activation_url, true)
                ));
            $mail->send();
        // Automatic activation
        } elseif ($configs["activate_type"] == "auto") {
            $role = Xoops_Acl::MEMBER;

            // Sends welcome message
            if ($configs["welcome"]) {
                $login_url = $this->getFrontController()->getRouter()->assemble(
                    array(),
                    "login"
                );
                $mail = new Xoops_Mail();
                $mail->addTo($data["email"], $data["name"])
                    ->setSubject(XOOPS::_("Account registration"))
                    ->setTemplate("welcome.txt", "module:user")
                    ->assign(array(
                        "user_name" => empty($data["name"]) ? $data["identity"] : $data["name"],
                        "username"  => $data["identity"],
                        "login_url"    => XOOPS::url($login_url, true)
                    ));
                $mail->send();
            }
        // Requires activation by admin
        } else {
            $role = Xoops_Acl::INACTIVE;

            $activation_url = $this->getFrontController()->getRouter()->assemble(
                array(
                    "module"        => $module,
                    "controller"    => "activate",
                    "action"        => "index",
                ),
                "admin"
            );
            $mail = new Xoops_Mail();
            $mail->addTo(XOOPS::config("adminmail"))
                ->setSubject(XOOPS::_("Account activation"))
                ->setTemplate("activate_admin.txt", "module:user")
                ->assign(array(
                    "user_name" => empty($data["name"]) ? $data["identity"] : $data["name"],
                    "username"  => $data["identity"],
                    "activation_url"    => XOOPS::url($activation_url, true)
                ));
            $mail->send();
        }

        $status = $status && XOOPS::getModel("acl_user")->insert(array("user" => $id, "role" => $role));
        return $status ? $id : false;
    }
}
