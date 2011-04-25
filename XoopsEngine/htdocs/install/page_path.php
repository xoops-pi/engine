<?php
/**
 * Installer path configuration page
 *
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Xoops Engine
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         Xoops_Setup
 * @version         $Id$
 */

require_once __DIR__ . '/include/common.inc.php';
if (!defined('XOOPS_INSTALL')) { die('XOOPS Installation wizard die'); }
include_once __DIR__ . '/class/pathcontroller.php';

$pageHasForm = true;
$pageHasHelp = true;

$ctrl = new PathController($wizard);
$ctrl->init();

// form field validation
if (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET' && !empty($_GET['var']) && isset($_GET['action']) && $_GET['action'] == 'checkpath') {
    //$errorReporting = error_reporting(0);
    $path = $_GET['var'];
    $val = htmlspecialchars(trim($_GET['path']));
    list($type, $key) = explode("_", $path, 2);
    if ($type == "url") {
        $ctrl->paths[$key]['url'] = $val;
        $status = $ctrl->checkUrl($key);
        echo $ctrl->urlCheckHtml($key);
    } else {
        $ctrl->paths[$key]['path'] = $val;
        $status = $ctrl->checkPath($key);
        echo $ctrl->pathCheckHtml($key);
    }
    //error_reporting($errorReporting);
    exit();
}

// Validate configuration data
$isValid = $ctrl->validate();

// Valid POST submission, store configs to config files
$errorsSave = array();
if ($isValid && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $configs = array();

    $vars = $wizard->persistentData['paths'];

    // boot.php
    $persist_type = strtolower($wizard->persistentData['persist'] ?: "");
    $persist_prefix = 'x' . substr(md5($vars["www"]["url"]), 0, 4);
    $file_bootfile = $vars["www"]["path"] . '/boot.php';
    $file_bootfile_dist = __DIR__ . '/include/boot.php.dist';
    $content_bootfile = file_get_contents($file_bootfile_dist);
    $content_bootfile = preg_replace("/(define\()([\"'])(XOOPS_PATH)\\2,\s*([\"'])(.*?)\\4\s*\)/", "define('XOOPS_PATH', '" . $vars['lib']['path'] . "')", $content_bootfile);
    $content_bootfile = preg_replace("/(define\()([\"'])(XOOPS_PERSIST_TYPE)\\2,\s*([\"'])(.*?)\\4\s*\)/", "define('XOOPS_PERSIST_TYPE', '" . $persist_type . "')", $content_bootfile);
    $content_bootfile = preg_replace("/(define\()([\"'])(XOOPS_PERSIST_PREFIX)\\2,\s*([\"'])(.*?)\\4\s*\)/", "define('XOOPS_PERSIST_PREFIX', '" . $persist_prefix . "')", $content_bootfile);
    //rename($file_bootfile_dist, $file_bootfile);
    $configs[] = array("file" => $file_bootfile, "content" => $content_bootfile);

    // .htaccess
    $file_htaccess = $vars["www"]["path"] . '/.htaccess';
    $file_htaccess_dist = __DIR__ . '/include/.htaccess.dist';
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
        //chmod($config['file'], 0777);
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
        $iterator = new DirectoryIterator($vars["var"]["path"] . '/etc');
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
            copy($filename, $target);
        }


        // Clear caches
        $clearScript = $vars["www"]["path"] . '/clear.php';
        if (file_exists($clearScript)) {
            ob_start();
            include $clearScript;
            ob_end_clean();
        }
        $wizard->redirectToPage('+1');
    }
}

ob_start();
// Display saving error messages
if (!empty($errorsSave)) {
    foreach ($errorsSave as $error) {
?>
<div class='x2-note errorMsg'><?php echo sprintf(ERR_WRITE_CONFIGFILE, $error['file']); ?></div>
<textarea cols="50" rows="10"><?php echo $error['content'];?></textarea>
<?php
    }
// Display form
} else {
?>
<script type="text/javascript">
function pathIsAbsolute(path)
{
    if (/^[a-z]:[\\\/]/i.test(path)) return true;
    if (path.indexOf("\\") == 0)   return true;
    if (path.indexOf("/") == 0)   return true;
    return false;
}

function urlIsAbsolute(path)
{
    if (/^http(s?):\/\//i.test(path)) return true;
    return false;
}

function removeTrailingSlash(val) {
    if (val[val.length-1] == '/') {
        val = val.substr(0, val.length-1);
    }
    return val;
}

// Clear permission message
function updatePath(key, val) {
    val = removeTrailingSlash(val);
    $(key).value = val;
    checkValidation(key, val);
    $(key+'_perms').style.display='none';
}

function updateUrl(key, val) {
    val = removeTrailingSlash(val);
    $(key).value = val;
    checkValidation(key, val);
}

function checkValidation(key, val) {
    new Ajax.Updater(
        key+'_img', '<?php echo $_SERVER['PHP_SELF']; ?>',
        { method:'get',parameters:'action=checkpath&var='+key+'&path='+escape(val) }
    );
}

function updatePathUsr(key, val) {
    updatePath(key, val);
    updatePath('path_app', val + '/apps');
    updatePath('path_plugin', val + '/plugins');
    updatePath('path_applet', val + '/applets');
}

function updatePathUpload(key, val) {
    // Set default upload path if not specified
    // And update upload URL
    if (val == '') {
        val = $("path_www").value + '/uploads';
        updateUrl("url_upload", 'uploads');
    }
    // Conver relative path to full path
    if (!pathIsAbsolute(val)) {
        val = $("path_www").value + '/' + val;
    }
    updatePath(key, val);
}

function updatePathTheme(key, val) {
    // Set default theme path if not specified
    // And update theme URL
    if (val == '') {
        val = $("path_www").value + '/themes';
        updateUrl("url_theme", 'themes');
    }
    // Conver relative path to full path
    if (!pathIsAbsolute(val)) {
        val = $("path_www").value + '/' + val;
    }
    updatePath(key, val);
}

function updatePathImg(key, val) {
    // Set default img path if not specified
    // And update img URL
    if (val == '') {
        val = $("path_www").value + '/img';
        updateUrl("url_img", $("url_www").value + '/img');
    }
    // Conver relative path to full path
    if (!pathIsAbsolute(val)) {
        val = $("path_www").value + '/' + val;
    }
    updatePath(key, val);
}

function updateUrlImg(key, val) {
    if (val == '') {
        val = $("url_www").value + '/img';
    }
    if (!urlIsAbsolute(val)) {
        val = $("url_www").value + '/' + val;
    }
    updateUrl(key, val);
}

</script>

<fieldset>
    <legend><?php echo XOOPS_SETTINGS_BASIC; ?></legend>
    <div class="xoform-help" style="display: block;"><?php echo XOOPS_SETTINGS_BASIC_HELP; ?></div>

    <label for="path_www"><?php echo XOOPS_ROOT_PATH_LABEL; ?></label>
    <div class="xoform-help"><?php echo XOOPS_ROOT_PATH_HELP; ?></div>
    <input type="text" name="path_www" id="path_www" value="<?php echo $ctrl->paths['www']['path']; ?>" onchange="updatePath(this.id, this.value)" />
    <?php echo $ctrl->pathCheckHtml('www'); ?>
    <?php
    if ($ctrl->validPath['www'] && !empty($ctrl->permErrors['www'])) {
        echo '<br style="clear: both;" />';
        echo '<div id="path_www_perms" class="x2-note">';
        echo CHECKING_PERMISSIONS . '<br /><p>' . ERR_NEED_WRITE_ACCESS . '</p>';
        echo '<ul class="diags">';
        foreach ($ctrl->permErrors['www'] as $path => $result) {
            if ($result) {
                echo '<li class="success">' . sprintf(IS_WRITABLE, $path) . '</li>';
            } else {
                echo '<li class="failure">' . sprintf(IS_NOT_WRITABLE, $path) . '</li>';
            }
        }
        echo '</ul>';
    } else {
        echo '<div id="path_www_perms" class="x2-note" style="display: none;" />';
    }
    ?>
    </div>
    <br style="clear: both;" />

    <label for="url_www"><?php echo XOOPS_URL_LABEL; ?></label>
    <div class="xoform-help"><?php echo XOOPS_URL_HELP; ?></div>
    <input type="text" name="url_www" id="url_www" value="<?php echo $ctrl->paths['www']['url']; ?>" onchange="updateUrl(this.id, this.value)" />
    <?php echo $ctrl->urlCheckHtml('www'); ?>
    <br style="clear: both;" />

</fieldset>

<fieldset>
    <legend><?php echo XOOPS_SETTINGS_ADVANCED; ?></legend>
    <div class="xoform-help" style="display: block;"><?php echo XOOPS_SETTINGS_ADVANCED_HELP; ?></div>

    <label for="path_lib"><?php echo XOOPS_LIB_PATH_LABEL; ?></label>
    <div class="xoform-help"><?php echo XOOPS_LIB_PATH_HELP; ?></div>
    <input type="text" name="path_lib" id="path_lib" value="<?php echo $ctrl->paths['lib']['path']; ?>" onchange="updatePath(this.id, this.value)" />
    <?php echo $ctrl->pathCheckHtml('lib'); ?>
    <?php
    if ($ctrl->validPath['lib'] && !empty($ctrl->permErrors['lib'])) {
        echo '<br style="clear: both;" />';
        echo '<div id="path_lib_perms" class="x2-note">';
        echo CHECKING_PERMISSIONS . '<br /><p>' . ERR_NEED_WRITE_ACCESS . '</p>';
        echo '<ul class="diags">';
        foreach ($ctrl->permErrors['lib'] as $path => $result) {
            if ($result) {
                echo '<li class="success">' . sprintf(IS_WRITABLE, $path) . '</li>';
            } else {
                echo '<li class="failure">' . sprintf(IS_NOT_WRITABLE, $path) . '</li>';
            }
        }
        echo '</ul>';
    } else {
        echo '<div id="path_lib_perms" class="x2-note" style="display: none;" />';
    }
    ?>
    </div>
    <br style="clear: both;" />

    <label for="path_var"><?php echo XOOPS_DATA_PATH_LABEL; ?></label>
    <div class="xoform-help"><?php echo XOOPS_DATA_PATH_HELP; ?></div>
    <input type="text" name="path_var" id="path_var" value="<?php echo $ctrl->paths['var']['path']; ?>" onchange="updatePath(this.id, this.value)" />
    <?php echo $ctrl->pathCheckHtml('var'); ?>
    <?php
    if ($ctrl->validPath['var'] && !empty($ctrl->permErrors['var'])) {
        echo '<br style="clear: both;" />';
        echo '<div id="path_var_perms" class="x2-note">';
        echo CHECKING_PERMISSIONS . '<br /><p>' . ERR_NEED_WRITE_ACCESS . '</p>';
        echo '<ul class="diags">';
        foreach ($ctrl->permErrors['var'] as $path => $result) {
            if ($result) {
                echo '<li class="success">' . sprintf(IS_WRITABLE, $path) . '</li>';
            } else {
                echo '<li class="failure">' . sprintf(IS_NOT_WRITABLE, $path) . '</li>';
            }
        }
        echo '</ul>';
    } else {
        echo '<div id="path_var_perms" class="x2-note" style="display: none;" />';
    }
    ?>
    </div>
    <br style="clear: both;" />

    <label for="path_usr"><?php echo XOOPS_USR_PATH_LABEL; ?></label>
    <div class="xoform-help"><?php echo XOOPS_USR_PATH_HELP; ?></div>
    <input type="text" name="path_usr" id="path_usr" value="<?php echo $ctrl->paths['usr']['path']; ?>" onchange="updatePathUsr(this.id, this.value)" />
    <?php echo $ctrl->pathCheckHtml('usr'); ?>
    <?php
    if ($ctrl->validPath['usr'] && !empty($ctrl->permErrors['usr'])) {
        echo '<br style="clear: both;" />';
        echo '<div id="path_usr_perms" class="x2-note">';
        echo CHECKING_PERMISSIONS . '<br /><p>' . ERR_NEED_WRITE_ACCESS . '</p>';
        echo '<ul class="diags">';
        foreach ($ctrl->permErrors['usr'] as $path => $result) {
            if ($result) {
                echo '<li class="success">' . sprintf(IS_WRITABLE, $path) . '</li>';
            } else {
                echo '<li class="failure">' . sprintf(IS_NOT_WRITABLE, $path) . '</li>';
            }
        }
        echo '</ul>';
    } else {
        echo '<div id="path_usr_perms" class="x2-note" style="display: none;" />';
    }
    ?>
    </div>
    <br style="clear: both;" />

    <label for="path_app"><?php echo XOOPS_APP_PATH_LABEL; ?></label>
    <div class="xoform-help"><?php echo XOOPS_APP_PATH_HELP; ?></div>
    <input type="text" name="path_app" id="path_app" value="<?php echo $ctrl->paths['app']['path']; ?>" onchange="updatePath(this.id, this.value)" />
    <?php echo $ctrl->pathCheckHtml('app'); ?>
    <div id="path_app_perms" class="x2-note" style="display: none;"></div>
    <br style="clear: both;" />

    <label for="url_app"><?php echo XOOPS_APP_URL_LABEL; ?></label>
    <div class="xoform-help"><?php echo XOOPS_APP_URL_HELP; ?></div>
    <input type="text" name="url_app" id="url_app" value="<?php echo $ctrl->paths['app']['url']; ?>" onchange="updateUrl(this.id, this.value)" />
    <?php echo $ctrl->urlCheckHtml('app'); ?>
    <br style="clear: both;" />

    <label for="path_plugin"><?php echo XOOPS_PLUGIN_PATH_LABEL; ?></label>
    <div class="xoform-help"><?php echo XOOPS_PLUGIN_PATH_HELP; ?></div>
    <input type="text" name="path_plugin" id="path_plugin" value="<?php echo $ctrl->paths['plugin']['path']; ?>" onchange="updatePath(this.id, this.value)" />
    <?php echo $ctrl->pathCheckHtml('plugin'); ?>
    <div id="path_plugin_perms" class="x2-note" style="display: none;"></div>
    <br style="clear: both;" />

    <label for="url_plugin"><?php echo XOOPS_PLUGIN_URL_LABEL; ?></label>
    <div class="xoform-help"><?php echo XOOPS_PLUGIN_URL_HELP; ?></div>
    <input type="text" name="url_plugin" id="url_plugin" value="<?php echo $ctrl->paths['plugin']['url']; ?>" onchange="updateUrl(this.id, this.value)" />
    <?php echo $ctrl->urlCheckHtml('plugin'); ?>
    <br style="clear: both;" />

    <label for="path_applet"><?php echo XOOPS_APPLET_PATH_LABEL; ?></label>
    <div class="xoform-help"><?php echo XOOPS_APPLET_PATH_HELP; ?></div>
    <input type="text" name="path_applet" id="path_applet" value="<?php echo $ctrl->paths['applet']['path']; ?>" onchange="updatePath(this.id, this.value)" />
    <?php echo $ctrl->pathCheckHtml('applet'); ?>
    <div id="path_applet_perms" class="x2-note" style="display: none;"></div>
    <br style="clear: both;" />

    <label for="url_applet"><?php echo XOOPS_APPLET_URL_LABEL; ?></label>
    <div class="xoform-help"><?php echo XOOPS_APPLET_URL_HELP; ?></div>
    <input type="text" name="url_applet" id="url_applet" value="<?php echo $ctrl->paths['applet']['url']; ?>" onchange="updateUrl(this.id, this.value)" />
    <?php echo $ctrl->urlCheckHtml('applet'); ?>
    <br style="clear: both;" />

    <label for="path_upload"><?php echo XOOPS_UPLOAD_PATH_LABEL; ?></label>
    <div class="xoform-help"><?php echo XOOPS_UPLOAD_PATH_HELP; ?></div>
    <input type="text" name="path_upload" id="path_upload" value="<?php echo $ctrl->paths['upload']['path']; ?>" onchange="updatePathUpload(this.id, this.value)" />
    <?php echo $ctrl->pathCheckHtml('upload'); ?>
    <?php
    if ($ctrl->validPath['upload'] && !empty($ctrl->permErrors['upload'])) {
        echo '<br style="clear: both;" />';
        echo '<div id="path_upload_perms" class="x2-note">';
        echo CHECKING_PERMISSIONS . '<br /><p>' . ERR_NEED_WRITE_ACCESS . '</p>';
        echo '<ul class="diags">';
        foreach ($ctrl->permErrors['upload'] as $path => $result) {
            if ($result) {
                echo '<li class="success">' . sprintf(IS_WRITABLE, $path) . '</li>';
            } else {
                echo '<li class="failure">' . sprintf(IS_NOT_WRITABLE, $path) . '</li>';
            }
        }
        echo '</ul>';
    } else {
        echo '<div id="path_upload_perms" class="x2-note" style="display: none;" />';
    }
    ?>
    </div>
    <br style="clear: both;" />

    <label for="url_upload"><?php echo XOOPS_UPLOAD_URL_LABEL; ?></label>
    <div class="xoform-help"><?php echo XOOPS_UPLOAD_URL_HELP; ?></div>
    <input type="text" name="url_upload" id="url_upload" value="<?php echo $ctrl->paths['upload']['url']; ?>" onchange="updateUrl(this.id, this.value)" />
    <?php echo $ctrl->urlCheckHtml('upload'); ?>
    <br style="clear: both;" />

    <label for="path_img"><?php echo XOOPS_IMG_PATH_LABEL; ?></label>
    <div class="xoform-help"><?php echo XOOPS_IMG_PATH_HELP; ?></div>
    <input type="text" name="path_img" id="path_img" value="<?php echo $ctrl->paths['img']['path']; ?>" onchange="updatePathImg(this.id, this.value)" />
    <?php echo $ctrl->pathCheckHtml('img'); ?>
    <div id="path_img_perms" class="x2-note" style="display: none;"></div>
    <br style="clear: both;" />

    <label for="url_img"><?php echo XOOPS_IMG_URL_LABEL; ?></label>
    <div class="xoform-help"><?php echo XOOPS_IMG_URL_HELP; ?></div>
    <input type="text" name="url_img" id="url_img" value="<?php echo $ctrl->paths['img']['url']; ?>" onchange="updateUrlImg(this.id, this.value)" />
    <?php echo $ctrl->urlCheckHtml('img'); ?>
    <br style="clear: both;" />

    <label for="path_theme"><?php echo XOOPS_THEME_PATH_LABEL; ?></label>
    <div class="xoform-help"><?php echo XOOPS_THEME_PATH_HELP; ?></div>
    <input type="text" name="path_theme" id="path_theme" value="<?php echo $ctrl->paths['theme']['path']; ?>" onchange="updatePathTheme(this.id, this.value)" />
    <?php echo $ctrl->pathCheckHtml('theme'); ?>
    <div id="path_theme_perms" class="x2-note" style="display: none;"></div>
    <br style="clear: both;" />

    <label for="url_theme"><?php echo XOOPS_THEME_URL_LABEL; ?></label>
    <div class="xoform-help"><?php echo XOOPS_THEME_URL_HELP; ?></div>
    <input type="text" name="url_theme" id="url_theme" value="<?php echo $ctrl->paths['theme']['url']; ?>" onchange="updateUrl(this.id, this.value)" />
    <?php echo $ctrl->urlCheckHtml('theme'); ?>
    <br style="clear: both;" />
</fieldset>

<?php
}
$content = ob_get_contents();
ob_end_clean();

include __DIR__ . '/include/install_tpl.php';