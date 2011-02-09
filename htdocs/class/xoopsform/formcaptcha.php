<?php
/**
 * CAPTCHA form element
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         kernel
 * @subpackage      form
 * @since           2.0.0
 * @author          Kazumi Ono <onokazu@xoops.org>
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @version         $Id: formcaptcha.php 1786 2008-05-28 09:56:27Z phppp $
 */

if (!defined('XOOPS_ROOT_PATH')) {
    die("XOOPS root path not defined");
}

//require_once XOOPS_ROOT_PATH . "/class/xoopsform/formelement.php";

/**
 * Usage of XoopsFormCaptcha
 *
 * For form creation:
 * Add form element where proper: <code>$xoopsform->addElement(new XoopsFormCaptcha($caption, $name, $skipmember, $configs));</code>
 *
 * For verification:
 * <code>
 *      xoops_load("captcha");
 *      $xoopsCaptcha =& XoopsCaptcha::getInstance();
 *      if (! $xoopsCaptcha->verify() ) {
 *          echo $xoopsCaptcha->getMessage();
 *          ...
 *      }
 * </code>
 *
 */

class XoopsFormCaptcha extends XoopsFormElement
{
    var $captchaHandler;

    /**
     * @param string    $caption        Caption of the form element, default value is defined in captcha/language/
     * @param string    $name           Name for the input box
     * @param boolean   $skipmember     Skip CAPTCHA check for members
     */
    function XoopsFormCaptcha($caption = '', $name = 'xoopscaptcha', $skipmember = true, $configs = array())
    {
        xoops_load("captcha");

        $this->captchaHandler =& XoopsCaptcha::getInstance();
        $configs['name'] = $name;
        $configs['skipmember'] = $skipmember;
        $this->captchaHandler->setConfigs($configs);
        if (!$this->captchaHandler->isActive()) {
            $this->setHidden();
        } else {
            $caption = !empty($caption) ? $caption : $this->captchaHandler->getCaption();
            $this->setCaption($caption);
            $this->setName($name);
        }
    }

    function setConfig($name, $val)
    {
        return $this->captchaHandler->setConfig($name, $val);
    }

    function render()
    {
        //if (!$this->isHidden()) {
            return $this->captchaHandler->render();
        //}
    }
}
?>