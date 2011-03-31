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

class Xoops_Zend_Auth_Adapter_Application implements Xoops_Zend_Auth_Adapter_Interface
{
    /**
     * $_identity - Identity value
     *
     * @var string
     */
    protected $_identity = null;

    /**
     * $_credential - Credential values
     *
     * @var string
     */
    protected $_credential = null;

    /**
     * $_identityKey - Identity key for authentication
     *
     * @var string
     */
    protected $_identityKey = "identity";

    /**
     * $_authenticateResultInfo
     *
     * @var array
     */
    protected $_authenticateResultInfo = null;

    /**
     * __construct() - Sets configuration options
     *
     * @param  string $identity
     * @param  string $credential
     * @return void
     */
    public function __construct($identity = NULL, $credential = NULL)
    {
        $this->setIdentity($identity);
        $this->setCredential($credential);
    }

    /**
     * setIdentity() - set the value to be used as the identity
     *
     * @param  string   $value  Value of identity
     * @return Zend_Auth_Adapter_DbTable Provides a fluent interface
     */
    public function setIdentity($value)
    {
        $this->_identity = $value;
        return $this;
    }

    /**
     * setCredential() - set the credential value to be used, optionally can specify a treatment
     * to be used, should be supplied in parameterized form, such as 'MD5(?)' or 'PASSWORD(?)'
     *
     * @param  string $credential
     * @return Zend_Auth_Adapter_DbTable Provides a fluent interface
     */
    public function setCredential($credential)
    {
        $this->_credential = $credential;
        return $this;
    }

    /**
     * Set the key to be used for identity in authentication
     *
     * @param  string   $key    Key for identity
     * @return Zend_Auth_Adapter_DbTable Provides a fluent interface
     */
    public function setIdentityKey($key = null)
    {
        if (!empty($key)) {
            $this->_identityKey = $key;
        }
        return $this;
    }

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

        $authResult = $this->_authenticateValidateResult($resultIdentities->current());
        return $authResult;
    }

    /**
     * _authenticateSetup() - This method abstracts the steps involved with making sure
     * that this adapter was indeed setup properly with all required peices of information.
     *
     * @throws Zend_Auth_Adapter_Exception - in the event that setup was not done properly
     * @return true
     */
    protected function _authenticateSetup()
    {
        $this->_authenticateResultInfo = array(
            'code'     => Zend_Auth_Result::FAILURE,
            'identity' => null,
            'messages' => array()
        );

        return true;
    }

    /**
     * Perform identity query
     *
     * @return array
     */
    protected function _authenticateQuery()
    {
        $identityModel = XOOPS::getModel("user_account");
        $rowset = $identityModel->fetchAll(array(
            $identityModel->getAdapter()->quoteIdentifier($this->_identityKey) . " = ?" => $this->_identity,
            "active = ?" => 1
        ));
        return $rowset;

        /*
        $criteria = new Criteria($this->_identityKey, $this->_identity);
        $resultIdentities = XOOPS::getHandler('user')->getObjects($criteria, false);
        return $resultIdentities;
        */
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
        if ($resultIdentities->count() < 1) {
            $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
            $this->_authenticateResultInfo['messages'][] = 'A record with the supplied identity could not be found.';
            return $this->_authenticateCreateAuthResult();
        } elseif ($resultIdentities->count() > 1) {
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
        if ($resultIdentity->transformCredential($this->_credential) != $resultIdentity->credential) {
            $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
            $this->_authenticateResultInfo['messages'][] = 'Supplied credential is invalid.';
            return $this->_authenticateCreateAuthResult();
        }

        /*
        if (!$resultIdentity->active) {
            $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_UNCATEGORIZED;
            $this->_authenticateResultInfo['messages'][] = 'The identity is not activated.';
            return $this->_authenticateCreateAuthResult();
        }
        */

        $this->_authenticateResultInfo['code'] = Zend_Auth_Result::SUCCESS;
        $this->_authenticateResultInfo['messages'][] = 'Authentication successful.';
        $this->_authenticateResultInfo['identity'] = $this->_authenticateCreateAuthIdentity($resultIdentity);
        return $this->_authenticateCreateAuthResult();
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
            "id"        => $resultIdentity->id,
            "identity"  => $resultIdentity->identity,
            "name"      => $resultIdentity->name,
            "time"      => time(),
        );
        if (empty($userData["name"])) {
            $userData["name"] = $userData["identity"];
        }
        $userData['role'] = Xoops_User::getRole($userData['id']);

        return $userData;
    }

    /**
     * _authenticateCreateAuthResult() - This method creates a Zend_Auth_Result object
     * from the information that has been collected during the authenticate() attempt.
     *
     * @return Zend_Auth_Result
     */
    protected function _authenticateCreateAuthResult()
    {
        return new Zend_Auth_Result(
            $this->_authenticateResultInfo['code'],
            $this->_authenticateResultInfo['identity'],
            $this->_authenticateResultInfo['messages']
        );
    }

    public function wakeup(&$data)
    {
        if (empty($data["id"])) {
            return false;
        }

        if (!empty($data['identity'])) {
            if (!isset($data['role'])) {
                $data['role'] = Xoops_User::getRole($data['id']);
            }
        }

        return true;
    }
}
