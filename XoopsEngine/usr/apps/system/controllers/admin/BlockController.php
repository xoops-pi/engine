<?php
/**
 * System admin block controller
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       The Xoops Engine http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Module
 * @package         System
 * @version         $Id$
 */

class System_BlockController extends Xoops_Zend_Controller_Action_Admin
{
    public function indexAction()
    {
        $this->setTemplate("system/admin/block_list.html");
        $module = $this->getRequest()->getModuleName();
        $modules = XOOPS::service("registry")->modulelist->read("active");
        $dirname = $this->_getParam("dirname", "");

        $moduleList = array();
        $moduleList[""] = array(
            "name"  => XOOPS::_("Custom Blocks"),
            "url"   => $this->getFrontController()->getRouter()->assemble(
                array(
                    "module"        => $module,
                    "controller"    => "block",
                    "action"        => "index",
                ),
                "admin"
            )
        );
        foreach (array_keys($modules) as $dir) {
            //if ($dir == $dirname) continue;
            $info = Xoops::service('module')->loadInfo($dir);
            // skip if the module does not have blocks
            if (empty($info['extensions']['block'])) {
                continue;
            }
            $moduleList[$dir] = array(
                "name"  => $modules[$dir]["name"],
                "url"   => $this->getFrontController()->getRouter()->assemble(
                    array(
                        "module"        => $module,
                        "controller"    => "block",
                        "action"        => "index",
                        "dirname"       => $dir,
                    ),
                    "admin"
                )
            );
        }

        $model = XOOPS::getModel("block");
        $select = $model->select()
                        ->where("module = ?", $dirname);
        $blocks = $model->fetchAll($select);

        $title = empty($dirname) ? XOOPS::_("Custom Blocks") : sprintf(XOOPS::_("Blocks of Module %s"), $modules[$dirname]["name"]);
        $action = $this->view->url(array("action" => "save", "controller" => "block", "module" => $module));
        $form = $this->getFormList("block_form_list", $blocks, $title, $action);
        $form->addElement(new XoopsFormHidden('dirname', $dirname));
        $form->assign($this->template);

        $this->template->assign("modules", $moduleList);
        $this->template->assign("dirname", $dirname);
    }

    /**
     * for block layout
     * @author xiaohui
     */
    public function layoutAction()
    {
        if ( $this->_request->isPost() ) {
            //Debug::e($_POST);
            exit;
        }
        $this->setTemplate("system/admin/block_layout.html");
        $module = $this->getRequest()->getModuleName();
        $modules = XOOPS::service("registry")->modulelist->read("active");
        $dirname = $this->_getParam("dirname", "system");

        foreach (array_keys($modules) as $dir) {
            //if ($dir == $dirname) continue;
            $info = Xoops::service('module')->loadInfo($dir);
            // skip if the module does not have blocks
            if (empty($info['extensions']['block'])) {
                continue;
            }
            $moduleList[$dir] = array(
                "name"  => $modules[$dir]["name"],
                "url"   => $this->getFrontController()->getRouter()->assemble(
                    array(
                        "module"        => $module,
                        "controller"    => "block",
                        "action"        => "layout"
                    ),
                    "admin"
                )
            );
        }

        $model = XOOPS::getModel("block");
        $select = $model->select()
                        ->where("module = ?", $dirname);
        $blocks = $model->fetchAll($select);

        $this->template->assign(array(
                                    "dirname"   => $dirname,
                                    "modules"   => $moduleList,
                                    "blocks"    => $blocks
                                ));
    }

    /**
     * for block layout
     * @author xiaohui
     */
    public function ajaxAction()
    {
        $this->setTemplate("system/admin/block_ajax.html");
        $module = $this->getRequest()->getModuleName();
        $modules = XOOPS::service("registry")->modulelist->read("active");
        $dirname = $this->_getParam("dirname", "system");

        $model = XOOPS::getModel("block");
        $select = $model->select()
                        ->where("module = ?", $dirname);
        $blocks = $model->fetchAll($select);
        $this->template->assign("blocks",$blocks);
    }

    public function addAction()
    {
        $this->setTemplate("system/admin/block_add.html");
        $module = $this->getRequest()->getModuleName();

        $block = array(
            "name"          => "",
            "title"         => "",
            "content"       => "",
            "type"          => "H",
            "cache_expire"  => 0,
            "cache_level"   => ""
        );
        $title = XOOPS::_("Add a new block");
        $action = $this->view->url(array("action" => "create", "controller" => "block", "module" => $module));
        $name = "block_form_edit";
        $form = $this->getFormCustom($name, $block, $title, $action);
        $form->assign($this->template);
    }

    public function editAction()
    {
        $this->setTemplate("system/admin/block_edit.html");
        $module = $this->getRequest()->getModuleName();
        $dirname = $this->_getParam("dirname");
        $id = $this->_getParam("id");

        $model = XOOPS::getModel("block");
        $select = $model->select()
                        ->where("id = ?", $id);
        $block = $model->fetchRow($select)->toArray();
        $block["content"] = $model->buildBlock($block, "E");
        //Debug::e($content);
        //$block["content"] = $content["content"];
        //$block = $model->fetchRow($select)->toArray();
        $title = XOOPS::_("Block Edit");
        $action = $this->view->url(array("action" => "create", "controller" => "block", "module" => $module));
        $name = "block_form_edit";
        if (empty($block["type"])) {
            $form = $this->getFormBlock($name, $block, $title, $action);
        } else {
            $form = $this->getFormCustom($name, $block, $title, $action);
        }
        $form->addElement(new XoopsFormHidden('dirname', $dirname));
        $form->addElement(new XoopsFormHidden('id', $id));
        $form->assign($this->template);
    }

    // distribute a block to pages
    public function distributeAction()
    {
        $this->setTemplate("system/admin/block_distribute.html");
        $module = $this->getRequest()->getModuleName();
        $block = $this->_getParam("block", 0);
        $blockDirname = $this->_getParam("bm", "");
        $pageModule = $this->_getParam("pm", "");
        $blockModule = null;
        if ("dir-" == substr($blockDirname, 0, 4)) {
            $blockModule = substr($blockDirname, 4);
        }

        $model = XOOPS::getModel("block");
        if (!empty($block)) {
            $select = $model->select()->where("id = ?", $block);
            if (!$blk = $model->fetchRow($select)) {
                $message = XOOPS::_("The block is not found.");
                $options = array("message" => $message, "time" => 3);
                $redirect = array("action" => "distribute");
                $this->redirect($redirect, $options);
                return;
            }
            $blockModule = $blk->module;
            $blockDirname = "dir-" . $blk->module;
        }
        $blockModules = array();
        $blockModules["-"] = array(
            "name"  => XOOPS::_("Select module"),
            "key"   => "",
            "url"   => $this->view->url(
                array(
                    "module"        => $module,
                    "controller"    => "block",
                    "action"        => "distribute",
                ),
                "admin"
            )
        );
        $select = $model->select()->from($model, "module")->distinct();
        $moduleList = $model->fetchAll($select)->toArray();
        foreach ($moduleList as $mod) {
            $blockModules[$mod["module"]] = 1;
        }
        $modules = XOOPS::service("registry")->modulelist->read("active");
        $modules[""] = array(
            "name"  => XOOPS::_("Custom blocks"),
        );
        foreach (array_keys($modules) as $dir) {
            if (!isset($blockModules[$dir])) continue;
            $blockModules[$dir] = array(
                "name"  => $modules[$dir]["name"],
                "key"   => "dir-" . $dir,
                "url"   => $this->view->url(
                    array(
                        "module"        => $module,
                        "controller"    => "block",
                        "action"        => "distribute",
                        "bm"            => "dir-" . $dir,
                    ),
                    "admin"
                )
            );
        }

        $blocks = array();
        if (isset($blockModule)) {
            $select = $model->select()->where("module = ?", $blockModule);
            $blocks = $model->fetchAll($select)->toArray();
        }
        array_unshift($blocks, array("id" => 0, "title" => XOOPS::_("Select block")));
        foreach ($blocks as $key => &$blk) {
            $blk["url"] = $this->view->url(
                array(
                    "module"        => $module,
                    "controller"    => "block",
                    "action"        => "distribute",
                    "bm"            => "dir-" . $blockModule,
                    "block"         => $blk["id"],
                ),
                "admin"
            );
        }

        $pageModules = array();
        if (!empty($block)) {
            $moduleDefault = array(
                ""  => array(
                    "name"  => XOOPS::_("All pages")
                ),
                "default"   => array(
                    "name"  => XOOPS::_("System Application")
                ),
            );
            unset($modules[""]);
            $modules = array_merge($moduleDefault, $modules);
            foreach (array_keys($modules) as $dir) {
                $pageModules[$dir] = array(
                    "name"  => $modules[$dir]["name"],
                    "key"   => $dir,
                    "url"   => $this->view->url(
                        array(
                            "module"        => $module,
                            "controller"    => "block",
                            "action"        => "distribute",
                            "bm"            => $blockModule,
                            "block"         => $block,
                            "pm"            => $dir,
                        ),
                        "admin"
                    )
                );
            }
            //Debug::e($pageModules);
            /*
            */
            $pages = array("new" => array(), "exist" => array());
            $modelPage = XOOPS::getModel("page");
            $modelLink = XOOPS::getModel("page_block");
            if (!empty($pageModule)) {
                $select = $modelPage->select()
                                ->where("section = ?", "front")
                                ->where("module = ?", $pageModule)
                                ->order(array("controller", "action"));
                $pagesRaw = $modelPage->fetchAll($select)->toArray();
                $pageIds = array();
                foreach ($pagesRaw as $key => &$page) {
                    $pageIds[$page['id']] = $key;
                    $page['position'] = -1;
                    $page['order'] = 5;
                    $page["description"] = $pageModule;
                    if (empty($page['controller'])) {
                        continue;
                    }
                    $page["description"] .= "-" . $page['controller'];
                    if (empty($page['action'])) {
                        continue;
                    }
                    $page["description"] .= "-" . $page['action'];
                }
                if (!empty($pagesRaw)) {
                    $select = $modelLink->select()
                                    ->where("block = ?", $block)
                                    ->where("page IN (?)", array_keys($pageIds));
                    $pageList = $modelLink->fetchAll($select)->toArray();
                    foreach ($pageList as $key => &$page) {
                        $page = array_merge($pagesRaw[$pageIds[$page['page']]], $page);
                    }
                    $pages['new'] = $pagesRaw;
                    $pages['exist'] = $pageList;
                }
            } else {
                $select = $modelLink->select()
                                ->where("block = ?", $block);
                $pageList = $modelLink->fetchAll($select);
                $count = count($pageList);
                $pages = array(
                    "exist"     => array(),
                    "new"       => array(),
                    "global"    => array("position" => -1, "order" => 5),
                );
                if ($count > 200) {
                    $pageList = array(
                        sprintf(XOOPS::_("There are too many items (over %d) to display on one single page. Please pick up one module to check details."), $count)
                    );
                } else {
                    $pageList = $pageList->toArray();
                    $pageIds = array();
                    foreach ($pageList as $key => $page) {
                        if ($page['page'] == 0) {
                            $pages["global"] = $page;
                            unset($pageList[$key]);
                        } else {
                            $pageIds[$page['page']] = $key;
                        }
                    }
                    if (!empty($pageIds)) {
                        $select = $modelPage->select()
                                        ->where("id IN (?)", array_keys($pageIds))
                                        ->order(array("controller", "action"));
                        $pagesRaw = $modelPage->fetchAll($select)->toArray();
                        $pageIds = array();
                        foreach ($pagesRaw as $key => $page) {
                            $pageIds[$page['id']] = $key;
                        }
                        foreach ($pageList as $key => &$page) {
                            $page = array_merge($pagesRaw[$pageIds[$page['page']]], $page);
                        }
                    }
                }
                $pages['exist'] = $pageList;
            }
            //Debug::e($pages);

            $title = XOOPS::_("Select pages");
            $action = $this->view->url(array("action" => "distributeblock", "controller" => "block", "module" => $module));
            $name = "block_form_distribute";
            $form = $this->getFormDistribute($name, $pages, $title, $action);
            $form->addElement(new XoopsFormHidden('block', $block));
            $form->addElement(new XoopsFormHidden('pm', $pageModule));
            $form->assign($this->template);
        }

        $pageTitle = XOOPS::_("Distribute a block on pages");
        $this->template->assign("title", $pageTitle);
        $this->template->assign("blockModule", $blockDirname);
        $this->template->assign("block", $block);
        $this->template->assign("blockModules", $blockModules);
        $this->template->assign("blocks", $blocks);
        $this->template->assign("pageModule", $pageModule);
        $this->template->assign("pageModules", $pageModules);
    }

    // Distribute a block on pages
    public function distributeblockAction()
    {
        $module = $this->getRequest()->getModuleName();
        //$pages = $this->getRequest()->getPost("pages");
        $positions_exist = $this->_getParam("positions_exist", array());
        $orders_exist = $this->_getParam("orders_exist", array());
        $positions = $this->_getParam("positions", array());
        $orders = $this->_getParam("orders", array());
        //$remove_all = $this->_getParam("remove_all", 0);
        $position_all = $this->_getParam("position_all");
        $order_all = $this->_getParam("order_all", 5);
        $block = $this->_getParam("block", 0);
        //$dirname = $this->_getParam("dirname", "");
        $pageModule = $this->_getParam("pm", "");

        $model = XOOPS::getModel('block');
        $select = $model->select()
                        ->where("id = ?", $block);
        if (!$blk = $model->fetchRow($select)) {
            $message = XOOPS::_("The block is not found.");
            $options = array("message" => $message, "time" => 3);
            $redirect = array("action" => "distribute");
            $this->redirect($redirect, $options);
            return;
        }

        $modelPage = XOOPS::getModel('page');
        $modelLink = XOOPS::getModel('page_block');
        $blockPositions = static::getBlockPositions();
        foreach (array_keys($positions_exist) as $key) {
            if (empty($key)) continue;
            if (!isset($blockPositions[$positions_exist[$key]]) || $positions_exist[$key] < 0) {
                $model->delete(array("id = ?" => $key));
                continue;
            }
            $data = array(
                "position"  => $positions_exist[$key],
                "order"     => $orders_exist[$key]
            );
            $modelLink->update($data, array("id = ?" => $key));
        }
        foreach (array_keys($positions) as $key) {
            if (!isset($blockPositions[$positions[$key]]) || $positions[$key] < 0) {
                continue;
            }
            $data = array(
                "block"     => $block,
                "position"  => $positions[$key],
                "page"      => $key,
                "order"     => $orders[$key]
            );
            $modelLink->insert($data);
        }
        /*
        if (!empty($remove_all)) {
            $modelLink->delete(array("block = ?", $block));
        }
        */
        $globalUpdate = false;
        if (empty($pageModule) && !is_null($position_all)) {
            $select = $modelLink->select()->where("block = ?", $block)->where("page = ?", 0);
            $rows = $select->query()->fetchAll();
            if (count($rows) > 1) {
                $globalUpdate = $modelLink->delete(array("block = ?" => $block, "page = ?" => 0));
            }
            if (!isset($blockPositions[$position_all]) || $position_all < 0) {
                $globalUpdate = $modelLink->delete(array("block = ?" => $block, "page = ?" => 0));
            } else {
                $data = array(
                    "page"      => 0,
                    "block"     => $block,
                    "position"  => $position_all,
                    "order"     => $order_all
                );
                if (count($rows) == 0) {
                    $modelLink->insert($data);
                    $globalUpdate = true;
                } else {
                    $item = array_pop($rows);
                    if ($data["position"] != $item["position"] || $data["order"] != $item["order"]) {
                        $modelLink->update($data, array("id = ?" => $item["id"]));
                        $globalUpdate = true;
                    }
                }
            }
        }

        if (!empty($pageModule)) {
            XOOPS::service("registry")->page->flush($pageModule);
        } elseif (!empty($globalUpdate)) {
            XOOPS::service("registry")->page->flush();
        }
        $options = array("message" => _SYSTEM_AM_DBUPDATED, "time" => 3);
        $redirect = array("action" => "distribute", "block" => $block);
        $this->redirect($redirect, $options);
    }

    public function saveAction()
    {
        $module = $this->getRequest()->getModuleName();
        $expires = $this->getRequest()->getPost("cache_expires");
        $levels = $this->getRequest()->getPost("cache_levels");
        $dirname = $this->getRequest()->getPost("dirname");
        $model = XOOPS::getModel('block');
        foreach (array_keys($expires) as $key) {
            $data = array("cache_expire" => $expires[$key], "cache_level" => $levels[$key]);
            $where = array("id = ?" => $key);
            $model->update($data, $where);
        }
        XOOPS::service("registry")->block->flush($dirname);
        $options = array("message" => _SYSTEM_AM_DBUPDATED, "time" => 3);
        $redirect = array("action" => "index", "dirname" => $dirname);
        $this->redirect($redirect, $options);
    }

    public function createAction()
    {
        $dirname = $this->getRequest()->getPost("dirname");

        $name           = $this->getRequest()->getPost("name");
        $title          = $this->getRequest()->getPost("title");
        $type           = $this->getRequest()->getPost("type");
        $content        = $this->getRequest()->getPost("content");
        $cache_expire   = $this->getRequest()->getPost("cache_expire");
        $cache_level    = $this->getRequest()->getPost("cache_level");
        $id             = $this->getRequest()->getPost("id", 0);

        $data = compact("name", "title", "type", "content", "cache_expire");
        if (!empty($type)) {
            $data["cache_level"] = $cache_level;
        }
        $model = XOOPS::getModel('block');
        // Create a new block
        if (empty($id)) {
            $uniqueViolated = false;
            if (!empty($name)) {
                $select = $model->select()
                                ->from($model, "COUNT(*) as count")
                                ->where("name = ?", $name);
                $count = $model->fetchRow($select)->count;
                if ($count > 0) {
                    $message = sprintf(XOOPS::_("The block name '%s' already exists."), $name);
                    $uniqueViolated = true;
                }
            }
            if (!$uniqueViolated) {
                $id = $model->insert($data);
                $message = XOOPS::_("The block is added successfully.");

                $modelRule = XOOPS::getModel("acl_rule");
                $roles = array("guest", "member");
                foreach ($roles as $role) {
                    $data = array(
                        "resource"  => $id,
                        "section"   => "block",
                        "role"      => $role,
                        "deny"      => 0
                    );
                    $modelRule->insert($data);
                }

            }
        // Update a block
        } else {
            $uniqueViolated = false;
            if (!empty($name)) {
                $select = $model->select()
                                ->from($model, "COUNT(*) as count")
                                ->where("name = ?", $name)
                                ->where("id <> ?", $id);
                $count = $model->fetchRow($select)->count;
                if ($count > 0) {
                    $message = sprintf(XOOPS::_("The block name '%s' conflicts with another block."), $name);
                    $uniqueViolated = true;
                }
            }
            if (!$uniqueViolated) {
                $where = array("id = ?" => $id);
                $model->update($data, $where);
                $message = XOOPS::_("The block is updated successfully.");
            }
        }
        XOOPS::service("registry")->block->flush($dirname);

        $options = array("message" => $message, "time" => 3);
        $redirect = array("action" => "index", "dirname" => $dirname);
        $this->redirect($redirect, $options);
    }

    public function opAction()
    {
        $id = $this->_getParam("id", 0);
        $op = $this->_getParam("op", "");

        $model = XOOPS::getModel('block');
        $select = $model->select()
                        ->where("id = ?", $id);
        if (!$block = $model->fetchRow($select)) {
            $message = XOOPS::_("The block is not found.");
        } elseif (empty($block->type) || !in_array($op, array("delete", "activate", "deactivate"))) {
            $message = XOOPS::_("Invalid operation.");
        } else {
            switch ($op) {
            case "delete":
                $model->delete(array("id = ?" => $id));
                break;
            case "activate":
                $model->update(array("active" => 1), array("id = ?" => $id));
                break;
            case "deactivate":
                $model->update(array("active" => 0), array("id = ?" => $id));
                break;
            }
            XOOPS::service("registry")->block->flush($block->module);
            $message = XOOPS::_("The block is operated successfully.");
        }

        $options = array("message" => $message, "time" => 3);
        $redirect = array("action" => "index", "dirname" => $block->module);
        $this->redirect($redirect, $options);
    }

    private function getFormBlock($name, $block, $title, $action)
    {
        //include_once XOOPS::path('www') . '/class/xoopsformloader.php';
        Xoops_Legacy::autoload();

        $form = new XoopsThemeForm($title, $name, $action, 'post', true);
        $name = new XoopsFormText(XOOPS::_('Name'), 'name', 50, 64, $block["name"]);
        $name->setDescription(XOOPS::_('Should be empty or unique'));
        $form->addElement($name);
        $form->addElement(new XoopsFormText(XOOPS::_('Title'), 'title', 50, 255, $block["title"]));
        $selectExpire = new XoopsFormSelect(XOOPS::_('Cache expire'), "cache_expire", $block["cache_expire"]);
        $selectExpire->addOptionArray(static::getExpireOptions());
        $form->addElement($selectExpire);
        /*
        $selectLevel = new XoopsFormSelect(XOOPS::_('Cache level'), "cache_level", $block["cache_level"]);
        $selectLevel->addOptionArray(static::getLevelOptions());
        $form->addElement($selectLevel);
        */
        if (!empty($block["content"])) {
            $form->addElement(new XoopsFormLabel(XOOPS::_('Options'), $block["content"]));
        }
        /*
        if (!empty($block["edit_func"])) {
            XOOPS::service('translate')->loadTranslation('blocks', $block['module']);
            $info = XOOPS::service('registry')->module->read($block['module']);
            include_once XOOPS::path($info['path'] . '/' . $block['module'] . '/blocks/' . $block['func_file']);
            $options = explode('|', $block['options']);
            $options_form = $block["edit_func"]($options);
            $form->addElement(new XoopsFormLabel(_AM_OPTIONS, $options_form));
        }
        */

        $form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
        return $form;
    }

    private function getFormCustom($name, $block, $title, $action)
    {
        //include_once XOOPS::path('www') . '/class/xoopsformloader.php';
        Xoops_Legacy::autoload();

        $form = new XoopsThemeForm($title, $name, $action, 'post', true);
        $name = new XoopsFormText(XOOPS::_('Name'), 'name', 50, 64, $block["name"]);
        $name->setDescription(XOOPS::_('Should be empty or unique'));
        $form->addElement($name);
        $form->addElement(new XoopsFormText(XOOPS::_('Title'), 'title', 50, 255, $block["title"]));
        $selectExpire = new XoopsFormSelect(XOOPS::_('Cache expire'), "cache_expire", $block["cache_expire"]);
        $selectExpire->addOptionArray(static::getExpireOptions());
        $form->addElement($selectExpire);
        $selectLevel = new XoopsFormSelect(XOOPS::_('Cache level'), "cache_level", $block["cache_level"]);
        $selectLevel->addOptionArray(static::getLevelOptions());
        $form->addElement($selectLevel);

        $textarea = new XoopsFormDhtmlTextArea(XOOPS::_('Content'), 'content', $block['content'], 15, 70);
        $textarea->setDescription("");
        $textarea->doHtml = true;
        $form->addElement($textarea, true);
        $selectType = new XoopsFormSelect(XOOPS::_('Type'), 'type', $block['type']);
        $selectType->addOptionArray(array(
            'H' => XOOPS::_('HTML'),
            'P' => XOOPS::_('PHP'),
            'S' => XOOPS::_('BB code with smiley'),
            'T' => XOOPS::_('BB code without simley')
        ));
        $form->addElement($selectType);

        $form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
        return $form;
    }

    private function getFormList($name, $blocks, $title, $action)
    {
        //include_once XOOPS::path('www') . '/class/xoopsformloader.php';
        Xoops_Legacy::autoload();
        $module = $this->getRequest()->getModuleName();

        $form = new XoopsThemeForm($title, $name, $action, 'post', true);
        foreach ($blocks as $key => $block) {
            $id = $block["id"];
            $ele = new XoopsFormElementTray($block["title"], ' ');
            $selectExpire = new XoopsFormSelect(XOOPS::_('Cache expire'), "cache_expires[{$id}]", $block['cache_expire']);
            $selectExpire->addOptionArray(static::getExpireOptions());
            $ele->addElement($selectExpire);
            unset($selectExpire);

            if (!empty($block['type'])) {
                $selectLevel = new XoopsFormSelect(XOOPS::_('Cache level'), "cache_levels[{$id}]", $block['cache_level']);
                $selectLevel->addOptionArray(static::getLevelOptions());
                $ele->addElement($selectLevel);
                unset($selectLevel);

                $href = $this->view->url(array(
                                            "op"            => "delete",
                                            "action"        => "op",
                                            "controller"    => "block",
                                            "module"        => $module,
                                            "id"            => $id,
                                            ), "admin");
                $editLink = "<a href=\"" . $href. "\" title=\"". $block["title"] ."\">" . XOOPS::_("Delete") . "</a>";
                $label = new XoopsFormLabel("", $editLink);
                $ele->addElement($label);
                unset($label);

                if ($block["active"]) {
                    list($op, $operation) = array("deactivate", "Deactivate");
                } else {
                    list($op, $operation) = array("activate", "Activate");
                }
                $href = $this->view->url(array(
                                            "op"            => $op,
                                            "action"        => "op",
                                            "controller"    => "block",
                                            "module"        => $module,
                                            "id"            => $id,
                                            ), "admin");
                $editLink = "<a href=\"" . $href. "\" title=\"". $block["title"] ."\">" . XOOPS::_($operation) . "</a>";
                $label = new XoopsFormLabel("", $editLink);
                $ele->addElement($label);
                unset($label);
            }

            $href = $this->view->url(array(
                                        "action"        => "edit",
                                        "controller"    => "block",
                                        "module"        => $module,
                                        "id"            => $id,
                                        ), "admin");
            $editLink = "<a href=\"" . $href. "\" title=\"". $block["title"] ."\">" . XOOPS::_("Manage") . "</a>";
            $label = new XoopsFormLabel("", $editLink);
            $ele->addElement($label);
            unset($label);

            $href = $this->view->url(array(
                                        "action"        => "distribute",
                                        "controller"    => "block",
                                        "module"        => $module,
                                        "block"         => $id,
                                        ), "admin");
            $editLink = "<a href=\"" . $href. "\" title=\"". $block["title"] ."\">" . XOOPS::_("Page") . "</a>";
            $label = new XoopsFormLabel("", $editLink);
            $ele->addElement($label);
            unset($label);

            $ele->setDescription($block['name']);
            $form->addElement($ele);
            unset($ele);
        }
        $form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
        return $form;
    }

    // pages form to distribute a block
    private function getFormDistribute($name, $pages, $title, $action)
    {
        //include_once XOOPS::path('www') . '/class/xoopsformloader.php';
        Xoops_Legacy::autoload();

        $form = new XoopsThemeForm($title, $name, $action, 'post', true);
        $blockPositions = static::getBlockPositions();
        if (!empty($pages['exist'])) {
            $form->insertBreak(XOOPS::_("Update pages"));
        }
        foreach ($pages['exist'] as $key => $page) {
            if (is_string($page)) {
                $form->addElement(new XoopsFormLabel('', $page));
                continue;
            }
            $ele = new XoopsFormElementTray($page['title'], ' ');
            $ele->setDescription($page['description']);
            $selectPosition = new XoopsFormSelect(XOOPS::_('Position'), "positions_exist[" . $page['id'] . "]", $page["position"]);
            $selectPosition->addOptionArray($blockPositions);
            $ele->addElement($selectPosition);
            unset($selectPosition);
            $ele->addElement(new XoopsFormText(XOOPS::_('Order'), "orders_exist[" . $page['id'] . "]", 10, 10, $page["order"]));
            $form->addElement($ele);
            unset($ele);
        }
        if (!empty($pages['new'])) {
            $form->insertBreak(XOOPS::_("Add to pages"));
        }
        foreach ($pages['new'] as $key => $page) {
            if (is_string($page)) {
                $form->addElement(new XoopsFormLabel('', $page));
                continue;
            }
            $ele = new XoopsFormElementTray($page['title'], ' ');
            $ele->setDescription($page['description']);
            $selectPosition = new XoopsFormSelect(XOOPS::_('Position'), "positions[" . $page['id'] . "]", $page["position"]);
            $selectPosition->addOptionArray($blockPositions);
            $ele->addElement($selectPosition);
            unset($selectPosition);
            $ele->addElement(new XoopsFormText(XOOPS::_('Order'), "orders[" . $page['id'] . "]", 10, 10, $page["order"]));
            $form->addElement($ele);
            unset($ele);
        }
        if (!empty($pages['global'])) {
            $form->insertBreak(XOOPS::_("Global setting"));
            //$form->addElement(new XoopsFormRadioYN(XOOPS::_("Remove from all pages"), "remove_all", 0));
            $ele = new XoopsFormElementTray(XOOPS::_("On all pages"), ' ');
            $selectPosition = new XoopsFormSelect(XOOPS::_('Position'), "position_all", $pages['global']['position']);
            $selectPosition->addOptionArray($blockPositions);
            $ele->addElement($selectPosition);
            unset($selectPosition);
            $ele->addElement(new XoopsFormText(XOOPS::_('Order'), "order_all", 10, 10, $pages['global']['order']));
            $form->addElement($ele);
            unset($ele);
        }

        $form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
        $description = XOOPS::_("Set order value: positive - to display corresponding block; 0 or negative - to disable a block");
        $form->setDescription($description);
        return $form;
    }

    protected static function getBlockPositions()
    {
        return array(
            0   => XOOPS::_('Left blocks'),
            1   => XOOPS::_('Right blocks'),
            2   => XOOPS::_('Top left blocks'),
            3   => XOOPS::_('Top center blocks'),
            4   => XOOPS::_('Top right blocks'),
            5   => XOOPS::_('Bottom left blocks'),
            6   => XOOPS::_('Bottom center blocks'),
            7   => XOOPS::_('Bottom right blocks'),
            -1  => XOOPS::_('Do not display'),
        );
    }

    protected static function getExpireOptions()
    {
        return array(
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
        return array(
            ""          => XOOPS::_('None'),
            "locale"    => XOOPS::_('Locale'),
            "role"      => XOOPS::_('Role'),
            "group"     => XOOPS::_('Group'),
            "user"      => XOOPS::_('User')
        );
    }
}