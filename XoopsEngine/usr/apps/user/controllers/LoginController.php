<?php
/**
 * User login controller
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

class User_LoginController extends Xoops_Zend_Controller_Action
{
    const   FORM_NAME = "xoopsLogin";

    public function preDispatch()
    {
        if (Xoops::service("auth")->hasIdentity()) {
            // Login should be disabled for logged-in users;
            // however, login is needed for an admin to enter admin area when he enabled "rememberme"
            /*
            if ('logout' != $this->getRequest()->getActionName()) {
                $this->_helper->redirector('index', 'index');
            }
            */
        } else {
            // Redirect anonymous to the login form on request of logout
            if ('logout' == $this->getRequest()->getActionName()) {
                $this->forward('index');
            }
        }
    }

    public function loginAction()
    {
        $this->forward("index");
    }

    public function indexAction()
    {
        $form = $this->getForm();
        $this->renderForm($form);
    }

    protected function renderForm($form)
    {
        $this->setTemplate("login.html");
        $module = $this->getRequest()->getModuleName();
        $configs = XOOPS::service("registry")->config->read($module, "login");
        $message = "";
        if (!empty($configs["attempts"])) {
            $sessionLogin = Xoops::service("session")->login;
            if (!empty($sessionLogin->attempts) && $sessionLogin->attempts >= $configs["attempts"]) {
                $waiting = Xoops::service("session")->getSaveHandler()->getLifeTime() / 60;
                $message = sprintf(XOOPS::_("Login with the account is suspended, please wait for %d minutes to try again."), $waiting);
                $this->setTemplate("login_suspended.html");
            } elseif (!empty($sessionLogin->attempts)) {
                $remaining = $configs["attempts"] - $sessionLogin->attempts;
                $message = sprintf(XOOPS::_("You have %d times to try."), $remaining);
            }
        }
        //$form = $this->getForm("xoopsLogin");
        $form->assign($this->view);
        $title = XOOPS::_("Login form");
        $this->template->assign("title", $title);
        $this->template->assign("message", $message);
    }

    public function logoutAction()
    {
        $this->setTemplate("logout.html");
        Xoops::service("auth")->destroy();

        $urlOptions = XOOPS::url("www");
        $message = XOOPS::_("You have logged out successfully. We look forward to seeing you soon ...");
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
            $form->addDecorators(array(
                //'Errors',
                //'FormErrors'
            ));
            //Debug::e(array_keys($form->getDecorators()));
            // Failed validation; redisplay form
            //$this->view->form = $form;
            return $this->renderForm($form);
            //return $this->render("index");
        }

        $module = $this->getRequest()->getModuleName();
        $configs = XOOPS::service("registry")->config->read($module, "login");

        $values = $form->getValues();
        $identity = $values["identity"];
        $credential = $values["credential"];
        $type = isset($values["type"]) ? $values["type"] : "";
        $rememberMe = $values["rememberme"];
        $redirect = isset($values["redirect"]) ? $values["redirect"] : "";

        if (!empty($configs["attempts"])) {
            $sessionLogin = Xoops::service("session")->login;
            if (!empty($sessionLogin->attempts) && $sessionLogin->attempts >= $configs["attempts"]) {
                XOOPS::service("event")->trigger("login_failure", array($identity, $sessionLogin->attempts, $type));
                return $this->_helper->redirector('index');
            }
        }

        $this->setTemplate("authenticate.html");
        Xoops::service("auth")->loadAdapter()->setIdentityKey($type);
        //$myts = MyTextsanitizer::getInstance();
        $result = Xoops::service("auth")->process($identity, $credential);

        if (!$result->isValid()) {
            $failureData = array($identity, null, $type);
            if (!empty($configs["attempts"])) {
                $sessionLogin = Xoops::service("session")->login;
                $sessionLogin->attempts++;
                $failureData[1] = $sessionLogin->attempts;
            }
            XOOPS::service("event")->trigger("login_failure", $failureData);
            //$message = XOOPS::_("Invalid credentials provided, please try again.");
            $message = XOOPS::_(implode(" ", $result->getMessages()));
            //$form->setDescription($message);
            $form->addError($message);
            $form->addDecorators(array(
                'Errors',
                //'FormErrors'
            ));
            return $this->renderForm($form);
        }

        if (!empty($configs["rememberme"]) && !empty($rememberMe)) {
            Xoops::service("auth")->rememberMe($configs["rememberme"]);
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
        if ($redirect) {
            $urlOptions = urldecode($redirect);
        } else {
            $urlOptions = Xoops::url("www");
        }

        if (!empty($configs["attempts"])) {
            $sessionLogin = Xoops::service("session")->login;
            $sessionLogin->unsetAll();
        }

        $options = array("time" => 3, "message" => $message);
        $this->redirect($urlOptions, $options);
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
        $action = $this->getFrontController()->getRouter()->assemble(array(
                "action"        => "process",
                "controller"    => "login",
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

        $configs = XOOPS::service("registry")->config->read($module, "login");
        $configs = array_merge($configs, XOOPS::service("registry")->config->read($module, "account"));
        $elementType = "Text";
        $elementName = "identity";
        switch ($configs["identity"]) {
            case "default":
            case "username":
            default:
                $options = array(
                    "label"         => "Username",
                    "required"      => true,
                    "validators"    => array(
                        "strlen"    => array(
                            "validator" => "StringLength",
                            "options"   => array(
                                "min"   => $configs["uname_min"],
                                "max"   => $configs["uname_max"],
                            ),
                        ),
                    ),
                    "filters"       => array(
                        "trim"      => array(
                            "filter"    => "StringTrim",
                        ),
                    ),
                );
                $form->addElement($elementType, $elementName, $options);
                break;
            case "userid":
                $options = array(
                    "label"         => "User ID",
                    "required"      => true,
                    "validators"    => array(
                        "digital"   => array(
                            "validator" => "Digits",
                        ),
                    ),
                    "filters"       => array(
                        "trim"      => array(
                            "filter"    => "StringTrim",
                        ),
                    ),
                );
                $form->addElement($elementType, $elementName, $options);
                break;
            case "email":
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
                    ),
                );
                $form->addElement($elementType, $elementName, $options);
                break;
            case "any":
                $elementName = "type";
                $elementType = "Select";
                $options = array(
                    "label"     => "",
                    "required"  => false,
                    "value"     => "",
                    "multioptions"   => array(
                        ""      => "Default (username)",
                        "uid"   => "User ID",
                        "email" => "Email",
                    ),
                );
                $form->addElement($elementType, $elementName, $options);

                $elementName = "identity";
                $elementType = "Text";
                $options = array(
                    "label"     => "",
                    "required"  => true,
                    "options"   => array(
                        "name"  => "identity",
                        "label" => "",
                        "value" => "",
                        "filters"       => array(
                            "trim"      => array(
                                "filter"    => "StringTrim",
                            ),
                        ),
                    ),
                );
                $form->addElement($elementType, $elementName, $options);

                $elements = array("type", "identity");
                $elementName = "identity-select";
                $options = array(
                    "label" => "Login selection",
                    "mode"  => "compound",
                );
                $form->addDisplayGroup($elements, $elementName, $options);
                break;
        }

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
        $form->addElement("password", "credential", $options);

        if (!empty($configs["rememberme"])) {
            $options = array(
                "label"         => "Remember me",
                "value"         => 1,
                "multioptions"   => array(
                    "1"     => sprintf("Remember login status for %d days", $configs["rememberme"]),
                ),
                //"description"   => sprintf("Remember login status for %d days", $configs["rememberme"])
            );
            //$form->addElement("checkbox", "rememberme", $options);
            $form->addElement("MultiCheckbox", "rememberme", $options);
        }

        $options = array(
            "label"     => "Login",
            "required"  => false,
            "ignore"    => true,
        );
        $form->addElement("submit", "login", $options);

        $redirect = $this->getRequest()->getParam("redirect", "");
        if (!empty($redirect)) {
            $form->addElement("hidden", "redirect", array("value" => $redirect));
        }

        $form->setDescription("Login to XOOPS system");

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
}
