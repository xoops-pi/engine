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
 * Inserts an htmlImage element
 * @see @Xoops_Zend_View_Helper_HtmlImage
 *
 * <code>
 * <{htmlImage src=uri alt='short description' width=80}>
 * </code>
 */

class Smarty_Compiler_HtmlImage extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for the {htmlImage} tag
     *
     * @param   array   $args array with attributes from parser
     * @param   object  $compiler compiler object
     * @return  string  compiled code
     */
    public function compile($args, $compiler)
    {
        $this->compiler = $compiler;
        $this->required_attributes = array("src");
        $this->optional_attributes = array("_any");
        $this->option_flags = array();

        // check and get attributes
        $_attr = $this->_get_attributes($args);

        $src = $_attr['src'];
        unset($_attr['src']);
        if (!isset($_attr['alt'])) {
            $alt = "";
        } else {
            $alt = (string) $_attr['alt'];
            unset($_attr['alt']);
        }
        $pars = array();
        foreach ($_attr as $k => $v) {
            $pars[] = var_export($k, true) . " => " . (empty($v) ? '""' : $v);
        }
        //$str = "XOOPS::registry(\"view\")->htmlImage({$src}, {$alt}, array(" . implode(", ", $pars) . "))";
        $str = "XOOPS::registry(\"view\")->htmlImage({$src}, {$alt}, ";
        $str .= "array(" . implode(", ", $pars) . ")";
        //$str .= var_export($_attr, true);
        $str .= ")";
        return "<?php echo {$str};?>";
    }
}