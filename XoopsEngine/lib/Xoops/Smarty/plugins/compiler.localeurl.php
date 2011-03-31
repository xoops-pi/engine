<?php
/**
 * XOOPS smarty compiler plugin
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Xoops Engine http://www.xoopsengine.org/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         Xoops_Smarty
 * @version         $Id$
 */

/**
 * Inserts the URL of a locale resource
 *
 * This plug-in allows you to generate an application URL for accessing localization file. It uses any URL rewriting
 * mechanism and rules you'll have configured for the system.
 *
 * To ensure this can be as optimized as possible, it accepts 2 modes of operation:
 *
 * <b>Static address generation</b>:<br>
 * When used, the URL is generated during
 * the template compilation, and statically written in the compiled template file.
 * To use it, you just need to provide a location in a format XOOPS understands.
 *
 * <code>
 * // Generate a locale URL mapping a domain resoruce
 * <{localeUrl path=path[ domain='domain:key']}>
 * // Generate a locale URL mapping a globale resoruce
 * <{localeUrl path=path domain=global}>
 * </code>
 *
 * <b>Dynamic address generation</b>:<br>
 * The URL is generated dynamically each time the template is displayed, thus allowing
 * you to use the value of a template variable in the location string. To use it, you
 * must surround your location with double-quotes ("), and use the
 * {@link http://smarty.php.net/manual/en/language.syntax.quotes.php Smarty quoted strings}
 * syntax to insert variables values.
 *
 * <code>
 * // Generate a locale URL mapping a domain resoruce with variable path
 * <{localeUrl path=$path domain='domain:key'}>
 * // Generate a locale URL mapping a variable domain resoruce with path
 * <{localeUrl path=$path domain="$domain:$key"}>
 * </code>
 */

class Smarty_Compiler_LocaleUrl  extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for the {localeUrl} tag
     *
     * @param   array   $args array with attributes from parser
     * @param   object  $compiler compiler object
     * @return  string  compiled code
     */
    public function compile($args, $compiler)
    {
        $this->compiler = $compiler;
        $this->required_attributes = array("path");
        $this->optional_attributes = array("domain");
        $this->option_flags = array();

        // check and get attributes
        $_attr = $this->_get_attributes($args);
        $path = $_attr["path"];
        $domain = isset($_attr["domain"]) ? $_attr["domain"] : "'theme:" . XOOPS::registry("view")->getTheme() . "'";
        $str = "XOOPS::localeUrl({$domain}, {$path})";
        return "<?php echo {$str};?>";
    }
}