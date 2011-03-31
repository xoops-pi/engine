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
 * Inserts jQuery files
 *
 * <code>
 * <{jQuery file=[file1, file2, "$path/file3"]}>
 * </code>
 */
class Smarty_Compiler_JQuery  extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for the {jQuery} tag
     *
     * @param   array   $args array with attributes from parser
     * @param   object  $compiler compiler object
     * @return  string  compiled code
     */
    public function compile($args, $compiler)
    {
        $this->compiler = $compiler;
        //$this->required_attributes = array("file");
        $this->optional_attributes = array('_any');
        $this->option_flags = array();
        // never compile as nocache code
        $this->compiler->suppressNocacheProcessing = true;
        $this->compiler->tag_nocache = true;

        // check and get attributes
        $_attr = $this->_get_attributes($args);
        //$params = is_string($_attr["file"]) ? array($_attr["file"]) : $_attr["file"];

        //Debug::e(is_string($_attr["file"]));
        //Debug::e($params);
        $str = "XOOPS::registry(\"view\")->jQuery(";
        $str .= empty($_attr["file"]) ? "'jquery.min.js'" : $_attr["file"];
        $str .= ")";
        return "<?php $str;?>";
        //return $compiler->insertNonCache($str);
    }
}