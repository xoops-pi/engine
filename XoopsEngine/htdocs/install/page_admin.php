<?php
/**
 * Installer site configuration page
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

$xoopsOption['hascommon'] = true;
require_once __DIR__ . '/include/common.inc.php';
if (!defined('XOOPS_INSTALL')) { die('XOOPS Installation wizard die'); }

$pageHasForm = true;

$vars =& $wizard->persistentData['siteconfig'];
$error =& $wizard->persistentData['error'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vars['adminname'] = $_POST['adminname'];
    $vars['adminmail'] = $_POST['adminmail'];
    $vars['adminpass'] = $_POST['adminpass'];
    $vars['adminpass2'] = $_POST['adminpass2'];
    $error = array();

    if (empty($vars['adminname'])) {
        $error['name'][] = ERR_REQUIRED;
    }
    if (empty($vars['adminmail'])) {
        $error['email'][] = ERR_REQUIRED;
    }
    if (empty($vars['adminpass'])) {
        $error['pass'][] = ERR_REQUIRED;
    }
    if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+([\.][a-z0-9-]+)+$/i", $vars['adminmail'])) {
        $error['email'][] = ERR_INVALID_EMAIL;
    }
    if ($vars['adminpass'] != $vars['adminpass2']) {
        $error['pass'][] = ERR_PASSWORD_MATCH;
    }
    if ($error) {
        $wizard->redirectToPage('+0');
        return 200;
    } else {
        $configModel = XOOPS::getModel("config");
        $configModel->update(array("value" => $vars['adminmail']), array("name = ?" => "adminmail"));
        $rootRow = XOOPS::getModel("user_root")->createRow(array(
            "identity"      => $vars['adminname'],
            "credential"    => $vars['adminpass'],
            "email"         => $vars['adminmail'],
        ));
        if (!$rootRow->save($data)) {
            $wizard->redirectToPage('+0');
        }
        $wizard->redirectToPage('+1');
        return 302;
    }
} else {
    $rootModel = XOOPS::getModel("user_root");
    $select = $rootModel->select()->from($rootModel, array("count" => new Zend_Db_Expr("COUNT(*)")));
    $row = $rootModel->fetchRow($select);
    $hasAdmin = $row->count;
}

ob_start();

if ($hasAdmin) {
    $pageHasForm = false;
    echo "<div class='x2-note errorMsg'>" . ADMIN_EXIST . "</div>\n";
} else {
    $vars['adminname'] = empty($vars['adminname']) ? "root" : $vars['adminname'];
    if (empty($vars['adminmail'])) {
        $hostname = preg_replace('/^www\./i', '', $_SERVER['SERVER_NAME']);
        if (false === strpos($hostname, '.')) {
            $hostname .= '.com';
        }
        $vars['adminmail'] = $vars['adminname'] . '@' . $hostname;
    }
?>
    <fieldset>
        <legend><?php echo LEGEND_ADMIN_ACCOUNT; ?></legend>

        <?php
        echo xoFormField('adminmail', $vars['adminmail'], ADMIN_EMAIL_LABEL);
        if (!empty($error["email"])) {
            echo '<ul class="diags">';
            foreach ($error["email"] as $errmsg) {
                echo '<li class="failure">' . $errmsg . '</li>';
            }
            echo '</ul>';
        }
        echo xoFormField('adminname', $vars['adminname'], ADMIN_LOGIN_LABEL);
        if (!empty($error["name"])) {
            echo '<ul class="diags">';
            foreach ($error["name"] as $errmsg) {
                echo '<li class="failure">' . $errmsg . '</li>';
            }
            echo '</ul>';
        }
        ?>

        <div id="password">
            <div id="passwordinput">
            <?php
            echo xoPassField('adminpass', '', ADMIN_PASS_LABEL);
            echo xoPassField('adminpass2', '', ADMIN_CONFIRMPASS_LABEL);
            if (!empty($error["pass"])) {
                echo '<ul class="diags">';
                foreach ($error["pass"] as $errmsg) {
                    echo '<li class="failure">' . $errmsg . '</li>';
                }
                echo '</ul>';
                echo '<br style="clear: both;" />';
            }
            ?>
            </div>
        </div>
        <br style="clear: both;" />
    </fieldset>
<?php
}
$content = ob_get_contents();
ob_end_clean();
$error = '';
include __DIR__ . '/include/install_tpl.php';