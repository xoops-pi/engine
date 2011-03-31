<?php
/**
 * XOOPS page registry
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

class Page extends \Kernel\Registry
{
    //protected $registry_key = "registry_page";

    protected function loadDynamic($options = array())
    {
        $model = \Xoops::getModel('page');
        $select = $model->select()
                        ->where('section = ?', $options['section'])
                        ->where('module = ?', (string) $options['module']);
        $pageList = $model->fetchAll($select);
        $pages = array();
        foreach ($pageList as $page) {
            list($module, $controller, $action) = array($page['module'], $page['controller'], $page['action']);
            $key = $page['module'];
            if (!empty($page['controller'])) {
                $key .= "-" . $page['controller'];
                if (!empty($page['action'])) {
                    $key .= "-" . $page['action'];
                }
            }
            $pages[$key] = $page["id"];
        }
        return $pages;
    }

    public function read($section, $module = null)
    {
        $options = compact('section', 'module');
        return $this->loadData($options);
    }

    public function create($section, $module = null)
    {
        self::delete($section, $module);
        self::read($section, $module);
        return true;
    }

    public function delete($section, $module = null)
    {
        $options = compact('section', 'module');
        return $this->cache->clean('matchingTag', self::createTags($options));
    }

    public function flush($module = null)
    {
        \Xoops::service("registry")->cache->flush($module);
        \Xoops::service("registry")->block->flush($module);
        \Xoops::service("registry")->resource->flush($module);
        return self::delete(null, $module);
    }
}