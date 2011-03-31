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

class Xoops_Zend_Session_SaveHandler_Db implements Zend_Session_SaveHandler_Interface
{
    /**
     * Session model
     *
     * @var {@Xoops_Zend_Db_Table}
     */
    protected $model;

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
        if (empty($config["model"])) {
            $this->model = XOOPS::getModel("session");
        } elseif (is_string($config["model"])) {
            $this->model = XOOPS::getModel($config["model"]);
        } elseif ($config["model"] instanceof Zend_Db_Table_Abstract) {
            $this->model = $config["model"];
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

    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        $return = '';
        $rows = $this->model->find($id);

        if (count($rows)) {
            if ($this->getExpirationTime($row = $rows->current()) > time()) {
                $return = $row->data;
                $this->setLifetime($row->lifetime);
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

        $data = array("modified"    => time(),
                      "data"        => (string) $data);

        if ($this->model->update($data, array("id = ?" => $id))) {
            $return = true;
        } else {
            $data["id"] = $id;
            $data["lifetime"] = $this->lifetime;
            if ($this->model->insert($data)) {
                $return = true;
            }
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

        //echo "<br />destroy: " . $id;
        if ($this->model->delete(array("id = ?" => $id))) {
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
        $this->model->delete($this->model->getAdapter()->quoteIdentifier("modified") . ' + '
                    . $this->model->getAdapter()->quoteIdentifier("lifetime") . ' < '
                    . $this->model->getAdapter()->quote(time()));

        return true;
    }


    /**
     * Retrieve session lifetime considering Zend_Session_SaveHandler_DbTable::OVERRIDE_LIFETIME
     *
     * @param Zend_Db_Table_Row_Abstract $row
     * @return int
     */
    protected function fetchLifetime(Zend_Db_Table_Row_Abstract $row)
    {
        if (!$this->overrideLifetime) {
            $return = (int) $row->lifetime;
        } else {
            $return = $this->lifetime;
        }

        return $return;
    }

    /**
     * Retrieve session expiration time
     *
     * @param Zend_Db_Table_Row_Abstract $row
     * @return int
     */
    protected function getExpirationTime(Zend_Db_Table_Row_Abstract $row)
    {
        return (int) $row->modified + $this->fetchLifetime($row);
    }
}
