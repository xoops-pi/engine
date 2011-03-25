<?php
/**
 * XOOPS module block installer
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
 * @package         Xoops_Installer
 * @subpackage      Installer
 * @version         $Id$
 */

/**
 * Block configuration specs
 *
 *  return array(
 *      "blockA" => array(
 *          "name"          => "BlockUniqueName",
 *          "title"         => "Block Title",
 *          "description"   => "Desribing the block",
 *          "file"          => "block_definition_file.php",  // In modules/module/blocks/
 *          "show_func"     => "function_name_to_fetch_display_content", // Defined in above file
 *          "edit_func"     => "function_name_to_edit_options", // Defined in above file, optional
 *          "options"       => "op1|op2|op3|op4", // Options for show_func
 *          "template"      => "template.html", // in modules/module/templates/blocks/
 *          "cache"         => "role", // Cache level
 *          "access"        => array(), // ACL rules
 *      ),
 *      ...
 *  );
 */

class Xoops_Installer_Module_Block extends Xoops_Installer_Abstract
{
    public function install(&$message)
    {
        $dirname = $this->module->dirname;
        $message = $this->message;
        $blocks = $this->config;
        foreach ($blocks as $key => $block) {
            // break the loop if missing block config
            if (!isset($block['file']) || !isset($block['show_func'])) {
                continue;
            }
            $blockModel = array(
                "key"           => isset($block["name"]) ? $block["name"] : $key,
                "name"          => isset($block["name"]) ? $dirname . "-" . $block["name"] : "",
                "title"         => $block['title'],
                "description"   => isset($block["description"]) ? $block["description"] : "",
                "module"        => $dirname,
                "func_file"     => $block['file'],
                "show_func"     => $block['show_func'],
                "edit_func"     => isset($block['edit_func']) ? $block['edit_func'] : '',
                "template"      => isset($block['template']) ? $block['template'] : '',
                "options"       => isset($block['options']) ? $block['options'] : '',
                "cache_level"   => isset($block['cache']) ? $block['cache'] : "",
                "active"        => 1,
                "access"        => isset($block['access']) ? $block['access'] : null,
            );

            $this->add($blockModel, $message);
        }

        XOOPS::service('registry')->block->flush($dirname);
    }

    public function update(&$message)
    {
        $dirname = $this->module->dirname;
        $message = $this->message;
        XOOPS::service('registry')->block->flush($dirname);

        if (version_compare($this->version, $this->module->version, ">=")) {
            return true;
        }

        $blocks = $this->config;
        if (empty($blocks)) {
            $blocks = array();
        }

        $model = XOOPS::getModel("block");
        $showfuncs = array();
        $funcfiles = array();
        foreach ($blocks as $key => $block) {
            if (empty($block['show_func']) || empty($block['file'])) {
                continue;
            }
            $showfuncs[] = $block['show_func'];
            $funcfiles[] = $block['file'];
            $blockKey = isset($block["name"]) ? $block["name"] : $key;

            $select = $model->select()
                            ->where('`key` = ?', $blockKey)
                            ->where('`show_func` = ?', $block['show_func'])
                            ->where("`func_file` = ?", $block['file']);
            $blockList = $select->query()->fetchAll();
            if (empty($blockList)) {
                $blockModel = array(
                    "key"       => $blockKey,
                    "name"      => isset($block["name"]) ? $dirname . "-" . $block["name"] : "",
                    "title"     => $block['title'],
                    "module"    => $dirname,
                    "func_file" => $block['file'],
                    "show_func" => $block['show_func'],
                    "edit_func" => isset($block['edit_func']) ? $block['edit_func'] : '',
                    "template"  => isset($block['template']) ? $block['template'] : '',
                    "options"   => isset($block['options']) ? $block['options'] : '',
                    "cache_level"   => isset($block['cache']) ? $block['cache'] : "",
                    "active"    => 1,
                );

                $this->add($blockModel, $message);
            } else {
                foreach ($blockList as $item) {
                    $data = array(
                        "name"      => isset($block["name"]) ? $dirname . "-" . $block["name"] : "",
                        "module"    => $dirname,
                        "edit_func" => isset($block['edit_func']) ? $block['edit_func'] : '',
                        "template"  => isset($block['template']) ? $block['template'] : '',
                        "cache_level"   => isset($block['cache']) ? $block['cache'] : "",
                    );
                    $where = array('id = ?' => $item["id"]);
                    $model->update($data, $where);
                }
            }
        }
        $clauseFunc = new Xoops_Zend_Db_Clause();
        $clauseFunc->add('show_func NOT IN (?)', array_unique($showfuncs));
        $clauseFunc->addOr('func_file NOT IN (?)', array_unique($funcfiles));
        $clause = new Xoops_Zend_Db_Clause();
        $clause->add("module = ?", $dirname);
        $clause->add("type = ?", "");
        $clause->add($clauseFunc);
        $select = $model->select()->where($clause);
        $blockList = $select->query()->fetchAll();
        $blockId = array();
        foreach ($blockList as $block) {
            $blockId[] = $block["id"];
        }
        $this->delete($blockId, $message);

    }

    public function uninstall(&$message)
    {
        if (!is_object($this->module)) {
            return;
        }

        $dirname = $this->module->dirname;
        $message = $this->message;

        $model = XOOPS::getModel("block");
        $select = $model->select()->where('module = ?', $dirname);
        $blockList = $select->query()->fetchAll();
        $blockId = array();
        foreach ($blockList as $block) {
            $blockId[] = $block["id"];
        }
        $this->delete($blockId, $message);

        XOOPS::service('registry')->block->flush($dirname);
    }

    public function activate(&$message)
    {
        $dirname = $this->module->dirname;
        $message = $this->message;

        $model = XOOPS::getModel("block");
        $where = array('module = ?' => $dirname);
        $model->update(array("active" => 1), $where);

        XOOPS::service('registry')->block->flush($dirname);
    }

    public function deactivate(&$message)
    {
        $dirname = $this->module->dirname;
        $message = $this->message;

        $model = XOOPS::getModel("block");
        $where = array('module = ?' => $dirname);
        $model->update(array("active" => 0), $where);

        XOOPS::service('registry')->block->flush($dirname);
    }

    private function add($block, &$message)
    {
        $dirname = $this->module->dirname;
        $modelBlock = XOOPS::getModel("block");
        $modelRule = XOOPS::getModel("acl_rule");

        $rules = array();
        if (array_key_exists("access", $block)) {
            $rules = $block["access"];
            unset($block["access"]);
        }
        $id = $modelBlock->insert($block);
        $dataRule = array(
            "resource"  => $id,
            "section"   => "block",
            "module"    => $dirname,
        );
        $roles = array("guest", "member");
        foreach ($roles as $role) {
            $dataRule["role"] = $role;
            if (isset($rules[$role])) {
                $dataRule["deny"] = empty($rules[$role]) ? 1 : 0;
            } else {
                $dataRule["deny"] = 0;
            }
            $modelRule->insert($dataRule);
        }
    }

    private function delete($id, &$message)
    {
        if (empty($id)) {
            return;
        }
        $dirname = $this->module->dirname;
        $modelBlock = XOOPS::getModel("block");
        $modelRule = XOOPS::getModel("acl_rule");
        $modelPage = XOOPS::getModel("page");
        $modelPageBlock = XOOPS::getModel("page_block");

        $id = is_array($id) ? $id : array($id);

        // delete from block table
        $where = array('id IN (?)' => $id);
        $modelBlock->delete($where);

        // delete from rule table
        $where = array(
            'resource IN (?)'   => $id,
            "section = ?"       => "block"
        );
        $modelRule->delete($where);

        // delete from page_block table
        $clause = new Xoops_Zend_Db_Clause('block IN (?)', $id);
        $cols = "page";
        $items = $modelPageBlock->get($clause, $cols);
        $modelPageBlock->delete($clause);
        $pages = array();
        foreach ($items as $item) {
            $pages[$item["page"]] = 1;
        }

        // Clean module block caches
        if (count($pages)) {
            if (isset($pages[0])) {
                XOOPS::service('registry')->block->flush();
            } else {
                $clause = new Xoops_Zend_Db_Clause('id IN (?)', array_keys($pages));
                $cols = "module";
                $items = $modelPage->get($clause, $cols);
                $modules = array();
                foreach ((array)$items as $item) {
                    $modules[$item["module"]] = 1;
                }
                foreach (array_keys($modules) as $module) {
                    if ($module == $dirname) continue;
                    XOOPS::service('registry')->block->flush($module);
                }
            }
        }
    }
}