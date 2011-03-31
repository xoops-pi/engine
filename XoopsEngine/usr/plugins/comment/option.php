<?php
/**
 * Plugin options
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
 * @category        Xoops_Plugin
 * @package         Comment
 * @version         $Id$
 */

return array(
    array(
        'name'          => "items_perpage",
        'title'         => "_PLUGIN_COMMENT_ITEMS_PERPAGE",
        'description'   => "_PLUGIN_COMMENT_ITEMS_PERPAGE_DESC",
        'edit'          => "text",
        'filter'        => "number_int",
        'default'       => 20),
    array(
        'name'          => "page_param",
        'title'         => "_PLUGIN_COMMENT_PAGE_PARAM",
        'description'   => "_PLUGIN_COMMENT_PAGE_PARAM_DESC",
        'edit'          => "text",
        'default'       => "cpage"),
    array(
        'name'          => "cache_expire",
        'title'         => "_PLUGIN_COMMENT_CACHE_EXPIRE",
        'description'   => "_PLUGIN_COMMENT_CACHE_EXPIRE_DESC",
        'edit'          => "CacheExpire",
        'default'       => 0,
        'filter'        => "number_int"),
    array(
        'name'          => "display_order",
        'title'         => "_PLUGIN_COMMENT_DISPLAY_ORDER",
        'description'   => "",
        'edit'          => "select",
        'filter'        => "string",
        'default'       => "desc",
        'options'       => array(
                            "_PLUGIN_COMMENT_DISPLAY_ORDER_DESC"    => "desc",
                            "_PLUGIN_COMMENT_DISPLAY_ORDER_ASC"     => "asc")),
    array(
        'name'          => "post_approval",
        'title'         => "_PLUGIN_COMMENT_POST_APPROVAL",
        'description'   => "_PLUGIN_COMMENT_POST_APPROVAL_DESC",
        'edit'          => "yesno",
        'filter'        => "boolean",
        'default'       => false),
    array(
        'name'          => "allow_anonymous",
        'title'         => "_PLUGIN_COMMENT_ALLOW_ANONYMOUS",
        'description'   => "_PLUGIN_COMMENT_ALLOW_ANONYMOUS_DESC",
        'edit'          => "yesno",
        'filter'        => "boolean",
        'default'       => false),
);