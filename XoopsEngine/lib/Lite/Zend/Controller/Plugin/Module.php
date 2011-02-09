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
 * @copyright       The Xoops Engine http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Controller
 * @version         $Id$
 */

class Lite_Zend_Controller_Plugin_Module extends Zend_Controller_Plugin_Abstract
{
    /**
     * Constructor: initialize module
     *
     * @param  array|Zend_Config $options
     * @return void
     * @throws Exception
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        if (!is_array($options)) {
            $options = array();
        }
    }

    /**
     * Predispatch
     *
     * Instantiate xoopsModule and load xoopsModuleConfig
     *
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        //global $xoops, $xoopsModule, $xoopsUser, $xoopsModuleConfig, $xoopsUserIsAdmin, $module_handler;
        //global $xoopsUser, $xoopsUserIsAdmin;

        //$xoopsUserIsAdmin = XOOPS::registry("user")->role == "admin";
        $moduleDirname = $request->getModuleName();

        if ($moduleDirname == XOOPS::registry('frontController')->getDefaultModule()) {
            /*
            $xoopsUser = isset($xoopsUser) ? $xoopsUser : null;
            if ($xoopsUser) {
                $xoopsUserIsAdmin = $xoopsUser->isAdmin(1);
            }
            */
            return;
        }

        $module = new Lite_Module($moduleDirname);
        XOOPS::registry("module", $module);
    }
}