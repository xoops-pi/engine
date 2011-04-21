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

require_once __DIR__ . '/include/common.inc.php';
if (!defined('XOOPS_INSTALL')) { die('XOOPS Installation wizard die'); }

$pageHasForm = true;
$pageHasHelp = true;

$vars =& $wizard->persistentData['settings'];
if (empty($vars)) {
    $vars = array();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $params = array('DB_TYPE', 'DB_HOST', 'DB_USER', 'DB_PASS');
    foreach ($params as $name) {
        $vars[$name] = $_POST[$name];
    }
    $vars['DB_PCONNECT'] = !empty($_POST['DB_PCONNECT']) ? 1 : 0;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($vars['DB_HOST']) && !empty($vars['DB_USER'])) {
    $func_connect = empty($vars['DB_PCONNECT']) ? "mysql_connect" : "mysql_pconnect";
    if (!$link = @$func_connect($vars['DB_HOST'], $vars['DB_USER'], $vars['DB_PASS'], true)) {
        $error = ERR_NO_DBCONNECTION . ' ' . mysql_error();
    }

    if (empty($error)) {
        $wizard->redirectToPage('+1');
        exit();
    }
}

if (empty($vars['DB_HOST'])) {
    // Fill with default values
    $vars = array_merge($vars,
            array( 'DB_TYPE'        => 'mysql',
                    'DB_HOST'        => 'localhost',
                    'DB_USER'        => '',
                    'DB_PASS'        => '',
                    'DB_PCONNECT'    => 0,
           )
   );
}
ob_start();
?>
<?php if (!empty($error)) echo '<div class="x2-note errorMsg">' . $error . "</div>\n"; ?>
<fieldset>
    <legend><?php echo LEGEND_CONNECTION; ?></legend>
    <label for="DB_DATABASE_LABEL"><?php echo DB_DATABASE_LABEL ; ?></label>
    <select size="1" name="DB_TYPE">
        <?php
        foreach ($wizard->configs['db_types'] as $db_type) {
            $selected = ($vars['DB_TYPE'] == $db_type) ? 'selected' : '';
            echo "<option value='$db_type' selected='$selected'>$db_type</option>";
        }
        ?>
    </select>

    <?php echo xoFormField('DB_HOST',    $vars['DB_HOST'],        DB_HOST_LABEL, DB_HOST_HELP); ?>
    <?php echo xoFormField('DB_USER',    $vars['DB_USER'],        DB_USER_LABEL, DB_USER_HELP); ?>
    <?php echo xoPassField('DB_PASS',    $vars['DB_PASS'],        DB_PASS_LABEL, DB_PASS_HELP); ?>

    <label for="DB_PCONNECT"><?php echo DB_PCONNECT_LABEL; ?>
        <input class="checkbox" type="checkbox" name="DB_PCONNECT" value="1" <?php echo $vars['DB_PCONNECT'] ? "'checked'" : ""; ?>/>
    </label>
    <div class="xoform-help"><?php echo DB_PCONNECT_HELP; ?></div>

</fieldset>

<fieldset>
    <legend><?php echo 'Skip Database'; ?></legend>
    <div>
        <?php echo 'If database is not utilized, you can skip database settings and the installer is finished.' ; ?>
    </div>
    <p>
        <button id='skip_database' type="button" accesskey="s" onclick="location.href='<?php echo $wizard->pageURI('finish'); ?>'">Skip database</button>
    </p>
</fieldset>

<?php
$content = ob_get_contents();
ob_end_clean();
include __DIR__ . '/include/install_tpl.php';