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
 * Inserts the URL of a file resource customizable by themes
 *
 * This plug-in works like the {@link Smarty_Compiler_ImgUrl() imgUrl} plug-in,
 * except that it is intended to generate the URL of resource files customizable by
 * themes.
 *
 * Here the current theme is asked to check if a custom version of the requested file exists, and
 * if one is found its URL is returned.
 *
 * <code>
 * // Generate an resourceUrl using specified resourcePath
 * <{resourceUrl path=$resourcePath}>
 * <{resourceUrl path="app/moduleName/resources/images/img.png"}>
 * </code>
 */

class Smarty_Compiler_ResourceUrl  extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for the {resourceUrl} tag
     *
     * @param   array   $args array with attributes from parser
     * @param   object  $compiler compiler object
     * @return  string  compiled code
     */
    public function compile($args, $compiler)
    {
        $this->compiler = $compiler;
        $this->required_attributes = array("path");
        //$this->optional_attributes = array();
        $this->option_flags = array();

        // check and get attributes
        $_attr = $this->_get_attributes($args);

        $url = $_attr["path"];
        $str = "XOOPS::registry('view')->resourcePath({$url})";
        return "<?php echo XOOPS::url({$str});?>";
    }
}