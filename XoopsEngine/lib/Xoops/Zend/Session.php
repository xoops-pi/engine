<?php
/**
 * Session handler for Xoops Engine
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
 * @package         Xoops_Zend
 * @version         $Id$
 */

class Xoops_Zend_Session extends Zend_Session
{
    /**#@+
     * The variables and methods are copied solely for late static call
     * And I am looking forward to PHP 5.3's late static call implemented in ZF
     */
    /**
     * Default number of seconds the session will be remembered for when asked to be remembered
     *
     * @var int
     */
    protected static $_rememberMeSeconds = 1209600; // 2 weeks

    /**
     * rememberMe() - Write a persistent cookie that expires after a number of seconds in the future. If no number of
     * seconds is specified, then this defaults to self::$_rememberMeSeconds.  Due to clock errors on end users' systems,
     * large values are recommended to avoid undesirable expiration of session cookies.
     *
     * @param $seconds integer - OPTIONAL specifies TTL for cookie in seconds from present time
     * @return void
     */
    public static function rememberMe($seconds = null)
    {
        $seconds = (int) $seconds;
        $seconds = ($seconds > 0) ? $seconds : static::$_rememberMeSeconds;

        static::rememberUntil($seconds);
    }


    /**
     * forgetMe() - Write a volatile session cookie, removing any persistent cookie that may have existed. The session
     * would end upon, for example, termination of a web browser program.
     *
     * @return void
     */
    public static function forgetMe()
    {
        static::rememberUntil(0);
    }
    /**#@-*/

    /**
     * rememberUntil() - This method does the work of changing the state of the session cookie and making
     * sure that it gets resent to the browser via regenerateId()
     *
     * @param int $seconds
     * @return void
     */
    public static function rememberUntil($seconds = 0)
    {
        if (static::$_unitTestEnabled) {
            static::regenerateId();
            return;
        }
        parent::rememberUntil($seconds);

        if ($seconds >= 0 && is_callable(array(static::getSaveHandler(), "setLifetime"))) {
            static::getSaveHandler()->setLifetime($seconds, true);
        }
    }

    /**
     * namespace() - create a namespace object
     *
     * @param string $namespace       - programmatic name of the requested namespace
     * @param bool $singleInstance    - prevent creation of additional accessor instance objects for this namespace
     * @return {@link Zend_Session_Namespace}
     */
    public static function &getNamespace($namespace = 'Default', $singleInstance = false)
    {
        $namespace = new Zend_Session_Namespace($namespace, $singleInstance);
        return $namespace;
    }

    public static function getExpiringData()
    {
        return static::$_expiringData;
    }
}
