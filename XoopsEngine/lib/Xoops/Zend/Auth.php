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
 * @package         Xoops_Zend
 * @version         $Id$
 */

class Xoops_Zend_Auth extends Zend_Auth
{
    protected static $rememberMe = 0;
    protected $adapter;
    protected $storage;
    //const   USER_KEY = "user";

    /**
     * Returns an instance of Zend_Auth
     *
     * Singleton pattern implementation
     *
     * @return Zend_Auth Provides a fluent interface
     */
    public static function getInstance()
    {
        if (null === static::$_instance) {
            static::$_instance = new self();
        }

        return static::$_instance;
    }

    /**
     * Sets adapter handler
     *
     * @param  Zend_Auth_Adpater_Interface $adapter
     * @return boolean
     */
    public function setAdapter($adapter)
    {
        if ($adapter instanceof Xoops_Zend_Auth_Adapter_Interface) {
            $this->adapter = $adapter;
            return true;
        }
        return false;
    }

    /**
     * Returns adapter handler
     *
     * @param  string|null $adapter
     * @return Zend_Auth_Adpater_Interface
     */
    public function loadAdapter($adapter = null)
    {
        if (!isset($adapter)) {
            if (isset($this->adapter)) {
                return $this->adapter;
            }
            $adapter = "application";
        }
        $class = 'Xoops_Zend_Auth_Adapter_' . ucfirst($adapter);
        $this->adapter = new $class();

        return $this->adapter;
    }

    /**
     * Returns storage handler
     *
     * @return Zend_Auth_Storage_Interface
     */
    public function loadStorage($storage = null)
    {
        if (!isset($storage)) {
            if (isset($this->storage)) {
                return $this->storage;
            }
            $storage = "session";
        }
        $class = 'Xoops_Zend_Auth_Storage_' . ucfirst($storage);
        if (!class_exists($class)) {
            $class = 'Zend_Auth_Storage_' . ucfirst($storage);
        }
        $this->storage = new $class();

        return $this->storage;
    }

    /**
     * Authenticates against the supplied adapter
     *
     * @param  Zend_Auth_Adapter_Interface $adapter
     * @return Zend_Auth_Result
     */
    public function authenticate(Zend_Auth_Adapter_Interface $adapter)
    {
        $result = $adapter->authenticate();

        if ($result->isValid()) {
            $this->getStorage()->write($result->getIdentity());
        }

        return $result;
    }

    /**
     * Process the authentication
     *
     * @param   string $identity
     * @param   string $credential
     * @param   string $adapter
     * @return  Zend_Auth_Result
     */
    public function process($identity, $credential, $adapter = null)
    {
        $adapter = $this->loadAdapter($adapter);
        $adapter->setIdentity($identity);
        $adapter->setCredential($credential);

        return $this->authenticate($adapter);
    }

    /**
     * Wake up current user
     *
     * @param   string $identity
     * @param   string $storage
     * @return  array
     */
    public function wakeup($identity = null, $adapter = null, $storage = null)
    {
        if (isset($adapter)) {
            $adapter = $this->loadAdapter($adapter);
        }
        if (isset($storage)) {
            $storage = $this->loadStorage($storage);
        }
        $this->setStorage($this->loadStorage());

        if (!isset($identity)) {
            $identity = $this->getIdentity();
        }
        $userData = array();
        if (!empty($identity)) {
            $status = $this->loadAdapter()->wakeup($identity);
            $this->getStorage()->write($identity);
            if ($status) {
                $userData = $identity;
            }
        } else {
            $this->clearIdentity();
        }
        XOOPS::registry("user")->assign($userData);
        // To load user preference and overload system configuration?

        return true;
    }

    public function destroy()
    {
        $this->clearIdentity();
        Xoops::service("session")->regenerateId();
    }

    public function rememberMe($days = null)
    {
        $rememberMeDays = isset($days) ? $days : static::$rememberMe;
        Xoops::service("session")->rememberMe($rememberMeDays * 86400);
    }

    public function setRememberMe($days = null)
    {
        static::$rememberMe = $days;
    }
}