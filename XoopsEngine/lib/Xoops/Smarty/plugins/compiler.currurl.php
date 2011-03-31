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
 * Inserts URL of an application page and inherites parameters from current page
 *
 * <b>Static address generation</b>:<br>
 * <code>
 * // Generate an URL using variables
 * <{currUrl var1=val1 var2=val2}>
 * // Generate an URL using specified route and variables
 * <{currUrl route=default var=val}>
 * </code>
 *
 * <b>Dynamic address generation</b>:<br>
 * <code>
 * // Generate an URL using a specified route and variables
 * <{currUrl route=default var1=$val1 var2=val2}>
 * // Generate an URL using variable route and variables
 * <{currUrl route=$route var=$val}>
 * </code>
 */

class Smarty_Compiler_CurrUrl  extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for the {currUrl} tag
     *
     * @param   array   $args array with attributes from parser
     * @param   object  $compiler compiler object
     * @return  string  compiled code
     */
    public function compile($args, $compiler)
    {
        $this->compiler = $compiler;
        //$this->required_attributes = array();
        $this->optional_attributes = array("_any");
        $this->option_flags = array();

        // check and get attributes
        $_attr = $this->_get_attributes($args);

        $route = "null";
        if (isset($params["route"])) {
            $route = $params["route"];
            unset($params["route"]);
        }
        $pars = array();
        foreach ($params as $k => $v) {
            $pars[] = var_export($k, true) . " => " . (empty($v) ? '""' : $v);
        }
        $route = empty($route) ? "null" : $route;
        $str = "XOOPS::registry('view')->url(";
        $str .= "array(" . implode(", ", $pars) . ")";
        //$str .= var_export($_attr, true);
        $str .= ", {$route}, false)";
        return "<?php echo {$str};?>";
    }
}