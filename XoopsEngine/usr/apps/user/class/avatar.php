<?php
/**
 * User avatar gateway
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

class Avatar
{
    /**
     * Get avatar file from user ID
     *
     * @return array
     */
    public static function getFile($id)
    {
        $model = \XOOPS::getModel("user_profile");
        if (!$row = $model->findRow($id)) {
            return false;
        }

        return $row->avatar;
    }

    /**
     * Get user's avatar full tag

     * @param int|string    string for raw avatar value from database or user ID
     * @param string        size of avatar image to be used, possible values: "" or "normal" or "n" - 80X80; "small" or "s" - icon size, 40X40; "mini" or "m" - 20X20; "original" or "o" - original size
     * @return string       Full img tag for avatar
     */
    public static function getTag($value, $size = "")
    {
        if (is_integer($value)) {
            $value = static::getFile($value);
        }
        if (false === $value) {
            return false;
        }

        $sizeMap = array(
            "m" => array(
                "width"     => 20,
                "height"    => 20,
            ),
            "s" => array(
                "width"     => 40,
                "height"    => 40,
            ),
            "n" => array(
                "width"     => 80,
                "height"    => 80,
            ),
        );
        if (false === ($pos = strpos($value, "/"))) {
            $path = "img/avatar/avatar.jpg";
        } else {
            $path = substr($value, 0, $pos) . "/avatar/" . substr($value, $pos);
        }
        switch ($size) {
            case "m":
            case "mini":
                $size_string = "width='" . $sizeMap['m']["width"]. "px' height='" . $sizeMap['m']["height"]. "px'";
                break;
            case "s":
            case "small":
                $size_string = "width='" . $sizeMap['s']["width"]. "px' height='" . $sizeMap['s']["height"]. "px'";
                break;
            case "n":
            case "normal":
                $size_string = "width='" . $sizeMap['n']["width"]. "px' height='" . $sizeMap['n']["height"]. "px'";
                break;
            default:
                $size_string = "";
                break;
        }
        $value = '<img src="' . \XOOPS::url($path) . '" ' . $size_string. ' alt="" title="" />';
        return $value;
    }
}