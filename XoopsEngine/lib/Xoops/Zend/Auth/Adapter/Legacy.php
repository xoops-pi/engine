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

class Xoops_Zend_Auth_Adapter_Legacy extends Xoops_Zend_Auth_Adapter_Application
{
    /**
     * $_identityKey - Identity key for authentication
     *
     * @var string
     */
    protected $_identityKey = "uname";

    /**
     * $_credentialKey - Credential key for authentication
     *
     * @var string
     */
    protected $_credentialKey = "pass";

    /**
     * authenticate() - defined by Zend_Auth_Adapter_Interface.  This method is called to
     * attempt an authenication.  Previous to this call, this adapter would have already
     * been configured with all nessissary information to successfully connect to a database
     * table and attempt to find a record matching the provided identity.
     *
     * @throws Zend_Auth_Adapter_Exception if answering the authentication query is impossible
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $this->_authenticateSetup();
        $resultIdentities = $this->_authenticateQuery();

        if (($authResult = $this->_authenticateValidateResultset($resultIdentities)) instanceof Zend_Auth_Result) {
            return $authResult;
        }

        $authResult = $this->_authenticateValidateResult(array_pop($resultIdentities));
        return $authResult;
    }

    /**
     * Creates identity data
     *
     * @param   object $resultIdentity
     * @return array
     */
    protected function _authenticateCreateAuthIdentity($resultIdentity)
    {
        $identity = array(
            "id"        => $resultIdentity->getVar('uid'),
            "identity"  => $resultIdentity->getVar('uname'),
            "name"      => $resultIdentity->getVar('name'),
            "time"      => time(),
        );
        if (empty($userData["name"])) {
            $identity["name"] = $userData["identity"];
        }
        $identity['groups'] = $resultIdentity->getGroups();

        return $identity;
    }

    /**
     * Perform identity query
     *
     * @return array
     */
    protected function _authenticateQuery()
    {
        $criteria = new Criteria($this->_identityKey, $this->_identity);
        $resultIdentities = XOOPS::getHandler('user')->getObjects($criteria, false);
        return $resultIdentities;
    }

    /**
     * _authenticateValidateResultSet() - This method attempts to make certian that only one
     * record was returned in the result set
     *
     * @param array $resultIdentities
     * @return true|Zend_Auth_Result
     */
    protected function _authenticateValidateResultSet($resultIdentities)
    {
        if (count($resultIdentities) < 1) {
            $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
            $this->_authenticateResultInfo['messages'][] = 'A record with the supplied identity could not be found.';
            return $this->_authenticateCreateAuthResult();
        } elseif (count($resultIdentities) > 1) {
            $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS;
            $this->_authenticateResultInfo['messages'][] = 'More than one record matches the supplied identity.';
            return $this->_authenticateCreateAuthResult();
        }

        return true;
    }

    /**
     * _authenticateValidateResult() - This method attempts to validate that the record in the
     * result set is indeed a record that matched the identity provided to this adapter.
     *
     * @param   object $resultIdentity
     * @return  Zend_Auth_Result
     */
    protected function _authenticateValidateResult($resultIdentity)
    {
        if ($resultIdentity->getVar($this->_credentialKey) != md5($this->_credential)) {
            $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
            $this->_authenticateResultInfo['messages'][] = 'Supplied credential is invalid.';
            return $this->_authenticateCreateAuthResult();
        }

        $this->_authenticateResultInfo['code'] = Zend_Auth_Result::SUCCESS;
        $this->_authenticateResultInfo['messages'][] = 'Authentication successful.';
        $this->_authenticateResultInfo['identity'] = $this->_authenticateCreateAuthIdentity($resultIdentity);
        return $this->_authenticateCreateAuthResult();
    }

    public function wakeup(&$data)
    {
        global $xoopsUser;

        if (empty($data["id"])) {
            return false;
        }
        $user = XOOPS::getHandler('user')->get($data["id"]);
        /* */
        if (is_object($user)) {
            if (isset($data['groups'])) {
                $user->setGroups($data['groups']);
            } else {
                $data['groups'] = $user->getGroups();
            }
        }
        /* */
        $xoopsUser = $user;
        return true;
    }
}
