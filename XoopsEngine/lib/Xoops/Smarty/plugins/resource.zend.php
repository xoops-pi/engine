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

class Smarty_Resource_Zend extends Xoops_Smarty_resource
{
    // properties
    public $usesCompiler = false;

    /**
     * Read template source from file
     *
     * @param object $_template template object
     * @return string content of template source file
     */
    public function getTemplateSource($_template)
    {
        // read template file
        if (file_exists($_template->getTemplateFilepath())) {
            $_template->template_source = file_get_contents($_template->getTemplateFilepath());
            $_template->template_source = str_replace('$this->', 'XOOPS::registry("view")->', $_template->template_source);
            return true;
        } else {
            return false;
        }
    }
}