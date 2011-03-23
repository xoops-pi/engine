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
 * @copyright       Xoops Engine http://www.xoopsengine.org
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         Xoops_Core
 * @subpackage      Security
 * @version         $Id$
 */

namespace Xoops;

class Security
{
    public static function deny($message = "")
    {
        if (substr(PHP_SAPI, 0, 3) == 'cgi') {
            header("Status: 403 Forbidden");
        } else {
            header("HTTP/1.1 403 Forbidden");
        }
        exit("Access denied" . ($message ? ": " . $message : "."));
    }

    public static function escape($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, \Xoops::config('charset'));
    }

    /**#@++
     * Check security settings
     *
     * Policy: Returns TRUE will cause process quite and the current request will be approved; returns FALSE will cause process quit and request will be denied
     */

    /**
     * Check for IPs
     */
    public static function ip($options = null)
    {
        $clientIp = array();
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $clientIp[] = $_SERVER['REMOTE_ADDR'];
        }

        // Find out IP behind proxy
        if (!empty($options['checkProxy'])) {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $clientIp[] = $_SERVER['HTTP_CLIENT_IP'];
            }
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $clientIp[] = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        }
        $clientIp = array_unique($clientIp);

        // Check upon bad IPs
        if (!empty($options['bad'])) {
            $pattern = is_array($options['bad']) ? implode("|", $options['bad']) : $options['bad'];
            foreach ($clientIp as $ip) {
                if (preg_match("/" . $pattern . "/", $ip)) {
                    return false;
                }
            }
        }

        // Check upon good IPs
        if (!empty($options['good'])) {
            $pattern = is_array($options['good']) ? implode("|", $options['good']) : $options['good'];
            foreach ($clientIp as $ip) {
                if (preg_match("/" . $pattern . "/", $ip)) {
                    return true;
                }
            }
        }

        return null;
    }

    /**
     * Check for super globals
     */
    public static function globals($options = null)
    {
        $items = is_array($options) ? $options : explode(",", $options);
        array_walk($items, 'trim');
        $items = array_filter($items);
        foreach ($items as $item) {
            if (isset($_REQUEST[$item])) {
                return false;
            }
        }

        return null;
    }

    public static function __callStatic($method, $args = null)
    {
        $class = "Xoops\\Security\\" . ucfirst($method);
        if (class_exists($class) && is_subclass_of($class, 'Xoops\\Security\\AbstractSecurity')) {
            $options = $args[0];
            return $class::check($options);
        }
        return null;
    }
    /*#@-*/
}