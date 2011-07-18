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

$pageProceed = false;
$pagePending = false;
$pageHasHelp = true;
$skip = true;

$resultSystem = $wizard->checkSystem();
$resultExtension = $wizard->checkExtension();
$valid = 1;
foreach ($resultSystem as $item => $result) {
    $valid = min($valid, $result['status']);
    if ($valid < 1) {
        $skip = false;
    }
    if ($valid < 0) {
        $pagePending = true;
        break;
    }
}
foreach ($resultExtension as $item => $result) {
    $valid = min($valid, $result['status']);
    if ($valid < 0) {
        $skip = false;
        $pagePending = true;
        break;
    }
}

if ($skip && empty($_GET['r'])) {
    $pageProceed = true;
}


ob_start();
?>


<fieldset>
    <legend><?php echo _INSTALL_REQUIREMENT_SYSTEM; ?></legend>
    <div class="xoform-help" style="display: block;"><?php echo _INSTALL_REQUIREMENT_SYSTEM_HELP; ?></div>
    <?php
    foreach ($resultSystem as $item => $result) {
    ?>
    <label for="<?php echo $item; ?>"><?php echo $result['title']; ?></label>
    <?php
        $value = $result['value'];
        $style = 'success';
        switch ($result['status']) {
            case -1:
                $style = 'failure';
                $value = $value ?: _INSTALL_REQUIREMENT_INVALID;
                break;
            case 0:
                $style = 'warning';
                $value = $value ?: _INSTALL_REQUIREMENT_UPDATE;
                break;
            case 1:
            default:
                $style = 'success';
                $value = $value ?: _INSTALL_REQUIREMENT_VALID;
                break;
        }
    ?>
    <ul class="diags"><li class="<?php echo $style; ?>"><?php echo $value; ?></li></ul>

    <?php
        if (!empty($result['message'])) {
    ?>
    <div class="xoform-help"><?php echo $result['message']; ?></div>
    <?php
        }
    }
    ?>
    <br style="clear: both;" />
</fieldset>

<fieldset>
    <legend><?php echo _INSTALL_REQUIREMENT_EXTENSION; ?></legend>
    <div class="xoform-help" style="display: block;"><?php echo _INSTALL_REQUIREMENT_EXTENSION_HELP; ?></div>
    <?php
    foreach ($resultExtension as $item => $result) {
    ?>
    <label for="<?php echo $item; ?>"><?php echo $result['title']; ?></label>
    <?php
        $value = $result['value'];
        $style = 'success';
        switch ($result['status']) {
            case -1:
                $style = 'failure';
                $value = $value ?: _INSTALL_REQUIREMENT_INVALID;
                break;
            case 0:
                $style = 'warning';
                $value = $value ?: _INSTALL_REQUIREMENT_UPDATE;
                break;
            case 1:
            default:
                $style = 'success';
                $value = $value ?: _INSTALL_REQUIREMENT_VALID;
                break;
        }
    ?>
    <ul class="diags"><li class="<?php echo $style; ?>"><?php echo $value; ?></li></ul>

    <?php
        if (!empty($result['message'])) {
    ?>
    <div class="xoform-help"><?php echo $result['message']; ?></div>
    <?php
        }
    }
    ?>
    <br style="clear: both;" />
</fieldset>

<?php
$content = ob_get_contents();
ob_end_clean();

include __DIR__ . '/include/install_tpl.php';