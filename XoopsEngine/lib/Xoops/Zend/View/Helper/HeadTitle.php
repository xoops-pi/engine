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
 * Helper for setting and retrieving title element for HTML head
 * @see Zend_View_Helper_HeadTitle
 *
 * <code>
 *  XOOPS::registry('view')->headTitle('Title');
 *  XOOPS::registry('view')->headTitle('Title', 'append');
 *  XOOPS::registry('view')->headTitle('Title', 'set');
 *  XOOPS::registry('view')->headTitle('Title', 'prepend');
 * </code>
 */
class Xoops_Zend_View_Helper_HeadTitle extends Zend_View_Helper_HeadTitle
{
    /**
     * Retrieve placeholder for title element and optionally set state
     *
     * @param  string $title
     * @param  string $setType potential value: set - replace existing content; append - append to existing content, default; prepend - prepend to existing content
     * @return Zend_View_Helper_HeadTitle
     */
    public function headTitle($title = null, $setType = null)
    {
        parent::headTitle($title, $setType ? strtoupper($setType) : null);
        return $this;
    }
}