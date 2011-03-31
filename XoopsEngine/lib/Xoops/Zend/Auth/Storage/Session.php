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
 * @package         Auth
 * @subpackage      Storage
 * @version         $Id$
 */

class Xoops_Zend_Auth_Storage_Session extends Zend_Auth_Storage_Session
{
    /**
     * Default session namespace
     */
    const NAMESPACE_DEFAULT = 'Auth';

    /**
     * Sets session storage options and initializes session namespace object
     *
     * @param  mixed $namespace
     * @param  mixed $member
     * @return void
     */
    public function __construct($namespace = null, $member = null)
    {
        $namespace = is_null($namespace) ? static::NAMESPACE_DEFAULT : $namespace;
        $member = is_null($member) ? static::MEMBER_DEFAULT : $member;
        parent::__construct($namespace, $member);
    }
}
