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
 * @copyright       The Xoops Engine
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         Xoops_Smarty
 * @version         $Id$
 */

/**
 * Inserts a named menu
 *
 * <code>
 * <{menu navigation=navigationName var1=val1 var2=val2 ...}>
 * </code>
 */


class Smarty_Compiler_Menu extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for the {menu} tag
     *
     * @param   array   $args array with attributes from parser
     * @param   object  $compiler compiler object
     * @return  string  compiled code
     */
    public function compile($args, $compiler)
    {
        $this->compiler = $compiler;
        $this->required_attributes = array("navigation");
        $this->optional_attributes = array("_any");
        $this->option_flags = array();

        // check and get attributes
        $_attr = $this->_get_attributes($args);

        $ulClass = empty($_attr["ulClass"]) ? "'jd_menu'" : $_attr["ulClass"];
        $str = '' . PHP_EOL;
        $str .= '$view = XOOPS::registry("view");' . PHP_EOL;
        $str .= '$module = XOOPS::registry("frontController")->getRequest()->getModuleName();' . PHP_EOL;
        $str .= 'XOOPS::service("registry")->navigation->read($_attr["navigation"], $module);' . PHP_EOL;
        $str .= '$container = new Xoops_Zend_Navigation($config);' . PHP_EOL;
        $str .= '$view->navigation($container);' . PHP_EOL;
        $str .= 'echo $view->navigation()->menu()->setUlClass(' . $ulClass . ')->render();' . PHP_EOL;
        return "<?php {$str}?>";
    }
}