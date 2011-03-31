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
 * Inserts a headScript element
 * @see Xoops_Zend_View_Helper_HeadScript
 *
 * <code>
 * <{headScript src='src' source='source' placement='append' type='text/javascript' defer=defer charset=utf-8 language=en-US}>
 * </code>
 */


class Smarty_Compiler_HeadScript  extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for the {headScript} tag
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
        // enter nocache mode
        $this->compiler->nocache = true;

        // check and get attributes
        $_attr = $this->_get_attributes($args);

        if (empty($_attr['source']) && empty($_attr['src'])) return false;
        if (empty($_attr['source'])) {
            $mode = '"file"';
            $spec = $_attr['src'];
            unset($_attr['src']);
        } else {
            $mode = '"script"';
            $spec = $_attr['source'];
            unset($_attr['source']);
        }

        $placement = '"append"';
        if (!empty($_attr['placement'])) {
            $placement = $_attr['placement'];
            unset($_attr['placement']);
        }

        if (!empty($_attr['type'])) {
            $type = $_attr['type'];
            unset($_attr['type']);
        }
        $pars = array();
        foreach ($_attr as $k => $v) {
            $pars[] = var_export($k, true) . " => " . (empty($v) ? '""' : $v);
        }
        $str = "XOOPS::registry(\"view\")->headScript({$mode}, {$spec}, {$placement}";
        $str .= ", ";
        $str .= "array(" . implode(", ", $pars) . ")";
        //$str .= var_export($_attr, true);
        if (!empty($type)) {
            $str .= ", " . $type;
        }
        $str .= ")";

        return "<?php $str;?>";
        //return $compiler->insertNonCache($str);
    }
}