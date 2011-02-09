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

class Xoops_Zend_Application_Resource_Translatelegacy extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        $options = $this->getOptions();
        $bootstrap = $this->getBootstrap();
        $bootstrap->bootstrap('config');

        $options['adapter'] = isset($options['adapter']) ? $options['adapter'] : 'legacy';
        $options['language'] = Xoops::config('language');
        $options['locale'] = Xoops::config('locale');

        $options['disableNotices'] = isset($options['disableNotices']) ? $options['disableNotices'] : true;
        $options['tag'] = isset($options['tag']) ? $options['tag'] : 'Xoops_Translate';
        $load = array();
        if (isset($options['load'])) {
            $load = $options['load'];
            unset($options['load']);
        }

        $translate = Xoops::service('translate', $options);

        foreach ($load as $data => $loader) {
            $loader = is_array($loader) ?: array();
            $_options = !isset($loader['options']) ? array() : $loader['options'];
            $_locale = empty($loader['locale']) ? null : $loader['locale'];
            $translate->loadTranslation($data, "", $_locale, $_options);
        }

        if (!empty($GLOBALS['xoopsOption']['pagetype'])) {
            $translate->loadTranslation($GLOBALS['xoopsOption']['pagetype']);
        }
    }
}