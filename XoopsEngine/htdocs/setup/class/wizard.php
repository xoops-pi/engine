<?php
/**
 * Xoops Engine Setup Wizard
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
 * @credits         Skalpa Keo <skalpa@xoops.org>
 * @since           3.0
 * @package         Setup
 * @version         $Id$
 */

namespace Xoops\Setup;

class Wizard
{
    protected $root;
    protected $request;
    protected $controller;
    protected $pageIndex = null;

    protected $persistentData = array();
    protected $locale = array('lang' => 'en', 'charset' => 'UTF-8');
    protected $language = 'english';
    protected $pages = array();
    protected $configs = array();

    public $support = array(
        "url"   => "http://www.xoopsengine.org",
        "title" => "Xoops Engine",
    );

    public function __construct()
    {
        $this->root = dirname(__DIR__);
        spl_autoload_register(array($this, 'autoload'));
        $this->request = new Request();
    }

    public function autoload($class)
    {
        $map = array(
            'Xoops\\Setup\\Controller'  => $this->root . '/controllers',
            'Xoops\\Setup'              => $this->root . '/class',
        );
        $pos = strrpos($class, '\\');
        $prefix = substr($class, 0, $pos);
        $suffix = substr($class, $pos + 1);
        if (!isset($map[$prefix])) {
            return false;
        }
        $classFile = $map[$prefix] . '/' . strtolower($suffix) . '.php';
        include $classFile;
    }

    public function init()
    {
        $this->loadPersist();

        // Load the main language file
        $this->setLocale();
        $this->setLanguage();
        $this->loadLanguage('setup');

        // Setup pages
        $this->pages = include $this->root . '/include/page.php';

        // Load default configs
        $this->configs = include $this->root . '/include/config.php';

        if (!$this->checkAccess()) {
            return false;
        }

        return true;
    }

    public function getConfig($key = null)
    {
        if (null === $key) {
            return $this->configs;
        }
        if (isset($this->configs[$key])) {
            return $this->configs[$key];
        }

        return null;
    }

    public function getRequest()
    {
        return $this->request;
    }

    protected function checkAccess()
    {
        return true;
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function loadLanguage($file)
    {
        if (file_exists($this->root . "/language/{$this->language}/{$file}.php")) {
            return include $this->root . "/language/{$this->language}/{$file}.php";
        } else {
            return include $this->root . "/language/english/{$file}.php";
        }
    }

    public function setLocale($locale = null)
    {
        if (empty($locale)) {
            if (!empty($this->persistentData['locale'])) {
                $this->locale = $this->persistentData['locale'];
            }
            return;
        }
        $this->locale = array(
            'lang'      => $locale[0],
            'charset'   => $locale[1],
        );
        $this->persistentData['locale'] = $this->locale;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setLanguage($language = null)
    {
        if (empty($language)) {
            if (!empty($this->persistentData['language'])) {
                $language = $this->persistentData['language'];
            }
        } else {
            $language = preg_replace("/[^a-z0-9_\-\.]/i", "", $language);
        }
        if (empty($language) || !is_dir($this->root . "/language/{$language}/")) {
            return false;
        }
        $this->persistentData['language'] = $this->language = $language;
        return true;
    }

    protected function getpage($page)
    {
        $page = (string) $page;
        $pageList = array_keys($this->pages);
        if (!isset($this->pages[$page])) {
            if (is_numeric($page)) {
                $pageIndex = (null === $this->pageIndex) ? 0 : $this->pageIndex;
                if ($page{0} == '+' || $page{0} == '-') {
                    $pageIndex += intval($page);
                } else {
                    $pageIndex = intval($page);
                }
            } else {
                $pageIndex = 0;
            }
            $page = $pageList[$pageIndex];
        }

        return $page;
    }

    public function dispatch()
    {
        $page = $this->request->getParam('page', '');
        $page = $this->getPage($page);
        $this->pageIndex = array_search($page, array_keys($this->pages));

        $controllerClass = 'Xoops\\Setup\\Controller\\' . ucfirst($page);
        $action = $this->request->getParam('action', '') ?: ($this->request->isPost() ? 'submit' : 'index');
        $action .= 'Action';
        $this->controller = new $controllerClass($this);
        $this->controller->$action();
    }

    public function render()
    {
        $status = $this->controller->getStatus();
        if ($status > 0 /*&& !$this->request->getParam('r')*/) {
            $this->gotoPage('+1');
        }
        $content = $this->controller->getContent();
        if ($this->request->isXmlHttpRequest()) {
            if ($this->controller->hasBootstrap()) {
                \Xoops::service('logger')->enabled(false);
            } else {
                error_reporting(0);
            }
            echo $content;
            return;
        }

        $pages = $this->pages;
        $navPages = array();
        foreach ($pages as $key => &$page) {
            $page['url'] = $this->url($key);
            if (empty($page['hide'])) {
                $navPages[$key] = $page;
            }
        }
        $pageIndex = $this->pageIndex;
        $pageList = array_keys($pages);
        $locale = $this->locale;
        $language = $this->language;

        $currentPage = $pages[$pageList[$pageIndex]];
        $currentPage['key'] = $pageList[$pageIndex];

        $title = $currentPage['title'] . ' - ' . _INSTALL_WIZARD . '(' . ($this->pageIndex + 1) . '/' . count($this->pages) . ')';
        $desc = $currentPage['desc'];

        if ($pageIndex > 0) {
            $previousUrl = $this->url('-1', array('r' => 1));
        }
        if ($status > -1 && $pageIndex < count($pages) - 1) {
            $nextUrl = $this->url('+1');
        }
        //$pageHasHelp = $this->controller->hasHelp();
        //$pageHasAjax = $this->controller->hasAjax();
        $pageHasForm = $this->controller->hasForm();
        $headContent = $this->controller->headContent();
        $footContent = $this->controller->footContent();
        $baseUrl = $this->request->getBaseUrl();

        $data = compact('status', 'locale', 'language', 'title', 'desc', 'baseUrl', 'navPages', 'pageIndex', 'currentPage', 'previousUrl', 'nextUrl', 'pageHasForm', 'content', 'headContent', 'footContent');
        ob_start();
        include $this->root . '/include/template.php';
        $content = ob_get_contents();
        ob_end_clean();

        // Prevent client caching
        header("Cache-Control: no-store, no-cache, must-revalidate", false);
        header("Pragma: no-cache");
        echo  $content;
    }

    public function url($page = '', $params = array())
    {
        $page = $this->getPage($page);
        if (!empty($page)) {
            $params['page'] = $page;
        }
        $query = http_build_query($params);
        $url = $this->request->getBaseUrl() . ($query ? '?' . $query : '');
        return $url;
    }

    public function gotoPage($page = '', $params = array())
    {
        $url = $this->url($page, $params);
        header("Location: " . $this->request->getScheme() . '://' . $this->request->getHttpHost() . $url);
        exit();
    }

    public function loadPersist()
    {
        session_start();
        $_SESSION[__CLASS__] = isset($_SESSION[__CLASS__]) ? $_SESSION[__CLASS__] : array();
        $this->persistentData =& $_SESSION[__CLASS__];
        return;
    }

    public function destroyPersist()
    {
        $this->persistentData = array();
        return true;
    }

    public function setPersist($key, $value)
    {
        $this->persistentData[$key] = $value;
        return $this;
    }

    public function getPersist($key)
    {
        return isset($this->persistentData[$key]) ? $this->persistentData[$key] : null;
    }

    public function shutdown()
    {
        $this->destroyPersist();
        return true;

        $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(\Xoops::path('www') . '/install'), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($objects as $object) {
            if ($object->isFile()) {
                @unlink($object->getPathname());
            } else {
                @rmdir($object->getPathname());
            }
        }
        @rmdir(\Xoops::path('www') . '/install');
    }
}