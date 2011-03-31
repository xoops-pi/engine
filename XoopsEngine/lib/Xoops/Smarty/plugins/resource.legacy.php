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

class Smarty_Resource_Legacy extends Xoops_Smarty_resource
{
    /**
     * Formulate resource name
     *
     * @param string $resource_name template file name
     * @return string
     */
    protected function buildResourceName($resource_name)
    {
        return $resource_name;
    }

    /**
     * Formulate resource name
     *
     * @param string $resource_name template file name
     * @return string
     */
    protected function ____buildTemplateFilepath($resource_name)
    {
        //Debug::backtrace();
        list($module, $template) = explode('_', $resource_name, 2);
        if ('block_' === substr($template, 0, 6)) {
            $template = 'blocks/' . $resource_name;
        } else {
            $template = $resource_name;
        }
        $template = "module/{$module}/templates/{$template}";
        $path = XOOPS::registry("view")->resourcePath($template, true);
        //trigger_error("path:".$path);
        if (!file_exists($path)) {
            $path = false;
        }
        return $path;
    }
}