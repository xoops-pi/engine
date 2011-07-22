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

class Xoops_Zend_Form_Decorator_Captcha_Image extends Zend_Form_Decorator_Captcha_Word
{
    /**
     * Render captcha
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        if (!method_exists($element, 'getCaptcha')) {
            return $content;
        }

        $view    = $element->getView();
        if (null === $view) {
            return $content;
        }

        $name = $element->getFullyQualifiedName();

        $hiddenName = $name . '[id]';
        $textName   = $name . '[input]';

        $label = $element->getDecorator("Label");
        if($label) {
            $label->setOption("id", $element->getId()."-input");
        }

        $placement = $this->getPlacement();
        $separator = $this->getSeparator();
        
        $captcha = $element->getCaptcha();
        $markup  = $captcha->render($view, $element);
        $captchaOpenTag = '<p style="padding: 2px 0">';
        /*
        if (($view instanceof Zend_View_Abstract) && !$view->doctype()->isXhtml()) {
            $captchaOpenTag= '<br>';
        }
        */
        $captchaCloseTag = '</p>';

        $hidden = $view->formHidden($hiddenName, $element->getValue(), $element->getAttribs());
        $text   = $view->formText($textName, '', $element->getAttribs());
        switch ($placement) {
            case 'PREPEND':
                $content = $hidden . $separator . $text . $separator . $captchaOpenTag . $markup . $captchaCloseTag . $separator .  $content;
                break;
            case 'APPEND':
            default:
                $content = $content . $separator . $hidden . $separator . $text . $separator . $captchaOpenTag . $markup . $captchaCloseTag;
        }
                
        return $content;
    }
}
