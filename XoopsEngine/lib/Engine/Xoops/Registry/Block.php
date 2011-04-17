<?php
/**
 * XOOPS page block registry
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

class Block extends \Kernel\Registry
{
    //protected $registry_key = "registry_block";

    protected function loadDynamic($options = array())
    {
        $model = \Xoops::getModel('page');
        $module = $options['module'];
        $role = isset($options['role']) ? $options['role'] : null;
        $select = $model->select()
                        ->where('module = ?', $module)
                        ->where('block = ?', 1);
        $pageList = $model->fetchAll($select);
        // Pseudo page for global blocks
        $pages = array("" => 0);
        // Module pages
        foreach ($pageList as $page) {
            $key = $module;
            if (!empty($page['controller'])) {
                $key .= "-" . $page['controller'];
                if (!empty($page['action'])) {
                    $key .= "-" . $page['action'];
                }
            }
            $pages[$key] = $page["id"];
        }
        //if (!empty($pages)) {
            $modelLinks = \Xoops::getModel('page_block');
            $clause = new \Xoops_Zend_Db_Clause("page IN (?)", array_values($pages));
            $clause->order(array("position", "order"));
            $blockLinks = $modelLinks->get($clause);
            // Blocks Ids
            $blocksId = array();
            // Local blocks added by the module
            $blocksLocal = array();
            // Global blocks will be inherited by the module
            $blocksGlobal = array();
            // Disabled global blocks
            $blocksDisable = array();

            // Get all block Ids
            foreach ($blockLinks as $link) {
                $blocksId[$link["block"]] = 1;
            }
            // Check for active for blocks
            if (!empty($blocksId)) {
                $modelBlock = \XOOPS::getModel('block');
                $select = $modelBlock->select()->where('id IN (?)', array_keys($blocksId))->where('active = ?', 0);
                $ids = $modelBlock->getAdapter()->fetchCol($select);
                foreach ($ids as $id) {
                    unset($blocksId[$id]);
                }
            }
            foreach ($blockLinks as $link) {
                // Skip inactive blocks
                if (!isset($blocksId[$link["block"]])) {
                    continue;
                }
                // negative order as global disabling
                if ($link["order"] <= 0) {
                    $blocksDisable[abs($link["order"])][] = $link["block"];
                // positive page as module local page
                } elseif ($link["page"] > 0) {
                    $blocksLocal[$link["page"]][$link["position"]][$link["order"]][] = $link["block"];
                }
            }

            // Get global blocks
            foreach ($blockLinks as $link) {
                // Skip invalide global blocks: page <> 0 or block disabled or not positive order
                if ($link["page"] != 0 || isset($blocksDisable[$link["id"]]) || $link["order"] <= 0) continue;
                // Valid global blocks
                $blocksGlobal[$link["position"]][$link["order"]][] = $link["block"];
            }

            $blocksAllowed = null;
            if (!is_null($role) && $role != \Xoops_Acl::ADMIN && !empty($blocksId)) {
                //$clause = new Xoops_Zend_Db_Clause("item IN (?)", array_keys($blocksId));
                $clause = new \Xoops_Zend_Db_Clause("resource IN (?)", array_keys($blocksId));
                $acl = new \Xoops_Acl("block");
                //$blocksAllowed = $acl->getItems("block", $clause);
                $blocksAllowed = $acl->getResources($clause);
            }

            // placeholder for ordered block IDs
            $pageRow = array();
            $positionGlobal = array_keys($blocksGlobal);
            ksort($blocksLocal);
            $pageList = array_unique(array_values($pages));
            foreach ($pageList as $page) {
                //\Debug::e("page-" . $page);
                // page local blocks
                $blocksPage = isset($blocksLocal[$page]) ? $blocksLocal[$page] : array();
                // Available positions for local blocks
                $positions = array_keys($blocksPage);
                //if ($page != 0) {
                    // Available positions for global blocks
                    $positions = array_unique(array_merge($positions, $positionGlobal));
                //}
                //\Debug::e($positions);
                foreach ($positions as $pos) {
                    //Debug::e("position-" . $pos);
                    // Available orders
                    $orders = array();
                    // global block orders
                    if (isset($blocksGlobal[$pos])) {
                        $orders = array_keys($blocksGlobal[$pos]);
                    }
                    //Debug::e($orders);
                    // page local block orders
                    if ($page != 0 && isset($blocksPage[$pos])) {
                        $ordersLocal = array_keys($blocksPage[$pos]);
                        $orders = array_merge($orders, $ordersLocal);
                    }
                    //Debug::e($blocksPage[$pos]);
                    //Debug::e($orders);
                    $orders = array_unique($orders);
                    sort($orders);
                    //Debug::e($orders);
                    // ordered blocks
                    $blocksOrdered = array();
                    foreach ($orders as $order) {
                        //\Debug::e("order-" . $order);
                        // global ordered blocks
                        if (isset($blocksGlobal[$pos][$order])) {
                            $blocks = $blocksGlobal[$pos][$order];
                            $blocksOrdered = array_merge($blocksOrdered, $blocks);
                        }
                        // page local ordered blocks
                        if (/*$page != 0 && */isset($blocksPage[$pos][$order])) {
                            $blocksOrdered = array_merge($blocksOrdered, $blocksPage[$pos][$order]);
                        }
                        //\Debug::e($blocksOrdered);
                    }
                    //\Debug::e("$page-$pos");
                    //\Debug::e($blocksOrdered);
                    // Allowed page block IDs
                    if (!is_null($blocksAllowed)) {
                        $blocksOrdered = array_intersect($blocksOrdered, $blocksAllowed);
                    }
                    $pageRow[$page][$pos] = $blocksOrdered;
                    //\Debug::e($blocksOrdered);
                }
            }
            //Debug::e($pageRow);
            // index => page ID
            foreach ($pages as $key => &$item) {
                if (isset($pageRow[$item])) {
                    $item = $pageRow[$item];
                } else {
                    $item = array();
                }
            }
            //Debug::e($pages);
            if (!isset($pages[$module]) && isset($pageRow[0])) {
                $pages[$module] = $pageRow[0];
            }
        //}
        return $pages;
    }

    public function read($module, $role = null)
    {
        $options = compact('module', 'role');
        return $this->loadData($options);
    }

    public function create($module, $role = null)
    {
        self::delete($module, $role);
        self::read($module, $role);
        return true;
    }

    public function delete($module = null, $role = null)
    {
        if (empty($role)) {
            $options = compact('module');
        } else {
            $options = compact('module', 'role');
        }
        return $this->cache->clean('matchingTag', self::createTags($options));
    }

    public function flush($module = null)
    {
        return self::delete($module);
    }
}