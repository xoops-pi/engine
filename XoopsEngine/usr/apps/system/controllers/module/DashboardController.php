<?php
/**
 * Generic module admin dashboard controller
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

class Module_DashboardController extends Xoops_Zend_Controller_Action_Admin
{
    public function indexAction()
    {
        $module = $this->getRequest()->getModuleName();
        $this->setTemplate("system/admin/dashboard.html");

        $this->view->headTitle("Index");
        $this->view->headTitle("Dashboard");
        //$this->skipCache();

        $modules = Xoops::service('registry')->module->read();
        $monitors = Xoops::service('registry')->monitor->read();

        $redirect = XOOPS::registry("frontController")->getRouter()->assemble(array('module' => $module, 'controller' => 'dashboard', 'action' => 'index'));
        $redirect = urlencode($redirect);
        $monitorList = array();
        foreach ($modules as $key => $app) {
            if (empty($app['active']) || !array_key_exists($key, $monitors)) continue;
            list($class, $method) = explode('::', $monitors[$key]);
            $data = $class::$method($key, $redirect);
            if (empty($data)) continue;
            $monitorList[$key] = array(
                'title' => $app['name'],
                'data'  => $data,
            );
        }
        $this->template->assign('monitors', $monitorList);

        $model = $this->getModel('task', 'system');
        $tasks = $model->fetchAll($model->select()->from($model, array('memo'))->order('time_created DESC')->where("time_finished = 0"));
        $this->template->assign('tasks', $tasks->toArray());

        $model = $this->getModel('shortcut', 'system');
        $shortcuts = $model->fetchAll($model->select()->from($model, array('link', 'title'))->order('order ASC'));
        $this->template->assign('shortcuts', $shortcuts->toArray());

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