<?php
/**
 * See the enclosed file license.txt for licensing information.
 * If you did not receive this file, get it at http://www.fsf.org/copyleft/gpl.html
 *
 * @copyright       Xoops Engine
 * @package         installer
 * @since           3.0
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @author          Skalpa Keo <skalpa@xoops.org>
 * @version         $Id$
 */


function xoFormField($name, $value, $label, $help = '')
{
    $label = htmlspecialchars($label, ENT_QUOTES, $GLOBALS['installWizard']->locale['charset'], false);
    $name = htmlspecialchars($name, ENT_QUOTES, $GLOBALS['installWizard']->locale['charset'], false);
    $value = htmlspecialchars($value, ENT_QUOTES);
    echo "<label for='{$name}'>{$label}</label>\n";
    if ($help) {
        echo "<div class='xoform-help'>{$help}</div>\n";
    }
    if ($name == "adminname") {
        echo "<input type='text' name='{$name}' id='{$name}' value='{$value}' maxlength='25' />";
    } else {
        echo "<input type='text' name='{$name}' id='{$name}' value='{$value}' />";
    }
}

function xoPassField($name, $value, $label, $help = '')
{
    $label = htmlspecialchars($label, ENT_QUOTES, $GLOBALS['installWizard']->locale['charset'], false);
    $name = htmlspecialchars($name, ENT_QUOTES, $GLOBALS['installWizard']->locale['charset'], false);
    $value = htmlspecialchars($value, ENT_QUOTES);
    echo "<label for='{$name}'>{$label}</label>\n";
    if ($help) {
        echo "<div class='xoform-help'>{$help}</div>\n";
    }

    if ($name == "adminpass") {
        echo "<input type='password' name='{$name}' id='{$name}' value='{$value}' />";
    } else {
        echo "<input type='password' name='{$name}' id='{$name}' value='{$value}' />";
    }
}

function getDbCharsets($link)
{
    static $charsets = array();
    if ($charsets) return $charsets;

    $charsets["utf8"] = "UTF-8 Unicode";
    $ut8_available = false;
    if ($result = mysql_query("SHOW CHARSET", $link)) {
        while ($row = mysql_fetch_assoc($result)) {
            $charsets[$row["Charset"]] = $row["Description"];
            if ($row["Charset"] == "utf8") {
                $ut8_available = true;
            }
        }
    }
    if (!$ut8_available) {
        unset($charsets["utf8"]);
    }

    return $charsets;
}

function getDbCollations($link, $charset)
{
    static $collations = array();
    if (!empty($collations[$charset])) {
        return $collations[$charset];
    }

    if ($result = mysql_query("SHOW COLLATION WHERE CHARSET = '" . mysql_real_escape_string($charset) . "'", $link)) {
        while ($row = mysql_fetch_assoc($result)) {
            $collations[$charset][$row["Collation"]] = $row["Default"] ? 1 : 0;
        }
    }

    return $collations[$charset];
}

function validateDbCharset($link, &$charset, &$collation)
{
    $error = null;

    if (empty($charset)) {
        $collation = "";
    }
    if (version_compare(mysql_get_server_info($link), "4.1.0", "lt")) {
        $charset = $collation = "";
    }
    if (empty($charset) && empty($collation)) {
        return $error;
    }

    $charsets = getDbCharsets($link);
    if (!isset($charsets[$charset])) {
        $error = sprintf(ERR_INVALID_DBCHARSET, $charset);
    } elseif (!empty($collation)) {
        $collations = getDbCollations($link, $charset);
        if (!isset($collations[$collation])) {
            $error = sprintf(ERR_INVALID_DBCOLLATION, $collation);
        }
    }

    return $error;
}

function xoFormFieldCollation($name, $value, $label, $help, $link, $charset)
{
    if (version_compare(mysql_get_server_info($link), "4.1.0", "lt")) {
        return "";
    }
    if (empty($charset) || !$collations = getDbCollations($link, $charset)) {
        return "";
    }

    $label = htmlspecialchars($label, ENT_QUOTES, $GLOBALS['installWizard']->locale['charset'], false);
    $name = htmlspecialchars($name, ENT_QUOTES, $GLOBALS['installWizard']->locale['charset'], false);
    $value = htmlspecialchars($value, ENT_QUOTES);

    $field = "<label for='$name'>$label</label>\n";
    if ($help) {
        $field .= '<div class="xoform-help">' . $help . "</div>\n";
    }
    $field .= "<select name='$name' id='$name'\">";

    $collation_default = "";
    $options = "";
    foreach ($collations as $key => $isDefault) {
        if ($isDefault) {
            $collation_default = $key;
            continue;
        }
        $options .= "<option value='{$key}'" . (($value == $key) ? " selected='selected'" : "") . ">{$key}</option>";
    }
    if ($collation_default) {
        $field .= "<option value='{$collation_default}'" . (($value == $collation_default || empty($value)) ? " 'selected'" : "") . ">{$collation_default} (Default)</option>";
    }
    $field .= $options;
    $field .= "</select>";

    return $field;
}

function xoFormBlockCollation($name, $value, $label, $help, $link, $charset)
{
    $block = '<div id="' . $name . '_div">';
    $block .= xoFormFieldCollation($name, $value, $label, $help, $link, $charset);
    $block .= '</div>';

    return $block;
}


function xoFormFieldCharset($name, $value, $label, $help = '', $link)
{
    if (version_compare(mysql_get_server_info($link), "4.1.0", "lt")) {
        return "";
    }
    if (!$chars = getDbCharsets($link)) {
        return "";
    }

    $charsets = array();
    if (isset($chars["utf8"])) {
        $charsets["utf8"] = $chars["utf8"];
        unset ($chars["utf8"]);
    }
    ksort($chars);
    $charsets = array_merge($charsets, $chars);

    $label = htmlspecialchars($label, ENT_QUOTES, $GLOBALS['installWizard']->locale['charset'], false);
    $name = htmlspecialchars($name, ENT_QUOTES, $GLOBALS['installWizard']->locale['charset'], false);
    $value = htmlspecialchars($value, ENT_QUOTES);

    $field = "<label for='{$name}'>{$label}</label>\n";
    if ($help) {
        $field .= '<div class="xoform-help">' . $help . "</div>\n";
    }
    $field .= "<select name='{$name}' id='{$name}' onchange=\"setFormFieldCollation('DB_COLLATION_div', this.value)\">";
    $field .= "<option value=''>None</option>";
    foreach ($charsets as $key => $desc) {
        $field .= "<option value='{$key}'" . (($value == $key) ? " selected='selected'" : "") . ">{$key} - {$desc}</option>";
    }
    $field .= "</select>";

    return $field;
}