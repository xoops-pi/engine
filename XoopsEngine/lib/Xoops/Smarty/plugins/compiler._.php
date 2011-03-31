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
 * Inserts a gettext translation
 *
 * <code>
 * // Generate a static translation
 * <{_ text="This is a test"}>
 * // Generate a dynamic translation
 * <{_ text=$xo_message}>
 * <{_ text="My text of $xo_message"}>
 * </code>
 */

class Smarty_Compiler__  extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for the {_} tag
     *
     * @param   array   $args array with attributes from parser
     * @param   object  $compiler compiler object
     * @return  string  compiled code
     */
    public function compile($args, $compiler)
    {
        $this->compiler = $compiler;
        $this->required_attributes = array("text");
        //$this->optional_attributes = array();
        $this->option_flags = array();

        // check and get attributes
        $_attr = $this->_get_attributes($args);
        $message = $_attr["text"];
        $str = "XOOPS::_($message)";
        return "<?php echo {$str};?>";
    }
}