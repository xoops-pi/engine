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

class Lite_Zend_Application_Resource_Auth extends Zend_Application_Resource_ResourceAbstract
{
    //private static $rememberMe = 0;
    const DEFAULT_REGISTRY_KEY = 'user';

    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return Zend_Db_Adapter_Abstract|null
     */
    public function init()
    {
        $options = $this->getOptions();

        $adapter = isset($options['adapter']) ? $options['adapter'] : null;
        Xoops::service("auth")->loadAdapter($adapter);
        $storage = isset($options['storage']) ? $options['storage'] : null;
        Xoops::service("auth")->loadStorage($storage);

        if (isset($options['rememberMe'])) {
            Xoops::service("auth")->setRememberMe(intval($options['rememberMe']));
        }
        $identity = Xoops::service("auth")->wakeup();
    }
}
