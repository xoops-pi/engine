<?php
/**
 * XOOPS navigation registry
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

class Navigation extends \Kernel\Registry
{
    protected $module;
    protected static $section = "front";
    protected static $route = "default";
    protected static $columns = array(
        "resource",
        "label",
        "pages",
        "uri",
        "visible"
    );
    protected static $mvcColumns = array(
        "route",
        "module",
        "controller",
        "action",
        "params",
    );

    protected function loadDynamic($options)
    {
        $name = $options['name'];
        if (is_callable(array($this, "load{$name}"))) {
            return $this->{"load{$name}"}($options);
        }
        return $this->loadNavigation($options);
    }

    protected function loadNavigation($options)
    {
        $name = $options['name'];
        $locale = $options['locale'];
        $module = $options['module'];
        $this->module = $module;
        $this->role = $options['role'];

        //$columns = array("id", "name", "label", "route", "module", "controller", "action", "uri", "resource", "visible");
        $columns = null;
        $model = \Xoops::getModel("navigation_page");
        $clause = new \Xoops_Zend_Db_Clause("navigation = ?", $name);
        $clause->add("active = ?", 1);
        $configGlobal = $model->enumerate($clause, $columns, false);

        // Translate global admin navigation
        //$domain = $module ?: "system";
        $domain = "system";
        $navigation = $this->translateConfig($configGlobal, $domain, $locale);

        return $navigation;
    }

    protected function loadFront($options)
    {
        $locale = $options['locale'];
        $module = $options['module'];
        $this->module = $module;
        $this->role = $options['role'];

        //$columns = array("id", "name", "label", "route", "module", "controller", "action", "uri", "resource", "visible");
        $columns = null;
        $model = \Xoops::getModel("navigation_page");
        $clause = new \Xoops_Zend_Db_Clause("navigation = ?", "front");
        $clause->add("active = ?", 1);
        $configGlobal = $model->enumerate($clause, $columns, false);
        $clause = new \Xoops_Zend_Db_Clause("navigation = ?", "front-modules");
        $clause->add("active = ?", 1);
        $configModule = $model->enumerate($clause, $columns, false);

        $keyModules = 0;
        foreach ($configGlobal as $key => $data) {
            if ($data["name"] == "modules" && $data["depth"] == 0) {
                $keyModules = $key;
                break;
            }
        }
        // Translate global admin navigation
        $domain = "system";
        $navigationGlobal = $this->translateConfig($configGlobal, $domain, $locale);
        $navigation = array();
        foreach ($navigationGlobal as $key => $data) {
            unset($navigationGlobal[$key]);
            if ($key == $keyModules) {
                break;
            }
            $navigation[] = $data;
        }
        if (!empty($configModule)) {
            foreach ($configModule as $key => $data) {
                $domain = $data["module"];
                $translateData = "navigation";
                \Xoops::service('translate')->loadTranslation($translateData, $domain, $locale);
                $translator = \Xoops::service('translate')->getAdapter();
                $this->translatePage($data, $domain, $translator);

                //$page = $this->translateConfig($data, $domain, $locale);
                $navigation[] = $data;
            }
        }
        if (!empty($navigationGlobal)) {
            $navigation = array_merge($navigation, $navigationGlobal);
        }

        return $navigation;
    }

    protected function loadAdmin($options)
    {
        static::$section = "admin";
        static::$route = "admin";
        $locale = $options['locale'];
        $module = $options['module'];
        $this->module = $module;
        $this->role = $options['role'];

        //$columns = array("id", "name", "label", "route", "module", "controller", "action", "uri", "resource", "visible");
        $columns = null;
        $model = \Xoops::getModel("navigation_page");
        $clause = new \Xoops_Zend_Db_Clause("navigation = ?", "admin");
        $clause->add("active = ?", 1);
        $configGlobal = $model->enumerate($clause, $columns, false);
        $clause = new \Xoops_Zend_Db_Clause("navigation = ?", "admin:" . $module);
        $clause->add("active = ?", 1);
        $configModule = $model->enumerate($clause, $columns, false);

        $keyModules = null;
        $keySpec = null;
        $keyModule = null;
        foreach ($configGlobal as $key => $data) {
            if ($data["depth"] == 0 && $data["name"] == "modules") {
                $keyModules = $key;
            } elseif ($data["depth"] == 0 && $data["name"] == "spec") {
                $keySpec = $key;
            } elseif ($data["depth"] == 0 && $data["name"] == "module") {
                $keyModule = $key;
            }
        }

        // Load module list
        $modules = \Xoops::service('registry')->module->read();
        //if (!empty($configGlobal[$keyModules]["child"])) {
            foreach ($modules as $dirname => $data) {
                if ($dirname == $module) {
                    continue;
                }
                $page = array(
                    "label"     => $data["name"],
                    "module"    => $dirname,
                    "route"     => "admin",
                    "resource"  => http_build_query(array(
                        "section"   => "admin",
                        "resource"  => $dirname
                    ))
                );
                $configGlobal[$keyModules]["child"][] = $page;
            }
        //}
        if (empty($configGlobal[$keyModules]["child"])) {
            unset($configGlobal[$keyModules]);
        }

        // Load module specs
        if (empty($module) || $module == "system" || !isset($modules[$module])) {
            unset($configGlobal[$keySpec]);
        } else {
            $info = \Xoops::service('module')->loadInfo($module);
            // Remove not applicable spec pages
            foreach (array_keys($configGlobal[$keySpec]["child"]) as $key) {
                $extensionName = $configGlobal[$keySpec]["child"][$key]["name"];
                if (!isset($info['extensions'][$extensionName]) && empty($info[$extensionName])) {
                    unset($configGlobal[$keySpec]["child"][$key]);
                } else {
                    // Set resource
                    $configGlobal[$keySpec]["child"][$key]["resource"] = array("section" => "admin", "resource" => $extensionName);
                }
            }
        }

        // Translate global admin navigation
        $domain = "system";
        $navigationGlobal = $this->translateConfig($configGlobal, $domain, $locale);
        $navigation = array();
        foreach ($navigationGlobal as $key => $data) {
            unset($navigationGlobal[$key]);
            if ($key == $keyModule) {
                break;
            }
            $navigation[] = $data;
        }
        if (!empty($configModule)) {
            $domain = $module;
            $navigationModule = $this->translateConfig($configModule, $domain, $locale);
            $navigation = array_merge($navigation, $navigationModule);
        }
        if (!empty($navigationGlobal)) {
            $navigation = array_merge($navigation, $navigationGlobal);
        }

        return $navigation;
    }

    public function read($name = null, $module = null, $role = null, $locale = '')
    {
        $options = compact('name', 'module', 'role', 'locale');
        $data = $this->loadData($options);
        return $data;
    }

    /**
     * Add a module menu
     */
    public function create($name = null, $module = null, $role = null, $locale = '')
    {
        $this->delete($name, $module, $role, $locale);
        $this->read($name, $module, $role, $locale);
        return true;
    }

    /**
     * Remove a module navigation
     */
    public function delete($name = null, $module = null, $role = null, $locale = '')
    {
        //$options = compact('module', 'role', 'locale');
        $options = compact('name', 'module');
        if (!empty($role)) {
            $options["role"] = $role;
        }
        if (!empty($locale)) {
            $options["locale"] = $locale;
        }
        return $this->cache->clean('matchingTag', $this->createTags($options));
    }

    public function flush($module = null, $role = null, $locale = '')
    {
        return $this->delete(null, $module, $role, $locale);
    }

    protected function translateConfig($config, $domain, $locale)
    {
        $translator = null;
        /*
        if (empty($config['translate'])) {
            $config['translate'] = "modinfo";
        }
        */
        $translateData = "navigation";
        \Xoops::service('translate')->loadTranslation($translateData, $domain, $locale);
        $translator = \Xoops::service('translate')->getAdapter();
        //unset($config['translate']);

        foreach ($config as $p => &$page) {
            $this->translatePage($page, $domain, $translator);
        }

        return $config;
    }

    protected function translatePage(&$page, $domain, $translator = null)
    {
        if (isset($page["child"])) {
            $page["pages"] = $page["child"];
            //$page["child"] = null;
        } else {
            $page['pages'] = array();
        }
        $validColumns = static::$columns;
        if (empty($page['uri'])) {
            if (empty($page['module'])) {
                $page['module'] = $this->module;
            }
            if (!isset($page['route'])) {
                $page['route'] = static::$route;
            }
            // set params
            if (!empty($page['params'])) {
                parse_str($page['params'], $params);
                $page['params'] = $params;
            } else {
                $page['params'] = array();
            }
            $validColumns = array_merge($validColumns, static::$mvcColumns);
        } else {
        }

        // Clean up
        foreach (array_keys($page) as $key) {
            if (!in_array($key, $validColumns)) {
                unset($page[$key]);
            }
        }

        // Check permission
        if (!$this->isAllowed($page)) {
            $page['visible'] = 0;
            $page['pages'] = array();
            return;
        }
        $page['resource'] = null;

        // translate label
        if ($translator && !empty($page['label'])) {
            $page['label'] = $translator->translate($page['label']);
        }
        // translate title
        if ($translator && !empty($page['title'])) {
            $page['title'] = $translator->translate($page['title']);
        }
        /*
        // set route
        if (!isset($page['uri']) && !isset($page['route']) && !empty($page['module'])) {
            $page['route'] = 'admin';
        }
        */
        //Debug::e($page["label"] . "-" . $page["module"]);
        if (!empty($page['pages'])) {
            foreach ($page['pages'] as $p => &$data) {
                $this->translatePage($data, $domain, $translator);
            }
        }
    }

    private function isAllowed($page)
    {
        if (!empty($page['resource'])) {
            return $this->isAllowedResource($page['resource']);
        }
        /*
         elseif (!empty($this->rules[$page["id"]])) {
            return $this->isAllowedRule($this->rules[$page["id"]]);
        }
        */
        return true;
    }

    private function isAllowedResource($resource)
    {
        $module = null;
        if (is_array($resource)) {
            $params = $resource;
        } else {
            parse_str($resource, $params);
        }
        $section = empty($params["section"]) ? static::$section : $params["section"];
        $resource = $params["resource"];
        if (!empty($params["item"])) {
            $resource = array($resource, $params["item"]);
        }
        $privilege = empty($params["privilege"]) ? null : $params["privilege"];
        $module = empty($params["module"]) ? $this->module : $params["module"];

        $acl = new \Xoops_Acl($section);
        $acl->setModule($module);
        $result = $acl->hasAccess($resource, $privilege);
        return $result;
    }
}