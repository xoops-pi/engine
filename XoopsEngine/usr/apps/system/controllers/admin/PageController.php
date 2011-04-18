<?php
/**
 * System admin page controller
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

class System_PageController extends Xoops_Zend_Controller_Action_Admin
{
    // page list
    public function indexAction()
    {
        $this->setTemplate("system/admin/page_list.html");
        $module = $this->getRequest()->getModuleName();
        $section = $this->_getParam("section", "front");
        $dirname = $this->_getParam("dirname", "default");

        $moduleList = $this->getModuleListOfPage();
        foreach ($moduleList as $dir =>& $item) {
            $item = array(
                'name'  => $item,
                'url'   => $this->view->url(
                    array(
                        "module"        => "system",
                        "controller"    => "page",
                        "action"        => "index",
                        "dirname"       => $dir,
                        "section"       => $section,
                    ),
                    "admin"
                )
            );
        }

        $modelPage = XOOPS::getModel("page");
        $select = $modelPage->select()->distinct()->from($modelPage, array("section"));
        $rowset = $modelPage->fetchAll($select);
        $sectionList = array(
            "front" => XOOPS::_("Front pages"),
            "admin" => XOOPS::_("Admin pages"),
            "feed"  => XOOPS::_("Feed pages"),
        );
        $sections = array();
        foreach ($rowset as $row) {
            $sections[$row->section] = array(
                "name"  => isset($sectionList[$row->section]) ? $sectionList[$row->section] : $row->section,
                "url"   => $this->view->url(
                    array(
                        "module"        => $module,
                        "controller"    => "page",
                        "action"        => "index",
                        "section"       => $row->section,
                    ),
                    "admin"
                )
            );
        }

        $select = $modelPage->select()
                        ->where("section = ?", $section)
                        ->where("module = ?", $dirname)
                        ->order(array("controller", "action"));
        $pages = $modelPage->fetchAll($select)->toArray();
        foreach ($pages as $id => &$page) {
            $page["description"] = $dirname;
            if (empty($page['controller'])) {
                continue;
            }
            $page["description"] .= "-" . $page['controller'];
            if (empty($page['action'])) {
                continue;
            }
            $page["description"] .= "-" . $page['action'];
        }

        $title = sprintf(XOOPS::_("Pages of module %s"), $moduleList[$dirname]["name"]);
        $action = $this->view->url(array("action" => "save", "controller" => "page", "module" => $module));
        $form = $this->getFormList("page_form_list", $pages, $title, $action, $section);
        $form->addElement(new XoopsFormHidden('section', $section));
        $form->addElement(new XoopsFormHidden('dirname', $dirname));
        $form->assign($this->template);

        $this->template->assign("sections", $sections);
        $this->template->assign("section", $section);
        $this->template->assign("modules", $moduleList);
        $this->template->assign("dirname", $dirname);
    }

    // add a new page
    public function addAction()
    {
        $this->setTemplate("system/admin/page_add.html");
        $module = $this->getRequest()->getModuleName();
        $section = $this->_getParam("section", "front");
        $dirname = $this->_getParam("dirname", "");

        $page = array(
            "title"         => "",
            "controller"    => "",
            "action"        => "",
            "block"         => 0,
            "cache_expire"  => 0,
            "cache_level"   => "",
        );
        $title = XOOPS::_("Add a new page");
        $action = $this->view->url(array("action" => "create", "controller" => "page", "module" => $module));
        $name = "page_form_edit";
        $form = $this->getFormPage($name, $page, $title, $action);
        $form->addElement(new XoopsFormHidden('section', $section));
        $form->addElement(new XoopsFormHidden('dirname', $dirname));
        $form->assign($this->template);
    }

    // edit a page
    public function editAction()
    {
        $this->setTemplate("system/admin/page_edit.html");
        $module = $this->getRequest()->getModuleName();
        $id = $this->_getParam("id");

        $model = XOOPS::getModel("page");
        $select = $model->select()
                        ->where("id = ?", $id);
        $page = $model->fetchRow($select)->toArray();
        $dirname = $page->dirname;
        $title = XOOPS::_("Page Edit");
        $action = $this->view->url(array("action" => "create", "controller" => "page", "module" => $module));
        $name = "page_form_edit";
        $form = $this->getFormPage($name, $page, $title, $action);
        //$form->addElement(new XoopsFormHidden('dirname', $dirname));
        $form->addElement(new XoopsFormHidden('id', $id));
        $form->assign($this->template);
    }

    // manage blocks on a page
    public function blockAction()
    {
        $this->setTemplate("system/admin/page_block.html");
        $module = $this->getRequest()->getModuleName();
        $id = $this->_getParam("page", 0);

        if ($id > 0) {
            $model = XOOPS::getModel('page');
            $select = $model->select()->where("id = ?", $id);
            if (!$page = $model->fetchRow($select)) {
                $message = XOOPS::_("The page is not found.");
                $options = array("message" => $message, "time" => 3);
                $redirect = array("action" => "index");
                $this->redirect($redirect, $options);
                return;
            }
            if (!$page->block) {
                $message = XOOPS::_("The page inherits blocks from parent, no need to set blocks explicitly.");
                $options = array("message" => $message, "time" => 3);
                $redirect = array("action" => "index", "section" => $page->section, "dirname" => $page->module);
                $this->redirect($redirect, $options);
                return;
            }
            $page = $page->toArray();
        } else {
            $page = array(
                "id" => 0,
                "title" => XOOPS::_("Global Blocks"),
            );
        }

        $modelLink = XOOPS::getModel('page_block');
        // Fetch blocks on current page
        $clause = new Xoops_Zend_Db_Clause("page = ?", $page["id"]);
        // Fetch global blocks
        $clause->add("page = ?", 0, "OR");
        $clause->order(array("position", "order"));
        $itemList = $modelLink->get($clause);
        $blocks = array();
        $blockIds = array();
        $blocksGlobal = array();
        foreach ($itemList as $item) {
            // Skip invalid ordered blocks
            if ($item["order"] < 0 && $item["page"] > 0) {
                // Record disables for global blocks
                $blocksGlobal[$item["position"]][(-1 * $item["order"])] = 1;
                continue;
            }
            $blockIds[$item['block']] = 1;
            $blocks[$item["position"]][] = $item;
        }
        //Debug::e($blocks);
        if (!empty($blockIds)) {
            $modelBlock = XOOPS::getModel('block');
            $select = $modelBlock->select()->where("id IN (?)", array_keys($blockIds));
            $blockList = $select->query()->fetchAll();
            $blockIndex = array();
            foreach ($blockList as $key => $block) {
                $blockIndex[$block["id"]] = $block;
            }
            foreach ($blocks as $position => &$items) {
                foreach ($items as $key => &$item) {
                    if (!isset($blockIndex[$item['block']])) {
                        unset($items[$key]);
                        continue;
                    }
                    $item['title'] = $blockIndex[$item['block']]['module'] . '-' . $blockIndex[$item['block']]['title'];
                    // If it is a global block
                    if ($item["page"] == 0) {
                        // Not disable
                        $item['global'] = false;
                        // Disabled
                        if (isset($blocksGlobal[$item["position"]][$item["id"]])) {
                            $item['global'] = true;
                        }
                    }
                }
            }
        }

        $title = sprintf(XOOPS::_("Set blocks for '%s'"), $page["title"]);
        $action = $this->view->url(array("action" => "saveblock", "controller" => "page", "module" => $module));
        $name = "page_form_blocks";
        $form = $this->getFormBlocks($name, $blocks, $title, $action, $id);
        $form->addElement(new XoopsFormHidden('page', $id));
        $form->assign($this->template);
    }

    // Insert blocks to a page
    public function insertAction()
    {
        $this->setTemplate("system/admin/page_insert.html");
        $module = $this->getRequest()->getModuleName();
        $page = $this->_getParam("page", 0);

        $model = XOOPS::getModel('page');
        $select = $model->select()
                        ->where("id = ?", $page);
        if (!$page = $model->fetchRow($select)) {
            $message = XOOPS::_("The page is not found.");
            $options = array("message" => $message, "time" => 3);
            $redirect = array("action" => "index");
            $this->redirect($redirect, $options);
            return;
        }

        $dirname = $this->_getParam("dirname", "");
        $position = $this->_getParam("position", 0);

        $moduleList = $this->getModuleListOfBlock();
        foreach ($moduleList as $dir =>& $item) {
            $item = array(
                'name'  => $item,
                'url'   => $this->getFrontController()->getRouter()->assemble(
                    array(
                        "module"        => $module,
                        "controller"    => "page",
                        "action"        => "insert",
                        "dirname"       => $dir,
                        "page"          => $page->id,
                        "position"      => $position
                    ),
                    "admin"
                )
            );
        }

        $model = XOOPS::getModel("block");
        $select = $model->select();
        if ('-' === $dirname) {
            $select->order("module ASC")->order("id ASC");
        } else {
            $select->where("module = ?", $dirname)->order("id ASC");
        }
        $blocks = $model->fetchAll($select)->toArray();

        $title = sprintf(XOOPS::_("Add blocks to page %s"), $page->title);
        $action = $this->view->url(array("action" => "insertblock", "controller" => "page", "module" => $module));
        $form = $this->getFormInsert("page_form_insert", $blocks, $title, $action);
        $form->addElement(new XoopsFormHidden('page', $page->id));
        $form->addElement(new XoopsFormHidden('position', $position));
        $form->assign($this->template);

        $this->template->assign("modules", $moduleList);
        $this->template->assign("dirname", $dirname);
    }

    // Save a module's page information into database
    public function saveAction()
    {
        $module = $this->getRequest()->getModuleName();
        $expires = $this->getRequest()->getPost("cache_expires");
        $levels = $this->getRequest()->getPost("cache_levels");
        $blocks = $this->getRequest()->getPost("blocks");
        $section = $this->getRequest()->getPost("section");
        $dirname = $this->getRequest()->getPost("dirname");
        $model = XOOPS::getModel('page');
        foreach (array_keys($expires) as $key) {
            $data = array(
                "block"         => $blocks[$key],
                "cache_expire"  => $expires[$key],
                "cache_level"   => $levels[$key]
            );
            $where = array("id = ?" => $key);
            $model->update($data, $where);
        }
        XOOPS::service("registry")->page->flush($dirname);
        $options = array("message" => _SYSTEM_AM_DBUPDATED, "time" => 3);
        $redirect = array("action" => "index", "section" => $section, "dirname" => $dirname);
        $this->redirect($redirect, $options);
    }

    // save a page's information into database
    public function createAction()
    {
        $dirname = $this->getRequest()->getPost("dirname");

        $section        = $this->getRequest()->getPost("section");
        $module         = $this->getRequest()->getPost("dirname");
        $parent         = $this->getRequest()->getPost("parent");
        $title          = $this->getRequest()->getPost("title");
        $controller     = $this->getRequest()->getPost("page_controller");
        $action         = $this->getRequest()->getPost("page_action");
        $block          = $this->getRequest()->getPost("block");
        $cache_expire   = $this->getRequest()->getPost("cache_expire");
        $cache_level    = $this->getRequest()->getPost("cache_level");
        $id             = $this->getRequest()->getPost("id", 0);

        $model = XOOPS::getModel('page');
        $modelResource = XOOPS::getModel('acl_resource')->setSection($section);

        if (empty($title)) {
            $title = $module;
            if (!empty($controller)) {
                $title .= "-" . $controller;
                if (!empty($action)) {
                    $title .= "-" . $action;
                }
            }
        }
        //$data = compact("title", "section", "module", "controller", "action", "block", "cache_expire", "cache_level");
        // Create a new page
        if (empty($id)) {
            $uniqueViolated = false;
            $select = $model->select()
                            ->from($model, "COUNT(*) as count")
                            ->where("section = ?", $section)
                            ->where("module = ?", $module)
                            ->where("controller = ?", $controller)
                            ->where("action = ?", $action);
            $count = $model->fetchRow($select)->count;
            if ($count > 0) {
                $message = XOOPS::_("The page already exists.");
                $uniqueViolated = true;
            }
            if (!$uniqueViolated) {
                $data = compact("title", "section", "module", "controller", "action", "block", "cache_expire", "cache_level");
                $data["custom"] = 1;
                $pageId = $model->insert($data);
                $message = XOOPS::_("The page is added successfully.");

                if ($data["module"] != "default") {
                    $resource = array(
                        "name"      => $pageId,
                        "section"   => $section,
                        "module"    => $module,
                        "title"     => $title,
                    );

                    if (empty($parent) && !empty($controller)) {
                        $select = $model->select()->where("section = ?", $section)->where("module = ?", $module)->where("action = ?", "");
                        $rowset = $model->fetchAll($select);
                        $resources = array();
                        foreach ($rowset as $row) {
                            $resources[$row->controller] = $row->id;
                        }
                        if (empty($action)) {
                            $parent = $resources[""];
                        } else {
                            $parent = $resources[$controller];
                        }
                        $select = $modelResource->select()->where("section = ?", $section)->where("name = ?", $parent);
                        $parent = $modelResource->fetchRow($select);
                    }
                    $modelResource->add($resource, $parent);
                }
            }
        // Update a page
        } else {
            $page = $model->findRow($id);
            $section = $page->section;
            $module = $page->module;
            $data = compact("title", "block", "cache_expire", "cache_level");
            $where = array("id = ?" => $id);
            $model->update($data, $where);
            $message = XOOPS::_("The page is updated successfully.");
            if ($module != "default") {
                $select = $modelResource->select()
                                        ->where("section = ?", $section)
                                        ->where("name = ?", $id);
                if (!$resource = $modelResource->fetchRow($select)) {
                    $resource = array(
                        "name"      => $id,
                        "section"   => $section,
                        "module"    => $module,
                        "title"     => $page->title,
                    );
                    $parent = null;
                    if (!empty($page->controller)) {
                        $select = $model->select()->where("section = ?", $section)->where("module = ?", $module)->where("action = ?", "");
                        $rowset = $model->fetchAll($select);
                        $resources = array();
                        foreach ($rowset as $row) {
                            $resources[$row->controller] = $row->id;
                        }
                        if (empty($action)) {
                            $parent = $resources[""];
                        } else {
                            $parent = $resources[$controller];
                        }
                        $select = $modelResource->select()->where("section = ?", $section)->where("name = ?", $parent);
                        $parent = $modelResource->fetchRow($select);
                    }
                    $modelResource->add($resource, $parent);
                }
            }
        }
        XOOPS::service("registry")->page->flush($module);

        $options = array("message" => $message, "time" => 3);
        $redirect = array("action" => "index", "section" => $section, "dirname" => $module);
        $this->redirect($redirect, $options);
    }

    // delete a page from database
    public function deleteAction()
    {
        $id = $this->_getParam("id", 0);

        $modelPage = XOOPS::getModel('page');
        if (!$page = $modelPage->findRow($id)) {
            $section = "front";
            $module = "default";
            $message = XOOPS::_("The page is not found.");
        } else {
            $section = $page->section;
            $module = $page->module;

            $moduleResource = XOOPS::getModel("acl_resource")->setSection($section);
            $select = $moduleResource->select()
                                        ->where("section = ?", $section)
                                        ->where("module = ?", $module)
                                        ->where("name = ?", $id);
            if ($resource = $moduleResource->fetchRow($select)) {
                $moduleResource->remove($resource);
            }
            $modelPage->delete(array("id = ?" => $id));
            XOOPS::service("registry")->page->flush($module);
            $message = XOOPS::_("The page is deleted successfully.");
        }

        $options = array("message" => $message, "time" => 3);
        $redirect = array("action" => "index", "section" => $section, "dirname" => $module);
        $this->redirect($redirect, $options);
    }

    // Save a page's block information into database
    public function saveblockAction()
    {
        $module = $this->getRequest()->getModuleName();
        $page = $this->_getParam("page", 0);
        $positions = $this->_getParam("positions", array());
        $orders = $this->_getParam("orders", array());
        $disables = $this->_getParam("disables", array());

        $model = XOOPS::getModel('page');
        $select = $model->select()
                        ->where("id = ?", $page);
        if (!$page = $model->fetchRow($select)) {
            $message = XOOPS::_("The page is not found.");
            $options = array("message" => $message, "time" => 3);
            $redirect = array("action" => "index");
            $this->redirect($redirect, $options);
            return;
        }
        $section = $page->section;
        $dirname = $page->module;
        $page = $page->id;
        $model = XOOPS::getModel('page_block');
        $blockPositions = static::getBlockPositions();
        foreach (array_keys($orders) as $key) {
            if (!isset($blockPositions[$positions[$key]]) || $positions[$key] < 0 || $orders[$key] < 0) {
                $model->delete(array("id = ?" => $key));
                continue;
            }
            $data = array(
                "order" => intval($orders[$key])
            );
            $where = array("id = ?" => $key);
            $model->update($data, $where);
        }
        // Handling global blocks
        if (!empty($disables)) {
            // To fetch existent global disable rows
            $select = $model->select()
                            ->where("`order` < ?", 0)
                            ->where("page = ?", $page);
            $list = $select->query()->fetchAll();
            $existent = array();
            foreach ($list as $row) {
                $existent[(-1 * $row["order"])] = $row["id"];
            }
            // To fetch active global rows
            $select = $model->select()
                            ->where("id IN (?)", array_keys($disables))
                            ->where("page = ?", 0);
            $rows = $select->query()->fetchAll();
            foreach ($rows as $row) {
                // Keep the global item if not disabled
                if (empty($disables[$row["id"]])) continue;
                // Remove the global item if disabled on global page
                if ($page == 0) {
                    $model->delete(array("id = ?" => $row["id"]));
                    continue;
                }
                if (!isset($existent[$row["id"]])) {
                    $data = array(
                        "page"      => $page,
                        "block"     => $row["block"],
                        "position"  => $row["position"],
                        "order"     => -1 * $row["id"],
                    );
                    $model->insert($data);
                } else {
                    unset($existent[$row["id"]]);
                }
            }
            if (!empty($existent)) {
                $model->delete(array("id IN (?)" => array_values($existent)));
            }
        } else {
            $where = array(
                $model->quoteIdentifier("page") . " = ?"      => $page,
                $model->quoteIdentifier("order") . " < ?"     => 1
            );
            $model->delete($where);
        }

        XOOPS::service("registry")->page->flush($dirname);
        $options = array("message" => _SYSTEM_AM_DBUPDATED, "time" => 3);
        $redirect = array("action" => "block", "page" => $page);
        $this->redirect($redirect, $options);
    }

    // Insert blocks into a page
    public function insertblockAction()
    {
        $module = $this->getRequest()->getModuleName();
        $page = $this->getRequest()->getPost("page");
        //$position = $this->getRequest()->getPost("position");
        $positions = $this->getRequest()->getPost("positions");
        $orders = $this->getRequest()->getPost("orders");
        $model = XOOPS::getModel('page');
        $select = $model->select()
                        ->where("id = ?", $page);
        if (!$page = $model->fetchRow($select)) {
            $message = XOOPS::_("The page is not found.");
            $options = array("message" => $message, "time" => 3);
            $redirect = array("action" => "index");
            $this->redirect($redirect, $options);
            return;
        }
        $dirname = $page->module;

        $model = XOOPS::getModel('page_block');
        $blockPositions = static::getBlockPositions();
        foreach (array_keys($orders) as $key) {
            if (!isset($blockPositions[$positions[$key]]) || $positions[$key] < 0) {
                continue;
            }
            $data = array(
                "block"     => $key,
                "position"  => $positions[$key],
                "page"      => $page->id,
                "order"     => $orders[$key]
            );
            $model->insert($data);
        }
        XOOPS::service("registry")->page->flush($dirname);
        $options = array("message" => _SYSTEM_AM_DBUPDATED, "time" => 3);
        $redirect = array("action" => "block", "page" => $page->id);
        $this->redirect($redirect, $options);
    }

    // Page form
    private function getFormPage($name, $page, $title, $action)
    {
        Xoops_Legacy::autoload();

        $form = new XoopsThemeForm($title, $name, $action, 'post', true);
        $form->addElement(new XoopsFormText(XOOPS::_('Controller'), 'page_controller', 50, 64, $page["controller"]));
        $form->addElement(new XoopsFormText(XOOPS::_('Action'), 'page_action', 50, 64, $page["action"]));
        $form->addElement(new XoopsFormText(XOOPS::_('Title'), 'title', 50, 255, $page["title"]));
        $selectExpire = new XoopsFormSelect(XOOPS::_('Cache expire'), "cache_expire", $page["cache_expire"]);
        $selectExpire->addOptionArray(static::getExpireOptions());
        $form->addElement($selectExpire);
        $selectLevel = new XoopsFormSelect(XOOPS::_('Cache level'), "cache_level", $page["cache_level"]);
        $selectLevel->addOptionArray(static::getLevelOptions());
        $form->addElement($selectLevel);
        $selectBlock = new XoopsFormSelect(XOOPS::_('Block option'), "block", $page["block"]);
        $selectBlock->addOptionArray(static::getBlockOptions());
        $form->addElement($selectBlock);

        $form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
        return $form;
    }

    // Module's page list form
    private function getFormList($name, $pages, $title, $action, $section)
    {
        Xoops_Legacy::autoload();
        $module = $this->getRequest()->getModuleName();

        $form = new XoopsThemeForm($title, $name, $action, 'post', true);
        foreach ($pages as $key => $page) {
            $id = $page["id"];
            $ele = new XoopsFormElementTray($page["title"], ' ');

            $selectBlock = new XoopsFormSelect(XOOPS::_('Block option'), "blocks[{$id}]", $page['block']);
            $selectBlock->addOptionArray(static::getBlockOptions());
            $ele->addElement($selectBlock);
            unset($selectBlock);

            $selectExpire = new XoopsFormSelect(XOOPS::_('Cache expire'), "cache_expires[{$id}]", $page['cache_expire']);
            $selectExpire->addOptionArray(static::getExpireOptions());
            $ele->addElement($selectExpire);
            unset($selectExpire);

            $selectLevel = new XoopsFormSelect(XOOPS::_('Cache level'), "cache_levels[{$id}]", $page['cache_level']);
            $selectLevel->addOptionArray(static::getLevelOptions());
            $ele->addElement($selectLevel);
            unset($selectLevel);

            $href = $this->view->url(array(
                                        "action"        => "edit",
                                        "controller"    => "page",
                                        "module"        => $module,
                                        "id"            => $id,
                                        ), "admin");
            $editLink = "<a href=\"" . $href . "\" title=\"" . $page["title"] . "\">" . XOOPS::_("Manage") . "</a>";
            $label = new XoopsFormLabel("", $editLink);
            $ele->addElement($label);
            unset($label);

            $href = $this->view->url(array(
                                        "action"        => "delete",
                                        "controller"    => "page",
                                        "module"        => $module,
                                        "id"            => $id,
                                        ), "admin");
            $editLink = "<a href=\"" . $href . "\" title=\"" . $page["title"] . "\">" . XOOPS::_("Delete") . "</a>";
            $label = new XoopsFormLabel("", $editLink);
            $ele->addElement($label);
            unset($label);

            if ($section == "front") {
                $href = $this->view->url(array(
                                            "action"        => "block",
                                            "controller"    => "page",
                                            "module"        => $module,
                                            "page"          => $id,
                                            ), "admin");
                $blockLink = "<a href=\"" . $href . "\" title=\"" . $page["title"] . "\">" . XOOPS::_("Block") . "</a>";
                $label = new XoopsFormLabel("", $blockLink);
                $ele->addElement($label);
                unset($label);
            }

            $ele->setDescription($page['description']);
            $form->addElement($ele);
            unset($ele);
        }
        $form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
        return $form;
    }

    // Page's block list form
    private function getFormBlocks($name, $blocks, $title, $action, $page)
    {
        Xoops_Legacy::autoload();
        $module = $this->getRequest()->getModuleName();

        $blockPositions = static::getBlockPositions();
        $form = new XoopsThemeForm($title, $name, $action, 'post', true);
        foreach ($blockPositions as $position => $title) {
            if ($position < 0 || empty($blocks[$position])) continue;
            $ele = new XoopsFormElementTray($title, '<br />');
            foreach ($blocks[$position] as $key => $block) {
                $element = new XoopsFormElementTray($block['title'], ' ');
                $selectPosition = new XoopsFormSelect(XOOPS::_('Position'), "positions[" . $block['id'] . "]", $position);
                $selectPosition->addOptionArray($blockPositions);
                if (isset($block["global"])) {
                    $selectPosition->setDisabled();
                }
                $element->addElement($selectPosition);
                unset($selectPosition);
                $order = new XoopsFormText(XOOPS::_('Order'), "orders[" . $block['id'] . "]", 10, 10, $block["order"]);
                if (isset($block["global"])) {
                    $order->setDisabled();
                }
                $element->addElement($order);
                unset($order);
                if (isset($block["global"])) {
                    $element->addElement(new XoopsFormRadioYN(XOOPS::_('Disable'), "disables[" . $block['id'] . "]", $block["global"]));
                }
                $ele->addElement($element);
                unset($element);
            }
            $form->addElement($ele);
            unset($ele);
        }
        $href = $this->view->url(
            array(
                "action"        => "insert",
                "controller"    => "page",
                "module"        => $module,
                "page"          => $page,
            ),
            "admin"
        );
        $editLink = "<a href=\"" . $href. "\" title=\"" . XOOPS::_("Add a block") . "\">" . XOOPS::_("Add a block") . "</a>";
        $label = new XoopsFormLabel(XOOPS::_("Add a block"), $editLink);
        $form->addElement($label);
        unset($label);

        $form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
        $description = XOOPS::_("Set order value: positive - to display corresponding block; 0 or negative - to disable a block");
        $form->setDescription($description);
        return $form;
    }

    // Blocks form to be added to a page
    private function getFormInsert($name, $blocks, $title, $action)
    {
        Xoops_Legacy::autoload();

        $form = new XoopsThemeForm($title, $name, $action, 'post', true);
        $blockPositions = static::getBlockPositions();
        foreach ($blocks as $key => $block) {
            $element = new XoopsFormElementTray($block['title'], ' ');
            $selectPosition = new XoopsFormSelect(XOOPS::_('Position'), "positions[" . $block['id'] . "]", -1);
            $selectPosition->addOptionArray($blockPositions);
            $element->addElement($selectPosition);
            unset($selectPosition);
            $element->addElement(new XoopsFormText(XOOPS::_('Order'), "orders[" . $block['id'] . "]", 10, 10, 5));
            $form->addElement($element);
            unset($element);
        }
        $form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
        $description = XOOPS::_("Set order value: positive - to display corresponding block; 0 or negative - to disable a block");
        $form->setDescription($description);
        return $form;
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

    protected static function getBlockOptions()
    {
        return array(
            0   => XOOPS::_('Inherit from parent'),
            1   => XOOPS::_('Set explicitly')
        );
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

    /**
     * for block layout
     * @author xiaohui
     */
    public function layoutAction()
    {
        $this->setTemplate("system/admin/page_layout.html");
        $module = $this->getRequest()->getModuleName();
        $dirname = $this->_getParam("dirname", "");

        $modulesPage = $this->getModuleListOfPage();
        $modulesBlock = $this->getModuleListOfBlock();

        $model = XOOPS::getModel("block");
        $select = $model->select()->where("active = ?", 1);
        if ('-' != $dirname) {
            $select->where("module = ?", $dirname);
        }
        $blocks = $model->fetchAll($select);

        $this->template->assign('dirname', $dirname);
        $this->template->assign('modulesPage', $modulesPage);
        $this->template->assign('modulesBlock', $modulesBlock);
        $this->template->assign('blocks', $blocks);
    }

    public function savelayoutAction()
    {
        //Debug::e($this->getRequest()->getPost());
    }

    /**
     * for block layout
     * @author xiaohui
     */
    public function ajaxblockAction()
    {
        $this->setTemplate("system/admin/page_ajax_block.html");
        $module = $this->getRequest()->getModuleName();
        $dirname = $this->_getParam("dirname", "");

        $model = XOOPS::getModel("block");
        $select = $model->select()->where("active = ?", 1);
        if ('-' != $dirname) {
            $select->where("module = ?", $dirname);
        }
        $blocks = $model->fetchAll($select);
        $this->template->assign("blocks",$blocks);
    }

    public function ajaxpageAction()
    {
        $dirname = $this->_getParam("dirname", "default");
        $section = $this->_getParam("section", "front");

        $modelPage = XOOPS::getModel("page");
        $select = $modelPage->select()
                        ->where("section = ?", $section)
                        ->where("module = ?", $dirname)
                        ->order(array("controller", "action"));
        $pages = $modelPage->fetchAll($select)->toArray();
        $html = "<option value=\"0\">" . XOOPS::_('Select Page') . "</option>" . PHP_EOF;
        foreach ($pages as $id => $page) {
            $title = $page['title'] . ' (' . $dirname;
            if (!empty($page['controller'])) {
                $title .= '-' . $page['controller'];
                if (!empty($page['action'])) {
                    $title .= '-' . $page['action'];
                }
            }
            $title .= ')';
            $html .= "<option value=\"{$id}\">" . Xoops\Security::escape($title) . "</option>" . PHP_EOF;
        }

        echo $html;
    }

    protected function getModuleListOfPage()
    {
        $modules = XOOPS::service("registry")->modulelist->read("active");

        $modulesPage = array("default" => XOOPS::_("System Application"));
        foreach (array_keys($modules) as $dir) {
            $modulesPage[$dir] = $modules[$dir]["name"];
        }

        return $modulesPage;
    }

    protected function getModuleListOfBlock()
    {
        $modules = XOOPS::service("registry")->modulelist->read("active");
        $modulesBlock = array();
        foreach (array_keys($modules) as $dir) {
            // skip if the module does not have blocks
            $info = Xoops::service('module')->loadInfo($dir, 'block');
            if (empty($info)) {
                continue;
            }
            $modulesBlock[$dir] = $modules[$dir]["name"];
        }
        $modulesBlock[''] = XOOPS::_("Custom blocks");
        $modulesBlock['-'] = XOOPS::_("All blocks");

        return $modulesBlock;
    }
}