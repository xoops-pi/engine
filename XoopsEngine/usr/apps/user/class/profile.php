<?php
/**
 * User custom profile
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

class Profile
{
    /*
    public static function listI($user)
    {
        $profileList = array(
            "message"   => array(
                "title" => XOOPS::_("User messages"),
                "link"  => XOOPS::registry("frontController")->getRouter()->assemble(
                    array(),
                    "profile",
                ),
            ),
            "admin" => array(
                "title" => XOOPS::_("Administration"),
                "link"  => XOOPS::registry("frontController")->getRouter()->assemble(
                    array(
                        "module"    => "system"
                    ),
                    "admin",
                ),
            ),
        );
        return $profileList;
    }
    */

    public static function gender($value)
    {
        switch ($value) {
            case "female":
                $value = \XOOPS::_("Female");
                break;
            case "male":
            default:
                $value = \XOOPS::_("Male");
                break;
        }

        return $value;
    }

    public static function birthday($value)
    {
        list($year, $month, $day) = explode("-", $value);
        $value = sprintf(\XOOPS::_("%s-%s-%s"), $year, $month, $day);
        return $value;
    }

    public static function rank($value)
    {
        $value = "Rank: {$value}";
        return $value;
    }

    public static function time($value)
    {
        $value = date("Y-m-d H:i:s", $value);
        return $value;
    }

    public static function signature($value)
    {
        $configs = \XOOPS::service("registry")->config->read("user");
        $format = $configs["signature"];
        switch ($format) {
            case "nohtml":
            default:
                $value = htmlspecialchars($value);
        }
        return $value;
    }

    public static function link($value)
    {
        if (false !== strpos($value, " ")) {
            list($value, $title) = explode(" ", $value, 2);
            if (!empty($title)) {
                $title = htmlspecialchars($title);
                if (!preg_match("/^http(s):\/\//", $value)) {
                    $value = "http://" . $value;
                }
                $value = "<a href='{$value}' title='{$title}' rel='external'>" . $title . "</a>";
            }
        }
        return $value;
    }

    public static function timezone($value)
    {
        $timezone = \Xoops_Zend_Locale::getTranslation($value, "CityToTimezone", "en");
        $value = empty($timezone) ? $value : $timezone;
        return $value;
    }

    /**
     * Get user's avatar full tag

     * @param string        string for raw avatar value
     * @param string        size of avatar image to be used, possible values: "" or "normal" or "n" - 80X80; "small" or "s" - icon size, 40X40; "mini" or "m" - 20X20; "original" or "o" - original size
     * @return string       Full img tag for avatar
     */
    public static function avatar($value, $size = "")
    {
        $value = Avatar::getTag($value, $size);
        return $value;
    }
}