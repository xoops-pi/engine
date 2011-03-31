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

/**
 * XOOPS table form decorator
 */

class Xoops_Zend_Form_Decorator_Table extends Zend_Form_Decorator_Abstract
{
    /**
     * Merges given two belongsTo (array notation) strings
     *
     * @param  string $baseBelongsTo
     * @param  string $belongsTo
     * @return string
     */
    public function mergeBelongsTo($baseBelongsTo, $belongsTo)
    {
        $endOfArrayName = strpos($belongsTo, '[');

        if ($endOfArrayName === false) {
            return $baseBelongsTo . '[' . $belongsTo . ']';
        }

        $arrayName = substr($belongsTo, 0, $endOfArrayName);

        return $baseBelongsTo . '[' . $arrayName . ']' . substr($belongsTo, $endOfArrayName);
    }

    /**
     * Render form elements
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element    = $this->getElement();
        if ((!$element instanceof Xoops_Zend_Form_Element_Table)) {
            return $content;
        }

        $belongsTo      = $element->getElementsBelongTo();
        $separator      = $this->getSeparator();
        $translator     = $element->getTranslator();
        $view           = $element->getView();
        $table          = $element->getTable();
        $table->setView($view)->setTranslator($translator);

        foreach ($element->getElements() as $item) {
            $item->setView($view)
                 ->setTranslator($translator);
            if (!empty($belongsTo) && ($item instanceof Zend_Form || $item->getType() == "compound")) {
                if ($item->isArray()) {
                    $name = $this->mergeBelongsTo($belongsTo, $item->getElementsBelongTo());
                    $item->setElementsBelongTo($name);
                } else {
                    $item->setElementsBelongTo($belongsTo);
                }
            } elseif ($item instanceof Zend_Form_Element) {
                $item->setBelongsTo($belongsTo);
            } elseif (!empty($belongsTo) && ($item instanceof Zend_Form_DisplayGroup)) {
                foreach ($item as $ele) {
                    $ele->setBelongsTo($belongsTo);
                }
            }

            if (($item instanceof Zend_Form_Element_File)
                || (($item instanceof Zend_Form)
                    && (Zend_Form::ENCTYPE_MULTIPART == $item->getEnctype()))
            ) {
                $element->getForm()->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
            }
        }

        $elementContent = $table->render();

        switch ($this->getPlacement()) {
            case self::PREPEND:
                return $elementContent . $separator . $content;
            case self::APPEND:
            default:
                return $content . $separator . $elementContent;
        }
    }
}
