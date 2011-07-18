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

namespace Kernel\Persist;

class Redis implements PersistInterface
{
    /**
     * Server Values
     */
    const SERVER_HOST = '127.0.0.1';
    const SERVER_PORT =  6379;
    const SERVER_TIMEOUT =  0;
    //const SERVER_AUTH = NULL; //replace with a string to use Redis authentication

    protected $redis;

    public function __construct()
    {
        if (!extension_loaded('redis')) {
            throw new \Exception('The redis extension must be loaded for using this model !');
        }
        $redis = new \Redis();
        $status = $redis->connect(self::SERVER_HOST, self::SERVER_PORT, self::SERVER_TIMEOUT);
        if (!$status) {
            throw new \Exception('The redis server connection failed.');
        }
        $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);   // use igBinary serialize/unserialize
        /*
        if(self::SERVER_AUTH) {
            $redis->auth(self::SERVER_AUTH);
        }
        */
        $this->redis = $redis;
    }

    public function getEngine()
    {
        return $this->redis;
    }

    /**
     * Test if an item is available for the given id and (if yes) return it (false else)
     *
     * @param  string  $id                     Item id
     * @return mixed|false Cached datas
     */
    public function load($id)
    {
        $data = $this->redis->get($id);
        /*
        if (is_string($data) && !is_numeric($data)) {
            $data = unserialize($data);
        }
        */

        return $data;
    }

    /**
     * Save some data in a key
     *
     * @param  mixed $data      Data to put in cache
     * @param  string $id       Store id
     * @return boolean True if no problem
     */
    public function save($data, $id, $ttl = 0)
    {
        /*
        if ((is_string($data) && !is_numeric($data)) || is_object($data) || is_array($data)) {
            $data = serialize($data);
        }
        */
        if ($ttl) {
            $result = $this->redis->setex($id, $data, $ttl);
        } else {
            $result = $this->redis->set($id, $data);
        }
        return $result;
    }

    /**
     * Remove an item
     *
     * @param  string $id Data id to remove
     * @return boolean True if ok
     */
    public function remove($id)
    {
        return $this->redis->delete($id);
    }

    /**
     * Clean cached entries
     *
     * @return boolean True if ok
     */
    public function clean($type = null)
    {
        return $this->redis->flushDB();
    }
}