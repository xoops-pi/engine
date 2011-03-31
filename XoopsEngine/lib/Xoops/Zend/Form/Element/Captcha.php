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
 * @copyright       Xoops Engine http://www.xoopsengine.org
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Form
 * @version         $Id$
 */

/**
 * XOOPS form element CAPTCHA
 */

class Xoops_Zend_Form_Element_Captcha extends Zend_Form_Element_Captcha
{
    /**
     * Retrieve plugin loader for validator or filter chain
     *
     * Support for plugin loader for Captcha adapters
     *
     * @param  string $type
     * @return Zend_Loader_PluginLoader
     * @throws Zend_Loader_Exception on invalid type.
     */
    public function getPluginLoader($type)
    {
        $type = strtoupper($type);
        if ($type == self::CAPTCHA) {
            if (!isset($this->_loaders[$type])) {
                //require_once 'Zend/Loader/PluginLoader.php';
                $this->_loaders[$type] = new Xoops_Zend_Loader_PluginLoader(
                    array(
                        'Zend_Captcha'          => XOOPS::path('lib') . '/Zend/Captcha/',
                        'Xoops_Zend_Captcha'    => XOOPS::path('lib') . '/Xoops/Zend/Captcha/'
                    )
                );
            }
            return $this->_loaders[$type];
        } else {
            return parent::getPluginLoader($type);
        }
    }
}