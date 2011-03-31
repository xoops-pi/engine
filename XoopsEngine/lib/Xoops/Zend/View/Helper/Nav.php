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
 * @package         View
 * @version         $Id$
 */

/**
 * Renders navigation
 *
 * <code>
 *
 * </code>
 */
class Xoops_Zend_View_Helper_Nav extends Zend_View_Helper_Abstract
{
    /**
     * Generates content from a named block
     *
     * @access public
     *
     * @param  string   $name       navigation name
     * @param  array    $options    options passed to the navigation:
     *                                  template
     *                                  ul_class
     *                                  menu
     *                                  breadcrumbs
     *                                  cache_id
     *                                  cache_level
     *                                  cache_expire
     * @return array    contents associated with proxy list
     */
    public function nav($name, $options = array())
    {
        if (empty($name)) return false;
        $template = empty($options["template"]) ? 'navigation.html' : $options["template"];
        $cache = array();

        if (isset($options['cache_expire'])) {
            $cache['key']       = "nav_{$name}" . (empty($options['cache_id']) ? '' : '_' . $options['cache_id']);
            $cache['handler']   = Xoops::registry('cache') ?: Xoops::persist()->getHandler();
            $cache['expire']    = empty($options["cache_expire"]) ? 86400 : $options["cache_expire"];
            $navigation         = $cache['handler']->read($cache['key']);
        }

        if (empty($navigation)) {
            $request = XOOPS::registry("frontController")->getRequest();
            $module = $request->getModuleName();
            $config = XOOPS::service("registry")->navigation->read($name, $module);
            $container = new Xoops_Zend_Navigation($config);
            $this->view->navigation($container);
            $navigation = array();
            if (!isset($options['menu']) || false !== $options['menu']) {
                $ulClass = empty($options["ul_class"]) ? 'jd_menu' : $options["ul_class"];
                $navigation['menu'] = $this->view->navigation()->menu()->setUlClass($ulClass)->render();
            }
            if (!isset($options['breadcrumbs']) || false !== $options['breadcrumbs']) {
                $navigation['breadcrumbs'] = $this->view->navigation()->breadcrumbs()->setMinDepth(0)->setLinkLast(false)->render();
            }
            if (!empty($cache)) {
                $cacheHandler->write($navigation, $cache['key'], $cache['expire']);
            }
        } else {
            XOOPS::service('logger')->log("Navigation is cached", 'debug');
        }

        $content = $this->view->getEngine()->fetch($template, null, null, array('navigation' => $navigation));
        return $content;
    }
}