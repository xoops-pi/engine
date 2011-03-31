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

class Session extends ServiceAbstract
{
    protected $sessionClass;

    /**
     * Constructor
     *
     * @param array     $options    Parameters to send to the service during instanciation
     */
    public function __construct($options = array())
    {
        if (array_key_exists('sessionClass', $options)) {
            $this->sessionClass = $options['sessionClass'];
            unset($options['sessionClass']);
        }
        parent::__construct($options);
        if (empty($this->sessionClass)) {
            throw new Exception("No sessionClass defined.");
        }
    }

    public function setSessionClass($class)
    {
        $this->sessionClass = $class;
        return $this;
    }

    public function getSessionClass()
    {
        return $this->sessionClass;
    }

    /**#@+
     * APIs for session handler
     */
    /**
     * Set options for session handler
     *
     * @param  array $userOptions - pass-by-keyword style array of <option name, option value> pairs
     * @return
     */
    public function setOptions($options = array())
    {
        $sessionClass = $this->sessionClass;
        $sessionClass::setOptions($options);
        //call_user_func($this->sessionClass . '::setOptions', $options);
        return $this;
    }

    /**
     * Session Save Handler assignment
     *
     * @param   object $savehan
     * @return
     */
    public function setSaveHandler($saveHandler)
    {
        $sessionClass = $this->sessionClass;
        $sessionClass::setSaveHandler($saveHandler);
        //call_user_func($this->sessionClass . '::setSaveHandler', $saveHandler);
        return $this;
    }

    /**
     * Get the session Save Handler
     *
     */
    public function getSaveHandler()
    {
        $sessionClass = $this->sessionClass;
        return $sessionClass::getSaveHandler();
        //return call_user_func($this->sessionClass . '::getSaveHandler');
    }

    /**
     * regenerateId() - Regenerate the session id.  Best practice is to call this after
     * session is started.  If called prior to session starting, session id will be regenerated
     * at start time.
     *
     * @return
     */
    public function regenerateId()
    {
        $sessionClass = $this->sessionClass;
        $sessionClass::regenerateId();
        //call_user_func($this->sessionClass . '::regenerateId');
        return $this;
    }

    /**
     * rememberMe() - Write a persistent cookie that expires after a number of seconds in the future. If no number of
     * seconds is specified, then this defaults to self::$_rememberMeSeconds.  Due to clock errors on end users' systems,
     * large values are recommended to avoid undesirable expiration of session cookies.
     *
     * @param $seconds integer - OPTIONAL specifies TTL for cookie in seconds from present time
     * @return
     */
    public function rememberMe($seconds = null)
    {
        $sessionClass = $this->sessionClass;
        $sessionClass::rememberMe($seconds);
        //call_user_func($this->sessionClass . '::rememberMe', $seconds);
        return $this;
    }

    /**
     * forgetMe() - Write a volatile session cookie, removing any persistent cookie that may have existed. The session
     * would end upon, for example, termination of a web browser program.
     *
     * @return
     */
    public function forgetMe()
    {
        $sessionClass = $this->sessionClass;
        $sessionClass::forgetMe();
        //call_user_func($this->sessionClass . '::forgetMe');
        return $this;
    }

    /**
     * sessionExists() - whether or not a session exists for the current request
     *
     * @return bool
     */
    public function sessionExists()
    {
        $sessionClass = $this->sessionClass;
        return $sessionClass::sessionExists();
        //return call_user_func($this->sessionClass . '::sessionExists');
    }

    /**
     * Whether or not session has been destroyed via session_destroy()
     *
     * @return bool
     */
    public function isDestroyed()
    {
        $sessionClass = $this->sessionClass;
        return $sessionClass::isDestroyed();
        //return call_user_func($this->sessionClass . '::isDestroyed');
    }

    /**
     * start() - Start the session.
     *
     * @param bool|array $options  OPTIONAL Either user supplied options, or flag indicating if start initiated automatically
     * @return
     */
    public function start($options = false)
    {
        $sessionClass = $this->sessionClass;
        $sessionClass::start($options);
        //call_user_func($this->sessionClass . '::start', $options);
        return $this;
    }

    /**
     * isStarted() - convenience method to determine if the session is already started.
     *
     * @return bool
     */
    public function isStarted()
    {
        $sessionClass = $this->sessionClass;
        return $sessionClass::isStarted();
        //return call_user_func($this->sessionClass . '::isStarted');
    }

    /**
     * isRegenerated() - convenience method to determine if session_regenerate_id()
     * has been called during this request by Zend_Session.
     *
     * @return bool
     */
    public function isRegenerated()
    {
        $sessionClass = $this->sessionClass;
        return $sessionClass::isRegenerated();
        //return call_user_func($this->sessionClass . '::isRegenerated');
    }

    /**
     * getId() - get the current session id
     *
     * @return string
     */
    public function getId()
    {
        $sessionClass = $this->sessionClass;
        return $sessionClass::getId();
        //return call_user_func($this->sessionClass . '::getId');
    }

    /**
     * setId() - set an id to a user specified id
     *
     * @param string $id
     * @return
     */
    public function setId($id)
    {
        $sessionClass = $this->sessionClass;
        $sessionClass::setId($id);
        //call_user_func($this->sessionClass . '::setId', $id);
        return $this;
    }

    /**
     * registerValidator() - register a validator that will attempt to validate this session for
     * every future request
     *
     * @param  $validator
     * @return
     */
    public function registerValidator($validator)
    {
        $sessionClass = $this->sessionClass;
        $sessionClass::registerValidator($validator);
        //call_user_func($this->sessionClass . '::registerValidator', $validator);
        return $this;
    }


    /**
     * stop() - Disable write access.  Optionally disable read (not implemented).
     *
     * @return
     */
    public function stop()
    {
        $sessionClass = $this->sessionClass;
        $sessionClass::stop();
        //call_user_func($this->sessionClass . '::stop');
        return $this;
    }


    /**
     * writeClose() - Shutdown the sesssion, close writing and detach $_SESSION from the back-end storage mechanism.
     * This will complete the internal data transformation on this request.
     *
     * @param bool $readonly
     * @return
     */
    public function writeClose($readonly = true)
    {
        $sessionClass = $this->sessionClass;
        $sessionClass::writeClose($readonly);
        //call_user_func($this->sessionClass . '::writeClose', $readonly);
        return $this;
    }


    /**
     * destroy() - This is used to destroy session data, and optionally, the session cookie itself
     *
     * @param bool $remove_cookie - OPTIONAL remove session id cookie, defaults to true (remove cookie)
     * @param bool $readonly
     * @return
     */
    public function destroy($remove_cookie = true, $readonly = true)
    {
        $sessionClass = $this->sessionClass;
        $sessionClass::destroy($remove_cookie, $readonly);
        //call_user_func($this->sessionClass . '::destroy', $remove_cookie, $readonly);
        return $this;
    }

    /**
     * expireSessionCookie() - Sends an expired session id cookie, causing the client to delete the session cookie
     *
     * @return
     */
    public function expireSessionCookie()
    {
        $sessionClass = $this->sessionClass;
        $sessionClass::expireSessionCookie();
        //call_user_func($this->sessionClass . '::expireSessionCookie');
        return $this;
    }

    /**
     * namespaceIsset() - check to see if a namespace is set
     *
     * @param string $namespace
     * @return bool
     */
    public function namespaceIsset($namespace)
    {
        $sessionClass = $this->sessionClass;
        return $sessionClass::namespaceIsset($namespace);
        //return call_user_func($this->sessionClass . '::namespaceIsset', $namespace);
    }

    /**
     * namespaceUnset() - unset a namespace or a variable within a namespace
     *
     * @param string $namespace
     * @return
     */
    public function namespaceUnset($namespace)
    {
        $sessionClass = $this->sessionClass;
        $sessionClass::namespaceUnset($namespace);
        //call_user_func($this->sessionClass . '::namespaceUnset', $namespace);
        return $this;
    }

    /**
     * getIterator() - return an iteratable object for use in foreach and the like,
     * this completes the IteratorAggregate interface
     *
     * @return ArrayObject
     */
    public function getIterator()
    {
        $sessionClass = $this->sessionClass;
        return $sessionClass::getIterator();
        //return call_user_func($this->sessionClass . '::getIterator');
    }

    /**
     * isWritable() - returns a boolean indicating if namespaces can write (use setters)
     *
     * @return bool
     */
    public function isWritable()
    {
        $sessionClass = $this->sessionClass;
        return $sessionClass::isWritable();
        //return call_user_func($this->sessionClass . '::isWritable');
    }

    /**
     * getNamespace() - create a namespace object
     *
     * "namespace" is not allowed to use as a method name, thus we use "getnamespace" and "ns"
     *
     * @param string $namespace       - programmatic name of the requested namespace
     * @param bool $singleInstance    - prevent creation of additional accessor instance objects for this namespace
     * @return
     */
    public function &getNamespace($namespace = 'Default', $singleInstance = false)
    {
        $sessionClass = $this->sessionClass;
        $ns = $sessionClass::getNamespace($namespace, $singleInstance);
        //$ns = call_user_func($this->sessionClass . '::getNamespace', $namespace, $singleInstance);
        return $ns;
    }

    public function &ns($namespace = 'Default', $singleInstance = false)
    {
        $ns = $this->getNamespace($namespace, $singleInstance);
        return $ns;
    }
    /*#@-*/

    /**
     * Custom methods, forwarded to session handler
     *
     * @return
     */
    public function __call($method, $args)
    {
        $sessionClass = $this->sessionClass;
        if (method_exists($sessionClass, $method)) {
            return call_user_func_array(array($sessionClass, $method), $args);
        }
        return null;
    }

    /**
     * Namespace
     *
     * @return
     */
    public function &__get($name)
    {
        $ns = $this->getNamespace($name);
        return $ns;
    }
}