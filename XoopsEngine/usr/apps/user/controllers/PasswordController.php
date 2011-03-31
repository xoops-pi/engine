<?php
/**
 * User password controller
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

class User_PasswordController extends Xoops_Zend_Controller_Action
{
    const   FORM_NAME = "xoopsPassword";

    /**
     * Reset password
     */
    public function indexAction()
    {
        $module = $this->getRequest()->getModuleName();
        $key = $this->getRequest()->getParam("key");

        // Create request form if no hash key available
        if (empty($key)) {
            $form = $this->getHashForm();
            return $this->renderHashForm($form);
        }

        $model = $this->getModel("hash");
        $status = "success";
        if ($rowHash = $model->findRow($key)) {
            $model->delete(array("hash = ?" => $key));
            // re-create a new key if hash expired
            if ($rowHash->expire > 0 && $rowHash->expire < time()) {
                $errorMessage = XOOPS::_("The request has expired. Please type your email to create new password.");
            // create password form if valid hash verified
            } else {
            }
        // hash not found
        } else {
            $errorMessage = XOOPS::_("The request was not found. Please type your email to create new password.");
        }


        $hash = substr(md5(uniqid(mt_rand(), 1)), 0, 8);
        $userModel = XOOPS::getModel("user_account");
        $userRow = $userModel->findRow($rowHash->user);
        if (!$userRow) {
            $this->_helper->redirector('index', 'register');
        }
        $userRow->credential = $hash;
        if (!$userRow->save()) {
            $errorMessage = XOOPS::_("The new credential was not created. Please type your email to create new password.");
        }
        //$userModel->update(array("credential" => md5($hash)), array("identity = ?" => $user->identity));
        //$userModel->update(array("credential" => $hash), array("identity = ?" => $rowHash->identity));

        if (!empty($errorMessage)) {
            $form = $this->getHashForm();
            $form->addError($errorMessage);
            $form->addDecorators(array(
                'Errors',
            ));
            return $this->renderHashForm($form);
        }

        // Send email now ...
        $login_url = $this->getFrontController()->getRouter()->assemble(
            array(),
            "login"
        );
        $mail = new Xoops_Mail();
        $mail->addTo($userRow->email, $userRow->name)
            ->setSubject(XOOPS::_("Account activation"))
            ->setTemplate("password_new.txt", "module:user")
            ->assign(array(
                "username"      => $user->identity,
                "password"      => $hash,
                "login_url"     => XOOPS::url($login_url, true),
                "user_ip"       => $this->getRequest()->getClientIp(),
            ));
        $status = $mail->send();
        if ($status) {
            $message = XOOPS::_("A new password has been sent to you. Please check your email and come back to login.");
        } else {
            $message = XOOPS::_("The email for sending password was not able to send. Please contact webmaster.");
        }

        $this->setTemplate("password.html");
        $title = XOOPS::_("New password");
        $this->template->assign("title", $title);
        $this->template->assign("message", $message);

        /*
        $session = Xoops::service("session")->password;
        //$session->valid = 1;
        $session->user = $user->id;

        $form = $this->getPasswordForm();
        return $this->renderPasswordForm($form);
        */
    }

    protected function renderHashForm($form)
    {
        $this->setTemplate("password_hash.html");
        $form->assign($this->view);
        $title = XOOPS::_("Forget password");
        $this->template->assign("title", $title);
    }

    /**
     * Request password reset
     */
    public function processAction()
    {
        $module = $this->getRequest()->getModuleName();
        if (!$this->getRequest()->isPost()) {
            return $this->_helper->redirector('index');
        }

        $posts = $this->getRequest()->getPost();
        if (empty($posts["identity"]) && empty($posts["email"])) {
            return $this->_helper->redirector('index');
        }
        $form = $this->getHashForm();
        if (!$form->isValid($posts)) {
            return $this->renderHashForm($form);
        }

        $posts = $form->getValues();
        $status = false;
        $userModel = XOOPS::getModel("user_account");
        $select = $userModel->select();
        if (!empty($posts["identity"])) {
            $select->where("identity = ?", $posts["identity"]);
        } elseif (!empty($posts["email"])) {
            $select->where("email = ?", $posts["email"]);
        }
        $user = $userModel->fetchRow($select);
        if (!$user) {
            $errorMessage = XOOPS::_("The account is not found.");
            $form->addError($errorMessage);
            $form->addDecorators(array(
                'Errors',
            ));
            return $this->renderHashForm($form);
        }

        $configs = XOOPS::service("registry")->config->read($module, "register");
        $hashModel = $this->getModel("hash");
        $hash = substr(md5(uniqid(mt_rand(), 1)), 0, 8);
        $data = array(
            "user"      => $user->id,
            "hash"      => $hash,
            "expire"    => empty($configs["activate_expire"]) ? 0 : $configs["activate_expire"] * 24 * 3600 + time(),
        );
        $hashModel->insert($data);

        // Send email now ...
        $password_url = $this->getFrontController()->getRouter()->assemble(
            array(
                "module"        => $module,
                "controller"    => "password",
                "action"        => "index",
                "key"           => $hash,
            ),
            "default"
        );
        $mail = new Xoops_Mail();
        $mail->addTo($user->email, $user->name)
            ->setSubject(XOOPS::_("Password reset"))
            ->setTemplate("password_reset.txt", "module:user")
            ->assign(array(
                "username"      => $user->identity,
                "password_url"  => XOOPS::url($password_url, true),
                "user_ip"       => $this->getRequest()->getClientIp(),
            ));
        $status = $mail->send();
        if ($status) {
            $message = XOOPS::_("A link for resetting password has been sent. Please check your email and come back to create new password as soon as possible.");
        } else {
            $message = sprintf(
                XOOPS::_("The email for resetting password was not able to send. Please contact webmaster or <a href='%s' title='Reset password'>press to continue</a>."),
                $password_url
            );
        }

        $this->setTemplate("password.html");
        $title = XOOPS::_("Reset password");
        $this->template->assign("title", $title);
        $this->template->assign("message", $message);
    }


    protected function ____renderPasswordForm($form)
    {
        $this->setTemplate("password.html");
        $form->assign($this->view);
        $title = XOOPS::_("Create new password");
        $this->template->assign("title", $title);
    }

    public function ____createAction()
    {
        $module = $this->getRequest()->getModuleName();
        $session = Xoops::service("session")->password;
        if (empty($session->user)) {
            return $this->_helper->redirector('index');
        }
        if (!$this->getRequest()->isPost()) {
            return $this->_helper->redirector('index');
        }

        $posts = $this->getRequest()->getPost();
        $form = $this->getPassordForm();
        if (!$form->isValid($posts)) {
            return $this->renderPasswordForm($form);
        }

        $posts = $form->getValues();
        $status = false;
        $userModel = XOOPS::getModel("user_account");
        $select = $userModel->select()->where("identity = ?", $session->user);
        $user = $userModel->fetchRow($select);
        if (!$user) {
            $session->unsetAll();
            return $this->_helper->redirector('index');
        }

        $status = $userModel->update(array("password" => $posts["password"]), array("identity = ?" => $session->user));
        if (!$status) {
            $errorMessage = XOOPS::_("The new password was not saved. Please try again");
            $form->addError($errorMessage);
            $form->addDecorators(array(
                'Errors',
            ));
            return $this->renderPasswordForm($form);
        }

        $session->unsetAll();
        $this->setTemplate("password_created.html");
        $title = XOOPS::_("Password creation");
        $this->template->assign("title", $title);
        $message = XOOPS::_("Your password has been created successfully. Please login");
        $this->template->assign("message", $message);
    }

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

    protected function createCaptcha($form = null)
    {
        $module = $this->getRequest()->getModuleName();
        $captchaName = "captcha";
        $options = array(
            "label"     => "Please type following characters",
            "captcha"   => array(
                "captcha"   => "Image",
            ),
            "description"   => "Click the above image to refresh",
        );
        if ($form instanceof Xoops_Zend_Form) {
        } else {
            $form = new Xoops_Zend_Form();
        }
        $captcha = $form->createElement("Captcha", $captchaName, $options);

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

        return $captcha;
    }

    // hash form
    public function getHashForm()
    {
        $module = $this->getRequest()->getModuleName();
        $this->view->headLink(array(
            "href"  => "form.css",
            "rel"   => "stylesheet",
            "type"  => "text/css"
        ));
        $action = $this->view->url(array(
                "action"        => "process",
                "controller"    => "password",
                "module"        => $module
            ),
            "default"
        );
        $options = array(
            "name"      => "xoopsHash",
            "action"    => $action,
            "method"    => "post",
        );
        $form = new Xoops_Zend_Form($options);

        $options = array(
            "label"         => "Username",
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
        );
        $form->addElement("Text", "identity", $options);

        $options = array(
            "label"         => "Or email",
            "validators"    => array(
                "email"     => array(
                    "validator" => "EmailAddress",
                    "options"   => array(
                        "domain"    => false,
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

        $captcha = $this->createCaptcha($form);
        $form->addElement($captcha);

        $options = array(
            "label"     => "Send",
            "required"  => false,
            "ignore"    => true,
        );
        $form->addElement("submit", "hash", $options);

        $form->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'xoops-form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));

        return $form;
    }

    // password form
    public function ____getPasswordForm()
    {
        $module = $this->getRequest()->getModuleName();
        $this->view->headLink(array(
            "href"  => "form.css",
            "rel"   => "stylesheet",
            "type"  => "text/css"
        ));
        $action = $this->view->url(array(
                "action"        => "create",
                "controller"    => "password",
                "module"        => $module
            ),
            "default"
        );
        $options = array(
            "name"      => "xoopsPassword",
            "action"    => $action,
            "method"    => "post",
        );
        $form = new Xoops_Zend_Form($options);

        $configs = XOOPS::service("registry")->config->read($module, "account");

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
        $form->addElement("password", "password", $options);

        $options = array(
            "label"     => "Confirm password",
            "required"  => true,
            "validators"    => array(
                "match"    => array(
                    "validator" => "PasswordConfirmation",
                    "options"   => array(
                        "variable"  => "password",
                    ),
                ),
            ),
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
        );
        $form->addElement("password", "password_confirm", $options);

        $captcha = $this->createCaptcha($form);
        $form->addElement($captcha);

        $options = array(
            "label"     => "Create",
            "required"  => false,
            "ignore"    => true,
        );
        $form->addElement("submit", "create", $options);

        $form->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'xoops-form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));

        return $form;
    }
}
