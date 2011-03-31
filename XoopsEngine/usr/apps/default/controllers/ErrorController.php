<?php
/**
 * Default error controller
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
 * @package         Default
 * @version         $Id$
 */

class Default_ErrorController extends \Application\Controller
{
    public function init()
    {
        $this->view->section = null;
        $this->setLayout("simple");
        $this->getHelper("viewRenderer")->setNeverRender(false);
    }

    public function deniedAction()
    {
        XOOPS::registry("user")->isGuest() ? $this->error401() : $this->error403();
    }

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');

        $errorCode = $errors->exception->getCode();
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                $errorCode = 404;
                break;
            default:
                break;
        }
        $method = "error" . $errorCode;
        if (method_exists($this, $method)) {
            $this->{$method}($errors);
        } else {
            $this->errorOther($errors);
        }
        $message = $errors->exception->getMessage();
        $this->template->assign("message", $message);

        // Clear previous content
        $this->getResponse()->clearBody();
    }

    protected function error404($errors = null)
    {
        $this->setTemplate("404.html");
        $this->template->assign("error_title", XOOPS::_('The page you requested was not found.'));
        if (substr(PHP_SAPI, 0, 3) == 'cgi') {
            $header = "Status: 404 Not Found";
        } else {
            $header = "HTTP/1.1 404 Not Found";
        }
        $this->getResponse()->setRawHeader($header);
    }

    protected function error403($errors = null)
    {
        $this->setTemplate("403.html");
        $this->template->assign("error_title", XOOPS::_('You are not allowed to access this page.'));

        if (substr(PHP_SAPI, 0, 3) == 'cgi') {
            $header = "Status: 403 Forbidden";
        } else {
            $header = "HTTP/1.1 403 Forbidden";
        }
        $this->getResponse()->setRawHeader($header);
    }

    protected function error401($errors = null)
    {
        $this->setTemplate("401.html");
        $this->template->assign("error_title", XOOPS::_('You are not allowed to access this page.'));

        if ($errors) {
            $redirect = $errors->request->getRequestUri();
        } else {
            $redirect = $errors->request->getServer("HTTP_REFERER");
        }

        $loginUrl = $this->getFrontController()->getRouter()->assemble(
            array(
                "redirect"  => urlencode($redirect),
            ),
            "login"
        );
        $this->template->assign("login", $loginUrl);

        if (substr(PHP_SAPI, 0, 3) == 'cgi') {
            $header = "Status: 401 Unauthorized";
        } else {
            $header = "HTTP/1.1 401 Unauthorized";
        }
        $this->getResponse()->setRawHeader($header);
    }

    protected function errorOther($errors = null)
    {
        $this->setTemplate("error.html");
        $this->template->assign("error_title", XOOPS::_('An unexpected error occurred. Please try again later.'));

        //$trace = $errors->exception->getTraceAsString();
        //$this->template->assign("trace", $trace);

        if (Xoops::service()->hasService('error') && $errors) {
            Xoops::service('error')->handleException($errors->exception);
        }

        if (substr(PHP_SAPI, 0, 3) == 'cgi') {
            $header = "Status: 500 Internal Server Error";
        } else {
            $header = "HTTP/1.1 500 Internal Server Error";
        }
        $this->getResponse()->setRawHeader($header);
    }
}