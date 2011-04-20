<?php
/**
 * XOOPS translate list registry
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
 * @package         Xoops_Core
 * @subpackage      Registry
 * @version         $Id$
 */

namespace Engine\Xoops\Registry;

class Translate extends \Kernel\Registry
{
    //protected $registry_key = "registry_translate";

    private function setOptions($domain, $charset = null)
    {
        if (empty($domain)) {
            list($domain, $key) = array("global", null);
        } elseif (strpos($domain, ":") !== false) {
            list($domain, $key) = explode(":", $domain, 2);
        } else {
            list($domain, $key) = array("module", $domain);
        }
        $options = compact("domain", "key", "charset");

        return $options;
    }

    protected function createTags(&$options = array())
    {
        if ($options["domain"] == "module" && !empty($options["key"])) {
            $options["module"] = $options["key"];
            unset($options["domain"], $options["key"]);
        }
        return parent::createTags($options);
    }

    protected function loadDynamic($options = array())
    {
        //global $xoops;

        $localeList = array();
        switch ($options['domain']) {
        // Global language
        case "":
        case "global":
            $path = 'www/language';
            break;
        // Themes
        case "theme":
            $path = "theme/" . $options['key'] . "/language";
            break;
        // Plugins
        case "plugin":
            $path = "plugin/" . $options['key'] . "/language";
            break;
        // Module or application
        case "module":
            if (\Xoops::service('module')->getType($options['key']) == 'legacy') {
                $path = "module/" . $options['key'] . "/language";
            } else {
                $path = "app/" . \Xoops::service('module')->getDirectory($options['key']) . "/language";
            }
            break;
        case "app":
            $path = "app/" . \Xoops::service('module')->getDirectory($options['key']) . "/language";
            break;
        // Other domain
        default:
            $path = $options['domain'] . "/" . $options['key'] . "/language";
            break;
        }
        $realPath = \Xoops::path($path);
        if (!is_dir($realPath)) {
            return $localeList;
        }
        $configLookup = \Xoops::loadConfig('registry.translate.ini.php');
        $iterator = new \DirectoryIterator($realPath);
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                continue;
            }
            $fileName = $fileinfo->getFilename();
            // split locale and charset
            if (strpos($fileName, ".")) {
                list($locale, $charset) = explode(".", $fileName, 2);
            } else {
                list($locale, $charset) = array($fileName, "utf-8");
            }
            // For legacy adapter
            if (isset($configLookup[$locale])) {
                $locale = $configLookup[$locale];
                if (strpos($locale, ".")) {
                    list($locale, $charset) = explode(".", $locale, 2);
                }
                $adapter = 'legacy';
            } else {
                $adapter = 'gettext';
            }
            if (!empty($options['charset']) && $charset != $options['charset']) {
                continue;
            }
            if (!\Xoops_Zend_Locale::isLocale($locale, true)) {
                continue;
            }
            $item = array();
            $item[$adapter] = $path . '/' . $fileinfo->getFilename();
            if (empty($options['charset'])) {
                $localeList[$locale][$charset] = $item;
            } else {
                $localeList[$locale] = $item;
            }
        }
        return $localeList;
    }

    public function read($domain, $charset = null)
    {
        $options = $this->setOptions($domain, $charset);
        return $this->loadData($options);
    }

    public function create($domain, $charset = null)
    {
        self::delete($domain, $charset);
        self::read($domain, $charset);
        return true;
    }

    public function delete($domain, $charset = null)
    {
        $options = $this->setOptions($domain, $charset);
        return $this->cache->clean('matchingTag', self::createTags($options));
    }

    public function flush($domain = null)
    {
        return self::delete($domain);
    }
}