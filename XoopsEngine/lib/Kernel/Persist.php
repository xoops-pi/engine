<?php
/**
 * Kernel persist
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
 * @package         Kernel
 * @since           3.0
 * @version         $Id$
 */

namespace Kernel;

class Persist
{
    protected static $instances = array();
    protected $handler;
    protected $type;
    protected $prefix;

    const NOT_EXIST_INTERNAL    = '__NOT_EXIST';
    const NOT_EXIST_EXTERNAL    = '';

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct($type = null)
    {
        // If type is specified, loads it
        if (!empty($type)) {
            $type = ucfirst($type);
            $this->handler = $this->loadHandler($type);
            if (!$this->handler) {
                throw new \Exception("Type '$type' is not supported.");
            }
            $this->type = $type;
            return;
        }
        // If type is not specified, loads a handler according to default priority
        foreach (array("Apc", "Memcached", "Memcache", "File") as $type) {
            if ($this->handler = $this->loadHandler($type)) {
                $this->type = $type;
                break;
            }
        }
    }

    public function loadHandler($type)
    {
        if (!isset(static::$instances[$type])) {
            static::$instances[$type] = false;

            $class = "Kernel\\Persist\\" . $type;
            if (!class_exists($class, false)) {
                include __DIR__ . "/Persist/" . $type . ".php";
            }
            if (!class_exists($class, false)) {
                return false;
            }
            try {
                $handler = new $class();
            } catch (\Exception $e) {
                $handler = false;
            }
            static::$instances[$type] = $handler;
        }
        return static::$instances[$type];
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function prefix($key = null)
    {
        if (null === $this->prefix) {
            $this->prefix = defined('XOOPS_PERSIST_PREFIX') ? constant('XOOPS_PERSIST_PREFIX') : 'persist';
        }
        if (null === $key) {
            return $this->prefix;
        }
        return $this->prefix . '.' . $key;
    }

    /**#@+
     * Class autoloader with class map
     */
    /**
     * Registers a class path information to perisistent caches
     *
     * @param string $class The full class name
     * @param string $path  Path to the class file
     * @return null|false|true
     */
    public function registerClass($class, $path)
    {
        if (!$this->isValid()) {
            return false;
        }

        $key = $this->prefix($class);
        $path = (self::NOT_EXIST_EXTERNAL === $path) ? self::NOT_EXIST_INTERNAL : $path;
        return $this->handler->save($path, $key);
    }

    /**
     * Registers a class path information to perisistent caches
     *
     * @param string $class The full class name
     * @return string path to class
     */
    public function loadClass($class)
    {
        if (!$this->isValid()) {
            return false;
        }

        $key = $this->prefix($class);
        $path = $this->handler->load($key);
        $path = (self::NOT_EXIST_INTERNAL === $path) ? self::NOT_EXIST_EXTERNAL : $path;
        return $path;
    }

    /**
     * Backend type
     *
     * @return string
     */
    public function isValid()
    {
        return (!empty($this->type) && $this->type != "File") ? true : false;
    }
    /**#@-*/

    /**#@+
     * Persist APIs, proxy to handler
     * @see \Kernel\Persist\PersistInterface
     */
    public function load($id)
    {
        $key = $this->prefix($id);
        return $this->handler->load($key);
    }

    public function save($data, $id, $ttl = 0)
    {
        $key = $this->prefix($id);
        return $this->handler->save($data, $key, $ttl);
    }

    public function remove($id)
    {
        $key = $this->prefix($id);
        return $this->handler->remove($key);
    }

    public function clean($type = null)
    {
        return $this->handler->clean($type);
    }
    /**#@-*/

    /**
     * @see Kernel\\Persist\\PersistInterface
     */
    public function __call($method, $params)
    {
        if (!$this->handler) {
            return false;
        }
        return call_user_func_array(array($this->handler, $method), $params);
    }
}

namespace Kernel\Persist;

interface PersistInterface
{
    /**
     * Test if an item is available for the given id and (if yes) return it (false else)
     *
     * @param  string  $id                     Item id
     * @return mixed|false Cached datas
     */
    public function load($id);

    /**
     * Save some data in a key
     *
     * @param  mixed $data      Data to put in cache
     * @param  string $id       Store id
     * @param  int $ttl
     * @return boolean True if no problem
     */
    public function save($data, $id, $ttl = 0);

    /**
     * Remove an item
     *
     * @param  string $id Data id to remove
     * @return boolean True if ok
     */
    public function remove($id);

    /**
     * Clean cached entries
     *
     * @return boolean True if ok
     */
    public function clean($type = null);
}