<?php
/**
 * XOOPS security service class
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
 * @package         Xoops_Service
 * @version         $Id$
 */

namespace Engine\Xoops\Service;

class Security extends \Kernel\Service\ServiceAbstract
{
    protected $errors = array();
    protected $formToken = array();
    protected $formSession;

    public function __construct($options = array())
    {
        parent::__construct($options);
        $GLOBALS['xoopsSecurity'] = $this;
    }

    /**
     * Check if there is a valid token in $_REQUEST[$name . '_REQUEST'] - can be expanded for more wide use, later (Mith)
     *
     * @param bool   $clearIfValid whether to clear the token after validation
     * @param string $token token to validate
     * @param string $name session name
     *
     * @return bool
     */
    public function check($clearIfValid = true, $token = false, $name = '')
    {
        return $this->validateToken($token, $clearIfValid, $name);
    }

    /**
     * Create a token in the user's session
     *
     * @param int $timeout time in seconds the token should be valid
     * @param string $name session name
     *
     * @return string token value
     */
    public function createToken($timeout = 0, $name = '')
    {
        if (!isset($this->formToken[$name])) {
            $this->formToken[$name] = $this->getToken(md5(uniqid(mt_rand(), true)));
        }
        $expire = time() + (empty($timeout) ? session_cache_expire() * 60 : $timeout);
        $this->setSession($name, $this->formToken[$name], $expire);
        return $this->formToken[$name];
    }

    /**
     * Check if a token is valid. If no token is specified, $_REQUEST[$name . '_REQUEST'] is checked
     *
     * @param string $token token to validate
     * @param bool   $clearIfValid whether to clear the token value if valid
     * @param string $name session name to validate
     *
     * @return bool
     */
    public function validateToken($token = false, $clearIfValid = true, $name = 'XOOPS_FORM_TOKEN')
    {
        $xoopsLogger = \XOOPS::service('logger');
        $token = ($token !== false) ? $token : ( isset($_REQUEST[$name]) ? $_REQUEST[$name] : '' );
        $token_data = $this->getSession($name);
        if (empty($token) || empty($token_data)) {
            $xoopsLogger->log('No valid token found in request/session', 'WARN');
            return false;
        }
        $validFound = false;
        if ($token === $this->getToken($token_data['id'])) {
            if ($clearIfValid) {
                // token should be valid once, so clear it once validated
                $this->setSession($name, null);
            }
            $xoopsLogger->log('Valid token found', 'INFO');
            $validFound = true;
        }

        if (!$validFound) {
            $xoopsLogger->log('No valid token found', 'WARN');
        }
        //$this->garbageCollection($name);
        return $validFound;
    }

    /**
     * Clear all token values from user's session
     *
     * @param string $name session name
     */
    public function clearTokens($name = 'XOOPS_FORM_TOKEN')
    {
        $this->setSession($name, null);
    }

    /**
     * Check superglobals for contamination
     *
     * @return void
     */
    public function checkSuperglobals()
    {
        foreach (array('GLOBALS', '_SESSION', 'HTTP_SESSION_VARS', '_GET', 'HTTP_GET_VARS', '_POST', 'HTTP_POST_VARS', '_COOKIE', 'HTTP_COOKIE_VARS', '_REQUEST', '_SERVER', 'HTTP_SERVER_VARS', '_ENV', 'HTTP_ENV_VARS', '_FILES', 'HTTP_POST_FILES', 'xoopsDB', 'xoopsUser', 'xoopsUserId', 'xoopsUserGroups', 'xoopsUserIsAdmin', 'xoopsConfig', 'xoopsOption', 'xoopsModule', 'xoopsModuleConfig', 'xoopsRequestUri') as $bad_global) {
            if (isset($_REQUEST[$bad_global])) {
                header('Location: ' . \XOOPS::url("www") . '/');
                exit();
            }
        }
    }

    /**
     * Check if visitor's IP address is banned
     * Should be changed to return bool and let the action be up to the calling script
     *
     * @return void
     */
    public function checkBadips()
    {
        global $xoopsConfig;
        if ($xoopsConfig['enable_badips'] == 1 && isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != '') {
            foreach ($xoopsConfig['bad_ips'] as $bi) {
                if (!empty($bi) && preg_match("/" . $bi . "/", $_SERVER['REMOTE_ADDR'])) {
                    exit();
                }
            }
        }
        unset($bi);
        unset($bad_ips);
        unset($xoopsConfig['badips']);
    }

    /**
     * Add an error
     *
     * @param   string  $error
     */
    public function setErrors($error)
    {
        $this->errors[] = trim($error);
    }

    /**
     * Get generated errors
     *
     * @param    bool    $ashtml Format using HTML?
     *
     * @return    array|string    Array of array messages OR HTML string
     */
    public function getErrors($ashtml = false)
    {
        if (!$ashtml) {
            return $this->errors;
        } else {
            $ret = '';
            if (count($this->errors) > 0) {
                foreach ($this->errors as $error) {
                    $ret .= $error . '<br />';
                }
            }
            return $ret;
        }
    }

    public function validateForm($post = null)
    {
        $hash = new Xoops_Zend_Form_Element_Hash();
        $hashSubmission = isset($post) ? $post[$hash->getName()] : $_POST[$hash->getName()];
        $hashSession = $hash->getSession()->hash;
        return $hashSubmission === $hashSession;
    }

    private function getFormSession()
    {
        if (null === $this->formSession) {
            $sessionName = __CLASS__ . '_' . \XOOPS::config('salt');
            $this->formSession = new Zend_Session_Namespace($sessionName);
        }
        return $this->formSession;
    }

    private function getSession($name)
    {
        return $this->getFormSession()->hash;
        $data = array();
        $sess = $this->getFormSession();
        if (!empty($sess->$name)) {
            $data = $sess->$name;
        }
        return $data;
    }

    private function setSession($name, $data, $expire)
    {
        $session = $this->getFormSession();
        $session->setExpirationHops(1, null, true);
        $session->setExpirationSeconds($expire);
        $session->hash = $data;
        return;
    }

    private function getToken($token_id)
    {
        return md5($token_id . $_SERVER['HTTP_USER_AGENT'] . \XOOPS::config('salt'));
    }
}