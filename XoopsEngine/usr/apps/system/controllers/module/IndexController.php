<?php
/**
 * Generic module admin default controller
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
 * @category        Xoops_Module
 * @package         System
 * @version         $Id$
 */

class Module_IndexController extends Xoops_Zend_Controller_Action_Admin
{
    public function indexAction()
    {
        $this->forward("index", "dashboard");
    }

    public function sitemapAction()
    {
        //$this->skipCache();
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        echo $this->view->navigation()->sitemap();
    }

    public function menuAction()
    {
    }

    public function readmeAction()
    {
        $title = XOOPS::_("XOOPS - eXtensible Object Oriented Portal System, an out-of-box solution for developers and users.");
        $this->template->assign("title", $title);
    }

    public function __call($method, $args)
    {
        Debug::e($method . ' called');
    }
}