<?php
/**
 * Smarty compiler plugin for Xoops Engine
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
 * Load locale translation
 *
 *
 * <b>Static content translation</b>:<br>
 *
 * <code>
 * // Load local translation
 * <{translation data=file[ domain='domain:key']}>
 * // Load global translation
 * <{translation data=file domain=global}>
 * </code>
 *
 * <b>Dynamic loading</b>:<br>
 *
 * <code>
 * <{translation data=$file domain='domain:key'}>
 * <{translation data=$file domain="$domain:$key"}>
 * </code>
 */


class Smarty_Compiler_Translation extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for the {translation} tag
     *
     * @param   array   $args array with attributes from parser
     * @param   object  $compiler compiler object
     * @return  string  compiled code
     */
    public function compile($args, $compiler)
    {
        $this->compiler = $compiler;
        $this->required_attributes = array("data");
        $this->optional_attributes = array("domain");
        $this->option_flags = array();

        // check and get attributes
        $_attr = $this->_get_attributes($args);

        $data = $_attr["data"];
        if (!empty($_attr["domain"])) {
            $domain = $_attr["domain"];
        } else {
            $domain = "'theme:" . XOOPS::registry("view")->getTheme() . "'";
        }

        $str = "XOOPS::service('translate')->loadTranslation({$data}, {$domain})";
        return "<?php {$str};?>";
    }
}