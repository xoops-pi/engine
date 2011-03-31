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
 * @copyright       Xoops Engine http://www.xoopsengine.org/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         Xoops_Editor
 * @version         $Id$
 */


namespace Xoops\Editor\Xoops;
//use Xoops\Editor\AbstractEditor;

class Handler extends \Xoops\Editor\AbstractEditor
{
    /**
     * The default number of rows for a textarea.
     *
     * @access protected
     *
     * @var int
     */
    protected $rows = 10;

    /**
     * The default number of columns for a textarea.
     *
     * @access protected
     *
     * @var int
     */
    protected $cols = 50;

    /**
     * Renders editor contents
     *
     * @param  Zend_View_Abstract $view
     * @return string
     */
    public function render(\Zend_View_Interface $view)
    {
        $attribs = $this->attribs;
        if (empty($attribs['rows'])) {
            $attribs['rows'] = (int) $this->rows;
        }
        if (empty($attribs['cols'])) {
            $attribs['cols'] = (int) $this->cols;
        }

        // build the element
        $xhtml = '<textarea name="' . $view->escape($this->name) . '"'
                . ' id="' . $view->escape($this->id) . '"'
                . $view->getHelper('formEditor')->htmlAttribs($attribs) . '>'
                . $view->escape($this->value) . '</textarea>';

        return $xhtml;
    }
}