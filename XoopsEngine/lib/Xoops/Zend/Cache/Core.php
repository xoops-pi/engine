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
 * @category        Xoops_Zend
 * @package         Cache
 * @version         $Id$
 */

class Xoops_Zend_Cache_Core extends Zend_Cache_Core
{
    public function __construct(array $options = array())
    {
        if (!array_key_exists('lifetime', $options)) $options['lifetime'] = null;
        if (!array_key_exists('automatic_serialization', $options)) $options['automatic_serialization'] = true;
        parent::__construct($options);
    }

    public function generateId($id, $level = "")
    {
        return Xoops_Zend_Cache::generateId($id, $level);
    }

    public function generateTag($level, $value = null)
    {
        return Xoops_Zend_Cache::generateTag($level, $value);
    }

    /**
     * Read if a cache is available for the given id and cache level, and (if yes) return it (false else)
     *
     * @param  string   $id     Cache id
     * @param  string   $level  Cache level, potentional values: user - data cached per user; role - cached per role; lang - cached per language
     * @return mixed|false Cached datas
     */
    public function read($id, $level = "")
    {
        $id = $this->generateId($id, $level);
        return $this->load($id);
    }

    /**
     * write some data in a cache
     *
     * @param  mixed    $data               Data to put in cache (can be another type than string if automatic_serialization is on)
     * @param  string   $id                 Cache id (if not set, the last cache id will be used)
     * @param  int      $specificLifetime   If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     * @param  string   $level              Cache level, potentional values: user - data cached per user; role - cached per role; lang - cached per language
     * @throws Zend_Cache_Exception
     * @return boolean True if no problem
     */
    public function write($data, $id = null, $specificLifetime = false, $level = "")
    {
        $id = $this->generateId($id, $level);
        $tags = array();
        if ($tag = $this->generateTag($level)) {
            $tags[] = $tag;
        }
        if ($controller = XOOPS::registry('frontController')) {
            if ($module = $controller->getRequest()->getModuleName()) {
                $tags[] = $this->generateTag("module", $module);
            }
        }
        $this->save($data, $id, $tags, $specificLifetime);
    }

    /**
     * Clean some module specific cache records
     *
     * Available modes are :
     * 'all' (default)  => remove all cache entries ($tags is not used)
     * 'old'            => remove too old cache entries ($tags is not used)
     * 'matchingTag'    => remove cache entries matching all given tags
     *                     ($tags can be an array of strings or a single string)
     * 'notMatchingTag' => remove cache entries not matching one of the given tags
     *                     ($tags can be an array of strings or a single string)
     * 'matchingAnyTag' => remove cache entries matching any given tags
     *                     ($tags can be an array of strings or a single string)
     *
     * @param string $mode clean mode
     * @param tags array $tags array of tags
     * @return boolean true if no problem
     */
    public function flush($mode = 'matchingAnyTag', $tags = array())
    {
        if ($controller = XOOPS::registry('frontController')) {
            if ($module = $controller->getRequest()->getModuleName()) {
                $tag = $this->generateTag("module", $module);
                $tags = is_array($tags) ? $tags : array($tags);
                if (!in_array($tag, $tags)) {
                    $tags[] = $tag;
                }
            }
        }
        if (empty($tags)) {
            return true;
        }
        return $this->_backend->clean($mode, $tags);
    }

    /**
     * Accesses backend object with magic methods.
     *
     * Be careful with performance
     *
     * @param string $name backend method name.
     * @param array $args The parameters for the method.
     * @return string The result of the method return.
     */
    public function __call($name, $args)
    {
        if (!is_callable(array($this->_backend, $name))) {
            return null;
        }
        return call_user_func_array(
            array($this->_backend, $name),
            $args
        );
    }

}
