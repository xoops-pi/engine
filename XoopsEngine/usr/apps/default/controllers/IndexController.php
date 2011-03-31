<?php
/**
 * Default index controller
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
 * @package         Default
 * @version         $Id$
 */

class Default_IndexController extends \Application\Controller
{
    public function indexAction()
    {
        if (XOOPS::config("startpage")) {
            $this->forward("index", "index", XOOPS::config("startpage"), array());
        }
        //Xoops::service('event')->attach('system', 'module_install', array('mvc_event', 'runtime', 'mc'));
        //Xoops::service('event')->trigger('module_install', 'module', 'system');
        // Set a specific template if index_index.html is not used
        $this->setTemplate('index.html'/*, 'layout'*/);
        $this->template->assign("message", XOOPS::_('This the default homepage.'));

        //$sqlFile = Xoops::service('module')->getPath('system') . '/sql/test.sql';
        //Xoops_Zend_Db_File_Mysql::queryFile($sqlFile, $log);
        //Debug::e($log);
        //$sql = "CREATE VIEW " . Xoops::registry('db')->prefix('modules') . " AS SELECT * FROM " . Xoops::getModel('module')->info('name');
        //Xoops::registry('db')->query($sql);

        return;
    }

    /**
     * any path that is not catch by any of our actions
     * will be catched by this function by default
     * @param $methodName string
     * @param $args array
     */
    public function __call($methodName, $args)
    {
        //$this->template->assign("");
        $this->template->assign("error_title", XOOPS::_('The page you requested was not found.'));
        $this->setTemplate('404.html');
        $this->view->section = null;
        $this->setLayout("simple");
    }
}
