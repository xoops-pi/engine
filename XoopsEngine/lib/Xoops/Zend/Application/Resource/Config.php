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

class Xoops_Zend_Application_Resource_Config extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return void
     */
    public function init()
    {
        $bootstrap = $this->getBootstrap();
        $bootstrap->bootstrap('db');

        // Load data from cache
        $config = XOOPS::service('registry')->config->read('', 'general');
        $localeFile = Xoops::path('language') . '/' . $config['language'] . '/locale.ini.php';
        if (file_exists($localeFile)) {
            $locale = parse_ini_file($localeFile);
            $config['locale'] = $locale['lang'];
            $config['charset'] = $locale['charset'];
        } else {
            $config['locale'] = $config['language'];
            $config['charset'] = 'UTF-8';
        }

        XOOPS::engine()->setConfigs($config);
    }
}