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

class Xoops_Zend_Form_Decorator_Date extends Zend_Form_Decorator_Abstract
{
    /**
     * Render content
     *
     * @param  string $content
     * @return string
     */
    public function  render($content)
    {
        $element = $this->getElement();
        if (!$element instanceof Xoops_Zend_Form_Element_Date) {
            // only want to render Date elements
            return $content;
        }

        $view = $element->getView();
        if (!$view instanceof Zend_View_Interface) {
            // using view helpers, so do nothing if no view present
            return $content;
        }

        $day   = $element->getDay();
        $month = $element->getMonth();
        $year  = $element->getYear();
        $name  = $element->getFullyQualifiedName();

        $yearStart = $this->getOption("start") ? $this->getOption("start") : 1900;
        $yearEnd = $this->getOption("end") ? $this->getOption("end") : 2050;

        for ($i = $yearStart; $i <= $yearEnd; $i ++) {
            $yearParams[$i] = $i;
        }

        //$monthList = Xoops_Zend_Locale::getTranslationList("Month", null, array("gregorian", "format", "narrow"));
        //Debug::e($monthList);
        for ($i = 1; $i <= 12; $i ++) {
            $key = str_pad($i, 2, "0", STR_PAD_LEFT);
            $monthParams[$key] = $key; //$monthList[$i];
        }

        for ($i = 1; $i <= 31; $i ++) {
            $key = str_pad($i, 2, "0", STR_PAD_LEFT);
            $dayParams[$key] = $key;
        }

        $markup = $view->formSelect($name . '[year]', $year, null, $yearParams)
                . ' / ' . $view->formSelect($name . '[month]', $month, null, $monthParams)
                . ' / ' . $view->formSelect($name . '[day]', $day, null, $dayParams);

        switch ($this->getPlacement()) {
            case self::PREPEND:
                return $markup . $this->getSeparator() . $content;
            case self::APPEND:
            default:
                return $content . $this->getSeparator() . $markup;
        }
    }
}
