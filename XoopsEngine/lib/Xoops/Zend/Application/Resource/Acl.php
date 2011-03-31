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
 * @package         Application
 * @subpackage      Resource
 * @version         $Id$
 */

class Xoops_Zend_Application_Resource_Acl extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return void
     */
    public function init()
    {
        $options = $this->getOptions();
        // Setting up the front controller
        $bootstrap = $this->getBootstrap();
        $bootstrap->bootstrap('Auth');

        // Registering the Plugin object
        $aclPlugin = new Xoops_Zend_Controller_Plugin_Acl();

        // Setting up the front controller
        $bootstrap = $this->getBootstrap();
        $bootstrap->bootstrap('FrontController');
        $front = $bootstrap->getResource('FrontController');
        // Make sure the ACL plugin is called before page cache
        $front->registerPlugin($aclPlugin, -95);

        // Registering the Action Helper object
        Xoops_Zend_Controller_Action_HelperBroker::addHelper(new Xoops_Zend_Controller_Action_Helper_Acl());
    }
}
