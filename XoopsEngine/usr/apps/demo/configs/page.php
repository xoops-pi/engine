<?php
/**
 * Demo module page config
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
 * @package         Demo
 * @version         $Id$
 */

return array(
    // Front section
    "front" => array(
        array(
            'cache_expire'  => 0,
            'cache_level'   => "locale",
            'title'         => _DEMO_MI_PAGE_HOME,
            'controller'    => "index",
            'action'        => "index",
        ),
        array(
            'cache_expire'  => 0,
            'cache_level'   => "locale",
            'title'         => _DEMO_MI_PAGE_DEFAULT,
            'controller'    => "index",
        ),
    ),
    // Feed section
    "feed" => array(
        array(
            'cache_expire'  => 0,
            'cache_level'   => "",
            'title'         => _DEMO_MI_PAGE_FEED,
        ),
        array(
            'cache_expire'  => 0,
            'cache_level'   => "",
            'title'         => _DEMO_MI_PAGE_FEED_TEST,
            'controller'    => "index",
            'action'        => "test",
        ),
        array(
            'cache_expire'  => 0,
            'cache_level'   => "",
            'title'         => _DEMO_MI_PAGE_FEED_TRY,
            'controller'    => "try",
        ),
    ),
);
