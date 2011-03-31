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

class Xoops_Zend_Controller_Action_HelperBroker extends Zend_Controller_Action_HelperBroker
{
    /**
     * Constructor
     *
     * @param Zend_Controller_Action $actionController
     * @return void
     */
    public function __construct(Zend_Controller_Action $actionController)
    {
        parent::__construct($actionController);
        static::getPluginLoader();
    }

    /**
     * Retrieve PluginLoader
     *
     * @return Zend_Loader_PluginLoader
     */
    public static function getPluginLoader()
    {
        if (null === self::$_pluginLoader) {
            self::$_pluginLoader = new Xoops_Zend_Loader_PluginLoader(array(
                'Zend_Controller_Action_Helper'         => XOOPS::path('lib') . '/Zend/Controller/Action/Helper/',
                'Xoops_Zend_Controller_Action_Helper'   => XOOPS::path('lib') . '/Xoops/Zend/Controller/Action/Helper/',
            ));
        }
        return self::$_pluginLoader;
    }
}