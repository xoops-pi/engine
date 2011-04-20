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
 * Memcached supported session handler can be configured in var/etc/resources.session.ini.php:
 *   save_handler = memcached
 *   save_path = "127.0.0.1:11211"
 * It is simple and does not need the custom session savehandler. However custom session expiration per user is not supported, see {@Xoops_Zend_Session::rememberMe}.
 *
 * @copyright       Xoops Engine http://www.xoopsengine.org
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Session
 * @version         $Id$
 */

class Xoops_Zend_Session_SaveHandler_Memcached implements Zend_Session_SaveHandler_Interface
{
    /**
     * Default Values
     */
    /*
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT =  11211;
    const DEFAULT_WEIGHT  = 1;
    */

    /**
     * Available options
     *
     * =====> (array) servers :
     * an array of memcached server ; each memcached server is described by an associative array :
     * 'host' => (string) : the name of the memcached server
     * 'port' => (int) : the port of the memcached server
     * 'weight' => (int) : number of buckets to create for this server which in turn control its
     *                     probability of it being selected. The probability is relative to the total
     *                     weight of all servers.
     * =====> (array) client :
     * an array of memcached client options ; the memcached client is described by an associative array :
     * @see http://php.net/manual/memcached.constants.php
     * - The option name can be the name of the constant without the prefix 'OPT_'
     *   or the integer value of this option constant
     *
     * @var array available options
     */
    /*
    protected $options = array(
        'servers' => array(array(
            'host'   => self::DEFAULT_HOST,
            'port'   => self::DEFAULT_PORT,
            'weight' => self::DEFAULT_WEIGHT,
        )),
        'client' => array()
    );
    */
    protected $options = array();

    /**
     * The memcache instance to store session data
     *
     * @var mixed
     */
    protected $handler = null;

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
        if (!extension_loaded('memcached')) {
            throw new Zend_Session_SaveHandler_Exception("Memcached is not supported");
        }
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }

        $lifetime = null;
        if (isset($config["lifetime"])) {
            $lifetime = $config["lifetime"];
            unset($config["lifetime"]);
        }
        $this->setLifetime($lifetime);
        if (isset($config["overrideLifetime"])) {
            $this->setOverrideLifetime($config["overrideLifetime"]);
            unset($config["overrideLifetime"]);
        }

        while (list($name, $value) = each($config)) {
            $this->setOption($name, $value);
        }

        /*
        $memcachedOptions = array();
        if (!empty($this->options['servers'])) {
            $memcachedOptions['servers'] = $this->options['servers'];
        }
        if (!empty($this->options['client'])) {
            $memcachedOptions['client'] = $this->options['client'];
        }
        */
        $memcachedOptions = isset($this->options['memcached']) ? $this->options['memcached'] : array();
        $this->handler = Xoops::service('memcached')->load($memcachedOptions);
    }

    /**
     * Destructor
     *
     * @return void
     */
    public function __destruct()
    {
        Xoops::service('session')->writeClose();
    }

    /**
     * Set an option
     *
     * @param  string $name
     * @param  mixed  $value
     * @throws Zend_Cache_Exception
     * @return void
     */
    public function setOption($name, $value)
    {
        if (!is_string($name)) {
            return;
        }
        $name = strtolower($name);
        $this->options[$name] = $value;
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

    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        $return = '';
        $tmp = $this->handler->get($id);
        if (is_array($tmp) && $tmp[1]) {
            $return = $tmp[0];
            $this->setLifetime($tmp[1]);
        } else {
            $this->destroy($id);
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
        $return = @$this->handler->set($id, array($data, $this->lifetime), $this->lifetime);

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
        return $this->handler->delete($id);
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
}