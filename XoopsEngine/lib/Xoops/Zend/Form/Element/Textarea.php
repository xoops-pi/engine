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
 * @package         Form
 * @version         $Id$
 */

class Xoops_Zend_Form_Element_Textarea extends Zend_Form_Element_Textarea
{
    protected function setDisable($disable = false)
    {
        $this->disable = $disable;
        if ($this->disable) {
            $this->helper = "formTextarea";
        }

        return $this;
    }

    protected function setHtml($html = false)
    {
        if ($html) {
            if (null === $this->editor) {
                $this->setEditor();
            }
        }

        return $this;
    }

    protected function setEditor($options = array())
    {
        if (!$this->disable) {
            $this->helper = "formEditor";
        }
        if (!empty($options)) {
            $this->options = $options;
        }

        return $this;
    }
}