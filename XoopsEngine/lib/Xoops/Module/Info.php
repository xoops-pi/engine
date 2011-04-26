<?php
/**
 * XOOPS module information loader
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
 * @version         $Id$
 */
class Xoops_Module_Info
{
    private static $modules = array();

    public static function load($module, $category = false)
    {
        $moduleType = Xoops::service('module')->getType($module);
        $modulePath = Xoops::service('module')->getPath($module);
        if (!isset(self::$modules[$module]['info'])) {
            //XOOPS::service('translate')->loadTranslation('modinfo', $module);
            //$path = Xoops::service('module')->getPath($module);
            $file = $modulePath . "/configs/info.php";
            if (file_exists($file)) {
                XOOPS::service('translate')->loadTranslation('info', $module);
                $moduleInfo = Xoops_Config::load($file);
                if (empty($moduleInfo['logo'])) {
                    $moduleInfo['logo'] = "img/images/module.png";
                } else {
                    $moduleInfo['logo'] = (($moduleType == 'app') ? 'app' : 'module') . '/' .  $module . '/' . $moduleInfo['logo'];
                }
            } else {
                /*
                $file = $path . "/xoops_version.php";
                if (!file_exists($file)) {
                    return false;
                }
                XOOPS::service('translate')->loadTranslation('modinfo', $module);
                include $file;
                $moduleInfo = static::transformLegacy($module, $modversion);
                */
                if (false === ($moduleInfo = static::loadLegacy($module))) {
                    return false;
                }
            }

            self::$modules[$module]['info'][0] = $moduleInfo;
        }
        $info = self::$modules[$module]['info'][0];
        if (!empty($category)) {
            if (is_string($category)) {
                if (!isset(self::$modules[$module]['info'][1]['extensions'][$category])) {
                    if (!empty($info['extensions'][$category])) {
                        if (is_string($info['extensions'][$category])) {
                            $file = $modulePath . "/configs/" . $info['extensions'][$category];
                            $info['extensions'][$category] = Xoops_Config::load($file);
                        }
                        $info = $info['extensions'][$category];
                    } else {
                        $info = array();
                    }
                } else {
                    $info = self::$modules[$module]['info'][1]['extensions'][$category];
                }
            } else {
                if (!isset(self::$modules[$module]['info'][1])) {
                    if (!empty($info['extensions'])) {
                        foreach ($info['extensions'] as $extension => $options) {
                            if (!is_string($options)) continue;
                            $file = $modulePath . "/configs/" . $options;
                            $info['extensions'][$extension] = Xoops_Config::load($file);
                        }
                    } else {
                        $info = array();
                    }
                    self::$modules[$module]['info'][1] = $info;
                } else {
                    $info = self::$modules[$module]['info'][1];
                }
            }
        }
        return $info;
    }

    protected static function loadLegacy($module)
    {
        $file = Xoops::path('www') . '/modules/' . $module . '/xoops_version.php';
        if (!file_exists($file)) {
            return false;
        }
        global $xoopsConfig, $xoopsModule, $xoopsUser;

        XOOPS::registry('application')->getBootstrap()->bootstrap('legacy');
        XOOPS::service('translate')->loadTranslation('global');
        XOOPS::service('translate')->loadTranslation('modinfo', $module);
        include $file;
        $config = $modversion;

        // Normalize version
        if (!is_string($config['version'])) {
            $version = (string) $config['version'];
            if (false === ($pos = strpos($version, "."))) {
                $version .= ".0.0";
            } else {
                $trailing = substr($version, $pos + 2);
                $version = substr($version, 0, $pos + 2) . "." . (empty($trailing) ? "0" : $trailing);
            }
            $config['version'] = $version;
        }

        // Normalize logo
        if (!empty($config['image'])) {
            $config['logo'] = "www/modules/{$module}/" . $config['image'];
            unset($config['image']);
        } else {
            $config['logo'] = "img/images/module.png";
        }

        // Normalize readme file
        if (!empty($config['help'])) {
            $config['readme'] = $config['help'];
            unset($config['help']);
        }

        // Normalize database configs
        if (!empty($config['sqlfile'])) {
            $config['extensions']['database']['sqlfile']['mysql'] = "www/modules/{$module}/" . $config['sqlfile']['mysql'];
            unset($config['sqlfile']);
        }
        if (!empty($config['tables'])) {
            $config['extensions']['database']['tables'] = $config['tables'];
            unset($config['tables']);
        }
        // Skip templates
        if (!empty($config['templates'])) {
            unset($config['templates']);
        }
        // Normalize block configs
        if (!empty($config['blocks'])) {
            foreach ($config['blocks'] as $key => &$block) {
                $block['title'] = $block['name'];
                unset($block['name']);
            }
            $config['extensions']['block'] = $config['blocks'];
            unset($config['blocks']);
        }
        // Normalize preference configs
        if (!empty($config['config'])) {
            foreach ($config['config'] as $key => &$conf) {
                //$conf['description'] = isset($conf['description']) ? $conf['description'] : (isset($conf['desc']) ? $conf['desc'] : "");
                if (isset($conf['desc'])) {
                    if (!isset($conf['description'])) {
                        $conf['description'] = $conf['desc'];
                    }
                    unset($conf['desc']);
                }
                if (isset($conf['formtype'])) {
                    if (!isset($conf['edit'])) {
                        switch ($conf['formtype']) {
                            case "select_multi":
                                $conf['edit'] = "Multiselect";
                                break;
                            case "textbox":
                                $conf['edit'] = "Text";
                                break;
                            default:
                                $conf['edit'] = $conf['formtype'];
                                break;
                        }
                    }
                    unset($conf['formtype']);
                }
                if (isset($conf['valuetype'])) {
                    if (!isset($conf['filter'])) {
                        switch ($conf['valuetype']) {
                            case "text":
                                $conf['filter'] = "string";
                                break;
                            case "textarea":
                                $conf['filter'] = "special_chars";
                                break;
                            default:
                                $conf['filter'] = "";
                                break;
                        }
                    }
                    unset($conf['valuetype']);
                }
            }
            $config['extensions']['config']['items'] = $config['config'];
            unset($config['config']);
        }
        if (!empty($config['configcat'])) {
            foreach ($config['configcat'] as $key => $cat) {
                $cat["key"] = $cat["nameid"];
                unset($cat["nameid"]);
                $cat['description'] = isset($cat['description']) ? $cat['description'] : (isset($cat['desc']) ? $cat['desc'] : "");
                $config['extensions']['config']['categories'][] = $cat;
            }
            unset($config['configcat']);
        }
        // Normalize comment configs
        if (!empty($config['comments'])) {
            $config['extensions']['comment'] = $config['comments'];
            unset($config['comments']);
        }
        // Normalize notification configs
        if (!empty($config['notification'])) {
            $config['extensions']['notification'] = $config['notification'];
            unset($config['notification']);
        }
        // Normalize admin menu
        if (!empty($config['adminmenu'])) {
            $config['extensions']['navigation']['admin'] = array();
            $adminmenu = array();
            include XOOPS::path("www") . "/modules/{$module}/{$config['adminmenu']}";
            foreach ($adminmenu as $key => $page) {
                /*
                $link = substr($page['link'], 6);
                if (false !== ($pos = strpos($link, "?"))) {
                    $controller = rtrim(substr($link, 0, $pos), ".php");
                    $params = parse_str(substr($link, $pos + 1));
                } else {
                    $controller = rtrim($link, ".php");
                    $params = array();
                }
                $config['extensions']['navigation']['admin'][md5($page['link'])] = array(
                    "label"         => $page['title'],
                    "route"         => "legacy",
                    "controller"    => $controller,
                    "action"        => "admin",
                    "params"        => $params,
                );
                */
                /**/
                $config['extensions']['navigation']['admin'][md5($page['link'])] = array(
                    "label"         => $page['title'],
                    "uri"           => $page['link'],
                );
                /**/
            }
            unset($config['adminmenu']);
        }
        // Normalize front navigation
        if (!empty($config['sub'])) {
            $config['extensions']['navigation']['front'] = array();
            foreach ($config['sub'] as $key => $page) {
                /*
                if (false !== ($pos = strpos($page['url'], "?"))) {
                    $controller = rtrim(substr($page['url'], 0, $pos), ".php");
                    $params = parse_str(substr($page['url'], $pos + 1));
                } else {
                    $controller = rtrim($page['url'], ".php");
                    $params = array();
                }
                $config['extensions']['navigation']['front'][md5($page['url'])] = array(
                    'label'         => $page['name'],
                    "route"         => "legacy",
                    "controller"    => $controller,
                    "params"        => $params,
                );
                */
                $config['extensions']['navigation']['front'][md5($page['url'])] = array(
                    'label'         => $page['name'],
                    "uri"           => $page['url'],
                );
            }
            unset($config['sub']);
        }

        return $config;
    }
}