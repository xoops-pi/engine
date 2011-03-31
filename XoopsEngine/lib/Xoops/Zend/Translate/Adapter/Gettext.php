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
 * @copyright       Xoops Engine http://www.xoopsengine.org
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Translate
 * @version         $Id$
 */

class Xoops_Zend_Translate_Adapter_Gettext extends Zend_Translate_Adapter_Gettext
{
    /**
     * Load translation data (MO file reader)
     *
     * @param  string  $filename  MO file to add, full path must be given for access
     * @param  string  $locale    New Locale/Language to set, identical with locale identifier,
     *                            see Zend_Locale for more information
     * @param  array   $option    OPTIONAL Options to use
     * @throws Zend_Translation_Exception
     * @return array
     */
    protected function _loadTranslationData($filename, $locale, array $options = array())
    {
        $filename = XOOPS::path($filename . ".mo");
        return parent::_loadTranslationData($filename, $locale, $options);
    }

    /**
     * Add translation data
     *
     * It may be a new language or additional data for existing language
     * If $clear parameter is true, then translation data for specified
     * language is replaced and added otherwise
     *
     * @param  array|string       $data    Translation data
     * @param  string|Zend_Locale $locale  (optional) Locale/Language to add data for, identical
     *                                        with locale identifier, see Zend_Locale for more information
     * @param  array              $options (optional) Option for this Adapter
     * @throws Zend_Translate_Exception
     * @return Zend_Translate_Adapter Provides fluent interface
     */
    public function ____addTranslation($data, $locale = null, array $options = array())
    {
        if (empty($data)) {
            return $this;
        }
        return parent::addTranslation($data, $locale, $options);
    }
}
