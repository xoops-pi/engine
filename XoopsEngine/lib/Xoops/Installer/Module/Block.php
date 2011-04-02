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
 *      // Block with legacy mode, e.g. file, show_func, edit_func and mixed options
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
 *      // Block with new mode, e.g. render, structured options
 *      "blockB" => array(
 *          "name"          => "BlockUniqueName",
 *          "title"         => "Block Title",
 *          "description"   => "Desribing the block",
 *          "render"        => 'class::method',
 *          "template"      => "template.html", // in modules/module/templates/blocks/
 *          "cache"         => "role", // Cache level
 *          "access"        => array(), // ACL rules
 *          "options"       => array(
 *              'a' => array(
 *                  'title'         => '_APP_MB_OPTION_A',
 *                  'description'   => '_APP_MB_OPTION_A_DESC',
 *                  'edit'          => 'select',
 *                  'filter'        => 'num_int',
 *                  'default'       => 1,
 *                  'options'       => array(
 *                      '_APP_MB_OPTION_A_1'    => 1,
 *                      '_APP_MB_OPTION_A_2'    => 2,
 *                      '_APP_MB_OPTION_A_3'    => 3,
 *                  ),
 *              ),
 *              'b'  => array(
 *                  'title'         => '_APP_MB_OPTION_B',
 *                  'description'   => '_APP_MB_OPTION_B_DESC',
 *                  'edit'          => array('module' => 'demo', 'type' => 'choose'),
 *                  'filter'        => 'string',
 *                  'default'       => 'good',
 *              ),
 *          ),
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
        $classPrefix = (('app' == Xoops::service('module')->getType($module)) ? 'app' : 'module') . '\\' . ($this->module->parent ?: $dirname);
        foreach ($blocks as $key => $block) {
            // break the loop if missing block config
            if (empty($block['render']) && (!isset($block['file']) || !isset($block['show_func']))) {
                continue;
            }
            $data = array(
                "key"           => isset($block["name"]) ? $block["name"] : $key,
                "name"          => isset($block["name"]) ? $dirname . "-" . $block["name"] : "",
                "title"         => $block['title'],
                "description"   => isset($block["description"]) ? $block["description"] : "",
                "module"        => $dirname,
                "render"        => empty($block['render']) ? '' : $classPrefix . '\\' . $block['render'],
                "func_file"     => isset($block['func_file']) ? $block['func_file'] : '',
                "show_func"     => isset($block['show_func']) ? $block['show_func'] : '',
                "edit_func"     => isset($block['edit_func']) ? $block['edit_func'] : '',
                "template"      => isset($block['template']) ? $block['template'] : '',
                "options"       => isset($block['options']) ? $block['options'] : '',
                "cache_level"   => isset($block['cache']) ? $block['cache'] : "",
                "active"        => 1,
                "access"        => isset($block['access']) ? $block['access'] : null,
            );

            $this->addBlock($data, $message);
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
        $classPrefix = (('app' == Xoops::service('module')->getType($module)) ? 'app' : 'module') . '_' . ($this->module->parent ?: $dirname);
        foreach ($blocks as $key => $block) {
            if (!empty($block['func_file'])) {
                $funcfiles[] = $block['file'];
            }
            if (!empty($block['show_func'])) {
                $showfuncs[] = $block['show_func'];
            }
            $blockKey = isset($block["name"]) ? $block["name"] : $key;

            $select = $model->select()
                            ->where('`key` = ?', $blockKey)
                            ->where('`module` = ?', $dirname)
                            ->where('`root` = ?', 0)
                            ->where('`show_func` = ?', $block['show_func'])
                            ->where("`func_file` = ?", $block['file']);
            $blockList = $select->query()->fetchAll();
            if (empty($blockList)) {
                $data = array(
                    "key"           => $blockKey,
                    "name"          => isset($block["name"]) ? $dirname . "-" . $block["name"] : "",
                    "title"         => $block['title'],
                    "module"        => $dirname,
                    "render"        => empty($block['render']) ? '' : $classPrefix . '\\' . $block['render'],
                    "func_file"     => isset($block['func_file']) ? $block['func_file'] : '',
                    "show_func"     => isset($block['show_func']) ? $block['show_func'] : '',
                    "edit_func"     => isset($block['edit_func']) ? $block['edit_func'] : '',
                    "template"      => isset($block['template']) ? $block['template'] : '',
                    "options"       => isset($block['options']) ? $block['options'] : '',
                    "cache_level"   => isset($block['cache']) ? $block['cache'] : "",
                    "active"        => 1,
                );

                $this->addBlock($data, $message);
            } else {
                foreach ($blockList as $item) {
                    $data = array(
                        "name"          => isset($block["name"]) ? $dirname . "-" . $block["name"] : "",
                        "render"        => empty($block['render']) ? '' : $classPrefix . '\\' . $block['render'],
                        "edit_func"     => isset($block['edit_func']) ? $block['edit_func'] : '',
                        "template"      => isset($block['template']) ? $block['template'] : '',
                        "cache_level"   => isset($block['cache']) ? $block['cache'] : "",
                        "options"       => isset($block['options']) ? $block['options'] : '',
                    );
                    /*
                    $where = array('id = ?' => $item["id"]);
                    $model->update($data, $where);
                    */
                    $this->updateBlock($item["id"], $data, $message);
                }
            }
        }
        $clauseFunc = new Xoops_Zend_Db_Clause();
        $clauseFunc->add('show_func NOT IN (?)', array_unique($showfuncs));
        $clauseFunc->addOr('func_file NOT IN (?)', array_unique($funcfiles));
        $clause = new Xoops_Zend_Db_Clause();
        $clause->add("module = ?", $dirname);
        //$clause->add("type = ?", "");
        $clause->add($clauseFunc);
        $select = $model->select()->where($clause);
        $blockList = $select->query()->fetchAll();
        $blockId = array();
        foreach ($blockList as $block) {
            $blockId[] = $block["id"];
        }
        $this->deleteBlock($blockId, $message);

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
        $this->deleteBlock($blockId, $message);

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

    /**
     * Adds a block and its relevant options, ACL rules
     */
    private function addBlock($block, &$message)
    {
        $dirname = $this->module->dirname;
        $modelBlock = XOOPS::getModel("block");
        $modelOption = XOOPS::getModel("block_option");
        $modelRule = XOOPS::getModel("acl_rule");

        $rules = array();
        if (array_key_exists("access", $block)) {
            $rules = $block["access"];
            unset($block["access"]);
        }
        $options = array();
        $opts = array();
        if (is_array($block['options'])) {
            $order = 0;
            foreach ($block['options'] as $name => $option) {
                $opts[$name] = $option['default'];
                $option['name'] = $name;
                $option['order'] = $order++;
                $options[] = $option;
            }
            $block['options'] = serialize($opts);
        } elseif (!empty($block['options'])) {
            $block['options'] = serialize(explode('|', $block['options']));
        }
        $id = $modelBlock->insert($block);

        foreach ($options as $option) {
            $option['block'] = $id;
            /*
            if (!empty($option['options']) && is_array($option['options'])) {
                $option['options'] = serialize($option['options']);
            }
            */
            //$modelOption->insert($option);
            $optionRow = $modelOption->createRow($option);
            $optionRow->save();
        }

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

    /**
     * Updates a block and its relevant options
     */
    private function updateBlock($id, $data, &$message)
    {
        if (empty($id)) {
            return;
        }
        $dirname = $this->module->dirname;
        $modelBlock = XOOPS::getModel("block");
        $modelOption = XOOPS::getModel("block_option");

        $options = $block['options'];
        unset($block['options']);
        $where = array('id = ?' => $id);
        $modelBlock->update($data, $where);
        unset($data['name']);
        $where = array('root = ?' => $id);
        $modelBlock->update($data, $where);

        if (!is_array($options)) {
            $modelOption->delete(array('block = ?' => $id));
        } else {
            $optionList = array();
            $order = 0;
            foreach ($options as $name => $option) {
                $option['name'] = $name;
                $option['order'] = $order++;
                $optionList[] = $option;
            }

            $clause = new Xoops_Zend_Db_Clause('block', $id);
            $optionExist = $modelOption->get($clause);

            $findPosition = function ($name, $candidates)
            {
                $pos = false;
                foreach ($candidates as $key => $item) {
                    if ($item['name'] == $name) {
                        $pos = $key;
                        break;
                    }
                }
                return $pos;
            };

            $hasChange = false;
            $toDelete = array();
            $toUpdate = array();
            $deleteData = array();
            $newData = array();
            foreach ($optionExist as $option) {
                $pos = $findPosition($option->name, $options);
                if ($pos === false) {
                    $toDelete[] = $option->id;
                    $deleteData[$option->name] = 1;
                    continue;
                }
                //$option->order = $pos;
                $toUpdate[] = $option;
            }
            // Delete not used options
            if (!empty($toDelete)) {
                $modelOption->delete(array("id IN (?)" => $toDelete));
                $hasChange = true;
            }
            // Update existing options
            foreach ($toUpdate as $option) {
                $option->setFromArray($optionList[$option->order])->save();
                unset($optionList[$option->order]);
            }
            // Insert new options
            foreach ($optionList as $option) {
                $option['block'] = $id;
                //$modelOption->insert($option);
                $optionRow = $modelOption->createRow($option);
                $optionRow->save();
                $newData[$option['name']] = $option['default'];
                $hasChange = true;
            }

            if ($hasChange) {
                // Update block options data
                $options = array_diff_key($options, $deleteData);
                $options = array_merge($newData, $options);
                $where = array('id = ?' => $id);
                $data = array('options' => serialize($options));
                $modelBlock->update($data, $where);

                // Update cloned blocks
                $select = $model->select()->where('root = ?', $id);
                $blockList = $select->query()->fetchAll();
                foreach ($blockList as $block) {
                    $options = empty($block['options']) ? array() : unserialize($block['options']);
                    $options = array_diff_key($options, $deleteData);
                    $options = array_merge($newData, $options);

                    $where = array('id = ?' => $block['id']);
                    $data = array('options' => serialize($options));
                    $modelBlock->update($data, $where);
                }
            }
        }
    }

    /**
     * Deletes a block and its relevant options, ACL rules
     */
    private function deleteBlock($id, &$message)
    {
        if (empty($id)) {
            return;
        }
        $dirname = $this->module->dirname;
        $modelBlock = XOOPS::getModel("block");
        $modelOption = XOOPS::getModel("block_option");
        $modelRule = XOOPS::getModel("acl_rule");
        $modelPage = XOOPS::getModel("page");
        $modelPageBlock = XOOPS::getModel("page_block");

        $id = is_array($id) ? $id : array($id);

        // delete from block table
        $clause = new Xoops_Zend_Db_Clause('id IN (?)', $id);
        $clause->addOr('root IN (?)', $id);
        //$where = array('id IN (?)' => $id);
        //Debug::e($id);
        //Debug::e($clause);
        //Debug::e($clause->render($modelBlock->getAdapter()));
        //define('exit', 1);
        $modelBlock->delete($clause);
        //exit();

        // delete from block option table
        $where = array('block IN (?)' => $id);
        $modelOption->delete($where);

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