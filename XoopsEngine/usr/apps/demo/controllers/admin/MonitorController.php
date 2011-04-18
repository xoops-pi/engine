<?php
/**
 * Demo admin monitor controller
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
 * @package         Demo
 * @version         $Id$
 */

class Demo_MonitorController extends Xoops_Zend_Controller_Action
{
    public function resetAction()
    {
        $model = \Xoops::service('module')->getModel('test', $module);
        $model->update(array('active' => 0), array('active = ?' => 1));

        $redirect = $this->getRequest()->getParam('redirect');
        Debug::e($this->getRequest()->getParams());
        Debug::e($redirect);
        $this->redirect($redirect);
    }
}