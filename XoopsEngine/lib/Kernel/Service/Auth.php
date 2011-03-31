<?php
/**
 * Kernel service
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
 * @package         Kernel/Service
 * @version         $Id$
 */

namespace Kernel\Service;

class Auth extends ServiceAbstract
{
    protected $authClass;
    protected $authHandler;

    /**
     * Constructor
     *
     * @param array     $options    Parameters to send to the service during instanciation
     */
    public function __construct($options = array())
    {
        if (array_key_exists('authClass', $options)) {
            $this->authClass = $options['authClass'];
            unset($options['authClass']);
        }
        parent::__construct($options);
        if (empty($this->authClass)) {
            throw new \Exception("No authClass defined.");
        }
        $authClass = $this->authClass;
        $this->authHandler = $authClass::getInstance();
    }

    public function setAuthHandler($handler)
    {
        $this->authHandler = $handler;
        return $this;
    }

    public function getAuthHandler()
    {
        return $this->authHandler;
    }

    /**#@+
     * APIs for authentication handler
     */
    /**
     * Returns true if and only if an identity is available from storage
     *
     * @return boolean
     */
    public function hasIdentity()
    {
        return $this->authHandler->hasIdentity();
    }

    /**
     * Returns the identity from storage or null if no identity is available
     *
     * @return mixed|null
     */
    public function getIdentity()
    {
        return $this->authHandler->getIdentity();
    }

    public function destroy()
    {
        return $this->authHandler->destroy();
    }

    /**
     * Returns adapter handler
     *
     * @param  string|null $adapter
     * @return
     */
    public function loadAdapter($adapter = null)
    {
        return $this->authHandler->loadAdapter($adapter);
    }

    /**
     * Loads storage handler
     *
     * @return
     */
    public function loadStorage($storage = null)
    {
        return $this->authHandler->loadStorage($storage);
    }

    /**
     * Process the authentication
     *
     * @param   string $identity
     * @param   string $credential
     * @param   string $adapter
     * @return
     */
    public function process($identity, $credential, $adapter = null)
    {
        return $this->authHandler->process($identity, $credential, $adapter);
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
        return $this->authHandler->wakeup($identity, $adapter, $storage);
    }

    public function rememberMe($days = null)
    {
        return $this->authHandler->rememberMe($days);
    }

    public function setRememberMe($days = null)
    {
        return $this->authHandler->setRememberMe($days);
    }
    /*#@-*/

    /**
     * Custom methods, forwarded to auth handler
     *
     * @return
     */
    public function __call($method, $args)
    {
        if (is_callable(array($this->authHandler, $method))) {
            return call_user_func_array(array($this->authHandler, $method), $args);
        }
        return null;
    }
}