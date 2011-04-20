<?php
/**
 * Installer language selection page
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
$wizard->persistentData = array();


// Fetch language list from global language folder
$installPath = './language';
$langPath = '../language';
// Available translations
$iterator = new DirectoryIterator($langPath);
$localeFile = $langPath . '/' . $wizard->language . '/locales.php';
$locales = file_exists($localeFile) ? include $localeFile : array();
// Container for available locales
$localeList = array();
// Container for available languages
$languageList = array();
foreach ($iterator as $fileinfo) {
    if (!$fileinfo->isDir() || $fileinfo->isDot()) {
        continue;
    }
    $languageName = $fileinfo->getFilename();
    if (!is_dir($installPath . '/' . $languageName)) {
        continue;
    }

    $localeFile = $fileinfo->getPathname() . '/locale.ini.php';
    if (!file_exists($localeFile)) {
        list($language, $charset) = array('', '');
    } else {
        $locale = parse_ini_file($localeFile);
        $language = $locale['lang'];
        $charset = empty($locale['charset']) ? 'UTF-8' : $locale['charset'];
        $localeList[$languageName] = array($language, $charset);
    }

    $languageList[$languageName] = isset($locales[$language]) ? $locales[$language] : $languageName;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['language'])) {
    $success = true;
    $language = htmlspecialchars($_POST['language']);
    if (isset($localeList[$language])) {
        $wizard->setLocale($localeList[$language]);
    } elseif (!empty($_POST['lang']) && !empty($_POST['charset'])) {
        $wizard->setLocale(array(htmlspecialchars($_POST['lang']), htmlspecialchars($_POST['charset'])));
    } else {
        $success = false;
    }
    if ($success && $wizard->setLanguage($language)) {
        //$wizard->setLanguage($language);
        $wizard->redirectToPage('+1');
        exit();
    }
}

$pageHasForm = true;
$pageHasHelp = true;
//$content = '<select name="language" size="10">';

/*
// Fetch language list from global language folder
$installPath = './language';
$langPath = '../language';
// Available translations
$iterator = new DirectoryIterator($langPath);
$localeFile = $langPath . '/' . $wizard->language . '/locales.php';
$locales = file_exists($localeFile) ? include $localeFile : array();
// Container for available locales
$localeList = array();
// Container for available languages
$languageList = array();
foreach ($iterator as $fileinfo) {
    if (!$fileinfo->isDir() || $fileinfo->isDot()) {
        continue;
    }
    $languageName = $fileinfo->getFilename();
    if (!is_dir($installPath . '/' . $languageName)) {
        continue;
    }

    $localeFile = $fileinfo->getPathname() . '/locale.ini.php';
    if (!file_exists($localeFile)) {
        list($language, $charset) = array('', '');
    } else {
        $locale = parse_ini_file($localeFile);
        $language = $locale['lang'];
        $charset = empty($locale['charset']) ? 'UTF-8' : $locale['charset'];
        $localeList[$languageName] = array($language, $charset);
    }

    $languageList[$languageName] = isset($locales[$language]) ? $locales[$language] : $languageName;
    //$languageString .= " - " . $charset . " ({$languageName})";
    //$sel = ($languageName == $wizard->language) ? ' selected="selected"' : '';
    //$content .= "<option value=\"{$languageName}\"{$sel}>{$languageString}</option>\n";
}
//$content .= "</select>";
*/

ob_start();
?>
<script type="text/javascript">
var localeList = new Array();
<?php
    foreach ($localeList as $language => $locale) {
        echo "localeList['" . $language . "'] = ['" . $locale[0] . "', '" . $locale[1] . "'];" . PHP_EOL;
    }
?>
function setLocale(languageValue)
{
    if (languageValue in localeList) {
        $('lang').value = localeList[languageValue][0];
        $('charset').value = localeList[languageValue][1];
        $('lang').disabled = true;
        $('charset').disabled = true;
    } else {
        $('lang').value = '';
        $('charset').value = '';
        $('lang').disabled = false;
        $('charset').disabled = false;
    }
}
</script>

<fieldset>
    <legend><?php echo LEGEND_LANGUAGE; ?></legend>
    <label for="language"><?php echo LANGUAGE_LABEL ; ?></label>
    <div class='xoform-help'><?php echo LANGUAGE_HELP;?></div>
    <select size="10" name="language" onchange='setLocale(this.options[this.selectedIndex].value)'>
        <?php
        foreach ($languageList as $language => $name) {
            $selected = ($language == $wizard->language) ? " selected='selected'" : "";
            echo "<option value='{$language}'{$selected}>{$name}</option>";
        }
        ?>
    </select>
</fieldset>

<fieldset>
    <legend><?php echo LEGEND_LOCALE;?></legend>
    <label for='lang'><?php echo LOCALE_LANG_LABEL;?></label>
    <div class='xoform-help'><?php echo LOCALE_LANG_HELP;?></div>
    <input type='text' name='lang' id='lang' value="<?php echo $wizard->locale['lang'];?>" disabled='disabled' />

    <label for='lang'><?php echo LOCALE_CHARSET_LABEL;?></label>
    <div class='xoform-help'><?php echo LOCALE_CHARSET_HELP;?></div>
    <input type='text' name='charset' id='charset' value="<?php echo $wizard->locale['charset'];?>" disabled='disabled' />
    </label>
</fieldset>

<?php
$content = ob_get_contents();
ob_end_clean();

include __DIR__ . '/include/install_tpl.php';