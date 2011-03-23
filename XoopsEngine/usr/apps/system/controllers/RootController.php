<?php
/**
 * System admin root controller
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

class System_RootController extends Xoops_Zend_Controller_Action
{
    const   FORM_NAME = "xoopsRoot";

    public function loginAction()
    {
        $this->forward("index");
    }

    public function indexAction()
    {
        $form = $this->getForm();
        $this->renderForm($form);
        if (Xoops::service("auth")->hasIdentity()) {
            $identity = Xoops::service("auth")->getIdentity();
            if ($identity["role"] == Xoops_Acl::ADMIN && $identity["id"] == 0) {
                $this->template->assign("root", $identity["identity"]);
            }
        }
    }

    protected function renderForm($form)
    {
        $this->setTemplate("root_login.html");
        $module = $this->getRequest()->getModuleName();
        $configs = XOOPS::service("registry")->config->read("", "root");

        if (!empty($configs["ips"])) {
            $status = $this->validateIp($configs["ips"]);
        }

        $message = "";
        if (!empty($configs["attempts"])) {
            $sessionLogin = Xoops::service("session")->login;
            if (!empty($sessionLogin->attempts) && $sessionLogin->attempts >= $configs["attempts"]) {
                $waiting = Xoops::service("session")->getSaveHandler()->getLifeTime() / 60;
                $message = sprintf(XOOPS::_("Login with the account is suspended, please wait for %d minutes to try again."), $waiting);
                $this->setTemplate("root_suspended.html");
            } elseif (!empty($sessionLogin->attempts)) {
                $remaining = $configs["attempts"] - $sessionLogin->attempts;
                $message = sprintf(XOOPS::_("You have %d times to try."), $remaining);
            }
        }
        $form->assign($this->view);
        $title = XOOPS::_("Root user");
        $this->template->assign("title", $title);
        $this->template->assign("message", $message);

        //Debug::e('session:');
        //Debug::e($_SESSION);
        //Debug::e(Xoops::persist()->load('captcha'));
        //Debug::e(Xoops::persist()->load('captcha-session'));
        //Debug::e(Xoops::persist()->load('captcha-session-generate'));
        //Debug::e(Xoops::persist()->load('captcha-session-image'));
    }

    public function logoutAction()
    {
        //$this->setTemplate("root_logout.html");
        Xoops::service("auth")->destroy();

        $urlOptions = XOOPS::url("www");
        $message = XOOPS::_("You have logged out successfully.");
        $options = array("time" => 3, "message" => $message);
        $this->redirect($urlOptions, $options);
    }

    public function processAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->_helper->redirector('index');
        }

        $posts = $this->getRequest()->getPost();
        $form = $this->getForm();
        if (!$form->isValid($posts)) {
            return $this->renderForm($form);
        }

        $module = $this->getRequest()->getModuleName();
        $configs = XOOPS::service("registry")->config->read("", "root");

        if (!empty($configs["ips"])) {
            $status = $this->validateIp($configs["ips"]);
        }

        $values = $form->getValues();
        $identity = $values["identity"];
        $credential = $values["credential"];

        if (!empty($configs["attempts"])) {
            $sessionLogin = Xoops::service("session")->login;
            if (!empty($sessionLogin->attempts) && $sessionLogin->attempts >= $configs["attempts"]) {
                return $this->_helper->redirector('index');
            }
        }

        //$this->setTemplate("root_authenticate.html");
        Xoops::service("auth")->loadAdapter("root");
        $result = Xoops::service("auth")->process($identity, $credential);

        if (!$result->isValid()) {
            if (!empty($configs["attempts"])) {
                $sessionLogin = Xoops::service("session")->login;
                $sessionLogin->attempts = isset($sessionLogin->attempts) ? ($sessionLogin->attempts + 1) : 1;
            }
            $message = "Invalid credentials provided, please try again.";
            $form->addError($message);
            $form->addDecorators(array(
                'Errors',
                //'FormErrors'
            ));
            return $this->renderForm($form);
        }

        Xoops::service("auth")->wakeup($result->getIdentity());
        /*
        if (!empty($configs["log_onsuccess"])) {
            $GLOBALS['xoopsUser']->setVar('last_login', time());
            if (!$member_handler->insertUser($GLOBALS['xoopsUser'])) {
            }
        }
        */

        XOOPS::service("event")->trigger("login", $result->getIdentity());
        $message = XOOPS::_("You have logged in successfully. Wait to be redirecting ...");
        if (!empty($redirect)) {
            $urlOptions = urldecode($redirect);
        } else {
            $urlOptions = array("route" => "admin", "module" => "system", "controller" => "index", "action" => "index");
        }

        if (!empty($configs["attempts"])) {
            $sessionLogin = Xoops::service("session")->login;
            $sessionLogin->unsetAll();
        }

        $options = array("time" => 3, "message" => $message);
        $this->redirect($urlOptions, $options);
    }

    protected function createCaptcha($form = null)
    {
        $module = $this->getRequest()->getModuleName();
        $captchaName = "captcha";
        $options = array(
            "label"     => "Please type following characters",
            //"captcha"   => array(
                "captcha"   => "Image",
                // Indicates CAPTCHA to use admin session to store token, applicable only if Image CAPTCHA with remote image generator script is used
                "captchaOptions"    => array(
                    "section"   => "admin",
                ),
            //),
            "description"   => "Click the above image to refresh",
        );
        if ($form instanceof Xoops_Zend_Form) {
        } else {
            $form = new Xoops_Zend_Form();
        }
        $captcha = $form->createElement("Captcha", $captchaName, $options);

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
            ),
            "root"
        );
        $options = array(
            "name"      => self::FORM_NAME,
            "action"    => $action,
            "method"    => "post",
        );
        $form = new Xoops_Zend_Form($options);

        $configs = XOOPS::service("registry")->config->read("", "root");
        $options = array(
            "label"         => "Username",
            "required"      => true,
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
        );
        $form->addElement("Text", "identity", $options);

        $options = array(
            "label"     => "Password",
            "required"  => true,
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
        );
        $form->addElement("password", "credential", $options);

        if (extension_loaded("gd") && !empty($configs["captcha"])) {
            $captcha = $this->createCaptcha($form);
            $form->addElement($captcha);
        }

        $options = array(
            "label"     => "Login",
            "required"  => false,
            "ignore"    => true,
        );
        $form->addElement("submit", "login", $options);

        $form->setDescription("Root user login");

        // We want to display a 'failed authentication' message if necessary;
        // we'll do that with the form 'description', so we need to add that
        // decorator.
        $form->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'xoops-form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));

        return $form;
    }

    protected function validateIp($ips)
    {
        $userIP = $this->getRequest()->getClientIp();
        $ipsAllowed = array();
        $ipsBlocked = array();
        foreach (array_filter($ips) as $ip) {
            if ($ip{0} == "-") {
                $ipsBlocked[] = substr($ip, 1);
            } else {
                $ipsAllowed[] = $ip;
            }
        }
        $status = null;
        foreach ($ipsBlocked as $ip) {
            if ($this->checkIp($userIP, $ip)) {
                $status = false;
                break;
            }
        }
        if ($status === null) {
            $status = empty($ipsAllowed) ? true : false;
            foreach ($ipsAllowed as $ip) {
                if ($this->checkIp($userIP, $ip)) {
                    $status = true;
                    break;
                }
            }
        }

        if (!$status) {
            $urlOptions = XOOPS::url("www");
            $message = XOOPS::_("You are not allowed to access this area.");
            $options = array("time" => 3, "message" => $message);
            $this->redirect($urlOptions, $options);
            return;
        }
    }

    protected function checkIp($userIP, $ip)
    {
        $userIpSegs = explode(".", $userIP);
        $ipSegs = explode(".", $ip);
        $count = count($ipSegs);
        $status = false;
        for ($i = 0; $i < $count; $i++) {
            if ($ipSegs[$i] != "*" && $ipSegs[$i] != $userIpSegs[$i]) {
                break;
            }
            if ($ipSegs[$i] == "*") {
                $status = true;
                break;
            }
        }

        return $status;
    }
}
