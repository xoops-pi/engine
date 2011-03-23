<?php
/**
 * Generic module admin block controller
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

class Module_BlockController extends Xoops_Zend_Controller_Action_Admin
{
    public function indexAction()
    {
        $this->setTemplate("system/admin/block_list_module.html");
        $module = $this->getRequest()->getModuleName();
        $modules = XOOPS::service("registry")->module->read();

        $model = XOOPS::getModel("block");
        $select = $model->select()
                        ->where("module = ?", $module);
        $blocks = $model->fetchAll($select);

        $title = sprintf(XOOPS::_("Blocks of Module %s"), $modules[$module]["name"]);
        $action = $this->view->url(array("action" => "save", "controller" => "block", "module" => $module));
        $form = $this->getFormList("block_form_list", $blocks, $title, $action);
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
        $form = $this->getFormBlock($name, $block, $title, $action);
        $form->addElement(new XoopsFormHidden('id', $id));
        $form->assign($this->template);
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
            $form->addElement(new XoopsFormLabel(XOOPS::_("Options"), $block["content"]));
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

            /*
            $href = $this->view->url(array(
                                        "action"        => "distribute",
                                        "controller"    => "page",
                                        "module"        => $module,
                                        "block"         => $id,
                                        ), "admin");
            $editLink = "<a href=\"" . $href. "\" title=\"". $block["title"] ."\">" . XOOPS::_("Page") . "</a>";
            $label = new XoopsFormLabel("", $editLink);
            $ele->addElement($label);
            unset($label);
            */

            $ele->setDescription($block['name']);
            $form->addElement($ele);
            unset($ele);
        }
        $form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
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
}