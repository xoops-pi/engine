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
 * Call a view helper
 *
 * <code>
 * <{view helper=helperName var1=val1 var2=val2}>
 * </code>
 */

class Smarty_Compiler_View  extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for the {view} tag
     *
     * @param   array   $args array with attributes from parser
     * @param   object  $compiler compiler object
     * @return  string  compiled code
     */
    public function compile($args, $compiler)
    {
        $this->compiler = $compiler;
        $this->required_attributes = array("helper");
        $this->optional_attributes = array("_any");
        $this->option_flags = array();

        // check and get attributes
        $_attr = $this->_get_attributes($args);

        $helperName = $_attr["helper"];
        unset($_attr["helper"]);
        $pars = array();
        foreach ($_attr as $k => $v) {
            $pars[] = var_export($k, true) . " => " . (empty($v) ? '""' : $v);
        }
        $str = '' . PHP_EOL;
        $str .= "echo call_user_func_array(" . PHP_EOL;
        $str .= "   array(XOOPS::registry('view'), {$helperName})," . PHP_EOL;
        $str .= "   array(" . implode(", ", $pars) . ")" . PHP_EOL;
        $str .= ");" . PHP_EOL;
        return "<?php $str?>";
    }
}