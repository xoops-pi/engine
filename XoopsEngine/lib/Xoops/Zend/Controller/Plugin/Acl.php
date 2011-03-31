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
 * @copyright       Xoops Engine http://www.xoopsengine.org/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Controller
 * @version         $Id$
 */

class Xoops_Zend_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{
    /**
     * @var Zend_Acl
     */
    protected $_acl;

    /**
     * @var string
     */
    protected $_roleName;

    /**
     * @var string
     */
    //public $section = "front";

    /**
     * Constructor
     *
     * @param mixed $aclData
     * @param $roleName
     * @return void
     */
    public function __construct($section = null, $roleName = null)
    {
        if (empty($roleName)) {
            $this->_roleName = XOOPS::registry('user')->role;
        } else {
            $this->_roleName = $roleName;
        }
        $this->getAcl($section)->setRole($this->_roleName);

        /*
        if (null !== $section) {
            $this->section = $section;
        }
        */
    }

    /**
     * Sets the ACL object
     *
     * @param mixed $aclData
     * @return void
     */
    public function setAcl(Zend_Acl $aclData)
    {
        $this->_acl = $aclData;
    }

    /**
     * Returns the ACL object
     *
     * @return Zend_Acl
     */
    public function getAcl($section = null)
    {
        if (!isset($this->_acl)) {
            if (is_null($section)) {
                $section = XOOPS::registry("frontController")->getParam("section");
            }
            // Creating the ACL object
            $this->_acl = new Xoops_Acl($section);
        }

        return $this->_acl;
    }

    /**
     * Sets the ACL role to use
     *
     * @param string $roleName
     */
    public function setRoleName($roleName)
    {
        $this->getAcl()->setRole($roleName);
        return $this;
    }

    /**
     * Returns the ACL role used
     *
     * @return string
     */
    public function getRoleName()
    {
        return $this->_roleName;
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
        /*
        Debug::e(
            $request->getModuleName() . "-" .
            $request->getControllerName() . "-" .
            $request->getActionName()
        );
        /**/
        if (XOOPS::registry("frontController")->getDispatcher()->getDefaultModule() == $request->getModuleName()) {
            return;
        }

        /** Check if the controller/action can be accessed by the current user */
        if (!$this->getAcl()->hasAccess($request)) {
            /** Redirect to access denied page */
            $this->denyAccess();
        }
    }

    /**
     * Deny Access Function
     * Redirects to errorPage, this can be called from an action using the action helper
     *
     * @return void
     */
    public function denyAccess()
    {
        throw new Zend_Controller_Exception(
            'Access to the page is denied by ACL: ' . XOOPS::registry("frontController")->getParam("section") . ':' . $this->getRequest()->getModuleName() . ':' . $this->getRequest()->getControllerName() . ':' . $this->getRequest()->getActionName(),
            XOOPS::registry("user")->isGuest() ? 401 : 403
        );
    }
}