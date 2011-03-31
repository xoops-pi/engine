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

class Xoops_Zend_Application_Resource_Security extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Check security settings
     *
     * Policy: quit the process and approve current request if TRUE is returned by a check; quit and deny the request if FALSE is return; continue with next check if NULL is returned
     */
    public function init()
    {
        $options = $this->getOptions();
        foreach ($options as $type => $opt) {
            if (empty($opt)) {
                continue;
            }
            $status = Xoops\Security::$type($opt);
            if ($status) return true;
            if (false === $status) {
                Xoops\Security::deny($type);
            }
        }
    }
}