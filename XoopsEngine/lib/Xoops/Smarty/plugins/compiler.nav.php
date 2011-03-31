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
 * Inserts a custome navigation
 *
 * <code>
 * <{nav name=navigationName template="navigation.html" ul_class=val1 cache_expire=val2 cache_id=val3}>
 * </code>
 *
 * A global navigation can be inserted via: (cache parameters for a global navigation are set via layout automatically)
 *
 * <code>
 * <{navigation name=navigationName [template="navigation.html"] [ul_class=val1]}>
 * </code>
 */

class Smarty_Compiler_Nav extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for the {nav} tag
     *
     * @param   array   $args array with attributes from parser
     * @param   object  $compiler compiler object
     * @return  string  compiled code
     */
    public function compile($args, $compiler)
    {
        $this->compiler = $compiler;
        $this->required_attributes = array("name");
        $this->optional_attributes = array("_any");
        $this->option_flags = array();

        // check and get attributes
        $_attr = $this->_get_attributes($args);

        $name = $_attr["name"];
        unset($_attr["name"]);

        $pars = array();
        foreach ($_attr as $k => $v) {
            $pars[] = var_export($k, true) . " => " . (empty($v) ? '""' : $v);
        }
        $str = "XOOPS::registry(\"view\")->nav({$name}, ";
        $str .= "array(" . implode(", ", $pars) . ")";
        $str .= ")";
        return "<?php echo $str;?>";
    }
}