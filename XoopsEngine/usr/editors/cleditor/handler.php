<?php
/**
 * Xoops Engine Editor Default
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Xoops Engine http://www.xoopsengine.org
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         Xoops_Editor
 * @version         $Id$
 */


namespace Editor\Cleditor;

class Handler extends \Xoops\Editor\AbstractEditor
{
    /**
     * Renders editor contents
     *
     * @param  Zend_View_Abstract $view
     * @return string
     */
    public function render(\Zend_View_Interface $view)
    {
        $view->jQuery(array(
            'extensions/cleditor/jquery.cleditor.css',
            'extensions/cleditor/jquery.cleditor.js',
        ));
        $view->headScript('script', '$(document).ready(function() { $("#' . $this->id . '").cleditor(); });');

        // build the element
        $xhtml = '<textarea name="' . $view->escape($this->name) . '"'
                . ' id="' . $view->escape($this->id) . '"'
                . $view->getHelper('formEditor')->htmlAttribs($this->attribs) . '>'
                . $view->escape($this->value) . '</textarea>';

        return $xhtml;
    }
}