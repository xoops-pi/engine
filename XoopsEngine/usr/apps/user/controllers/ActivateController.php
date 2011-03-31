<?php
/**
 * User module activate controller
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

class User_ActivateController extends Xoops_Zend_Controller_Action
{
    const   FORM_NAME = "xoopsActivate";

    public function indexAction()
    {
        //$this->setTemplate("activate.html");
        $module = $this->getRequest()->getModuleName();
        $key = $this->getRequest()->getParam("key");
        $model = $this->getModel("hash");
        $status = "success";

        if (empty($key)) {
            $errorMessage = XOOPS::_("Please type your email to create activation code.");
        } elseif ($hash = $model->findRow($key)) {
            $model->delete(array("hash = ?" => $key));
            // hash expired
            if ($hash->expire > 0 && $hash->expire < time()) {
                $errorMessage = XOOPS::_("The activation code has expired. Please type your email to regenerate activation code.");
            // valid hash
            } else {
                 $aclModel = XOOPS::getModel("acl_user");
                 $row = $aclModel->findRow($hash->user);
                 if ($row) {
                     if ($row->role == Xoops_Acl::INACTIVE) {
                         $aclModel->update(array("role" => Xoops_Acl::MEMBER), array("user = ?" => $row->user));
                     } else {
                        $status = "activated";
                     }
                 } else {
                     $aclModel->insert(array("user" => $hash->user, "role" => Xoops_Acl::MEMBER));
                 }
                 $userRow = XOOPS::getModel("user_account")->findRow($hash->user);
                 $userRow->active = 1;
                 if (!$userRow->save()) {
                    $errorMessage = XOOPS::_("User account was not activated.");
                 } else {
                    XOOPS::service("event")->trigger("activate", $userRow);
                 }
            }
        // hash not found
        } else {
            $errorMessage = XOOPS::_("The activation code was not found. Please type your email to regenerate activation code.");
        }

        if (!empty($errorMessage)) {
            $form = $this->getForm();
            $form->addError($errorMessage);
            $form->addDecorators(array(
                'Errors',
            ));
            return $this->renderForm($form);
        } elseif ($status == "activated") {
            return $this->_helper->redirector('index', 'login');
        }

        $urlOptions = $this->getFrontController()->getRouter()->assemble(
            array(),
            "login"
        );
        $message = XOOPS::_("Your account has been activated successfully. please login ...");
        $options = array("time" => 3, "message" => $message);
        $this->redirect($urlOptions, $options);
    }

    protected function renderForm($form)
    {
        $this->setTemplate("activate_form.html");
        $form->assign($this->view);
        $title = XOOPS::_("Send activation code");
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

        $posts = $form->getValues();
        $status = false;
        $userModel = XOOPS::getModel("user");
        $select = $userModel->select()->where("email = ?", $posts["email"]);
        $user = $userModel->fetchRow($select);
        if (!$user) {
            $errorMessage = XOOPS::_("The email is not found.");
            $form->addError($errorMessage);
            $form->addDecorators(array(
                'Errors',
            ));
            return $this->renderForm($form);
        }

        $configs = XOOPS::service("registry")->config->read($module, "register");
        $hashModel = $this->getModel("hash");
        $hash = substr(md5(uniqid(mt_rand(), 1)), 0, 8);
        $data = array(
            "user"        => $user->id,
            "hash"        => $hash,
            "expire"    => empty($configs["activate_expire"]) ? 0 : $configs["activate_expire"] * 24 * 3600 + time(),
        );
        $hashModel->insert($data);

        // Send email now ...
        $activation_url => $this->getFrontController()->getRouter()->assemble(
            array(
                "module"        => $module,
                "controller"    => "activate",
                "action"        => "index",
                "key"           => $hash,
            ),
            "default"
        );
        $mail = new Xoops_Mail();
        $mail->addTo($user->email, $user->name)
            ->setSubject(XOOPS::_("Account activation"))
            ->setTemplate("activate.txt", "module:user")
            ->assign(array(
                "username"  => $user->identity,
                "user_name" => $user->name,
                "activation_url"    => XOOPS::url($activation_url, true)
            ));
        $mail->send();
        $this->setTemplate("activate_send.html");
        $title = XOOPS::_("Send activation code");
        $this->template->assign("title", $title);
        $message = XOOPS::_("Activation code has been sent. Please check your email and come back to activate as soon as possible.");
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
                "captcha"           => "Image",
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

    // activate form
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
                "controller"    => "activate",
                "module"        => $module
            ),
            "default"
        );
        $options = array(
            "name"      => "xoopsActivate",
            "action"    => $action,
            "method"    => "post",
        );
        $form = new Xoops_Zend_Form($options);

        $options = array(
            "label"         => "Email",
            "required"      => true,
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
}
