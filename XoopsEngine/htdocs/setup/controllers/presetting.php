<?php
/**
 * Xoops Engine Setup Controller
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

namespace Xoops\Setup\Controller;

class Presetting extends AbstractController
{
    public function init()
    {
        $this->wizard->destroyPersist();
    }

    public function submitAction()
    {
        $language = $this->request->getParam('language');
        if (!empty($language)) {
            $languageList = $this->getLanguages();
            $language = htmlspecialchars($language);
            if (isset($languageList[$language])) {
                $this->wizard->setLanguage($language);
                $this->wizard->setLocale($this->getlocale($language));
                //$this->status = 1;
            }
        }
        /*
        if ($this->status < 1) {
            $this->loadContent();
        }
        */

        echo "language: " . $language;
    }

    public function indexAction()
    {
        $this->loadContent();
    }

    protected function getLanguages()
    {
        // Fetch language list from global language folder
        $langPath = dirname($this->wizard->getRoot()) . '/language';

        // Available translations
        $localeFile = $langPath . '/' . $this->wizard->getLanguage() . '/locales.php';
        $locales = file_exists($localeFile) ? include $localeFile : array();

        // Container for available locales
        $localeList = array();
        // Container for available languages
        $languageList = array();

        $iterator = new \DirectoryIterator($langPath);
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                continue;
            }
            $languageName = $fileinfo->getFilename();
            if (!is_dir($this->wizard->getRoot() . '/language/' . $languageName)) {
                continue;
            }

            $localeFile = $fileinfo->getPathname() . '/locale.ini.php';
            if (!file_exists($localeFile)) {
                list($language, $charset) = array('', '');
            } else {
                $locale = parse_ini_file($localeFile);
                $language = $locale['lang'];
                $charset = empty($locale['charset']) ? 'UTF-8' : $locale['charset'];
                $localeList[$languageName] = array($language, $charset);
            }

            $languageList[$languageName] = isset($locales[$language]) ? $locales[$language] : $languageName;
        }

        return $languageList;
    }

    protected function getLocale($language)
    {
        // Fetch language list from global language folder
        $localeFile = dirname($this->wizard->getRoot()) . '/language/' . $language. '/locale.ini.php';
        if (!file_exists($localeFile)) {
            list($language, $charset) = array('', '');
        } else {
            $locale = parse_ini_file($localeFile);
            $language = $locale['lang'];
            $charset = empty($locale['charset']) ? 'UTF-8' : $locale['charset'];
        }
        return array($language, $charset);
    }

    protected function loadContent()
    {
        $this->loadLanguageForm();
        $this->loadRequirementForm();
    }

    protected function loadLanguageForm()
    {
        $languageList = $this->getLanguages();

        $content = '
            <h2>' . _INSTALL_LANGUAGE_LEGEND . '</h2>
            <p class="caption">' . _INSTALL_LANGUAGE_DESC . '</p>
            <div class="install-form">
                <p>
                    <select id="language-selector" size="5" name="language">';
                        foreach ($languageList as $language => $name) {
                            $selected = ($language == $this->wizard->getLanguage()) ? " selected='selected'" : "";
                            $content .= "<option value='{$language}'{$selected}>{$name}</option>";
                        }
                        $content .= '</select>
                </p>
            </div>';
        $this->content .= $content;

        $this->headContent .= '
            <style type="text/css" media="screen">
                #language-selector {
                    width: 300px;
                    margin: 10px auto;
                    border: 1px solid #ddd;
                }

                #language-selector li {
                    margin: 0;
                    list-style: none;
                    cursor: pointer;
                }

                #language-selector .ui-selecting {
                    background: #ccc;
                }

                #language-selector .ui-selected {
                    background: #999;
                    color: #fff;
                }
            </style>';

        $this->footContent .= '
            <script type="text/javascript">
            $("#language-selector").change(function () {
                $.ajax({
                  url: "' . $_SERVER['PHP_SELF'] . '",
                  data: {page: "presetting", language: this.value, action: "submit"},
                });
            });
            </script>';

    }

    protected function loadRequirementForm()
    {
        $this->verifyRequirement();
        if ($this->status < 0) {
        	$content = '<h2><span class="failure">' . _INSTALL_SERVER_LEGEND . '</span> <a href="javascript:void(0);" id="advanced-label"><span style="display: none;">[+]</span><span>[-]</span></a></h2>';
        } else {
        	$content = '<h2><span class="success">' . _INSTALL_SERVER_LEGEND . '</span> <a href="javascript:void(0);" id="advanced-label"><span>[+]</span><span style="display: none;">[-]</span></a></h2>';
        }
        //$content = '<h2> <span class="' . (($this->status < 0) ? 'failure' : 'success') . '">' . _INSTALL_SERVER_LEGEND . '</span> <a href="javascript:void(0);" id="advanced-label"><span>[+]</span><span style="display: none;">[-]</span></a></h2>';
        $content .= '
            <p class="caption">' . _INSTALL_SERVER_DESC . '</p>
            <div class="install-form advanced-form" id="advanced-form">
                <h3 class="section">' . _INSTALL_REQUIREMENT_SYSTEM . '</h3>
                <p class="caption">' . _INSTALL_REQUIREMENT_SYSTEM_HELP . '</p>';
                foreach ($this->result['system'] as $item => $result) {
                    $value = $result['value'];
                    $style = 'success';
                    switch ($result['status']) {
                        case -1:
                            $style = 'failure';
                            $value = $value ?: _INSTALL_REQUIREMENT_INVALID;
                            break;
                        case 0:
                            $style = 'warning';
                            $value = $value ?: _INSTALL_REQUIREMENT_UPDATE;
                            break;
                        case 1:
                        default:
                            $style = 'success';
                            $value = $value ?: _INSTALL_REQUIREMENT_VALID;
                            break;
                    }
                    $content .= '
                        <p><div class="label">' . $result['title'] . '</div>
                        <div class="text"><span class="' . $style . '">' . $value . '</span>';

                    if (!empty($result['message'])) {
                        $content .= '<em class="message">' . $result['message'] . '</em>';
                    }
                    $content .= '</div></p>';
                }

                $content .= '
                <h3 class="section">' . _INSTALL_REQUIREMENT_EXTENSION . '</h3>
                <p class="caption">' . _INSTALL_REQUIREMENT_EXTENSION_HELP . '</p>';
                foreach ($this->result['extension'] as $item => $result) {
                    $value = $result['value'];
                    $style = 'success';
                    switch ($result['status']) {
                        case -1:
                            $style = 'failure';
                            $value = $value ?: _INSTALL_REQUIREMENT_INVALID;
                            break;
                        case 0:
                            $style = 'warning';
                            $value = $value ?: _INSTALL_REQUIREMENT_UPDATE;
                            break;
                        case 1:
                        default:
                            $style = 'success';
                            $value = $value ?: _INSTALL_REQUIREMENT_VALID;
                            break;
                    }
                    $content .= '
                        <p><div class="label">' . $result['title'] . '</div>
                        <div class="text"><span class="' . $style . '">' . $value . '</span>';

                    if (!empty($result['message'])) {
                        $content .= '<span class="message">' . $result['message'] . '</span>';
                    }
                    $content .= '</div></p>';
                }

        $content .= '
            </div>';
        $this->content .= $content;

        $this->footContent .= '
            <script type="text/javascript">
            $(function() {' .
                (($this->status < 0)
                    ? '
                        $("#advanced-form").slideToggle();
                        $("#advanced-label span.toggle-span").toggle();
                    '
                    :''
                ) .
                '$("#advanced-label").click(function() {
                    $("#advanced-form").slideToggle();
                    $("#advanced-label span").toggle();
                });
            })
            </script>';

    }

    protected function verifyRequirement()
    {
        $this->result['system'] = $this->checkSystem();
        $this->result['extension'] = $this->checkExtension();
        foreach ($this->result['system'] as $item => $result) {
            $this->status = min($this->status, $result['status']);
            if ($this->status < 0) {
                break;
            }
        }
        $status = 1;
        foreach ($this->result['extension'] as $item => $result) {
            $status = min($status, $result['status']);
            if ($status < 0) {
                $this->status = -1;
                break;
            }
        }
    }

    protected function checkExtension($item = null)
    {
        if (empty($item)) {
            $result = array();
            foreach ($this->wizard->getConfig('extension') as $item => $title) {
                $res = $this->checkExtension($item);
                $res['title'] = defined($title) ? constant($title) : $title;
                $result[$item] = $res;
            }

            return $result;
        }

        $status = 1;
        $value = '';
        $message = '';
        if (!extension_loaded($item)) {
            $status = 0;
            if (defined('_INSTALL_EXTENSION_' . strtoupper($item). '_PROMPT')) {
                $message = constant('_INSTALL_EXTENSION_' . strtoupper($item). '_PROMPT');
            }
        }
        switch ($item) {
            case 'gd':
                if ($status) {
                    $gdlib = gd_info();
                    $value = $gdlib['GD Version'];
                }
                break;
            default:
                break;
        }

        $result = array(
            'status'    => $status,
            'value'     => $value,
            'message'   => $message,
        );
        return $result;
    }

    protected function checkSystem($item = null)
    {
        if (empty($item)) {
            $result = array();
            foreach ($this->wizard->getConfig('system') as $item => $title) {
                $res = $this->checkSystem($item);
                $res['title'] = defined($title) ? constant($title) : $title;
                $result[$item] = $res;
            }

            return $result;
        }

        $result = array(
            'status'    => 0,
            'value'     => _INSTALL_REQUIREMENT_UNKNOWN,
            'message'   => '',
        );
        $method = 'checkSystem' . ucfirst($item);
        if (!method_exists($this, $method)) {
            return $result;
        }
        return $this->$method();
    }


    protected function checkSystemServer()
    {
        $status = 1;
        $value = '';
        $message = '';
        if (stristr($_SERVER["SERVER_SOFTWARE"], 'nginx')) {
            $value = 'nginx';
            $status = 0;
            $message = _INSTALL_REQUIREMENT_SERVER_NGINX;
        } elseif (stristr($_SERVER["SERVER_SOFTWARE"], 'apache')) {
            $value = 'Apache';
            $modules = apache_get_modules();
            if (!in_array('mod_rewrite', $modules)) {
                $status = -1;
                $message = _INSTALL_REQUIREMENT_SERVER_MOD_REWRITE;
            }
        } else {
            $value = $_SERVER["SERVER_SOFTWARE"];
            $status = -1;
            $message = _INSTALL_REQUIREMENT_SERVER_NOT_SUPPORTED;
        }

        $result = array(
            'status'    => $status,
            'value'     => $value,
            'message'   => $message,
        );
        return $result;
    }

    protected function checkSystemPhp()
    {
        $status = 1;
        $value = PHP_VERSION;
        //$value = '5.2';
        $message = '';
        if (version_compare($value, '5.3.0') < 0) {
            $status = -1;
            $message = sprintf(_INSTALL_REQUIREMENT_VERSION_REQUIRED, '5.3.0 or higher');
        }

        $result = array(
            'status'    => $status,
            'value'     => $value,
            'message'   => $message,
        );
        return $result;
    }

    protected function checkSystemPdo()
    {
        $status = 1;
        $value = '';
        $message = '';
        if (!extension_loaded('pdo')) {
            $status = 0;
        }
        $drivers = \PDO::getAvailableDrivers();
        $value = implode(', ', $drivers);
        if (empty($drivers) || !in_array('mysql', $drivers)) {
            $status = 0;
        }
        if (!$status) {
            $message = _INSTALL_REQUIREMENT_PDO_PROMPT;
        }

        $result = array(
            'status'    => $status,
            'value'     => $value,
            'message'   => $message,
        );
        return $result;
    }

    protected function checkSystemPersist()
    {
        $status = 1;
        $value = '';
        $message = '';
        $items = array();
        $persistList = array('apc', 'redis', 'memcached', 'memcache');
        foreach($persistList as $item) {
            if (extension_loaded($item)) {
                $items[] = $item;
            }
        }
        if (!empty($items)) {
            $value = implode(', ', $items);
        } else {
            $status = 0;
            $message = sprintf(_INSTALL_REQUIREMENT_PERSIST_PROMPT, implode(', ', $persistList));
        }

        $result = array(
            'status'    => $status,
            'value'     => $value,
            'message'   => $message,
        );
        return $result;
    }
}