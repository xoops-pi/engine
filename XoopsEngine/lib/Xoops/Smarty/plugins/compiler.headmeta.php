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
 * Inserts a headMeta element
 * @see Xoops_Zend_View_Helper_HeadMeta
 *
 * <code>
 * <{headMeta key=keywords content="xoops, zend, meta" type=name lang=en scheme=test}>
 * </code>
 */


class Smarty_Compiler_HeadMeta  extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for the {headMeta} tag
     *
     * @param   array   $args array with attributes from parser
     * @param   object  $compiler compiler object
     * @return  string  compiled code
     */
    public function compile($args, $compiler)
    {
        $this->compiler = $compiler;
        $this->required_attributes = array("content");
        $this->optional_attributes = array('_any');
        $this->option_flags = array();
        // enter nocache mode
        $this->compiler->nocache = true;

        // check and get attributes
        $_attr = $this->_get_attributes($args);

        $str = "XOOPS::registry(\"view\")->headMeta(";
        $str .= $_attr['content'];
        unset($_attr['content']);
        if (!empty($_attr['key'])) {
            $str .= ", " . $_attr['key'];
            unset($_attr['key']);
        } else {
            $str .= ", null";
        }
        if (!empty($_attr['type'])) {
            $str .= ", " . $_attr['type'];
            unset($_attr['type']);
        } else {
            $str .= ", 'name'";
        }
        $str .= ", ";
        if (!empty($_attr['placement'])) {
            $placement = $_attr['placement'];
            unset($_attr['placement']);
        }
        $pars = array();
        foreach ($_attr as $k => $v) {
            $pars[] = var_export($k, true) . " => " . (empty($v) ? '""' : $v);
        }
        $str .= "array(" . implode(", ", $pars) . ")";
        //$str .= var_export($_attr, true);
        if (!empty($placement)) {
            $str .= ", " . $placement;
        }
        $str .= ")";

        return "<?php $str;?>";
        //return $compiler->insertNonCache("{$str};");
    }
}