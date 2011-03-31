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
 * @copyright       Xoops Engine http://www.xoopsengine.org
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Session
 * @version         $Id$
 */

class Xoops_Zend_Session_SaveHandler_Cookie implements Zend_Session_SaveHandler_Interface
{
    /**
     * Session lifetime
     *
     * @var int
     */
    protected $lifetime = 0;

    /**
     * Whether or not the lifetime of an existing session should be overridden
     *
     * @var boolean
     */
    protected $overrideLifetime = false;

    /**
     * Session save path
     *
     * @var string
     */
    protected $sessionSavePath;

    /**
     * Session name
     *
     * @var string
     */
    protected $sessionName;

    /**
     * Constructor
     *
     * $config is an instance of Zend_Config or an array of key/value pairs containing configuration options
     *
     *
     * lifetime          => (integer) Session lifetime (optional; default: ini_get('session.gc_maxlifetime'))
     *
     * overrideLifetime  => (boolean) Whether or not the lifetime of an existing session should be overridden
     *      (optional; default: false)
     *
     * @param  Zend_Config|array $config      User-provided configuration
     * @return void
     * @throws Zend_Session_SaveHandler_Exception
     */
    public function __construct($config = array())
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }
        $lifetime = isset($config["lifetime"]) ? $config["lifetime"] : null;
        $this->setLifetime($lifetime);
        if (isset($config["overrideLifetime"])) {
            $this->setOverrideLifetime($config["overrideLifetime"]);
        }
    }

    /**
     * Destructor
     *
     * @return void
     */
    public function __destruct()
    {
        Xoops::service("session")->writeClose();
    }

    /**
     * Set session lifetime and optional whether or not the lifetime of an existing session should be overridden
     *
     * $lifetime === false resets lifetime to session.gc_maxlifetime
     *
     * @param int $lifetime
     * @param boolean $overrideLifetime (optional)
     * @return Zend_Session_SaveHandler_DbTable
     */
    public function setLifetime($lifetime, $overrideLifetime = null)
    {
        if ($lifetime < 0) {
            throw new Zend_Session_SaveHandler_Exception();
        } elseif (empty($lifetime)) {
            $this->lifetime = (int) ini_get('session.gc_maxlifetime');
        } else {
            $this->lifetime = (int) $lifetime;
        }

        if ($overrideLifetime != null) {
            $this->setOverrideLifetime($overrideLifetime);
        }

        return $this;
    }

    /**
     * Retrieve session lifetime
     *
     * @return int
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * Set whether or not the lifetime of an existing session should be overridden
     *
     * @param boolean $overrideLifetime
     * @return Zend_Session_SaveHandler_DbTable
     */
    public function setOverrideLifetime($overrideLifetime)
    {
        $this->overrideLifetime = (boolean) $overrideLifetime;

        return $this;
    }

    /**
     * Retrieve whether or not the lifetime of an existing session should be overridden
     *
     * @return boolean
     */
    public function getOverrideLifetime()
    {
        return $this->overrideLifetime;
    }

    /**
     * Open Session
     *
     * @param string $save_path
     * @param string $name
     * @return boolean
     */
    public function open($save_path, $name)
    {
        $this->sessionSavePath = $save_path;
        $this->sessionName     = $name;

        return true;
    }

    /**
     * Close session
     *
     * @return boolean
     */
    public function close()
    {
        return true;
    }

    protected function generateHash()
    {
        return md5(Xoops::config('identifier') . '.' . Xoops::config('salt'));
    }

    protected function getCookieName($id)
    {
        return Xoops::config('identifier') . '-sess';
    }

    protected function readCookie($id)
    {
        $cookieName = $this->getCookieName($id);
        if (empty($_COOKIE[$cookieName])) {
            return false;
        }
        // Data schema: lifetime.modified.data
        $rawData = explode('.', $_COOKIE[$cookieName], 3);
        if (count($rawData) < 3) {
            return false;
        }
        if ($rawData[2]) {
            $rawData[2] = Xoops_Zend_Filter::filterStatic($rawData[2], 'decrypt');
        }
        return $rawData;
    }

    protected function writeCookie($id, $data, $lifetime)
    {
        $cookieName = $this->getCookieName($id);
        $modified = time();
        if ($data) {
            $data = Xoops_Zend_Filter::filterStatic($data, 'encrypt');
        }
        $rawData = $lifetime . '.' . $modified . '.' . $data;
        setcookie($cookieName, $rawData, $lifetime + time(), Xoops::host()->get('baseUrl'));
        return true;
    }

    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        $return = '';
        $rawData = $this->readCookie($id);

        if (!empty($rawData)) {
            list($lifetime, $modified, $data) = $rawData;
            if ($this->getExpirationTime($modified, $lifetime) > time()) {
                $return = $data;
                $this->setLifetime($lifetime);
            } else {
                $this->destroy($id);
            }
        }

        return $return;
    }

    /**
     * Write session data
     *
     * @param string $id
     * @param string $data
     * @return boolean
     */
    public function write($id, $data)
    {
        $return = false;
        if ($this->writeCookie($id, $data, $this->lifetime)) {
            $return = true;
        }

        return $return;
    }

    /**
     * Destroy session
     *
     * @param string $id
     * @return boolean
     */
    public function destroy($id)
    {
        $return = false;

        if ($this->writeCookie($id, false, -1)) {
            $return = true;
        }

        return $return;
    }

    /**
     * Garbage Collection
     *
     * @param int $maxlifetime
     * @return true
     */
    public function gc($maxlifetime)
    {
        return true;
    }

    /**
     * Retrieve session lifetime considering Zend_Session_SaveHandler_DbTable::OVERRIDE_LIFETIME
     *
     * @param int $lifetime
     * @return int
     */
    protected function fetchLifetime($lifetime)
    {
        if (!$this->overrideLifetime) {
            $return = (int) $lifetime;
        } else {
            $return = $this->lifetime;
        }

        return $return;
    }

    /**
     * Retrieve session expiration time
     *
     * @param int $modified
     * @param int $lifetime
     * @return int
     */
    protected function getExpirationTime($modified, $lifetime)
    {
        return (int) $modified + $this->fetchLifetime($lifetime);
    }
}
