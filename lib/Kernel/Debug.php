<?php
/**
 * Kernel debug
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
 * @package         Kernel
 * @since           3.0
 * @version         $Id$
 */

class Debug
{
    public static function e($data)
    {
        echo static::render($data);
    }

    public static function _($data)
    {
        return static::render($data);
    }

    public static function display($data)
    {
        echo static::render($data);
    }

    /**
     * Renders a variable or an object
     */
    public static function render($data)
    {
        $location = '';
        $list = debug_backtrace();
        foreach ($list as $item) {
            if ($item['file'] === __FILE__) continue;
            $location .= basename($item['file']) . ':' . $item['line'];
            break;
        }

        if (PHP_SAPI === 'cli') {
            $result = PHP_EOL;
            if (is_array($data) || is_object($data)) {
                $result .= $location;
                $result .= PHP_EOL;
                $result .= print_r($data, true);
                $result .= PHP_EOL;
            } else {
                $result .= $data;
                $result .= ' [' . $location . ']';
            }
            $result .= PHP_EOL;
        } else {
            $result = '<div style="padding: .8em; margin-bottom: 1em; border: 2px solid #ddd;">';
            if (is_array($data) || is_object($data)) {
                $result .= $location;
                $result .= "<div><pre>";
                $result .= print_r($data, true);
                $result .= "</pre></div>";
            } else {
                $result .= "<div>{$data} [{$location}]</div>";
            }
            $result .= '</div>';
        }

        return $result;
    }

    /**
     * Displays formatted backtrace information
     */
    public static function backtrace($display = true)
    {
        $list = debug_backtrace();
        $list = array_reverse($list);

        if (PHP_SAPI === 'cli') {
            $bt = PHP_EOL;
            $bt .= "Backtrace at: " . microtime(true) . PHP_EOL . PHP_EOL;
            foreach ($list as $backtrace) {
                $bt .= (empty($backtrace['file']) ? "Internal" : $backtrace['file'] . '(' . $backtrace['line'] . ')') . ': ' . (empty($backtrace['class']) ? "" : $backtrace['class'] . '::') . $backtrace['function'] . PHP_EOL;
            }
            $bt .= PHP_EOL;
        } else {
            $bt = "<pre>";
            $bt .= "<strong>Backtrace at: " . microtime(true) . "</strong><ul>";
            foreach ($list as $backtrace) {
                $bt .= "<li>" . (empty($backtrace['file']) ? "Internal" : $backtrace['file'] . '(' . $backtrace['line'] . ')') . ': ' . (empty($backtrace['class']) ? "" : $backtrace['class'] . '::') . $backtrace['function'] . "</li>";
            }
            $bt .= "</ul>";
            $bt .= "</pre>";
        }

        if ($display) {
            echo $bt;
        } else {
            return $bt;
        }
    }

    /**
     * Debug helper function.  This is a wrapper for var_dump() that adds
     * the <pre /> tags, cleans up newlines and indents, and runs
     * htmlentities() before output.
     *
     * From Zend_Debug
     *
     * @param  mixed  $var   The variable to dump.
     * @param  bool   $echo  OPTIONAL Echo output if true.
     * @return string
     */
    public static function dump($var, $echo = true)
    {
        // var_dump the variable into a buffer and keep the output
        ob_start();
        var_dump($var);
        $output = ob_get_clean();

        // neaten the newlines and indents
        $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
        if (PHP_SAPI === 'cli') {
            $output = PHP_EOL . $output . PHP_EOL;
        } else {
            if(!extension_loaded('xdebug')) {
                $output = htmlspecialchars($output, ENT_QUOTES);
            }
            $output = '<pre>' . $output . '</pre>';
        }

        if ($echo) {
            echo($output);
        }
        return $output;
    }
}