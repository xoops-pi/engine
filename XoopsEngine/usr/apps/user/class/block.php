<?php
/**
 * User block class
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
 * @package         User
 * @version         $Id$
 */

namespace App\User;

class Block extends \Xoops\BlockRender
{
    static public function login($options = array(), $module = null)
    {
        $block = array();

        $configs = \XOOPS::service("registry")->config->read("user", "login");
        $configs = array_merge($configs, $options);
        if (!empty($configs["rememberme"])) {
            $block["rememberme"] = sprintf("Remember login status for %d days", $configs["rememberme"]);
        }
        $block["form"] = empty($configs["form"]) ? "blockLogin" : $configs["form"];
        $block["action"] = !empty($configs["action"]) ? $configs["action"] : \XOOPS::registry("view")->url(
            array(
                "module"        => "user",
                "controller"    => "login",
                "action"        => "process",
            ),
            "default"
        );
        $block["redirect"] = !empty($configs["redirect"]) ? $configs["redirect"] : \XOOPS::registry("frontController")->getRequest()->getRequestUri();
        return $block;
    }

    static public function user($options = array(), $module = null)
    {
        if (\XOOPS::registry('user')->isGuest()) {
            return false;
        }
        $block = array(
            "id"    => \XOOPS::registry('user')->id,
            "list"  => array(
                "profile"   => array(
                    "title" => \XOOPS::_("Profile details"),
                    "link"  => \XOOPS::registry("view")->url(
                        array(),
                        "profile"
                    ),
                ),
                "edit"      => array(
                    "title" => \XOOPS::_("Edit information"),
                    "link"  => \XOOPS::registry("view")->url(
                        array(
                            "module"        => "user",
                            "controller"    => "profile",
                            "action"        => "edit",
                        ),
                        "default"
                    ),
                ),
                "logout"    => array(
                    "title" => \XOOPS::_("Logout"),
                    "link"  => \XOOPS::registry("view")->url(
                        array(),
                        "logout"
                    ),
                ),
            ),
        );
        return $block;
    }

    static public function account($options = array(), $module = null)
    {
        $block = array();
        if ($id = \XOOPS::registry('user')->id) {
            $session = \Xoops::service("session")->account;
            $avatar =& $session->avatar;
            if (is_null($avatar)) {
                $size = isset($options["size"]) ? $options["size"] : "s";
                $avatar = Avatar::getTag($id, $size);
            }
            $block['user'] = array(
                "avatar"    => $avatar,
                "name"      => \XOOPS::registry('user')->name ?: \XOOPS::registry('user')->identity,
                "link"      => \XOOPS::registry('view')->url(array(), "profile"),
            );
            $block['link_logout'] = \XOOPS::registry('view')->url(array(), "logout");
        } else {
            $block['link_login'] = \XOOPS::registry('view')->url(array(), "login");
            $block['link_register'] = \XOOPS::registry('view')->url(array(), "register");
        }
        return $block;
    }
}