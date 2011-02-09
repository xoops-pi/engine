<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/**
 * Xoops Editor element
 *
 * @copyright   The XOOPS project http://www.xoops.org/
 * @license     http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package        core
 * @since       2.3.0
 * @author        Taiwen Jiang <phppp@users.sourceforge.net>
 * @version        $Id: formeditor.php 2632 2009-01-10 03:08:10Z phppp $
 */

//xoops_load('XoopsFormTextArea');

class XoopsFormEditor extends XoopsFormTextArea
{
    var $editor;

    /**
     * Constructor
     *
     * @param    string  $caption    Caption
     * @param    string  $name       Name for textarea field
     * @param    string  $value        Initial text
     * @param    array     $configs    configures: editor - editor identifier; name - textarea field name; width, height - dimensions for textarea; value - text content
     * @param    bool      $noHtml        use non-WYSIWYG eitor onfailure
     * @param    string  $OnFailure    editor to be used if current one failed
     */
    function XoopsFormEditor($caption, $name, $configs = null, $nohtml = false, $OnFailure = "")
    {
        // Backward compatibility: $name -> editor name; $configs["name"] -> textarea field name
        if (!isset($configs["editor"])) {
            $configs["editor"] = $name;
            $name = $configs["name"];
        // New: $name -> textarea field name; $configs["editor"] -> editor name; $configs["name"] -> textarea field name
        } else {
            $configs["name"] = $name;
        }
        $this->XoopsFormTextArea($caption, $name);
        xoops_load('XoopsEditorHandler');
        $editor_handler = XoopsEditorHandler::getInstance();
        $this->editor = $editor_handler->get($configs["editor"], $configs, $nohtml, $OnFailure);
    }

    /**
     * renderValidationJS
     * TEMPORARY SOLUTION to 'override' original renderValidationJS method
     * with custom XoopsEditor's renderValidationJS method
     */
    function renderValidationJS()
    {
        if (is_object($this->editor) && $this->isRequired()) {
            if (method_exists($this->editor,'renderValidationJS')) {
                $this->editor->setName($this->getName());
                $this->editor->setCaption($this->getCaption());
                $this->editor->_required = $this->isRequired();
                $ret = $this->editor->renderValidationJS();
                return $ret;
            } else {
                parent::renderValidationJS();
            }
        }
        return '';
    }

    function render()
    {
        if (is_object($this->editor)) {
            return $this->editor->render();
        }
    }
}
?>
