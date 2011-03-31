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
 * Inserts a paginator
 *
 * <code>
 * <{paginator data=$paginatorData [scrollingStyle=Sliding] [partial="paginator.html"] [var=val vara=vala]}>
 * </code>
 */

class Smarty_Compiler_Paginator  extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for the {paginator} tag
     *
     * @param   array   $args array with attributes from parser
     * @param   object  $compiler compiler object
     * @return  string  compiled code
     */
    public function compile($args, $compiler)
    {
        $this->compiler = $compiler;
        $this->required_attributes = array("data");
        $this->optional_attributes = array("_any");
        $this->option_flags = array();

        // check and get attributes
        $_attr = $this->_get_attributes($args);

        $data = $_attr['data'];
        unset($_attr['data']);
        if (!isset($_attr['scrollingStyle'])) {
            $scrollingStyle = "null";
        } else {
            $scrollingStyle = $_attr['scrollingStyle'];
            unset($_attr['scrollingStyle']);
        }
        if (!isset($_attr['partial'])) {
            $partial = "'theme/paginator.html'";
        } else {
            $partial = $_attr['partial'];
            unset($_attr['partial']);
        }
        $pars = array();
        foreach ($_attr as $k => $v) {
            $pars[] = var_export($k, true) . " => " . (empty($v) ? '""' : $v);
        }
        $params_string = "array(" . implode(", ", $pars) . ")";
        $str = "XOOPS::registry('view')->PaginationControl({$data}, {$scrollingStyle}, {$partial}";
        $str .= ", ";
        $str .= $params_string;
        //$str .= var_export($_attr, true);
        $str .= ")";
        return "<?php echo {$str};?>";
    }
}