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
 * @category        Xoops_Zend
 * @package         Log
 * @version         $Id$
 */

class Xoops_Zend_Log_Writer_Debugger extends Zend_Log_Writer_Abstract
{
    private $items = array();

    /**
     * Class Constructor
     */
    public function __construct()
    {
        $this->_formatter = new Xoops_Zend_Log_Formatter_Debugger();
    }

    /**
     * Create a new instance of Xoops_Zend_Log_Writer_Debugger
     *
     * @param  array|Zend_Config $config
     * @return Zend_Log_Writer_Null
     * @throws Zend_Log_Exception
     */
    static public function factory($config)
    {
        return new self();
    }

    /**
     * Write a message to the log.
     *
     * @param  array  $event  event data
     * @return void
     */
    protected function _write($event)
    {
        list($category, $line) = $this->_formatter->format($event);
        $this->items[$category][] = $line;
    }

    private function systemLog()
    {
        // Calculates memory consumption
        $memory = 0;
        if (function_exists('memory_get_usage')) {
            $memory = memory_get_usage() . ' bytes';
        } else {
            $os = isset($_ENV['OS']) ? $_ENV['OS'] : $_SERVER['OS'];
            // Windows system
            if (strpos(strtolower($os), 'windows') !== false) {
                $out = array();
                exec('tasklist /FI "PID eq ' . getmypid() . '" /FO LIST', $out);
                $memory = substr($out[5], strpos($out[5], ':') + 1) . ' [Estimated]';
            }
        }

        $system = array();
        $files_included = get_included_files();
        $system['Included files'] = count ($files_included) . ' files';
        if ($memory) {
            $system['Memory usage'] = $memory;
        }
        $system['PHP Version'] = phpversion();
        if (isset($GLOBALS['xoopsDB']) && method_exists($GLOBALS['xoopsDB']->conn, "getServerVersion")) {
            $system['MySQL Version'] = $GLOBALS['xoopsDB']->conn->getServerVersion();
        } else {
            //$system['MySQL Version'] = mysql_get_server_info();
            $system['MySQL Version'] = "Not detected";
        }
        //defined('XOOPS_VERSION') OR include XOOPS::path('www/include/version.php');
        $system['Xoops Version'] = Xoops::version();
        $system['Zend Version'] = Zend_Version::VERSION;
        if (XOOPS::registry("module")) {
            $system['Module Version'] = XOOPS::registry("module")->version;
        }
        if (XOOPS::registry("view")) {
            $system['Smarty Version'] = XOOPS::registry("view")->getEngine()->getVersion();
        }
        $system['safe_mode'] = ini_get('safe_mode') ? "On" : "Off";
        $system['register_globals'] = ini_get('register_globals') ? "On" : "Off";
        //$system['xml'] = extension_loaded('xml') ? "On" : "Off";
        //$system['mbstring'] = extension_loaded('mbstring') ? "On" : "Off";

        $sys_info = "";
        foreach ($system as $key => $val) {
            $sys_info .= "<div style='clear: both;'><span class='label'>{$key}:</span><span class='text'>{$val}</span></div>";
        }

        $this->_write(array("category" => "extra", "message" => $sys_info, "priorityName" => "info", "timestamp" => ""));
    }

    public function render()
    {
        $this->systemLog();

        $log = '';
        $log .= "\n<div id=\"xo-logger-output\">\n<div id='xo-logger-tabs'>\n";
        foreach (array_keys($this->items) as $category) {
            $count = count($this->items[$category]);
            $log .= "<span id='xo-logger-tab-{$category}'><a href='javascript:xoSwitchCategoryDisplay(\"{$category}\")'>{$category}({$count})</a></span> | \n";
        }
        $log .= "<span id='xo-logger-tab-all'><a href='javascript:xoSwitchCategoryDisplay(\"all\")'>all</a></span>\n";
        $log .= "</div>\n";

        $log .= "<div id='xo-logger-categories'>\n";
        foreach ($this->items as $category => $events) {
            $log .= "<div id='xo-logger-category-{$category}' class=\"xo-events\">\n";
            $log .= "<div class=\"xo-category\">{$category}</div>\n";
            $log .= implode("", $events);
            $log .= "</div>\n";
        }

        $log .= "</div>\n</div>\n";

        $scripts_css =
<<<EOT
        <style type="text/css">
            #xo-logger-output {
                font-family: monospace;
                padding: 10px;
            }

            #xo-logger-output #xo-logger-tabs {
                border-top: 1px solid;
            }

            #xo-logger-output #xo-logger-categories {
                display: block;
            }

            #xo-logger-output a,
            #xo-logger-output a:visited {
                font-weight: normal;
                color: inherit;
            }

            #xo-logger-output div.xo-events {
                clear: both;
            }

            #xo-logger-output div.xo-category {
                font-weight: bold;
                padding: 10px 0 5px 0;
            }

            #xo-logger-output div.xo-event {
                clear: both;
            }

            #xo-logger-output div.xo-event .time {
                font-weight: bold;
            }

            #xo-logger-output div.xo-event .message {
                margin-left: 50px;
                font-weight: normal;
            }

            #xo-logger-output #xo-logger-errors .xo-event .message {
                color: red;
            }

            #xo-logger-output .xo-event .error,
            #xo-logger-output .xo-event .err {
                color: #FF0000;
                font-weight: bold;
            }

            #xo-logger-output .xo-event .exception {
                color: #FF0000;
            }

            #xo-logger-output .xo-event .warning,
            #xo-logger-output .xo-event .warn {
                color: #D2691E;
            }

            #xo-logger-output .xo-event .notice {
                color: #A0522D;
            }

            #xo-logger-output .xo-event .message span {
                padding-left: 5px;
            }

            #xo-logger-output .xo-event .message .label {
                width: 150px;
                text-align: right;
                float: left;
                font-weight: bold;
                padding: 2px 5px;
            }

            #xo-logger-output .xo-event .message .text {
                display: block;
                float: left;
                padding: 2px 5px;
            }
        </style>
EOT;

        $scripts_js = '<script type="text/javascript">var cookie_path = "' . (($baseUrl = XOOPS::host()->get('baseUrl')) ? rtrim($baseUrl, "/") . "/" : "/") . '";</script>' .
<<<EOT
        <script type="text/javascript">
            var cookieName = "XoopsLoggerView";
            function xoLogCreateCookie(name,value) {
                value = value ? "+" : "-";
                document.cookie = cookieName+"=["+name+value+"]; path=" + cookie_path;
            }
            function xoLogReadCookie() {
                var ret = new Array("", 0);
                var nameEQ = cookieName + "=";
                var ca = document.cookie.split(';');
                for(var i=0;i < ca.length;i++) {
                    var c = ca[i];
                    while (c.charAt(0)==' ') c = c.substring(1,c.length);
                    if (c.indexOf(nameEQ) == 0) {
                        var valid = c.substring(c.indexOf("[")+1, c.indexOf("]"));
                        ret[0] = valid.substring(0,valid.length-1);
                        var str = valid.substring(valid.length-1,valid.length);
                        ret[1] = (str == "+") ? 1 : 0;
                        return ret;
                    }
                }
                return ret;
            }

            function xoSwitchCategoryDisplay(name) {
                var data = xoLogReadCookie();
                var loggerview  = (name == data[0]) ? (data[1] ? 0 : 1) : 1;
                return xoSetCategoryDisplay(name, loggerview);
            }

            function xoSetCategoryDisplay(name, loggerview) {
                var log = document.getElementById("xo-logger-categories");
                if (!log) return;
                var old = xoLogReadCookie();
                var oldElt = document.getElementById("xo-logger-tab-" + old[0]);
                if (oldElt) {
                    oldElt.style.textDecoration = "none";
                }
                var i, elt;
                for (i=0; i!=log.childNodes.length; i++) {
                    elt = log.childNodes[i];
                    if (!elt.tagName || elt.tagName.toLowerCase() != 'div' || !elt.id) continue;
                    var elestyle = elt.style;
                    if (name == 'all' || elt.id == "xo-logger-category-" + name) {
                        if (loggerview) {
                            elestyle.display = "block";
                            document.getElementById("xo-logger-tab-" + name).style.textDecoration = "underline";
                        } else {
                            elestyle.display = "none";
                            document.getElementById("xo-logger-tab-" + name).style.textDecoration = "none";
                        }
                    } else {
                        elestyle.display = "none";
                    }
                }
                log.style.display = "block";
                xoLogCreateCookie(name, loggerview);
            }

            function xoSetLoggerView(data) {
                return xoSetCategoryDisplay(data[0], data[1]);
            }

            function xoSwitchElementDisplay(id) {
                var elestyle = document.getElementById(id).style;
                if (elestyle.display == "none") {
                    elestyle.display = "block";
                } else {
                    elestyle.display = "none";
                }
            }
            // set logger output view
            xoSetLoggerView(xoLogReadCookie());
        </script>

EOT;

        echo $scripts_css . $log . $scripts_js;
    }
}