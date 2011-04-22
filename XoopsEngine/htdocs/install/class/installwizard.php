<?php
/**
 * See the enclosed file license.txt for licensing information.
 * If you did not receive this file, get it at http://www.fsf.org/copyleft/gpl.html
 *
 * @copyright       Xoops Engine
 * @license         BSD License
 * @package         installer
 * @since           3.0
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @author          Skalpa Keo <skalpa@xoops.org>
 * @version         $Id$
 */

class XoopsInstallWizard
{
    public $locale = array('lang' => 'en', 'charset' => 'UTF-8');
    public $language = 'english';
    public $pages = array();
    public $currentPage = '';
    public $pageIndex = 0;
    public $configs = array();
    public $persistentData = array();
    private $persistentFile = 'xoops_install_persistent.php';
    public $support = array(
        "url"   => "http://xoops.sf.net/",
        "title" => "Xoops Engine",
    );

    public function __construct()
    {
        if (empty($_SERVER['REQUEST_URI'])) {
            $_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
        }

        //$this->persistentFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . md5($this->baseLocation()) . '.php';
        $this->loadPersist();
        //register_shutdown_function(array($this, 'savePersist'));

        // Load the main language file
        $this->setLocale();
        $this->setLanguage();
        $this->loadLangFile('install');
        // Setup pages
        include_once __DIR__ . '/../include/page.php';
        $this->pages = $pages;

        // Load default configs
        include_once __DIR__ . '/../include/config.php';
        $this->configs = $configs;

        if (!$this->checkAccess()) {
            return false;
        }

        $pagename = preg_replace('~(page_)(.*)~', '$2', basename($_SERVER['PHP_SELF'], ".php"));
        $this->setPage($pagename);

        // Prevent client caching
        header("Cache-Control: no-store, no-cache, must-revalidate", false);
        header("Pragma: no-cache");
        return true;
    }

    function checkAccess()
    {
        if (INSTALL_USER != '' && INSTALL_PASSWORD != '') {
            if (!isset($_SERVER['PHP_AUTH_USER'])) {
                header('WWW-Authenticate: Basic realm="XOOPS Installer"');
                header('HTTP/1.0 401 Unauthorized');
                echo 'You can not access this XOOPS installer.';
                return false;
            }
            if(INSTALL_USER != '' && $_SERVER['PHP_AUTH_USER'] != INSTALL_USER) {
                header('HTTP/1.0 401 Unauthorized');
                echo 'You can not access this XOOPS installer.';
                return false;
            }
            if(INSTALL_PASSWORD != $_SERVER['PHP_AUTH_PW']){
                header('HTTP/1.0 401 Unauthorized');
                echo 'You can not access this XOOPS installer.';
                return false;
            }
        }
        return true;
    }

    function loadLangFile($file)
    {
        if (file_exists(__DIR__ . "/../language/{$this->language}/{$file}.php")) {
            return include __DIR__ . "/../language/{$this->language}/{$file}.php";
        } else {
            return include __DIR__ . "/../language/english/{$file}.php";
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

    function setLanguage($language = null)
    {
        if (empty($language)) {
            if (!empty($this->persistentData['language'])) {
                $language = $this->persistentData['language'];
            }
        } else {
            $language = preg_replace("/[^a-z0-9_\-\.]/i", "", $language);
        }
        if (empty($language) || !is_dir("../language/{$language}/")) {
            return false;
        }
        $this->persistentData['language'] = $this->language = $language;
        //$localeFile =  "../language/{$language}/locale.ini.php";
        //$locale = parse_ini_file($localeFile);
        //$this->locale = array('lang' => $locale['lang'], 'charset' => empty($meta['charset']) ? 'UTF-8' : $meta['charset']);

        //$this->loadLangFile('install');
        return true;
    }

    function setPage($page)
    {
        $pages = array_keys($this->pages);
        if ((int)$page && $page >= 0 && $page < count($pages)) {
            $this->pageIndex = $page;
            $this->currentPage = $pages[$page];
        } elseif (isset($this->pages[$page])) {
            $this->currentPage = $page;
            $this->pageIndex = array_search($this->currentPage, $pages);
        } else {
            $this->currentPage = array_shift($pages);
            return false;
        }

        /*
        if ($this->pageIndex > 0 && !isset($_COOKIE['xo_install_lang'])) {
            header('Location: index.php');
        }
        */

        return $this->pageIndex;
    }

    // Check http://shiflett.org/blog/2006/mar/server-name-versus-http-host
    function baseLocation()
    {
        $proto  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'];
        $base   = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'));
        return $proto . '://' . $host . $base;
    }

    function pageURI($page)
    {
        $pages = array_keys($this->pages);
        $pageIndex = $this->pageIndex;
        if (!(int)$page{0}) {
            if ($page{0} == '+') {
                $pageIndex += substr($page, 1);
            } elseif ($page{0} == '-') {
                $pageIndex -= substr($page, 1);
            } else {
                $pageIndex = (int)array_search($page, $pages);
            }
        }
        if (!isset($pages[$pageIndex])) {
            if ($pageIndex > 0) {
                return dirname($this->baseLocation()); //XOOPS_URL != '' ? XOOPS_URL : '/'; //dirname($this->baseLocation());
            } else {
                return $this->baseLocation();
            }
        }
        $page = $pages[$pageIndex];
        return $this->baseLocation() . "/page_{$page}.php";
    }

    function redirectToPage($page, $status = 303, $message = 'See other')
    {
        $location = $this->pageURI($page);
        $proto = !empty($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
        if (substr(PHP_SAPI, 0, 3) == 'cgi') {
            header("Status: {$status} {$message}");
        } else {
            header("{$proto} {$status} {$message}");
        }
        header("Location: {$location}");
        //session_write_close();
    }

    public function loadPersist()
    {
        session_start();
        $_SESSION[__CLASS__] = isset($_SESSION[__CLASS__]) ? $_SESSION[__CLASS__] : array();
        $this->persistentData =& $_SESSION[__CLASS__];
        return;

        if (is_readable($this->persistentFile)) {
            $persistentData = include $this->persistentFile;
            $this->persistentData = is_array($persistentData) ? $persistentData : array();
        }
    }

    public function savePersist()
    {
        session_destroy();
        return true;

        if (!$fp = fopen($this->persistentFile, "w")) {
            return false;
        }
        $content = "<?php return " . var_export($this->persistentData, true) . "; ?>";
        fputs($fp, $content);
        fclose($fp);
        return true;
    }

    public function destroyPersist()
    {
        return true;

        unlink($this->persistentFile);
    }

    public function shutdown()
    {
        $this->destroyPersist();

        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(Xoops::path('www') . '/install'), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($objects as $object) {
            if ($object->isFile()) {
                @unlink($object->getPathname());
            } else {
                @rmdir($object->getPathname());
            }
        }
        @rmdir(Xoops::path('www') . '/install');
    }
}