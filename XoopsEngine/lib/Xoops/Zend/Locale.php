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
 * @package         Locale
 * @version         $Id$
 */

class Xoops_Zend_Locale extends Zend_Locale
{
    /**
     * Character set
     *
     * @var string
     */
    protected static $charset = "utf-8";

    /**
     * Generates a locale object
     * If no locale is given a automatic search is done
     * Then the most probable locale will be automatically set
     * Search order is
     *  1. Given Locale
     *  2. HTTP Client
     *  3. Server Environment
     *  4. Framework Standard
     *
     * @param  string|Zend_Locale $locale (Optional) Locale for parsing input
     * @param  string $charset (Optional) Default charset
     * @throws Zend_Locale_Exception When autodetection has been failed
     */
    public function __construct($locale = null, $charset = null)
    {
        $locale = static::normalizeLocale($locale);
        $charset = static::normalizeCharset($charset);
        parent::__construct($locale);
        if (!empty($charset)) {
            $this->setCharset($charset);
        }
    }

    public static function normalizeLocale($locale)
    {
        if (!is_string($locale)) {
            return $locale;
        }
        if (false !== strpos($locale, "-")) {
            $locale = str_replace("-", "_", $locale);
        }
        if (false !== strpos($locale, "_")) {
            $data = explode("_", $locale, 2);
            $locale = $data[0] . "_" . strtoupper($data[1]);
        }

        return $locale;
    }

    public static function normalizeCharset($charset)
    {
        if (!is_string($charset)) {
            return $charset;
        }
        if (false !== strpos($charset, "_")) {
            $charset = str_replace("_", "-", $charset);
        }
        $charset = strtolower($charset);

        return $charset;
    }

    public function setCharset($charset)
    {
        static::$charset = $charset;
    }

    public function getCharset()
    {
        return static::$charset;
    }

    public function getLocale()
    {
        return $this->_locale;
    }

    /**
     * Finds the proper locale based on the input
     * Checks if it exists, degrades it when necessary
     * Detects registry locale and when all fails tries to detect a automatic locale
     * Returns the found locale as string
     *
     * @param string $locale
     * @throws Zend_Locale_Exception When the given locale is no locale or the autodetection fails
     * @return string
     */
    public static function findLocale($locale = null)
    {
        $locale = static::normalizeLocale($locale);
        if ($locale === null) {
            if (XOOPS::registry('locale')) {
                $locale = XOOPS::registry('locale');
            }
        }
        //return parent::findLocale($locale);
        $locale = parent::findLocale($locale);
        //Debug::_e(__METHOD__ . ": $locale");
        return $locale;
    }

    /**
     * Checks if a locale identifier is a real locale or not
     * Examples:
     * "en_XX" refers to "en", which returns true
     * "XX_yy" refers to "root", which returns false
     *
     * @param  string|Zend_Locale $locale     Locale to check for
     * @param  boolean            $strict     (Optional) If true, no rerouting will be done when checking
     * @param  boolean            $compatible (DEPRECATED) Only for internal usage, brakes compatibility mode
     * @return boolean If the locale is known dependend on the settings
     */
    public static function isLocale($locale, $strict = false, $compatible = true)
    {
        $locale = static::normalizeLocale($locale);
        return parent::isLocale($locale, $strict, $compatible);
    }
}
