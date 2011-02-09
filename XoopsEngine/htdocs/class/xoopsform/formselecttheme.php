<?php
/**
 * XOOPS Framework
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license         BSD liscense
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         form
 * @version         $Id$
 */

//include_once XOOPS_ROOT_PATH . "/class/xoopsform/formselect.php";

class XoopsFormSelectTheme extends XoopsFormSelect
{
    private $useImage = true;
    private $themes = array();

    /**
     * Constructor
     *
     * @param    string    $caption
     * @param    string    $name
     * @param    mixed    $value    Pre-selected value (or array of them).
     * @param    boolean    $multiple Multiple selection
     * @param    boolean    $image To display theme screenshot
     */
    public function __construct($caption, $name, $value = null, $multiple = false, $image = true)
    {
        $image = false;
        $themes = XOOPS::service("registry")->theme->read();
        if (!$image) {
            foreach ($themes as $key => &$theme) {
                $theme = $theme["title"] . " ({$key})";
            }
            if ($multiple) {
                $this->XoopsFormSelect($caption, $name, $value, 5, true);
            } else {
                $this->XoopsFormSelect($caption, $name, $value, 1);
            }
            $this->addOptionArray($themes);
            $this->useImage = false;
            return;
        } else {
            $this->themes = $themes;
        }
    }

    /**
     * Prepare HTML for output
     *
     * @return    string  HTML
     */
    public function render()
    {
        if (!$this->useImage) {
            return parent::render();
        }

        // TODO
        $theme = array(
            // Title of the theme
            "title"         => "XoopsTheme",
            // Author information: name, email, website
            "author"        => "ThemeAuthor",
            // Screenshot image
            // "images/screenshot.jpg" refers to  www/themes/themeName/images/screenshot.jpg
            // or "screenshot.jpg" refers to  www/themes/themeName/screenshot.jpg
            "screenhot"     => "screenshot.png",
            // License
            "license"       => "ThemeLicense",
        );
        $ele_name = $this->getName();
        $ele_value = $this->getValue();
        $ele_options = $this->getOptions();
        $ret = "<select  " . ($this->getDisabled() ? "disabled " : "") . "size='" . $this->getSize() . "'" . $this->getExtra();
        if ($this->isMultiple() != false) {
            $ret .= " name='{$ele_name}[]' id='{$ele_name}' multiple='multiple'>\n";
        } else {
            $ret .= " name='{$ele_name}' id='{$ele_name}'>\n";
        }
        foreach ( $ele_options as $value => $name ) {
            $ret .= "<option value='" . htmlspecialchars($value, ENT_QUOTES) . "'";
            if (count($ele_value) > 0 && in_array($value, $ele_value)) {
                    $ret .= " selected='selected'";
            }
            $ret .= ">{$name}</option>\n";
        }
        $ret .= "</select>";
        return $ret;
    }

}
?>