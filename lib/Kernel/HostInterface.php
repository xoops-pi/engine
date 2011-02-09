<?php
/**
 * Kernel host handler interface
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

interface HostInterface
{
    /**
     * Set host data
     *
     * @param  array    $hostVars
     * @return void
     */
    public function setHosts($hostVars = null);

    /**
     * Convert a path to a physical one
     *
     * @param string    $url        path:
     *                                  with URI scheme "://" - absolute URI, do not convert
     *                                  with reserved section separator ":" - do not convert, usually for paths in tempalte engine
     *                                  with leading slash "/" - absolute path, do not convert
     *                                  w/o "/" - relative path, will be translated
     * @param bool      $virtual    whether convert to full URI
     */
    public function path($url, $virtual = false);

    /**
     * Convert a path to an URL
     *
     * @param string    $url        url to be converted: with leading slash "/" - absolute path, do not convert; w/o "/" - relative path, will be translated
     * @param bool      $absolute   whether convert to full URI; relative URI is used by default, i.e. no hostname
     */
    public function url($url, $absolute = false);

    /**
     * Build an URL with the specified request params
     */
    public function buildUrl($url, $params = array());

    /**
     * Build URL
     *
     * @param   array   $params
     * @param   string  $route  route name
     * @param   bool    $reset  Whether or not to reset the route defaults with those provided
     * @return  string  assembled URI
     */
    public function assembleUrl($params = array(), $route = 'legacy', $reset = true, $encode = true);

    /**
     * Build application URL
     *
     * @param   array   $params
     * @param   string  $route  route name
     * @param   bool    $reset  Whether or not to reset the route defaults with those provided
     * @return  string  assembled URI
     */
    public function appUrl($params = array(), $route = 'default', $reset = true, $encode = true);

    /**
     * Build URL mapping a locale resource
     *
     * @param   string  $domain     domain name, potential values: "", moduleName, theme:default, etc.
     * @param   string  $path       path to locale resource
     * @return  string  assembled URI
     */
    public function localeUrl($domain = "", $path = "");

    public function get($var);
    public function set($var, $value = null);
}