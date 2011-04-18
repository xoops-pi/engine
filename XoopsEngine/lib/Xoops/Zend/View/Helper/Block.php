<?php
/**
 * Zend Framework for Xoops Engine
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
 * @category        Xoops_Zend
 * @package         View
 * @version         $Id$
 */

/**
 * Helper for fetching and rendering a block
 *
 * <code>
 * XOOPS::registry('view')->block('block-name', array('title_hidden' => 1, 'opt1' => 'val1', 'opt2' => 'val2'));
 * XOOPS::registry('view')->block('block-name', array('link' => '/link/to/a/URL', 'opt1' => 'val1', 'opt2' => 'val2'));
 * XOOPS::registry('view')->block('block-name', array('style' => 'specified-css-class', 'opt1' => 'val1', 'opt2' => 'val2'));
 * XOOPS::registry('view')->block(24, array('opt1' => 'val1', 'opt2' => 'val2'));
 * </code>
 */

class Xoops_Zend_View_Helper_Block extends Zend_View_Helper_Abstract
{
    /**
     * Generates content from a named block
     *
     * @access public
     *
     * @param  array|string|int|object    $block        block name, id or object
     * @param  array                $options    options passed to the block: parameters and cache_expire, cache_level
     * @return string content of the block
     */
    public function block($block, $options = array())
    {
        if (empty($block)) return false;

        //Debug::e($block);
        $model = XOOPS::getModel("block");
        if (!is_object($block)) {
            $select = $model->select();
            if (is_numeric($block)) {
                $select->where($model->getAdapter()->quoteIdentifier("id") . " = ?", $block);
                $block = $model->fetchRow($select);
            } elseif (is_string($block)) {
                $select->where($model->getAdapter()->quoteIdentifier("name") . " = ?", $block);
                $block = $model->fetchRow($select);
            }
        }

        if (is_object($block)) {
            $block = $block->toArray();
        }
        if (empty($block) || empty($block['active'])) {
            return false;
        }

        foreach (array("title", "title_hidden", "link", "style", "cache_expire", "cache_level", "template") as $key) {
            if (isset($options[$key])) {
                $block[$key] = $options[$key];
                //unset($options[$key]);
            }
        }
        if (!empty($block['title_hidden'])) {
            $block['title'] = '';
        }
        if (!empty($block['link']) && !preg_match("/^http[s]*:\/\//i", $block['link'])) {
            $block['link'] = Xoops::url('www') . '/' . ltrim($block['link'], '/');
        }

        $tplName = ($tplName = $block['template'])
                    ? ($block['module']
                        ? "file:block/" . $block['module'] . "/" . $tplName
                        : "file:block/system/" . $tplName)
                    : "file:block/system/dummy.html";
        $template = $this->view->getEngine();

        $keySeed = empty($options) ? md5($block['id']) : md5($block['id'] . serialize($options));
        $cache_key = Xoops_Zend_Cache::generateId('blk_' . $keySeed, $block["cache_level"]);
        $template->setCompileId($this->view->getTheme(), $block['module']);
        if ($block["cache_expire"] <= 0) {
            $template->caching = 0;
        } else {
            $template->caching = 1;
        }
        $template->cache_lifetime = $block["cache_expire"];
        $logger = XOOPS::service('logger');
        // Generate block content if not cached
        if (!$block["cache_expire"] || !$template->is_cached($tplName, $cache_key)) {
            if ($content = $model->buildBlock($block, $options)) {
                $template->assign('block', $content);
                $block['content'] = $template->fetch($tplName, $cache_key);
                $logger->log($block["title"] . " generated", 'debug', 'block');
            } else {
                $logger->log($block["title"] . " skipped", 'warn', 'block');
                $block = false;
            }
        } else {
            $logger->log($block["title"] . " fetched (cache time: " . $block["cache_expire"] . ")", 'debug', 'block');
            $content = $template->fetch($tplName, $cache_key);
            $block['content'] = $content;
        }
        return $block;
    }
}