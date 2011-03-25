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
 * @package         Mail
 * @version         $Id$
 * @uses            Xoops_Zend_View
 */

class Xoops_Zend_Mail_Template// extends Xoops_Smarty_Template
{
    /**
     * Mail handler
     */
    protected $mailer;
    protected $template;
    protected $domain;

    public function __construct($mailer = null)
    {
        $this->mailer = $mailer;
        if (!$view = XOOPS::registry("view")) {
            $view = new Xoops_Zend_View();
        }
        $this->engine = clone $view->getEngine();
        $this->assign(array(
            "xoops_url"         => XOOPS::url("www", true),
            "xoops_sitename"    => Xoops\Security::escape(XOOPS::config('sitename')),
        ));
    }

    public function setMailer($mailer)
    {
        $this->mailer = $mailer;

        return $this;
    }

    public function setTemplate($template, $domain = "")
    {
        list($this->template, $this->domain) = array($template, $domain);

        return $this;
    }

    public function assign($var, $val = null)
    {
        $this->engine->assign($var, $val);
    }

    public function render()
    {
        $path = XOOPS::path(XOOPS::service("translate")->getPath($this->domain, $this->mailer->getLocale())) . "/mails";
        $content = $this->engine->fetch($path . "/" . $this->template);
        $pos = strrpos($this->template, ".");
        if (substr($this->template, $pos + 1) == "html") {
            $this->mailer->setBodyHtml($content);
        } else {
            $this->mailer->setBodyText($content);
        }
        return $this;
    }
}