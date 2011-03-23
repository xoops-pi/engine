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

class Xoops_Zend_Translate_Adapter_Legacy extends Zend_Translate_Adapter
{
    protected static $loaded;

    /**
     * Generates the adapter
     *
     * @param  array|Zend_Config $options Translation options for this adapter
     * @return void
     */
    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            $options = array('content' => $options);
        }
        $this->normalizeOptions($options);
        parent::__construct($options);
    }

    protected function normalizeOptions(&$options)
    {
        if (array_key_exists('language', $options)) {
            $options['locale'] = $options['language'];
            unset($options['language']);
        }
        if (array_key_exists('locale', $options) && empty($options['locale'])) {
            unset($options['locale']);
        }
    }

    /**
     * Sets new adapter options
     *
     * @param  array $options Adapter options
     * @throws Zend_Translate_Exception
     * @return Zend_Translate_Adapter Provides fluent interface
     */
    public function setOptions(array $options = array())
    {
        $this->normalizeOptions($options);
        parent::setOptions($options);
        return $this;
    }

    /**
     * Sets locale
     *
     * @param  string $locale Locale to set
     * @return Zend_Translate_Adapter Provides fluent interface
     */
    public function setLocale($locale)
    {
        if ($this->_options['locale'] != $locale) {
            $this->_options['locale'] = $locale;

            if (isset(self::$_cache)) {
                $id = 'Zend_Translate_' . $this->toString() . '_Options';
                if (self::$_cacheTags) {
                    self::$_cache->save($this->_options, $id, array($this->_options['tag']));
                } else {
                    self::$_cache->save($this->_options, $id);
                }
            }
        }

        return $this;
    }

    /**
     * Add translations
     *
     * This may be a new language or additional content for an existing language
     * If the key 'clear' is true, then translations for the specified
     * language will be replaced and added otherwise
     *
     * @param  array|Zend_Config $options Options and translations to be added
     * @throws Zend_Translate_Exception
     * @return Zend_Translate_Adapter Provides fluent interface
     */
    public function addTranslation($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (func_num_args() > 1) {
            $args = func_get_args();
            $options            = array();
            $options['content'] = array_shift($args);

            if (!empty($args)) {
                $options['locale'] = array_shift($args);
            }

            if (!empty($args)) {
                $opt = array_shift($args);
                $options = array_merge($opt, $options);
            }
        } else if (!is_array($options)) {
            $options = array('content' => $options);
        }
        $this->normalizeOptions($options);

        $key = md5(serialize($options));
        if (isset(self::$loaded[$key])) {
            return;
        }
        self::$loaded[$key] = 1;

        $originate = null;
        if (!empty($options['locale'])) {
            $originate = (string) $options['locale'];
        }

        if ((array_key_exists('log', $options)) && !($options['log'] instanceof Zend_Log)) {
            throw new Zend_Translate_Exception('Instance of Zend_Log expected for option log');
        }

        try {
            if (!($options['content'] instanceof Zend_Translate) && !($options['content'] instanceof Zend_Translate_Adapter)) {
                if (empty($options['locale'])) {
                    $options['locale'] = null;
                }

                //$options['locale'] = Zend_Locale::findLocale($options['locale']);
            }
        } catch (Zend_Locale_Exception $e) {
            throw new Zend_Translate_Exception("The given Language '{$options['locale']}' does not exist", 0, $e);
        }

        $options  = $options + $this->_options;
        $this->_addTranslationData($options);

        if ((isset($this->_translate[$originate]) === true) and (count($this->_translate[$originate]) > 0)) {
            $this->setLocale($originate);
        }

        return $this;
    }

    /**
     * Load translation data
     *
     * @param  string|array  $filename
     * @param  string        $locale  Locale/Language to add data for, identical with locale identifier,
     *                                see Zend_Locale for more information
     * @param  array         $options OPTIONAL Options to use
     * @return bool
     */
    protected function _loadTranslationData($filename, $locale, array $options = array())
    {
        $domain = isset($options['domain']) ? $options['domain'] : '';
        $path = '';
        if (false !== $domain) {
            $path = $this->getPath($domain, $locale, true);
            if (false === $path) {
                trigger_error("Translation domain-locale '{$domain}'-'{$locale}' is not found");
                return array();
            }
        }
        $filename = ($path ? ($path . '/') : '') . $filename . ".php";
        //Debug::e('to load: '.$filename);
        if (file_exists($filename)) {
            $data = include $filename;
        } else {
            trigger_error("Translation data file '{$filename}' is not found");
        }

        return array();
    }

    /**
     * Internal function for adding translation data
     *
     * This may be a new language or additional data for an existing language
     * If the options 'clear' is true, then the translation data for the specified
     * language is replaced and added otherwise
     *
     * @param  array|Zend_Config $content Translation data to add
     * @throws Zend_Translate_Exception
     * @return Zend_Translate_Adapter Provides fluent interface
     */
    private function _addTranslationData($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (func_num_args() > 1) {
            $args = func_get_args();
            $options['content'] = array_shift($args);

            if (!empty($args)) {
                $options['locale'] = array_shift($args);
            }

            if (!empty($args)) {
                $options += array_shift($args);
            }
        }

        if (($options['content'] instanceof Zend_Translate) || ($options['content'] instanceof Zend_Translate_Adapter)) {
            $options['usetranslateadapter'] = true;
            if (!empty($options['locale']) && ($options['locale'] !== 'auto')) {
                $options['content'] = $options['content']->getMessages($options['locale']);
            } else {
                $content = $options['content'];
                $locales = $content->getList();
                foreach ($locales as $locale) {
                    $options['locale']  = $locale;
                    $options['content'] = $content->getMessages($locale);
                    $this->_addTranslationData($options);
                }

                return $this;
            }
        }
        //Debug::e($this->_options['locale']);

        /*
        try {
            $options['locale'] = Zend_Locale::findLocale($options['locale']);
        } catch (Zend_Locale_Exception $e) {
            require_once 'Zend/Translate/Exception.php';
            throw new Zend_Translate_Exception("The given Language '{$options['locale']}' does not exist", 0, $e);
        }
        */

        if ($options['clear'] || !isset($this->_translate[$options['locale']])) {
            $this->_translate[$options['locale']] = array();
        }

        $read = true;
        if (isset(self::$_cache)) {
            $id = 'Xoops_Translate_' . md5(serialize($options)) . '_' . $this->toString();
            //$id = 'Zend_Translate_' . md5(serialize($options['content'])) . '_' . $this->toString();
            $temp = self::$_cache->load($id);
            if ($temp) {
                $read = false;
            }
        }

        if ($options['reload']) {
            $read = true;
        }

        if ($read) {
            if (!empty($options['usetranslateadapter'])) {
                $temp = array($options['locale'] => $options['content']);
            } else {
                $temp = $this->_loadTranslationData($options['content'], $options['locale'], $options);
            }
        }

        if (empty($temp)) {
            $temp = array();
        }

        $keys = array_keys($temp);
        foreach($keys as $key) {
            if (!isset($this->_translate[$key])) {
                $this->_translate[$key] = array();
            }

            if (array_key_exists($key, $temp) && is_array($temp[$key])) {
                $this->_translate[$key] = $temp[$key] + $this->_translate[$key];
            }
        }

        /*
        if ($this->_automatic === true) {
            $find = new Zend_Locale($options['locale']);
            $browser = $find->getEnvironment() + $find->getBrowser();
            arsort($browser);
            foreach($browser as $language => $quality) {
                if (isset($this->_translate[$language])) {
                    $this->_options['locale'] = $language;
                    break;
                }
            }
        }
        */

        if (($read) and (isset(self::$_cache))) {
            //$id = 'Zend_Translate_' . md5(serialize($options['content'])) . '_' . $this->toString();
            if (self::$_cacheTags) {
                self::$_cache->save($temp, $id, array($this->_options['tag']));
            } else {
                self::$_cache->save($temp, $id);
            }
        }

        //Debug::e($this->_options['locale']);
        return $this;
    }

    /**
     * returns the adapters name
     *
     * @return string
     */
    public function toString()
    {
        return "Legacy";
    }

    /**
     * Translates the given string
     * returns the translation
     *
     * @see Zend_Locale
     * @param  string|array       $messageId Translation string, or Array for plural translations
     * @param  string|Zend_Locale $locale    (optional) Locale/Language to use, identical with
     *                                       locale identifier, @see Zend_Locale for more information
     * @return string
     */
    public function translate($messageId, $locale = null)
    {
        return defined($messageId) ? constant($messageId) : $messageId;
    }

    /**
     * Checks if a string is translated within the source or not
     * returns boolean
     *
     * @param  string             $messageId Translation string
     * @param  boolean            $original  (optional) Allow translation only for original language
     *                                       when true, a translation for 'en_US' would give false when it can
     *                                       be translated with 'en' only
     * @param  string|Zend_Locale $locale    (optional) Locale/Language to use, identical with locale identifier,
     *                                       see Zend_Locale for more information
     * @return boolean
     */
    public function isTranslated($messageId, $original = false, $locale = null)
    {
        return defined($messageId) ? true : false;
    }

    /**
     * Get translation data path
     *
     *
     */
    public function getPath($domain = "", $locale = null, $absolute = false)
    {
        $domain = $domain ?: 'global';
        $persistKey = 'translate.legacy.' . Xoops::config('identifier') . '.' . $domain . '.' . (string) $absolute;
        if (!$path = Xoops::persist()->load($persistKey)) {
            if (empty($domain) || $domain === 'global') {
                list($domain, $key) = array("global", null);
            } elseif (strpos($domain, ":") !== false) {
                list($domain, $key) = explode(":", $domain, 2);
            } else {
                list($domain, $key) = array("module", $domain);
            }

            switch ($domain) {
            // Global language
            case "":
            case "global":
                $path = 'www/language';
                break;
            // Themes
            case "theme":
                $path = "theme/" . $key . "/language";
                break;
            // Plugins
            case "plugin":
                $path = "plugin/" . $key . "/language";
                break;
            // Module or application
            case "module":
                if (Xoops::service('module')->getType($key) == 'app') {
                    $path = "app/" . Xoops::service('module')->getDirectory($key) . "/language";
                } else {
                    $path = "module/" . $key . "/language";
                }
                break;
            case "app":
                $path = "app/" . Xoops::service('module')->getDirectory($key) . "/language";
                break;
            // Other domain
            default:
                $path = $domain . "/" . $key . "/language";
                break;
            }
            $fullPath = Xoops::path($path) ?: '';
            $path = empty($fullPath) ? 'none' : ($absolute ? $fullPath : $path);
            Xoops::persist()->save($path, $persistKey);
        }
        if ($path === 'none') {
            return false;
        }

        $path .= '/' . ($locale ?: $this->getLocale());

        return $path;
    }
}