<?php
/**
 * Xoops Engine Setup Controller
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
 * @credits         Skalpa Keo <skalpa@xoops.org>
 * @since           3.0
 * @package         Setup
 * @version         $Id$
 */

namespace Xoops\Setup\Controller;

class Directive extends AbstractController
{
    protected $pathController;

    public function init()
    {
        @set_time_limit(0);
        //$this->pathController = new \Xoops\Setup\PathController($this->wizard);
        //$this->pathController->init();
    }

    protected function loadForm()
    {
        $this->hasForm = true;
        $this->loadPathForm();
        $this->loadPersistForm();
    }

    public function indexAction()
    {
        $this->loadForm();
    }

    public function persistAction()
    {
        $persist = $this->request->getParam('persist');
        $this->wizard->setPersist('persist', $persist);
    }

    public function pathAction()
    {
        $this->pathController = new \Xoops\Setup\PathController($this->wizard);
        $this->pathController->init();

        $path = $this->request->getParam('var');
        $val = htmlspecialchars(trim($this->request->getParam('path')));
        $this->pathController->setPath($path, $val);
        list($type, $key) = explode("_", $path, 2);
        if ($type == "url") {
            $status = $this->pathController->checkUrl($key);
        } else {
            $status = $this->pathController->checkPath($key);
        }
        echo $status;
        return;
    }

    public function messageAction()
    {
        $this->pathController = new \Xoops\Setup\PathController($this->wizard);
        $this->pathController->init();

        $path = $this->request->getParam('var');
        $val = htmlspecialchars(trim($this->request->getParam('path')));
        $this->pathController->setPath($path, $val);
        list($type, $key) = explode("_", $path, 2);
        if ($type == "path") {
            $messages = $this->pathController->checkSub($key);
        }
        $messageString = "";
        if (!empty($messages)) {
            $messageString = "<ul>";
            foreach (array_keys($messages) as $key) {
                $messageString .= "<li>" . sprintf(_INSTALL_IS_NOT_WRITABLE, $key) . "</li>";
            }
            $messageString .= "</ul>";
        }
        echo $messageString;
    }

    public function submitAction()
    {
        /*
        $this->pathController = new \Xoops\Setup\PathController($this->wizard);
        $this->pathController->init();

        $isValid = $this->pathController->validate();
        if (!$isValid) {
            $this->status = -1;
            $this->loadForm();
            return;
        }
        */

        $wizard = $this->wizard;
        $errorsSave = array();
        $errorsConfig = array();
        $configs = array();

        $vars = $wizard->getPersist('paths');

        // boot.php
        $persist_type = strtolower($wizard->getPersist('persist') ?: "");
        $persist_prefix = 'x' . substr(md5($vars["www"]["url"]), 0, 4);
        $file_bootfile = $vars["www"]["path"] . '/boot.php';
        $file_bootfile_dist = $wizard->getRoot() . '/include/boot.php.dist';
        $content_bootfile = file_get_contents($file_bootfile_dist);
        $content_bootfile = preg_replace("/(define\()([\"'])(XOOPS_PATH)\\2,\s*([\"'])(.*?)\\4\s*\)/", "define('XOOPS_PATH', '" . $vars['lib']['path'] . "')", $content_bootfile);
        $content_bootfile = preg_replace("/(define\()([\"'])(XOOPS_PERSIST_TYPE)\\2,\s*([\"'])(.*?)\\4\s*\)/", "define('XOOPS_PERSIST_TYPE', '" . $persist_type . "')", $content_bootfile);
        $content_bootfile = preg_replace("/(define\()([\"'])(XOOPS_PERSIST_PREFIX)\\2,\s*([\"'])(.*?)\\4\s*\)/", "define('XOOPS_PERSIST_PREFIX', '" . $persist_prefix . "')", $content_bootfile);
        $configs[] = array("file" => $file_bootfile, "content" => $content_bootfile);

        // .htaccess
        $file_htaccess = $vars["www"]["path"] . '/.htaccess';
        $file_htaccess_dist = $wizard->getRoot() . '/include/.htaccess.dist';
        if (!file_exists($file_htaccess_dist)) {
            die("not found $file_htaccess_dist");
        }
        $content_htaccess = file_get_contents($file_htaccess_dist);
        $configs[] = array("file" => $file_htaccess, "content" => $content_htaccess);

        // hosts
        $hostPathDesc = array();
        $hostPathDesc['paths_desc'] = ";Paths/URLs to system folders" . PHP_EOL .
                                    ";URIs without a leading slash are considered relative to the current XOOPS host location" . PHP_EOL .
                                    ";URIs with a leading slash are considered semi-relative (you must setup proper rewriting rules in your server conf)" . PHP_EOL;
        $hostPathDesc['www'] = ';Document root';
        $hostPathDesc['var'] = ";VAR or intermediate data directory, w/o URI access";
        $hostPathDesc['lib'] = ";Library directory";
        $hostPathDesc['usr'] = ";User extension directory";
        $hostPathDesc['app'] = ";Application module directory";
        $hostPathDesc['plugin'] = ";Plugin directory";
        $hostPathDesc['applet'] = ";Applet directory";
        $hostPathDesc['img'] = ";Static file directory with independent URI";
        $hostPathDesc['module'] = ";Legacy module directory";
        $hostPathDesc['theme'] = ";Theme directory";
        $hostPathDesc['upload'] = ";Upload directory with URI access";

        // New contents
        $host = array();
        $host['location'] = $vars["www"]["url"];
        $host['paths']['www'][] = $vars["www"]["path"];
        $host['paths']['www'][] = '';
        $host['paths']['var'][] = $vars["var"]["path"];
        $host['paths']['var'][] = empty($vars["var"]["url"]) ? "browse.php?var" : $vars["var"]["url"];
        $host['paths']['lib'][] = $vars["lib"]["path"];
        $host['paths']['lib'][] = empty($vars["lib"]["url"]) ? "browse.php?lib" : $vars["lib"]["url"];
        $host['paths']['usr'][] = $vars["usr"]["path"];
        $host['paths']['usr'][] = "browse.php?usr";
        $host['paths']['app'][] = $vars["app"]["path"];
        $host['paths']['app'][] = empty($vars["app"]["url"]) ? "browse.php?app" : $vars["app"]["url"];
        $host['paths']['plugin'][] = $vars["plugin"]["path"];
        $host['paths']['plugin'][] = empty($vars["plugin"]["url"]) ? "browse.php?plugin" : $vars["plugin"]["url"];
        $host['paths']['applet'][] = $vars["applet"]["path"];
        $host['paths']['applet'][] = empty($vars["applet"]["url"]) ? "browse.php?applet" : $vars["applet"]["url"];
        $host['paths']['img'][] = $vars["img"]["path"];
        $host['paths']['img'][] = $vars["img"]["url"];
        $host['paths']['module'][] = $vars["www"]["path"] . '/modules';
        $host['paths']['module'][] = 'modules';
        $host['paths']['theme'][] = $vars["theme"]["path"];
        $host['paths']['theme'][] = $vars["theme"]["url"];
        $host['paths']['upload'][] = $vars["upload"]["path"];
        $host['paths']['upload'][] = $vars["upload"]["url"];

        $file_host_ini = $vars["lib"]["path"] . '/boot/hosts.xoops.ini.php';

        $pos = strpos($host['location'], "/", 9);
        if ($pos === false) {
            $baseLocation = $host["location"];
            $baseUrl = "";
        } else {
            $baseLocation = substr($host["location"], 0, $pos);
            $baseUrl = substr($host["location"], $pos);
        }
        $content_hosts = array();
        $content_hosts[] = ';<?php __halt_compiler();' . PHP_EOL . PHP_EOL;
        $content_hosts[] = ";Hosts definition file" . PHP_EOL;
        $content_hosts[] = $hostPathDesc['paths_desc'] . PHP_EOL;
        $content_hosts[] =  PHP_EOL . PHP_EOL;
        $content_hosts[] = ";Host location" . PHP_EOL;
        $content_hosts[] = "[location]" . PHP_EOL;
        $content_hosts[] = 'baseLocation = "' . $baseLocation . '"' . PHP_EOL;
        $content_hosts[] = 'baseUrl = "' . $baseUrl . '"' . PHP_EOL . PHP_EOL;
        $content_hosts[] = "[paths]" . PHP_EOL;
        foreach (array_keys($host['paths']) as $path) {
            $content_hosts[] = PHP_EOL;
            if (!empty($hostPathDesc[$path])) {
                $content_hosts[] = $hostPathDesc[$path] . PHP_EOL;
            }
            $content_hosts[] = $path . '[] = "' . $host['paths'][$path][0] . '"' . PHP_EOL;
            $content_hosts[] = $path . '[] = "' . $host['paths'][$path][1] . '"' . PHP_EOL;
        }

        $content_hosts = implode("", $content_hosts);
        $configs[] = array("file" => $file_host_ini, "content" => $content_hosts);

        //System configurations
        $file_system_ini = $vars["lib"]["path"] . '/boot/engine.xoops.ini.php';
        $content_system = array();
        $content_system[] = ';<?php __halt_compiler();' . PHP_EOL . PHP_EOL;
        $content_system[] = ";XOOPS engine configurations" . PHP_EOL . PHP_EOL;
        $content_system[] = "[engine]" . PHP_EOL . PHP_EOL;
        $content_system[] = ";Site specific identifier, you should not change it after installation" . PHP_EOL;
        $content_system[] = "identifier = xoops";
        $content_system[] =  PHP_EOL . PHP_EOL;
        $content_system[] = ";Salt for hashing" . PHP_EOL;
        $content_system[] = "salt = xo" . md5(uniqid(mt_rand(), true));
        $content_system[] =  PHP_EOL . PHP_EOL;
        $content_system[] = ";Run mode. Potential values: production - for production, qa - for QA testing, debug - for users debugging, development - for developers" . PHP_EOL;
        $content_system[] = "environment = debug" . PHP_EOL;
        $content_system[] =  PHP_EOL . PHP_EOL;
        $content_system[] = ';Services' . PHP_EOL;
        $content_system[] = "[services]" . PHP_EOL;
        $content_system[] = "error = true" . PHP_EOL;
        $content_system[] = "logger = true" . PHP_EOL;
        $content_system[] = "profiler = true" . PHP_EOL;
        $content_system = implode("", $content_system);
        $configs[] = array("file" => $file_system_ini, "content" => $content_system);

        foreach ($configs as $config) {
            $error = false;
            if (!$file = fopen($config['file'], "w")) {
                $error = true;
            } else {
                if (fwrite($file, $config['content']) == -1) {
                    $error = true;
                }
                fclose($file);
            }
            if ($error) {
                $errorsSave[] = $config;
            }
        }

        if (empty($errorsSave)) {
            // Prepare for config files from dist files
            $iterator = new \DirectoryIterator($vars["var"]["path"] . '/etc');
            foreach ($iterator as $fileinfo) {
                if (!$fileinfo->isFile()) {
                    continue;
                }
                $filename = $fileinfo->getPathname();
                $suffix = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if ('dist' !== $suffix) {
                    continue;
                }
                $target = substr($filename, 0, -5);
                if (file_exists($target) && !is_writable($target)) {
                    @chmod($target, 0777);
                }
                $status = @copy($filename, $target);
                if (!$status || !is_readable($target)) {
                    $errorsConfig[] = $target;
                }
            }

            if (empty($errorsConfig)) {
                // Clear caches
                $clearScript = $vars["www"]["path"] . '/clear.php';
                if (file_exists($clearScript)) {
                    ob_start();
                    include $clearScript;
                    ob_end_clean();
                }
                $this->status = 1;
            }
        }

        $content = '';
        // Display saving error messages
        if (!empty($errorsSave)) {
            $content .= '
                <h3>' . _INSTALL_ERR_WRITE_CONFIGFILE_LABEL . '</h3>';
            foreach ($errorsSave as $error) {
                $content .= '
                    <p class="caption" style="margin-top: 10px;">' . sprintf(_INSTALL_ERR_WRITE_CONFIGFILE_DESC, $error['file']) . '</p>
                    <textarea cols="80" rows="10">' . $error['content'] . '</textarea>';
            }
        // Display config file error messages
        } elseif (!empty($errorsConfig)) {
            $content .= '
                <h3>' . _INSTALL_ERR_COPY_CONFIGFILES_LABEL . '</h3>
                <p class="caption">' . _INSTALL_ERR_COPY_CONFIGFILES_DESC . '</p>
                <div class="message error">
                <ul>';
            foreach ($errorsConfig as $file) {
                $content .= '<li>' . $file . '</li>';
            }
            $content .= '</ul></div>';
        }
        $this->content .= $content;
    }

    protected function loadPersistForm()
    {
        $persist = $this->wizard->getPersist('persist');
        $content = '';

        $valid = false;
        if (extension_loaded('apc')) {
            $persist = $persist ?: 'apc';
            $valid = true;
            $checkedString = ($persist == 'apc') ? 'checked' : '';
        } else {
            $checkedString = 'disabled';
        }
        $content .= '<div><input type="radio" name="persist" value="apc" ' . $checkedString . ' />' . _INSTALL_EXTENSION_APC . '</div>';
        $content .= '<p class="caption">' . _INSTALL_EXTENSION_APC_PROMPT . '</p>';

        if (extension_loaded('redis')) {
            $persist = $persist ?: 'redis';
            $valid = true;
            $checkedString = ($persist == 'redis') ? 'checked' : '';
            $content .= '<div><input type="radio" name="persist" value="redis" ' . $checkedString . ' />' . _INSTALL_EXTENSION_REDIS . '</div>';
            $content .= '<p class="caption">' . _INSTALL_EXTENSION_REDIS_PROMPT . '</p>';
        }

        if (extension_loaded('memcached')) {
            $persist = $persist ?: 'memcached';
            $checkedString = ($persist == 'memcached') ? 'checked' : '';
            $valid = true;
        } else {
            $checkedString = ' disabled';
        }
        $content .= '<div><input type="radio" name="persist" value="memcached" ' . $checkedString . ' />' . _INSTALL_EXTENSION_MEMCACHED . '</div>';
        $content .= '<p class="caption">' . _INSTALL_EXTENSION_MEMCACHED_PROMPT . '</p>';

        if (extension_loaded('memcache')) {
            $persist = $persist ?: 'memcache';
            $checkedString = ($persist == 'memcache') ? 'checked' : '';
            $valid = true;
        } else {
            $checkedString = ' disabled';
        }
        $content .= '<div><input type="radio" name="persist" value="memcache" ' . $checkedString . ' />' . _INSTALL_EXTENSION_MEMCACHE . '</div>';
        $content .= '<p class="caption">' . _INSTALL_EXTENSION_MEMCACHE_PROMPT . '</p>';

        $checkedString = ($persist == 'file') ? 'checked' : '';
        $content .= '<div><input type="radio" name="persist" value="file" ' . $checkedString . ' />' . _INSTALL_EXTENSION_FILE . '</div>';
        $content .= '<p class="caption warning">' . _INSTALL_EXTENSION_FILE_PROMPT . '</p>';
        $content .= '</div>';

        $content = '
            <h2> <span class="' . (empty($valid) ? 'warning' : 'success') . '">' . _INSTALL_PERSIST . '</span> <a href="javascript:void(0);" id="persist-label"><span>[+]</span><span style="display: none;">[-]</span></a></h2>
            <p class="caption">' . _INSTALL_PERSIST_DESC . '</p>
            <div class="install-form advanced-form" id="advanced-persist">' .
            $content .
            '</div>';

        $this->content .= $content;

        $this->footContent .= '
            <script type="text/javascript">
            $("input[name=persist]").click(function() {
                $.ajax({
                  url: "' . $_SERVER['PHP_SELF'] . '",
                  data: {page: "directive", persist: $(this).val(), action: "persist"},
                });
            });

            $("#persist-label").click(function() {
                $("#advanced-persist").slideToggle();
                $("#persist-label span").toggle();
            });
            </script>';

        $persist = $persist ?: 'file';
        $this->wizard->setPersist('persist', $persist);
    }

    protected function loadPathForm()
    {
        $this->pathController = new \Xoops\Setup\PathController($this->wizard);
        $this->pathController->init(true);

        $controller = $this->pathController;
        $displayItem = function ($item) use ($controller) {
            $content = '<div class="item">
                <label for="' . $item . '">' . constant('_INSTALL_' . strtoupper($item) . '_LABEL') . '</label>
                <p class="caption">' . constant('_INSTALL_' . strtoupper($item) . '_HELP') . '</p>
                <input type="text" name="' . $item . '" id="' . $item . '" value="' . $controller->getPath($item) . '" />
                <em id="' . $item . '-status" class="loading">&nbsp;</em>
                <p id="' . $item . '-message" class="path-message">&nbsp;</p>
                </div>';
            return $content;
        };

        $status = $statusBasic = $statusAdvanced = '';
        $content = '';
        $itemList = array('path_www', 'url_www');
        foreach ($itemList as $item) {
            $content .= $displayItem($item);
        }

        $contentBasic = '
            <h3 class="section"><span id="path-basic-label" class="' . $statusBasic . '">' . _INSTALL_XOOPS_SETTINGS_BASIC . '</span> <a href="javascript:void(0);" id="path-basic-toggle"><span>[+]</span><span style="display: none;">[-]</span></a></h3>
            <p class="caption">' . _INSTALL_XOOPS_SETTINGS_BASIC_HELP . '</p>
            <div class="install-form advanced-form item-container" id="path-basic">' .
            $content .
            '</div>';

        $content = '';
        $itemList = array('path_upload', 'url_upload', 'path_img', 'url_img', 'path_theme', 'url_theme', 'path_lib', 'path_var');
        foreach ($itemList as $item) {
            $content .= $displayItem($item);
        }

        $subContent = '';
        $itemList = array('path_app', 'url_app', 'path_plugin', 'url_plugin', 'path_applet', 'url_applet');
        foreach ($itemList as $item) {
            $subContent .= $displayItem($item);
        }

        $item = 'path_usr';
        $content .= '<div class="item">
            <label for="' . $item . '">' . constant('_INSTALL_' . strtoupper($item) . '_LABEL') . '
            <a href="javascript:void(0);" id="path-advanced-usr-toggle"><span>[+]</span><span style="display: none;">[-]</span></a>
            </label>
            <p class="caption">' . constant('_INSTALL_' . strtoupper($item) . '_HELP') . '</p>
            <input type="text" name="' . $item . '" id="' . $item . '" value="' . $this->pathController->getPath($item) . '" />
            <em id="' . $item . '-status" class="loading">&nbsp;</em>
            <p id="' . $item . '-message" class="path-message">&nbsp;</p>
            </div>
            <div class="install-form advanced-form item-container" id="path-advanced-usr">' .
            $subContent .
            '</div>';

        $contentAdvanced = '
            <h3 class="section"><span id="path-advanced-label" class="' . $statusAdvanced . '">' . _INSTALL_XOOPS_SETTINGS_ADVANCED . '</span> <a href="javascript:void(0);" id="path-advanced-toggle"><span>[+]</span><span style="display: none;">[-]</span></a></h3>
            <p class="caption">' . _INSTALL_XOOPS_SETTINGS_ADVANCED_HELP . '</p>
            <div class="install-form advanced-form item-container" id="path-advanced">' .
            $content .
            '</div>';

        $content = '
            <h2><span id="paths-label" class="' . $status . '">' . _INSTALL_PATHS . '</span> <a href="javascript:void(0);" id="paths-toggle"><span>[+]</span><span style="display: none;">[-]</span></a></h2>
            <p class="caption">' . _INSTALL_PATHS_DESC . '</p>
            <div class="install-form advanced-form item-container" id="paths">' .
            $contentBasic . $contentAdvanced .
            '</div>';

        $this->content .= $content;


        $this->headContent .= '
        <style type="text/css" media="screen">
            #paths .path-message {
                display: none;
                font-size: 80%;
                background-color: yellow;
                border: 1px solid #666;
                margin-top: 5px;
                padding-left: 5px;
            }
            #paths .item {
                margin-top: 20px;
            }
            #paths p.caption, #paths label {
                margin: 0px;
            }
        </style>

        <script type="text/javascript">
        function update(id) {
            verifyPath(id);
            checkPath(id);
            if (id == "path_usr") {
                var val = $("#"+id).val();
                setPath("path_app", val + "/apps");
                update("path_app");
                setPath("path_plugin", val + "/plugins");
                update("path_plugin");
                setPath("path_applet", val + "/applets");
                update("path_applet");
            }
        }

        function verifyPath(id) {
            var val = $("#"+id).val();
            val = val.replace(/([\/]*$)/g, "");
            $("#"+id).val(val);
        }

        function setPath(id, val) {
            $("#"+id).val(val);
        }

        function checkPath(id) {
            var val = $("#"+id).val();
            var isPath = (id.substr(0, 4) == "url_") ? 0 : 1;
            if (isPath) {
                if (!pathIsAbsolute(val) && id != "path_www") {
                    val = $("#path_www").val() + "/" + val;
                }
            } else {
                if (!urlIsAbsolute(val) && id != "url_www") {
                    val = $("#url_www").val() + "/" + val;
                }
            }

            var url="' . $_SERVER['PHP_SELF'] . '";
            $.get(url, {"action": "message", "var": id, "path": val, "page": "directive"}, function (data) {
                if (data.length == 0) {
                    $("#"+id+"-message").css("display", "none");
                } else {
                    $("#"+id+"-message").html(data);
                    $("#"+id+"-message").css("display", "block");
                    triggerParents(id);
                }
            });

            $.get(url, {"action": "path", "var": id, "path": val, "page": "directive"}, function (data) {
                var statusClass = "warning";
                if (data == 1) {
                    statusClass = "success";
                }
                if (data == -1) {
                    statusClass = "failure";
                    triggerParents(id);
                }
                $("#"+id+"-status").attr("class", statusClass);
            });
        }

        function triggerParents(id) {
            $("#" + id).parents(".item-container").each(function(index) {
                $(this).slideDown();
                $("#" + $(this).attr("id") + "-toggle span").css("display", "none").next().css("display", "inline");
                //$("#" + $(this).attr("id") + "-label").attr("class", "failure");
            });
        }

        function pathIsAbsolute(path) {
            if (/^[a-z]:[\\\/]/i.test(path)) return true;
            if (path.indexOf("\\\\") == 0)   return true;
            if (path.indexOf("/") == 0)   return true;
            return false;
        }

        function urlIsAbsolute(path) {
            if (/^http(s?):\/\//i.test(path)) return true;
            return false;
        }

        </script>';

        $this->footContent .= '
            <script type="text/javascript">
            $(document).ready(function(){
                $("#paths input[type=text]").each(function(index) {
                    checkPath($(this).attr("id"));
                    $(this).bind("change", function() {
                        update($(this).attr("id"));
                    });
                });
            });

            $("#paths-toggle").click(function() {
                $("#paths").slideToggle();
                $("#paths-toggle span").toggle();
            });
            $("#path-basic-toggle").click(function() {
                $("#path-basic").slideToggle();
                $("#path-basic-toggle span").toggle();
            });
            $("#path-advanced-toggle").click(function() {
                $("#path-advanced").slideToggle();
                $("#path-advanced-toggle span").toggle();
            });
            $("#path-advanced-usr-toggle").click(function() {
                $("#path-advanced-usr").slideToggle();
                $("#path-advanced-usr-toggle span").toggle();
            });
            </script>';
    }
}