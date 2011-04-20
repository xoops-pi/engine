<?php
/**
 * Search module config
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
 * @category        Xoops_Module
 * @package         Search
 * @version         $Id$
 */

return array(
    'name'          => _SEARCH_MI_NAME,
    'description'   => _SEARCH_MI_DESC,
    'version'       => "1.0.0 Alpha",
    'email'         => "infomax@gmail.com",
    'author'        => "Taiwen Jiang <phppp@users.sourceforge.net>",
    'credits'       => "XOOPS Development Team",
    'license'       => "GPL v2",
    'logo'          => "resources/images/logo.png",
    'readme'        => "docs/readme.txt",

    'extensions'    => array(
        'database'  => array(
            'sqlfile'   => array(
                'mysql' => "sql/mysql.sql"),
            'tables'    => array(
                'search_cache')),
        'config'    => array(
            "items"         => array(
                array(
                    'name'          => "expire",
                    'title'         => "_SEARCH_AM_EXPIRE",
                    'description'   => "_SEARCH_AM_EXPIRE_DESC",
                    'edit'          => "text",
                    'filter'        => "number_int",
                    'default'       => 60),
                array(
                    'name'          => "keyword_min",
                    'title'         => "_SEARCH_AM_KEYWORD_MIN",
                    'description'   => "_SEARCH_AM_KEYWORD_MIN_DESC",
                    'edit'          => "text",
                    'filter'        => "number_int",
                    'default'       => 5),
                array(
                    'name'          => "item_perpage",
                    'title'         => "_SEARCH_AM_ITEM_PERPAGE",
                    'description'   => "_SEARCH_AM_ITEM_PERPAGE_DESC",
                    'edit'          => "text",
                    'filter'        => "number_int",
                    'default'       => 20),
                array(
                    'name'          => "item_permodule",
                    'title'         => "_SEARCH_AM_ITEM_PERMODULE",
                    'description'   => "_SEARCH_AM_ITEM_PERMODULE_DESC",
                    'edit'          => "text",
                    'filter'        => "number_int",
                    'default'       => 5))),
        'route'     => "route.ini.php",
        'navigation'    => "navigation.php",
        'search'    => array("callback" => "demo::index"),
        'block'     => array(
            'search'  => array(
                'title'         => _SEARCH_MI_BLOCK,
                'description'   => _SEARCH_MI_BLOCK_DESC,
                'render'        => "block::render",
                'template'      => 'search.html')),

        ));
?>