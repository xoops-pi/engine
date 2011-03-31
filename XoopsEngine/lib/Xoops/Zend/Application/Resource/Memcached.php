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
 * @package         Application
 * @subpackage      Resource
 * @version         $Id$
 */

class Xoops_Zend_Application_Resource_Memcached
    extends Zend_Application_Resource_ResourceAbstract
{
    const DEFAULT_REGISTRY_KEY = 'memcached';

    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return null
     */
    public function init()
    {
        $options = $this->getOptions();
        //To use following Zend Memcache Class:
        $frontendOptions = array();
        $backendOptions = $options;
        // getting a Zend_Cache_Core object
        $cache = Xoops_Zend_Cache::factory('Core',
                                     'Memcached',
                                     $frontendOptions,
                                     $backendOptions);

        $key = (isset($options['registry_key']) && !is_numeric($options['registry_key']))
            ? $options['registry_key']
            : self::DEFAULT_REGISTRY_KEY;
        XOOPS::registry($key, $cache);
        XOOPS::registry("memcached_object", $cache->getBackend()->memcached);
    }
}
