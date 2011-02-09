<?php
/**
 * Mvc index controller
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       The Xoops Engine http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Module
 * @package         Mvc
 * @version         $Id$
 */

class Mvc_IndexController extends Xoops_Zend_Controller_Action
{
    public function indexAction()
    {
        //$this->setTemplate("index.html");
        $this->view->assign("data", "XoopsZend");
        $this->view->assign("module", $this->getRequest()->getModuleName());
        //$this->skipCache();
        //$this->cacheLevel('user');

        $this->view->headTitle(__METHOD__);
        $this->view->headMeta()->prependName("generator", "MVC");
    }

    public function testAction()
    {
        //$model = $this->getModel('test');
        echo "<br />No template rendering.<br />Test is now at " . __METHOD__;
        $this->skipCache(false);
        $this->cacheLevel('public');

        XOOPS::service('event')->trigger('user_call', XOOPS::_("Triggered data from MVC module"), "mvc");
        //$this->plugin("comment")->enable();
        //$this->getHelper("comment")->clearCache(1);
        $this->plugin("notification")->trigger("index1");
    }

    public function userAction()
    {
        Debug::e("Test for user_call event");
        XOOPS::service("event")->trigger("user_call");
    }

    public function redirectAction()
    {
        $options = array("time" => 5, "message" => "Test for redirection.");
        $this->redirect('mvc', $options);
    }

    public function forwardAction()
    {
        $this->_forward('test', 'index', 'mvc');
    }

    public function apiAction()
    {
        Debug::e(__METHOD__);
    }
}
