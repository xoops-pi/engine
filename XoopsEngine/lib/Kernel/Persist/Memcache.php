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

class Memcache implements PersistInterface
{
    /**
     * Server Values
     */
    const DEFAULT_HOST = '127.0.0.1';

    protected $memcache;

    public function __construct()
    {
        if (!extension_loaded('memcache')) {
            throw new \Exception('The memcache extension must be loaded for using this model !');
        }
        $this->memcache = new \memcache;
        $this->memcache->addServer(self::DEFAULT_HOST);
    }

    public function getEngine()
    {
        return $this->memcache;
    }

    /**
     * Test if an item is available for the given id and (if yes) return it (false else)
     *
     * @param  string  $id                     Item id
     * @return mixed|false Cached datas
     */
    public function load($id)
    {
        return $this->memcache->get($id);
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
        if (!($result = $this->memcache->add($id, $data, $ttl))) {
            $result = $this->memcache->set($id, $data, $ttl);
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
        return $this->memcache->delete($id);
    }

    /**
     * Clean cached entries
     *
     * @return boolean True if ok
     */
    public function clean($type = null)
    {
        return $this->memcache->flush();
    }
}