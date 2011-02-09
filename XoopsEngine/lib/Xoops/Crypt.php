<?php
/**
 * Crypt (encrypt/decrypt) for Xoops Engine
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
 * @package         Xoops_Core
 * @since           3.0
 * @version         $Id$
 */

class Xoops_Crypt
{
    protected static $instances = array();
    protected static $autoloaded = false;
    protected static $key = null;
    protected static $defaultType = 'Rc4';

    public static function load($type = null, $key = null)
    {
        $type = ucfirst(strtolower($type ?: static::$defaultType));
        if ($type === 'Mcrypt') {
            $type = 'Rc4';
        }
        if (isset(static::$instances[$type])) {
            return static::$instances[$type];
        }
        if (!static::$autoloaded) {
            static::$autoloaded = true;

            $cryptRoot = Xoops::path('lib') . '/Phpseclib/Crypt';
            Xoops::autoloader()->registerMap(
                array(
                    'Crypt_Aes'         => $cryptRoot . '/AES.php',
                    'Crypt_Des'         => $cryptRoot . '/DES.php',
                    'Crypt_Hash'        => $cryptRoot . '/Hash.php',
                    'Crypt_Random'      => $cryptRoot . '/Random.php',
                    'Crypt_Rc4'         => $cryptRoot . '/RC4.php',
                    'Crypt_Rijndael'    => $cryptRoot . '/Rijndael.php',
                    'Crypt_Rsa'         => $cryptRoot . '/RSA.php',
                    'Crypt_Tripledes'   => $cryptRoot . '/TripleDES.php',
                )
            );
        }
        $cryptClass = 'Crypt_' . $type;
        $crypt = false;
        if (class_exists($cryptClass)) {
            $crypt = new $cryptClass;
            $key = $key ?: static::getKey();
            $crypt->setKey($key);
        }
        static::$instances[$type] = $crypt;

        return static::$instances[$type];
    }

    public static function encrypt($text)
    {
        return static::load()->encrypt($text);
    }

    public static function decrypt($text)
    {
        return static::load()->decrypt($text);
    }

    public static function setKey($key = null)
    {
        static::$key = $key;
    }

    public static function getKey()
    {
        if (static::$key === null) {
            static::$key = Xoops::config('salt');
        }

        return static::$key;
    }
}