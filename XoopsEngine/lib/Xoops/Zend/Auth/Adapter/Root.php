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
 * @package         Auth
 * @subpackage      Adapter
 * @version         $Id$
 */

class Xoops_Zend_Auth_Adapter_Root extends Xoops_Zend_Auth_Adapter_Admin
{
    /**
     * $_identityKey - Identity key for authentication
     *
     * @var string
     */
    protected $_identityKey = "identity";

    /**
     * $_primaryIdentity - Primary identity key for user data wakeup
     *
     * @var string
     */
    protected $_primaryIdentity = "identity";

    /**
     * $_credentialKey - Credential key for authentication
     *
     * @var string
     */
    protected $_credentialKey = "credential";

    /**
     * _authenticateQuerySelect() - This method accepts a Zend_Db_Select object and
     * performs a query against the database with that object.
     *
     * @param Zend_Db_Select $dbSelect
     * @throws Zend_Auth_Adapter_Exception - when a invalid select object is encoutered
     * @return array
     */
    protected function _authenticateQuery()
    {
        $identityModel = XOOPS::getModel("user_root");
        $rowset = $identityModel->fetchAll(array("identity = ?" => $this->_identity));
        return $rowset;
    }

    /**
     * Creates identity data
     *
     * @param   object $resultIdentity
     * @return array
     */
    protected function _authenticateCreateAuthIdentity($resultIdentity)
    {
        $userData = array(
            "id"        => 0,
            "identity"  => $resultIdentity->identity,
            "name"      => $resultIdentity->name,
            "time"      => time(),
        );
        if (empty($userData["name"])) {
            $userData["name"] = $userData["identity"];
        }
        $userData['role'] = Xoops_Acl::ADMIN;

        return $userData;
    }

    public function wakeup(&$data)
    {
        if (empty($data)) {
            return false;
        }
        // Overwrite custom expiration time set by "rememberme" for security consideration
        $sessionLifetType =  Xoops::service("session")->getSaveHandler()->getLifeTime();
        if (empty($data["time"]) || ($data["time"] + $sessionLifetType) < time()) {
            return false;
        } else {
            $data["time"] = time();
        }
        if (!isset($data['role'])) {
            $data['role'] = Xoops_Acl::ADMIN;
        }
        //$xoopsUser = $user;
        return true;
    }
}
