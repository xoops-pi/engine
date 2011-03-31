<?php
/**
 * Search block functions
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

function search_block_show($options = array())
{
    $block = array();
    $block["form"] = isset($options["form"]) ? $options["form"] : uniqid("xoops-search-");
    if (isset($options["action"])) {
        $action = $options["action"];
    } else {
        $action = XOOPS::registry("view")->url(
            array(
                "module"        => "search",
                "controller"    => "index",
                "action"        => "index",
            ),
            "default"
        );
    }
    $block["action"] = $action;
    $block["method"] = isset($options["method"]) ? $options["method"] : "GET";
    if (!isset($options["advanced"]) || !empty($options["advanced"])) {
        $block["advanced"] = XOOPS::registry("view")->url(
            array(
                "module"        => "search",
                "controller"    => "index",
                "action"        => "index",
            ),
            "default"
        );
    }

    return $block;

    $string = "<form action='{$action}' method='{$method}'>"
                . "<input type='text' name='query' id='search-q' size='15' />"
                . "<button type='submit' value='" . XOOPS::_("Search") . "'>" . XOOPS::_("Search") . "</button>"
                . "</form>";
    return $string;
}