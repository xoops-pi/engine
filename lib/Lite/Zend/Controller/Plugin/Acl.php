<?php
/**
 * Zend Framework for Xoops Engine
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       The Xoops Engine http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Controller
 * @version         $Id$
 */

class Lite_Zend_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{
    /**
     *
     * @var array These modules can be accessed without login
     */
    protected $freeModules = array();

    /**
     * Constructor
     *
     * @param mixed $aclData
     * @param $roleName
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Predispatch
     *
     * Checks if the current user identified by roleName has rights to the requested url (module/controller/action)
     * If not, it will call denyAccess to be redirected to errorPage
     *
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        if (empty($this->freeModules) || array_search($request->getModuleName(), $this->freeModules) !== FALSE) {
            return;
        }

        if (!Mu_Api_User::isLogin()) {
            $this->denyAccess($request);
        }

        $params = $request->getParams();
        if ($request->isXmlHttpRequest() && isset($params[Mu_Api_User::AJAX_ACTION_FLAG])) {
            return;
        }

    }

    /**
     * Deny Access Function
     * Redirects to errorPage, this can be called from an action using the action helper
     *
     * @return void
     */
    public function denyAccess(Zend_Controller_Request_Abstract $request)
    {
        $redirectUrl = Engine::appUrl(array(
            'module'     => 'mu',
            'controller' => 'login',
            'action'     => 'index',
        ));

        if ($request->isXmlHttpRequest()) {
            $result             = Mu_Api_User::getAjaxResult();
            $result['redirect'] = $redirectUrl;
            $json               = new Zend_Controller_Action_Helper_Json();
            $json->direct($result);
        } else {
            $redirector = new Lite_Controller_Action_Helper_Redirector();
            $redirector->gotoUrl($redirectUrl);
        }
        exit();
//        throw new Zend_Controller_Exception('Access to the page is denied by ACL: ' . XOOPS::registry("frontController")->getParam("section") . ':' . $this->getRequest()->getModuleName() . ':' . $this->getRequest()->getControllerName() . ':' . $this->getRequest()->getActionName(), 403);
    }

    public function forceRelog(Zend_Controller_Request_Abstract $request)
    {
        Mu_Api_User::logout();
        $redirectUrl = Engine::appUrl(array(
            'module'     => 'mu',
            'controller' => 'login',
            'action'     => 'index',
        ));
        if ($request->isXmlHttpRequest()) {
            $result             = Mu_Api_User::getAjaxResult();
            $result['redirect'] = $redirectUrl;
            $json               = new Zend_Controller_Action_Helper_Json();
            $json->direct($result);
        } else {
            $redirector = new Lite_Controller_Action_Helper_Redirector();
            $redirector->gotoUrl($redirectUrl);
        }
        exit();
    }
}