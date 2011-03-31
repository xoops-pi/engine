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

class Xoops_Zend_Application_Resource_Auth extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Resource type
     */
    protected $_explicitType = "auth";

    public function init()
    {
        $options = $this->getOptions();

        $adapter = isset($options['adapter']) ? $options['adapter'] : null;
        $adapter = Xoops::service("auth")->loadAdapter($adapter);
        $storage = isset($options['storage']) ? $options['storage'] : null;
        $storage = Xoops::service("auth")->loadStorage($storage);

        if (isset($options['rememberMe'])) {
            Xoops::service("auth")->setRememberMe(intval($options['rememberMe']));
        }
        $identity = Xoops::service("auth")->wakeup();
    }
}
