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

class Xoops_Zend_Application_Resource_Legacy extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return null
     */
    public function init()
    {
        $bootstrap = $this->getBootstrap();
        $bootstrap->bootstrap('db');
        $bootstrap->bootstrap('config');

        define("XOOPS_GROUP_ADMIN", "1");
        define("XOOPS_GROUP_USERS", "2");
        define("XOOPS_GROUP_ANONYMOUS", "3");

        define("XOOPS_MATCH_START", 0);
        define("XOOPS_MATCH_END", 1);
        define("XOOPS_MATCH_EQUAL", 2);
        define("XOOPS_MATCH_CONTAIN", 3);

        define("XOOPS_URL", XOOPS::url('www'));
        define("XOOPS_ROOT_PATH", XOOPS::path('www'));
        define("XOOPS_VAR_PATH", XOOPS::path('var'));
        define("XOOPS_THEME_PATH", XOOPS::path('www') . '/themes');
        define("XOOPS_THEME_URL", XOOPS::url('www') . '/themes');
        define("XOOPS_UPLOAD_PATH", XOOPS::path('www') . '/uploads');
        define("XOOPS_UPLOAD_URL", XOOPS::url('www') . '/uploads');
        define("XOOPS_COMPILE_PATH", XOOPS::path('var') . '/cache/smarty/compile');
        define("XOOPS_CACHE_PATH", XOOPS::path('var') . '/cache/system');

        $GLOBALS['xoops'] = new Legacy_Xoops();

        require XOOPS::path("www") . "/kernel/object.php";
        require XOOPS::path("www") . "/class/criteria.php";
        require XOOPS::path("www") . "/class/module.textsanitizer.php";
        require XOOPS::path("www") . "/include/functions.php";
        require XOOPS::path("www") . "/include/version.php";
        require XOOPS::path("www") . "/class/xoopssecurity.php";
        $GLOBALS['xoopsSecurity'] = new XoopsSecurity();

        $GLOBALS['xoopsDB'] = new Legacy_Db(Xoops::registry('db'));
        $GLOBALS['member_handler'] = XOOPS::getHandler('member');
        $GLOBALS['xoopsLogger'] = XOOPS::service('logger');
        $GLOBALS['xoopsConfig'] = XOOPS::config();

        // Legacy config items ...
        $GLOBALS['xoopsConfig']['anonymous'] = 'Anonymous';
        $GLOBALS['xoopsConfig']['theme_set_allowed'] = array();
    }
}