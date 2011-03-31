<?php
/**
 * Search admin index controller
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
 * @package         Search
 * @version         $Id$
 */

class Search_IndexController extends Xoops_Zend_Controller_Action
{
    public function indexAction()
    {
        $this->view->assign("data", "XoopsZend");
        $this->view->assign("module", $this->getRequest()->getModuleName());
        $this->cacheLevel('user');

        XOOPS::service('event')->trigger('user_call', "Triggered data from MVC module", "mvc");
    }

    public function testAction()
    {
        echo "<br />No template rendering.<br />Test is now at " . __CLASS__ . "::" . __FUNCTION__;
        $this->skipCache(false);
        $this->cacheLevel('public');
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
}
