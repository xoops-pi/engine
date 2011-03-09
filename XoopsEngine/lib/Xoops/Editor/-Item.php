<?php
/**
 * Xoops Engine Editor Interface
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
 * @package         Xoops_Editor
 * @version         $Id$
 */

namespace Xoops\Editor;

interface Item
{
    /**
     * Constructor
     *
     * @param  array $config
     * @return void
     */
    public function __construct($config = array());

    /**
     * Renders editor contents
     *
     * @param  Zend_View_Abstract $view
     * @return string
     */
    public function render(\Zend_View_Interface $view);
}