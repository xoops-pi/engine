<?php
/**
 * XOOPS SMARTY template engine
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
 * @package         Xoops_Smarty
 * @version         $Id$
 */

/**
 * set SMARTY_DIR to absolute path to Smarty library files.
 * Sets SMARTY_DIR only if user application has not already defined it.
 */
if (!defined('SMARTY_DIR')) {
    define('SMARTY_DIR', XOOPS::path("lib") . DIRECTORY_SEPARATOR . 'Smarty' . DIRECTORY_SEPARATOR);
}

/**
 * Base class: Smarty template engine
 */
require SMARTY_DIR . 'Smarty.class.php';

/**
 * Autoloader
 */
function Xoops_Smarty_Autoload($class)
{
    $_class = strtolower($class);
    if (substr($_class, 0, 16) === 'smarty_internal_' || $_class == 'smarty_security') {
        return SMARTY_SYSPLUGINS_DIR . $_class . '.php';
    }
}
XOOPS::autoloader()->registerCallback("Xoops_Smarty_Autoload");

/**
 * Template engine
 */
class Xoops_Smarty_Engine extends Smarty
{
    public $currentTemplate;
    //private $cache_id;

    public function __construct($options = array())
    {
        parent::__construct();
        // caching type
        //$this->caching_type = 'xoops';

        //$this->caching = false;

        $this->left_delimiter = '<{';
        $this->right_delimiter = '}>';

        $this->cache_dir = XOOPS::path("var") . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "smarty" . DIRECTORY_SEPARATOR . "cache";
        $this->compile_dir = XOOPS::path("var") . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "smarty" . DIRECTORY_SEPARATOR . "compile";

        $this->setTemplateDir(array());
        $this->addPluginsDir(__DIR__ . DIRECTORY_SEPARATOR . 'plugins');
        $this->template_class = "Xoops_Smarty_Template";
        $this->default_template_handler_func = array(&$this, "templateHandler");

        $this->setOptions($options);
    }

    public function setOptions($options = array())
    {
        $properties = array(
            'caching'           => false,
            'compile_check'     => false,
            'debugging'         => false,
            'force_compile'     => false,
            'template_class'    => '',
            'error_unassigned'  => false,
        );
        foreach ($options as $key => $val) {
            if (array_key_exists($key, $properties)) {
                $this->$key = $val;
            }
        }
        if (XOOPS::service('logger')->silent()) {
            $this->debugging = false;
        }
    }

    /**
     * Create compile_id with purposes of distinguishing theme set, module and domain. Template set is not considered
     */
    public function setCompileId($theme_set = null, $module_dirname = null)
    {
        $this->_compile_id = $this->compile_id = $this->generateCompileId($theme_set, $module_dirname);
        return $this;
    }

    public function generateCompileId($theme_set = null, $module_dirname = null)
    {
        $segs = array();
        $segs[] = is_null($module_dirname)
                        ? XOOPS::config('identifier')
                        : $module_dirname;
        $segs[] = is_null($theme_set)
                        ? XOOPS::config('theme_set')
                        : $theme_set;
        $segs = array_filter($segs);
        $compile_id = empty($segs) ? null : implode('-',  $segs);

        return $compile_id;
    }

    /**
     * Create cache_id with purposes of distinguishing cache level
     */
    public function setCacheId($cache_id = null, $level = null)
    {
        $this->cache_id = Xoops_Zend_Cache::generateId($cache_id, $level);
        return $this;
    }

    /**
     * test to see if valid cache exists for this template
     *
     * @param string $tpl_file name of template file
     * @param string $cache_id
     * @param string $compile_id
     * @return string|false results of {@link _read_cache_file()}
     */
    public function is_cached($tpl_file, $cache_id = null, $compile_id = null)
    {
        $cache_id = is_null($cache_id) ? $this->cache_id : $cache_id;
        return parent::isCached($tpl_file, $cache_id, $compile_id);
    }

    /**
     * fetches a rendered Smarty template
     *
     * @param string $template the resource handle of the template file or template object
     * @param mixed $cache_id cache id to be used with this template
     * @param mixed $compile_id compile id to be used with this template
     * @param object $ |null $parent next higher level of Smarty variables
     * @return string rendered template output
     */
    public function fetch($template, $cache_id = null, $compile_id = null, $parent = null, $display = false)
    {
        $this->currentTemplate = ($template instanceof $this->template_class)
            ? $template->buildTemplateFilepath()
            : $template;
        $cache_id = is_null($cache_id) ? $this->cache_id : $cache_id;
        try {
            $output = parent::fetch($template, $cache_id, $compile_id, $parent, $display);
        } catch (Exception $e) {
            trigger_error("<pre>" . $e->__toString() . "</pre><br />" . $template);
            $output = $e->getMessage();
        }

        return $output;
    }

    /**
     * Clears module and theme specified compiled templates
     *
     * @see Smarty_Internal_Utility::clearCompiledTemplate
     */
    public function clearTemplate($module_dirname = null, $theme_set = null)
    {
        $compile_id = $this->generateCompileId($theme_set, $module_dirname);
        return $this->clearCompiledTemplate(null, $compile_id);
    }

    /**
     * Clears module and theme specified caches
     *
     * @see Smarty_Internal_Cache::clear
     */
    public function clearCaches($module_dirname = null, $theme_set = null)
    {
        $compile_id = $this->generateCompileId($theme_set, $module_dirname);
        return $this->clearCache(null, null, $compile_id);
    }

    /**
     * Clears module caches and compiled templates
     */
    public function clearModuleCache($module_dirname = null)
    {
        if (empty($module_dirname)) {
            $this->clearTemplate(null, null);
            $this->clearCache(null, null);
            return true;
        }

        $themes = XOOPS::service("registry")->theme->read();
        foreach (array_keys($themes) as $theme) {
            $this->clearTemplate($module_dirname, $theme);
            $this->clearCache($module_dirname, $theme);
        }
        return true;
    }

    /**
     * Clears caches of a specified cache_id
     */
    public function clearCacheByCacheId($cache_id, $module_dirname = null)
    {
        if (empty($module_dirname)) {
            if (XOOPS::registry("module")) {
                $module_dirname = XOOPS::registry("module")->dirname;
            }
        }
        if (empty($cache_id) || empty($module_dirname)) {
            return false;
        }

        $themes = XOOPS::service("registry")->theme->read();
        foreach (array_keys($themes) as $theme) {
            $compile_id = $this->generateCompileId($theme, $module_dirname);
            $this->cache->clear(null, $cacheId, $compile_id);
        }
        return true;
    }

    public function getVersion()
    {
        return self::SMARTY_VERSION; //$this->_version;
    }

    /**
     * Default template handler
     *
     * Transform simplified path to regular path:
     *  app/module/template.html    => app/module/templates/template.html
     *  block/module/template.html  => app/module/templates/blocks/template.html
     *  admin/module/template.html  => app/module/templates/admin/template.html
     *
     * @see: Smarty_Internal_Template::buildTemplateFilepath()
     *
     * @param string $resource_type
     * @param string $resource_name
     * @param string $template_source
     * @param int $template_timestamp
     * @param {@Smarty_Internal_Template} $template
     * @return string translated template resource path
     */
    public function templateHandler($resource_type, $resource_name, &$template_source, &$template_timestamp, $template)
    {
        /**#@+
         * This is handled by viewRenderer
         * Thus in a template, "templates" need specified, like {include file="app/mymodule/templates/template.html"}
         */
        // Split "section/item/file" to "section/item" and "file"
        // Insert "templates" and assemble asd "section/item/templates/file"
        /**#@-*/

        $segs = explode("/", $resource_name, 3);
        if (count($segs) == 3 && $segs[0] != "theme") {
            switch ($segs[0]) {
                case "block":
                    $resource_name = "app/" . $segs[1] . "/templates/blocks/" . $segs[2];
                    break;
                case "admin":
                    $resource_name = "app/" . $segs[1] . "/templates/admin/" . $segs[2];
                    break;
                case "module":
                    $resource_name = "module/" . $segs[1] . "/templates/" . $segs[2];
                    break;
                default:
                case "app":
                    $resource_name = "app/" . $segs[1] . "/templates/" . $segs[2];
                    break;
            }
        }
        $path = XOOPS::registry("view")->resourcePath($resource_name, true);
        //trigger_error("path:".$path);
        if (!file_exists($path)) {
            $path = false;
        }
        return $path;
    }
}