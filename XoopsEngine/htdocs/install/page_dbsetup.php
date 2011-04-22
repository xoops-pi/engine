<?php
/**
 * Installer database configuration page
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

//$xoopsOption['hascommon'] = true;
require_once __DIR__ . '/include/common.inc.php';
if (!defined('XOOPS_INSTALL')) { die('XOOPS Installation wizard die'); }
defined('XOOPS_BOOTSTRAP') or define('XOOPS_BOOTSTRAP', false);
include '../boot.php';

$pageHasForm = true;
$pageHasHelp = true;

//$vars =& $_SESSION['settings'];
$vars =& $wizard->persistentData['settings'];

$func_connect = empty($vars['DB_PCONNECT']) ? "mysql_connect" : "mysql_pconnect";
if (!$link = $func_connect($vars['DB_HOST'], $vars['DB_USER'], $vars['DB_PASS'], true)) {
    $error = ERR_NO_DBCONNECTION;
    $wizard->redirectToPage('dbconnection', $error);
    exit();
}

// Set request data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $params = array('DB_NAME', 'DB_CHARSET', 'DB_COLLATION', 'DB_PREFIX');
    foreach ($params as $name) {
        $vars[$name] = isset($_POST[$name]) ? $_POST[$name] : "";
    }
// Fill with default values
} elseif (empty($vars['DB_NAME'])) {
    $vars = array_merge($vars,
            array('DB_NAME'         => 'xoops',
                    'DB_CHARSET'    => 'utf8',
                    'DB_COLLATION'  => '',
                    'DB_PREFIX'     => 'x' . substr(md5(time()), 0, 3),
           )
   );
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['charset']) && isset($_GET['action']) && $_GET['action'] == 'updateCollation') {
    echo xoFormFieldCollation('DB_COLLATION', $vars['DB_COLLATION'], DB_COLLATION_LABEL, DB_COLLATION_HELP, $link, $_GET['charset']);
    exit();
}

$error = '';
$errorDsn = array();
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($vars['DB_NAME'])) {
    $error = validateDbCharset($link, $vars['DB_CHARSET'], $vars['DB_COLLATION']);
    $db_exist = true;
    if (empty($error)) {
        if (!mysql_select_db($vars['DB_NAME'], $link)) {
            //$error =  mysql_error();
            // Database not here: try to create it
            $result = mysql_query("CREATE DATABASE `" . $vars['DB_NAME'] . '`');
            if (!$result) {
                $error = ERR_NO_DATABASE;
                $db_exist = false;
            }
        }
        if ($db_exist && $vars['DB_CHARSET']) {
            $sql = "ALTER DATABASE `" . $vars['DB_NAME'] . "` DEFAULT CHARACTER SET " . mysql_real_escape_string($vars['DB_CHARSET']) .
                        ($vars['DB_COLLATION'] ? " COLLATE " . mysql_real_escape_string($vars['DB_COLLATION']) : "");
            if (!mysql_query($sql)) {
                $error = ERR_CHARSET_NOT_SET . $sql;
            }
        }
    }
    if (empty($error)) {
        $dsn = array();
        $dsn['adapter'] = '"' . "Pdo_Mysql" . '"';
        $dsn['driver'] = '"' . "Pdo_Mysql" . '"';
        $dsn['dbname'] = '"' . $vars["DB_NAME"] . '"';
        $dsn['host'] = '"' . $vars["DB_HOST"] . '"';
        $dsn['username'] = '"' . $vars["DB_USER"] . '"';
        $dsn['password'] = '"' . $vars["DB_PASS"] . '"';
        $dsn['prefix'] = '"' . $vars["DB_PREFIX"] . '"';
        $dsn['persistent'] = intval($vars["DB_PCONNECT"]);
        $dsn['charset'] = '"' . $vars["DB_CHARSET"] . '"';
        $dsn['driver_options.LOCAL_INFILE'] = true;

        $content_dsn = ';<?php __halt_compiler();' . PHP_EOL . PHP_EOL;
        $content_dsn .= ';DSN to database' . PHP_EOL;
        foreach ($dsn as $key => $val) {
            $content_dsn .= $key . " = " . $val . PHP_EOL;
        }

        $error_dsn = false;
        $file_dsn_ini = XOOPS::path("var") . '/etc/resource.db.ini.php';
        if (!$file = fopen($file_dsn_ini, "w")) {
            $error_dsn = true;
        } else {
            $result = fwrite($file, $content_dsn);
            if ($result == false || $result < 1) {
                $error_dsn = true;
            }
            fclose($file);
        }
        if (empty($error_dsn)) {
            $wizard->redirectToPage('+1');
            exit();
        } else {
            $errorDsn = array("file" => $file_dsn_ini, "content" => $content_dsn);
        }
    }
}

ob_start();
// Display saving error messages
if (!empty($errorDsn)) {
?>
<div class='x2-note errorMsg'><?php echo sprintf(ERR_WRITE_CONFIGFILE, $errorDsn['file']); ?></div>
<textarea cols="50" rows="10"><?php echo $errorDsn['content'];?></textarea>
<?php
// Display form
} else {
if (!empty($error)) echo '<div class="x2-note errorMsg">' . $error . "</div>\n";

// Check mysql mode, see http://dev.mysql.com/doc/refman/5.5/en/faqs-sql-modes.html
$isStrict = false;
$queryMode = mysql_query("SELECT @@session.sql_mode");
if ($queryMode && mysql_num_rows($queryMode) > 0){
    $modeList = mysql_fetch_array($queryMode, MYSQL_NUM);
    foreach ($modeList as $mode) {
        if (stristr($mode, "STRICT_TRANS_TABLES") !== false || stristr($mode, "STRICT_ALL_TABLES") !== false) {
            $isStrict = true;
            break;
        }
    }
}
if (!empty($isStrict)) echo '<div class="x2-note warning">' . ERR_MYSQL_STRICT_MODE . "</div>\n";
?>

<script type="text/javascript">
function setFormFieldCollation(id, val)
{
    var display = (val == '') ? 'none' : '';
    $(id).style.display = display;
    new Ajax.Updater(
        id, '<?php echo $_SERVER['PHP_SELF']; ?>',
        { method:'get',parameters:'action=updateCollation&charset='+val }
   );
}
</script>

<fieldset>
    <legend><?php echo LEGEND_DATABASE; ?></legend>
    <?php echo xoFormField('DB_NAME',        $vars['DB_NAME'],        DB_NAME_LABEL,     DB_NAME_HELP); ?>
    <?php echo xoFormField('DB_PREFIX',    $vars['DB_PREFIX'],        DB_PREFIX_LABEL, DB_PREFIX_HELP); ?>
    <?php echo xoFormFieldCharset('DB_CHARSET',    $vars['DB_CHARSET'],    DB_CHARSET_LABEL, DB_CHARSET_HELP, $link); ?>
    <?php echo xoFormBlockCollation('DB_COLLATION',    $vars['DB_COLLATION'],    DB_COLLATION_LABEL, DB_COLLATION_HELP, $link, $vars['DB_CHARSET']); ?>
</fieldset>

<?php
}
$content = ob_get_contents();
ob_end_clean();
include __DIR__ . '/include/install_tpl.php';