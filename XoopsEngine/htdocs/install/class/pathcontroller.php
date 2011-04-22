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

class PathController
{
    private $wizard;

    public $paths = array(
        'www'       => '',
        'lib'       => '',
        'var'       => '',
        'usr'       => '',
        'app'       => '',
        'plugin'    => '',
        'applet'    => '',
        'img'       => '',
        'theme'     => '',
        'upload'    => '',
    );

    public $validPath = array(
        'www'       => -1,
        'var'       => -1,
        'lib'       => -1,
        'app'       => -1,
        'usr'       => -1,
        'plugin'    => -1,
        'applet'    => -1,
        'img'       => 0,
        'theme'     => 0,
        'upload'    => -1,
    );

    public $validUrl = array(
        'www'       => -1,
        'lib'       => 0,
        'app'       => 0,
        'plugin'    => 0,
        'applet'    => 0,
        'var'       => 0,
        'img'       => -1,
        'theme'     => -1,
        'upload'    => -1,
    );

    public $permErrors = array(
        'www'       => false,
        'var'       => false,
        'lib'       => false,
        'upload'    => false,
    );

    public function __construct($wizard)
    {
        $this->wizard = $wizard;
    }

    public function init()
    {
        $this->setRequest();
        // Load from persistent
        if (isset($this->wizard->persistentData['paths'])) {
            foreach ($this->paths as $key => &$path) {
                $path = array(
                    "path"  => $this->wizard->persistentData['paths'][$key]['path'],
                    "url"   => isset($this->wizard->persistentData['paths'][$key]['url']) ? $this->wizard->persistentData['paths'][$key]['url'] : "",
                );
            }
        // Initialize
        } else {
            $baseUrl = $this->wizard->baseLocation();
            $this->paths['www'] = array(
                'path'  => rtrim(str_replace("\\", "/", realpath('../')), "/"),
                'url'   => substr($baseUrl, 0, strrpos($baseUrl, '/'))
            );
            foreach ($this->wizard->configs['paths'] as $key => $inits) {
                foreach ((array) $inits['path'] as $init) {
                    if ($init{0} === "%") {
                        list($idx, $loc) = explode('/', $init, 2);
                        $idx = substr($idx, 1);
                        if (isset($this->paths[$idx]['path'])) {
                            $init = $this->paths[$idx]['path'] . '/' . $loc;
                        }
                    } else {
                        $init = $this->paths['www']['path'] . '/' . $init;
                    }
                    $path = preg_replace('/\w+\/\.\.\//', '', $init);
                    if (is_dir($path . "/")) break;
                }
                $this->paths[$key]['path'] = $path;

                if (!isset($inits['url']) || false === $inits['url']) continue;
                foreach ((array) $inits['url'] as $init) {
                    if ($init{0} === "%") {
                        list($idx, $loc) = explode('/', $init, 2);
                        $idx = substr($idx, 1);
                        if (isset($this->paths[$idx]['url'])) {
                            $init = $this->paths[$idx]['url'] . '/' . $loc;
                        }
                    }
                    $this->paths[$key]['url'] = $init;
                    if (0 <= $this->checkUrl($key)) break;
                }
            }
        }
    }

    private function setRequest()
    {
        foreach ($this->paths as $key => &$path) {
            $reqKey = "path_" . $key;
            if (isset($_POST[$reqKey])) {
                $request = str_replace("\\", "/", trim($_POST[$reqKey]));
                $this->wizard->persistentData['paths'][$key]['path'] = rtrim($request, '/');
            }
            $reqKey = "url_" . $key;
            if (isset($_POST[$reqKey])) {
                $request = str_replace("\\", "/", trim($_POST[$reqKey]));
                $this->wizard->persistentData['paths'][$key]['url'] = rtrim($request, '/');
            }
        }
    }

    public function validate()
    {
        $ret = true;
        foreach (array_keys($this->paths) as $key) {
            if ($this->checkPath($key) >= 0) {
                $this->checkPermissions($key);
            } else {
                $ret = false;
            }
            $result = $this->checkUrl($key);
            if ($result < 0) {
                $ret = false;
            }
        }
        $validPerms = true;
        foreach ($this->permErrors as $key => $errs) {
            if (empty($errs)) continue;
            foreach ($errs as $path => $status) {
                if (empty($status)) {
                    $ret = false;
                    break;
                }
            }
        }
        return $ret;
    }

    public function checkPath($path = '')
    {
        if (isset($this->paths[$path]['path'])) {
            if (is_dir($this->paths[$path]['path']) && is_readable($this->paths[$path]['path'])) {
                $this->validPath[$path] = 1;
            } elseif (!empty($this->paths[$path]['path'])) {
                $this->validPath[$path] = -1;
            }
            $ret = $this->validPath[$path];
        } else {
            $ret = -1;
        }
        return $ret;
    }

    public function checkUrl($key = '')
    {
        $ret = 0;
        if (isset($this->paths[$key]['url']) && isset($this->validUrl[$key])) {
            $ret = $this->validUrl[$key];
            if (!empty($this->paths[$key]['url'])) {
                $method = "validate_url_{$key}";
                if (is_callable(array($this, $method))) {
                    $res = $this->{$method}($this->paths[$key]['url']);
                } else {
                    $res = $this->validateUrl($this->paths[$key]['url']);
                }
                if ($res === null) {
                    $this->validUrl[$key] = 0;
                } else {
                    $this->validUrl[$key] = empty($res) ? -1 : 1;
                }
                $ret = $this->validUrl[$key];
            }
        }

        return $ret;
    }

    private function validateImageUrl($url)
    {
        $mimeType = 'image/gif';

        if (!function_exists('finfo_open') || !$finfo = finfo_open(FILEINFO_MIME)) {
            return $this->validateUrl($url, $mimeType);
        }
        if (!ini_get("allow_url_fopen")) return null;
        $url = $this->formulateUrl($url);
        $content = @file_get_contents($url);
        $ret = ($mimeType == finfo_buffer($finfo, $content, FILEINFO_MIME_TYPE)) ? true : false;
        finfo_close($finfo);
        return $ret;
    }

    private function validate_url_theme($url)
    {
        $url .= "/blank.gif";
        return $this->validateImageUrl($url);
    }

    private function validate_url_upload($url)
    {
        $url .= "/blank.gif";
        return $this->validateImageUrl($url);
    }

    private function validate_url_img($url)
    {
        $url .= "/blank.gif";
        return $this->validateImageUrl($url);
    }

    /*
    private function validate_url_app($url)
    {
        if (empty($url)) {
            return null;
        }
        $url .= "/system/images/blank.gif";
        return $this->validateImageUrl($url);
    }
    */

    private function formulateUrl($url)
    {
        if (strpos($url, '://') !== false) {
            return $url;
        }

        if ($url{0} != "/") {
            $url = (isset($this->paths["www"]['url']) ? $this->paths["www"]['url'] : "") . '/' . $url;
        } else {
            $proto    = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
            $host    = $_SERVER['HTTP_HOST'];
            $url = $proto . '://' .  $host . $url;
        }

        return $url;
    }

    private function validateUrl($url = "", $contentType = "")
    {
        if (empty($url)) return false;
        if (!ini_get("allow_url_fopen")) return null;
        if (!is_callable("get_headers")) return null;

        /* #@+ for php file */
        $path = parse_url($url, PHP_URL_PATH);
        $basename = basename($path);
        if (strpos($url, '://') === false && $pos = strrpos($basename, '.')) {
            if (substr($basename, $pos, 4) === '.php') {
                $localUrl = $path;
                if ($localUrl{0} != "/") {
                    $localUrl = (isset($this->paths["www"]['path']) ? $this->paths["www"]['path'] : "") . '/' . $localUrl;
                }
                return file_exists($localUrl);
            }
        }
        /* #@- */

        $url = $this->formulateUrl($url);
        // Set options to disable redirects, otherwise get_headers will return multiple responses
        $opts = array('http' => array('max_redirects' => 1,'ignore_errors' => 1));
        stream_context_get_default($opts);
        $result = @get_headers($url, 1);
        $ret = preg_match("#HTTP/[^\s]+[\s][1-3][0-9]{2}([\s]?)#i", $result[0], $matches);
        if ($ret && !empty($contentType)) {
            $ret = (strpos($result['Content-Type'], $contentType) !== false);
        }
        return $ret;
    }

    private function setPermission($parent, $path, &$error)
    {
        if (is_array($path)) {
            foreach (array_keys($path) as $item) {
                if (is_string($item)) {
                    $error[$parent . "/" . $item] = $this->makeWritable($parent . "/" . $item);
                    if (empty($path[$item])) continue;
                    foreach ($path[$item] as $child) {
                        $this->setPermission($parent . "/" . $item, $child, $error);
                    }
                } else {
                    $error[$parent . "/" . $path[$item]] = $this->makeWritable($parent . "/" . $path[$item]);
                }
            }
        } else {
            $error[$parent . "/" . $path] = $this->makeWritable($parent . "/" . $path);
        }
        return;
    }

    private function checkPermissions($path)
    {
        $paths = $this->wizard->configs['writable'];

        if (!isset($this->paths[$path]['path'])) {
            return false;
        }
        if (!isset($this->permErrors[$path])) {
            return true;
        }
        $this->setPermission($this->paths[$path]['path'], $paths[$path], $errors);
        if (in_array(false, $errors)) {
            $this->permErrors[$path] = $errors;
        }
        return true;
    }

    /**
     * Write-enable the specified folder
     * @param string $path
     * @param bool $recurse
     * @param bool $create
     * @return bool
     */
    private function makeWritable($path, $recurse = true, $create = true)
    {
        $modeFolder = intval('0777', 8);
        $modeFile = intval('0666', 8);
        $isNew = false;
        if (!file_exists($path)) {
            if (!$create) {
                return false;
            } else {
                (false === strpos(basename($path), '.')) ? mkdir($path, $modeFolder) : touch($path);
                $isNew = true;
            }
        }
        if (!is_writable($path)) {
            @chmod($path, is_file($path) ? $modeFile : $modeFolder);
        }
        clearstatcache();
        $status = is_writable($path) ? 1 : 0;
        if (!$isNew && $status && $recurse && is_dir($path)) {
            $iterator = new DirectoryIterator($path);
            foreach ($iterator as $fileinfo) {
                if ($fileinfo->isDot()) {
                    continue;
                }
                if ($fileinfo->isWritable()) {
                    continue;
                }
                $status = $status * $this->makeWritable($fileinfo->getPathname(), $recurse, $create);
                if (!$status) {
                    break;
                }
            }
        }

        return $status;
    }

    public function pathCheckHtml($path)
    {
        $valid = $this->validPath[$path];
        $msg = "";
        if ($valid >= 0) {
            $msg = XOOPS_PATH_FOUND;
            $span = "success";
        }  else {
            $msg = ERR_COULD_NOT_ACCESS;
            $span = "error";
        }
        $display = empty($valid) ? 'style="display: none;"' : '';

        $string = "<div id='path_{$path}_img' class='pathmessage' $display><span class='$span'>{$msg}</span></div>";
        return $string;
    }

    public function urlCheckHtml($path)
    {
        $valid = $this->validUrl[$path];
        $msg = "";
        if ($valid >= 0) {
            $msg = XOOPS_URL_FOUND;
            $span = "success";
        }  else {
            $msg = ERR_URL_NOT_ACCESS;
            $span = "error";
        }
        $display = empty($valid) ? 'style="display: none;"' : '';

        $string = "<div id='url_{$path}_img' class='pathmessage' $display><span class='$span'>{$msg}</span></div>";
        return $string;
    }

}