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
 * @package         Application
 * @subpackage      Resource
 * @version         $Id$
 */

class Lite_Zend_Application_Resource_Locale extends Zend_Application_Resource_ResourceAbstract
{
    const DEFAULT_REGISTRY_KEY = 'locale';

    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return void
     */
    public function init()
    {
        $options = $this->getOptions();
        // Loads charset from system config
        $locale = XOOPS::config('locale');
        // Loads charset from system config
        $charset = XOOPS::config('charset');
        // Transform locale if composed
        if (false !== strpos($locale, '.')) {
            list($locale, $_charset) = explode(".", XOOPS::config('locale'), 2);
        }
        $charset = $charset ?: $_charset;

        // Load from options if not set in system config
        $locale = $locale ?: (isset($options['name']) ? $options['name'] : null);
        $charset = $charset ?: (isset($options['charset']) ? $options['charset'] : null);

        $locale = new Xoops_Zend_Locale($locale, $charset);
        XOOPS::registry('locale', $locale);
        Zend_Registry::set('Zend_Locale', $locale);
    }
}