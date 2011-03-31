<?php
/**
 * XOOPS page cache registry
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
 * @package         Xoops_Core
 * @subpackage      Registry
 * @version         $Id$
 */

namespace Engine\Xoops\Registry;

class Cache extends \Kernel\Registry
{
    //protected $registry_key = "registry_cache";

    protected function loadDynamic($options = array())
    {
        $modelPage = \Xoops::getModel('page');
        $select = $modelPage->select();
        $select->where('section = ?', $options['section']);
        $select->where('module = ?', $options['module']);
        $select->where('cache_expire >= 0');
        $cacheList = $modelPage->fetchAll($select);
        $caches = array();
        foreach ($cacheList as $cache) {
            $key = $cache['module'];
            if (!empty($cache['controller'])) {
                $key .= "-" . $cache['controller'];
                if (!empty($cache['action'])) {
                    $key .= "-" . $cache['action'];
                }
            }
            $caches[$key] = array('expire' => $cache['cache_expire'], 'level' => $cache['cache_level']);
        }

        return $caches;
    }

    public function read($section, $module)
    {
        $options = compact('section', 'module');
        return $this->loadData($options);
    }

    public function create($section, $module)
    {
        $options = compact('section', 'module');
        self::read($module, $section);
        return true;
    }

    public function delete($section = null, $module = null)
    {
        $options = compact('section', 'module');
        return $this->cache->clean('matchingTag', self::createTags($options));
    }

    public function flush($module = null)
    {
        return self::delete(null, $module);
    }
}