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
 * Inserts the URL
 *
 * To ensure this can be as optimized as possible, it accepts 2 modes of operation:
 *
 * <b>Static address generation</b>:<br>
 *
 * <code>
 * // Generate an URL using a physical path (not recommended)
 * <{url path="/modules/something/yourpage.php" vara=vala varb=valb}>
 * </code>
 *
 * <b>Dynamic address generation</b>:<br>
 * The URL is generated dynamically each time the template is displayed, thus allowing
 * you to use the value of a template variable in the location string. To use it, you
 * must surround your location with double-quotes ("), and use the
 * {@link http://www.smarty.net/manual/en/language.syntax.quotes.php Smarty quoted strings}
 * syntax to insert variables values.
 *
 * <code>
 * // Use the value of the $sortby template variable in the URL
 * <{url path="/modules/something/yourpage.php?order=$sortby"}>
 * // Generate an URL using smarty variables in parameters
 * <{url path="/modules/$xoops_dirname/yourpage.php" vara=$vala varb=$valb}>
 * </code>
 */


class Smarty_Compiler_Url  extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for the {Url} tag
     *
     * @param   array   $args array with attributes from parser
     * @param   object  $compiler compiler object
     * @return  string  compiled code
     */
    public function compile($args, $compiler)
    {
        $this->compiler = $compiler;
        $this->required_attributes = array("path");
        $this->optional_attributes = array("_any");
        $this->option_flags = array();

        // check and get attributes
        $_attr = $this->_get_attributes($args);
        if (array_key_exists('nocache', $_attr)) {
            unset($_attr['nocache']);
        }

        $url = $_attr['path'];
        unset($_attr['path']);

        if ($url == '.') {
            $url = $_SERVER['REQUEST_URI'];
        }
        $url = "XOOPS::url({$url}, true)";
        if (!empty($_attr)) {
            $pars = array();
            foreach ($_attr as $k => $v) {
                $pars[] = var_export($k, true) . " => " . (empty($v) ? '""' : $v);
            }
            $url = "XOOPS::buildUrl({$url}, ";
            $url .= "array(" . implode(", ", $pars) . ")";
            //$url .= var_export($_attr, true);
            $url .= ")";
        }

        return "<?php echo $url;?>";
    }
}
