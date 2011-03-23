<?php
/**
 * Generic legacy module admin entry controller
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

class Module_LegacyController extends Xoops_Zend_Controller_Action_Admin
{
    public function indexAction()
    {
        $this->setLayout("admin.legacy");
        $this->setTemplate("");
        $link = $this->_getParam("link", "");
        $module = $this->getRequest()->getModuleName();
        $link = Xoops::url('module') . '/' . $module . '/' . $link;
        $this->view->assign('legacylink', $link);
    }

    public function __call($method, $args)
    {
        Debug::e($method . ' called');
    }
}