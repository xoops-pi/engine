<?php
/**
 * XOOPS translate service class
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
 * @package         Xoops_Service
 * @version         $Id$
 */

namespace Engine\Xoops\Service;

class Translate extends \Kernel\Service\Translate
{
    public function loadHandler($options)
    {
        $options['language'] = isset($options['language']) ? $options['language'] : \Xoops::config('language');
        $options['locale'] = isset($options['locale']) ? $options['locale'] : \Xoops::config('locale');
        $options['disableNotices'] = isset($options['disableNotices']) ? $options['disableNotices'] : true;
        $options['tag'] = isset($options['tag']) ? $options['tag'] : 'Xoops_Translate';
        $translate = new \Xoops_Zend_Translate($options);
        \XOOPS::registry("translate", $translate);
        \Zend_Registry::set("Zend_Translate", $translate);

        $this->handler = $translate;
        return $this;
    }
}