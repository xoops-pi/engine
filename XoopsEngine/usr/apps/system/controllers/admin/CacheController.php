<?php
/**
 * System admin cache controller
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
 * @category        Xoops_Module
 * @package         System
 * @version         $Id$
 */

class System_CacheController extends Xoops_Zend_Controller_Action_Admin
{
    public function indexAction()
    {
        $this->setTemplate("system/admin/cache.html");
        $module = $this->getRequest()->getModuleName();
        $modules = XOOPS::service("registry")->module->read();

        XOOPS::service("translate")->loadTranslation("modinfo", $module);
        $model = XOOPS::getModel('cache');
        $select = $model->select()
                        ->where('controller = ?', "")
                        ->where('action = ?', "");
        $cacheList = $model->fetchAll($select);
        //$cacheList = $model->fetchAll($select)->toArray();

        $select = $model->select()
                        ->from($model, array("module", "COUNT(*) AS count"))
                        ->group("module");
        $countList = $model->fetchAll($select);
        //$countList = $model->fetchAll($select)->toArray();

        $caches = array();
        $caches["default"] = array("module" => "default", "title" => XOOPS::_("Global"));
        foreach ($cacheList as $cache) {
            $key = $cache->module;
            //if (empty($modules[$key])) continue;
            $caches[$key] = $cache->toArray();
        }
        foreach ($countList as $cache) {
            $key = $cache->module;
            //if (empty($modules[$key])) continue;
            $caches[$key]["count"] = $cache->count;
        }
        foreach (array_keys($modules) as $key) {
            $caches[$key]["title"] = $modules[$key]["name"];
            $caches[$key]["module"] = $key;
            $caches[$key]["count"] = isset($caches[$key]["count"]) ? $caches[$key]["count"] : 0;
            $caches[$key]["href"] = $this->view->url(array("action" => "index", "controller" => "cache", "module" => $key), "admin");
        }

        $title = XOOPS::_("Cache Rule of Modules");
        $action = $this->view->url(array("action" => "save", "controller" => "cache", "module" => $module));
        $form = $this->getFormEdit("cache_form_module", $caches, $title, $action);
        $form->assign($this->template);
        //$form = null;
        //unset($form);

        $cachesGlobal = array();
        if (!empty($caches["default"]["count"])) {
            $select = $model->select();
            $select->where('module = ?', "default");
            $cacheList = $model->fetchAll($select);
            foreach ($cacheList as $cache) {
                $key = "default";
                if (!empty($cache["controller"])) {
                    $key .= "-" . $cache["controller"];
                    if (!empty($cache["action"])) {
                        $key .= "-" . $cache["action"];
                    }
                }
                if (empty($cache["title"])) {
                    $cache["title"] = $key;
                }
                $cachesGlobal[$key] = array(
                    "module"    => "default",
                    "title"     => $cache["title"],
                    "expire"    => $cache["expire"],
                    "level"     => $cache["level"],
                    "key"       => $key
                );
            }
            $cachesGlobal["default"]["module"] = "default";
            $cachesGlobal["default"]["title"] = XOOPS::_("Global Caches");
            ksort($cachesGlobal);

            $title = XOOPS::_("Global cache rules");
            $action = $this->view->url(array("action" => "save", "controller" => "cache", "module" => $module));
            $form_global = $this->getFormEdit("cache_form_global", $cachesGlobal, $title, $action);
            $form_global->assign($this->template);
            //Debug::e($form_global);
        }

        $href = $this->view->url(array("action" => "add", "controller" => "cache", "module" => $module), "admin");
        $title = XOOPS::_("Add global cache rule");
        $this->template->assign('addcache', compact("href", "title"));
    }

    public function addAction()
    {
        $this->setTemplate("system/admin/cache_add.html");
        $module = $this->getRequest()->getModuleName();
        $modules = XOOPS::service("registry")->module->read();

        $title = XOOPS::_("Add Global Cache Rule");
        $action = $this->view->url(array("action" => "create", "controller" => "cache", "module" => $module));
        $form = $this->getFormAdd($title, $action);
        $form->assign($this->template);
    }

    public function saveAction()
    {
        $module = $this->getRequest()->getModuleName();
        XOOPS::service("registry")->cache->flush();
        $expires = $this->getRequest()->getPost("cache_expires");
        $levels = $this->getRequest()->getPost("cache_levels");
        $modules = $this->getRequest()->getPost("cache_modules");
        $model = XOOPS::getModel('cache');
        foreach (array_keys($expires) as $key) {
            $data = array("expire" => $expires[$key], "level" => $levels[$key]);
            $vars = explode("-", $key);
            $where = array();
            $where[] = $model->getAdapter()->quoteInto("module = ?", $modules[$key]);
            $where[] = $model->getAdapter()->quoteInto("controller = ?", empty($vars[1]) ? "" : $vars[1]);
            $where[] = $model->getAdapter()->quoteInto("action = ?", empty($vars[2]) ? "" : $vars[2]);
            $model->update($data, $where);
        }
        $where = array();
        $where[] = $model->getAdapter()->quoteInto("expire < ?", 0);
        $where[] = $model->getAdapter()->quoteInto("custom = ?", 1);
        $model->delete($where);
        $options = array("message" => _SYSTEM_AM_DBUPDATED, "time" => 3);
        $redirect = array("action" => "index");
        $this->redirect($redirect, $options);
    }

    public function createAction()
    {
        //$module = $this->getRequest()->getModuleName();
        $module = "default";
        XOOPS::service("registry")->cache->flush($module);

        $controller = $this->getRequest()->getPost("controller", "");
        $action = $this->getRequest()->getPost("action", "");
        $expire = $this->getRequest()->getPost("expire", 0);
        $level = $this->getRequest()->getPost("level", "");
        $title = $this->getRequest()->getPost("title", "");
        $custom = 1;

        $model = XOOPS::getModel('cache');
        $select = $model->select()
                        ->from($model, "COUNT(*) as count")
                        ->where("module = ?", $module)
                        ->where("controller = ?", $controller)
                        ->where("action = ?", $action);
        $count = $model->fetchRow($select)->count;
        if ($count > 0) {
            $message = XOOPS::_("The cache rule already exists.");
        } else {
            $data = compact("module", "controller", "action", "expire", "level", "title", "custom");
            $model->insert($data);
            $message = XOOPS::_("The cache rule is added successfully.");
        }
        $options = array("message" => $message, "time" => 3);
        $redirect = array("action" => "index");
        $this->redirect($redirect, $options);
    }

    private function getFormAdd($title, $action)
    {
        //global $xoops;
        //include_once XOOPS::path('www') . '/class/xoopsformloader.php';
        Xoops_Legacy::autoload();

        $form = new XoopsThemeForm($title, 'cache_form', $action, 'post', true);
        $form->addElement(new XoopsFormText(XOOPS::_('Controller'), 'controller', 50, 64), true);
        $form->addElement(new XoopsFormText(XOOPS::_('Action'), 'action', 50, 64));
        $form->addElement(new XoopsFormText(XOOPS::_('Title'), 'title', 50, 255));
        $selectExpire = new XoopsFormSelect(XOOPS::_('Cache expire'), "expire", 0);
        $selectExpire->addOptionArray(static::getExpireOptions());
        $form->addElement($selectExpire);
        $selectLevel = new XoopsFormSelect(XOOPS::_('Cache level'), "level");
        $selectLevel->addOptionArray(static::getLevelOptions());
        $form->addElement($selectLevel);
        $form->addElement(new XoopsFormHidden('custom', '1'));
        $form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
        return $form;
    }

    private function getFormEdit($name, $caches, $title, $action)
    {
        //global $xoops;
        //include_once XOOPS::path('www') . '/class/xoopsformloader.php';
        Xoops_Legacy::autoload();

        $form = new XoopsThemeForm($title, $name, $action, 'post', true);
        foreach ($caches as $key => $cache) {
            $ele = new XoopsFormElementTray($cache["title"], ' ');
            $selectExpire = new XoopsFormSelect(XOOPS::_('Cache expire'), "cache_expires[{$key}]", $cache['expire']);
            $selectExpire->addOptionArray(static::getExpireOptions());
            $ele->addElement($selectExpire);
            unset($selectExpire);
            $selectLevel = new XoopsFormSelect(XOOPS::_('Cache level'), "cache_levels[{$key}]", $cache['level']);
            $selectLevel->addOptionArray(static::getLevelOptions());
            $ele->addElement($selectLevel);
            unset($selectLevel);
            $editLink = "<a href=\"" . $cache["href"]. "\" title=\"". $cache["title"] ."\">" . XOOPS::_("Manage") . "</a>";
            $label = new XoopsFormLabel(sprintf(XOOPS::_('Including %d pages'), $cache['count']), $editLink);
            $ele->addElement($label);
            unset($label);
            if (!empty($cache['key'])) {
                $ele->setDescription($cache['key']);
            }
            $form->addElement($ele);
            $form->addElement(new XoopsFormHidden("cache_modules[{$key}]", $cache["module"]));
            unset($ele);
        }
        $form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
        return $form;
    }

    protected static function getExpireOptions()
    {
        return $expire_options = array(
            '-1'        => XOOPS::_('Disable'),
            '0'         => _NOCACHE,
            '30'        => sprintf(_SECONDS, 30),
            '60'        => _MINUTE,
            '300'       => sprintf(_MINUTES, 5),
            '1800'      => sprintf(_MINUTES, 30),
            '3600'      => _HOUR,
            '18000'     => sprintf(_HOURS, 5),
            '86400'     => _DAY,
            '259200'    => sprintf(_DAYS, 3),
            '604800'    => _WEEK,
            '2592000'   => _MONTH
        );
    }

    protected static function getLevelOptions()
    {
        return $level_options = array(
            ""          => XOOPS::_('None'),
            "locale"    => XOOPS::_('Locale'),
            "role"      => XOOPS::_('Role'),
            "group"     => XOOPS::_('Group'),
            "user"      => XOOPS::_('User')
        );
    }
}