<?php
/**
 * User module config
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       The Xoops Engine http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Module
 * @package         User
 * @version         $Id$
 */

//xoops_backtrace();
return array(
    'name'          => _USER_MI_NAME,
    'description'   => _USER_MI_DESC,
    'version'       => "1.0.0 Alpha",
    'email'         => "infomax@gmail.com",
    'author'        => "Taiwen Jiang <phppp@users.sourceforge.net>",
    'credits'       => "XOOPS Development Team; Zend Framework",
    'license'       => "GPL v2",
    'logo'          => "resources/images/logo.png",
    'readme'        => "docs/readme.txt",

    'onInstall'     => "User_Installer",
    'extensions'    => array(
        'database'  => array(
            'sqlfile'   => array(
                'mysql' => "sql/mysql.sql"),
            /*
            'tables'    => array(
                'user_log',
                'user_hash',
                'user_category',
                'user_meta_category',
            ),
            */
        ),
        'config'    => "config.php",
        'event'     => "event.php",
        'route'     => "route.ini",
        'navigation'    => "navigation.php",
        'search'    => array("callback" => "search::index"),
        'user'      => "user.php",
        'block'     => array(
            array(
                'name'         => "login",
                'file'          => "blocks.php",
                'title'         => _USER_MI_BLOCK_LOGIN,
                'description'   => _USER_MI_BLOCK_LOGIN_DESC,
                'show_func'     => "user_block_login",
                'template'      => 'login.html',
                'visible'       => 1,
                'access'        => array(
                    'guest'     => 1,
                    'member'    => 0,
                ),
            ),
            array(
                'name'         => "user",
                'file'          => "blocks.php",
                'title'         => _USER_MI_BLOCK_USER,
                'description'   => _USER_MI_BLOCK_USER_DESC,
                'show_func'     => "user_block_user",
                'template'      => 'user.html',
                'visible'       => 1,
                'cache'         => 'user',
                'access'        => array(
                    'guest'     => 0,
                    'member'    => 1,
                ),
            ),
            array(
                'name'          => "account",
                'file'          => "blocks.php",
                'title'         => _USER_MI_BLOCK_ACCOUNT,
                'description'   => _USER_MI_BLOCK_ACCOUNT_DESC,
                'show_func'     => "user_block_account",
                'template'      => 'account.html',
            ),
        ),
    )
);