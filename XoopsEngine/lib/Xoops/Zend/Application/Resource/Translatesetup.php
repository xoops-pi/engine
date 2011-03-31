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

class Xoops_Zend_Application_Resource_Translatesetup extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        $options = $this->getOptions();

        $options['adapter'] = isset($options['adapter']) ? $options['adapter'] : 'legacy';
        $options['disableNotices'] = isset($options['disableNotices']) ? $options['disableNotices'] : true;
        $options['tag'] = isset($options['tag']) ? $options['tag'] : 'Xoops_Translate';
        if (isset($options['load'])) {
            unset($options['load']);
        }
        if (!isset($options['language'])) {
            $options['language'] = Xoops::config('language') ?: $GLOBALS['installWizard']->language;
        }

        $translate = Xoops::service('translate', $options);
    }
}