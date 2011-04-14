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
 * Helper for fetching and rendering blocks on a page
 * @see Engine\Xoops\Registry\Block
 */

class Xoops_Zend_View_Helper_Blocks extends Zend_View_Helper_Abstract
{
    /**
     * Build layout block contents
     *
     * @access public
     *
     * @param  object   $request {@link Zend_Controller_Request_Abstract}
     * @return array    associative array of blocks
     */
    public function blocks($request)
    {
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        $info = XOOPS::service('registry')->block->read($module, XOOPS::registry("user")->role);
        $blocks = array();
        if (isset($info["{$module}-{$controller}-{$action}"])) {
            $blocks = $info["{$module}-{$controller}-{$action}"];
        } elseif (isset($info["{$module}-{$controller}"])) {
            $blocks = $info["{$module}-{$controller}"];
        } elseif (isset($info["{$module}"])) {
            $blocks = $info["{$module}"];
        }
        $blockIds = array();
        foreach ($blocks as $position => $ids) {
            $blockIds = array_merge($blockIds, $ids);
        }
        if (empty($blockIds)) {
            return array();
        }
        $blockIds = array_unique($blockIds);
        $modelBlock = XOOPS::getModel("block");
        $select = $modelBlock->select()->where("id IN (?)", $blockIds);
        $result = $modelBlock->fetchAll($select);
        $blockRows = array();
        foreach ($result as $row) {
            $blockRows[$row->id] = $row;
        }

        $layoutZones = array(
            0   => 'left',
            1   => 'right',
            2   => 'topleft',
            3   => 'topcenter',
            4   => 'topright',
            5   => 'bottomleft',
            6   => 'bottomcenter',
            7   => 'bottomright'
        );

        $layoutBlocks = array();
        foreach ($blocks as $position => $ids) {
            foreach ($ids as $id) {
                if ($result = $this->view->block($blockRows[$id])) {
                    $layoutBlocks[$layoutZones[$position]][] = $result;
                }
            }
        }

        return $layoutBlocks;
    }
}