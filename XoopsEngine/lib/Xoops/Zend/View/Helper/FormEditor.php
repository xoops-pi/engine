<?php
/**
 * Zend Framework for Xoops Engine
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
 * @category        Xoops_Zend
 * @package         View
 * @version         $Id$
 */

class Xoops_Zend_View_Helper_FormEditor extends Zend_View_Helper_FormElement
{
    /**
     * Generates an editor element.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are used in place of added parameters.
     *
     * @param mixed $value The element value.
     *
     * @param array $attribs Attributes for the element tag.
     *
     * @param array $options options for editor
     *
     * @return string The element editor.
     */
    public function formEditor($name, $value = null, $attribs = null, $options = null)
    {
        $info = $this->_getInfo($name, $value, $attribs, $options);
        extract($info); // id, name, value, attribs, options, listsep, disable

        $type = null;
        if (isset($options['type'])) {
            $type = $options['type'];
            unset($options['type']);
        }
        $options['attribs'] = (array) $attribs;
        $options = array_merge($options, compact('id', 'name', 'value'));
        /*
        $config = (array) $attribs;
        if (isset($options['config'])) {
            $config = array_merge($config, $options['config']);
        }
        $config['id'] = $id;
        $config['name'] = $name;
        $config['value'] = $value;
        $config['disabled'] = $disabled;
        $config = array_merge($config, compact('id', 'name', 'value', 'disable'));
        */
        $editor = \Xoops\Editor::load($type, $options);
        $xhtml = $editor->render($this->view);

        return $xhtml;
    }

    /**
     * Converts an associative array to a string of tag attributes.
     *
     * @access public
     *
     * @param array $attribs From this array, each key-value pair is
     * converted to an attribute name and value.
     *
     * @return string The XHTML for the attributes.
     */
    public function htmlAttribs($attribs)
    {
        return $this->_htmlAttribs($attribs);
    }
}
