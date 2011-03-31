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
 * @copyright       Xoops Engine http://www.xoopsengine.org/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Controller
 * @version         $Id$
 */

class Xoops_Zend_Controller_Plugin_Helper extends Zend_Controller_Plugin_Abstract
{
    /**
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $moduleDirname = $request->getModuleName();
        if ($moduleDirname == XOOPS::registry('frontController')->getDefaultModule()) {
            return;
        }

        $response = $this->getResponse();
        if ($response->isException() || $response->isRedirect()) {
            return;
        }
        $plugins = Xoops::service('plugin')->getList();
        foreach ($plugins as $key => $config) {
            if (empty($config["autoload"])) {
                continue;
            }
            // Registering the Action Helper object
            $plugin = Xoops::service('plugin')->load($key);
            if ($plugin && $plugin->active) {
                //Debug::e($key);
                Xoops_Zend_Controller_Action_HelperBroker::addHelper($plugin);
            }
        }
        return;
    }
}