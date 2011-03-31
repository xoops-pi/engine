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
 * Helper for adding link element for HTML head section
 * @see Xoops_Zend_View_Helper_HeadLink
 *
 * <code>
 * XOOPS::registry('view')->addLink('rel', 'href', array());
 * </code>
 */

class Xoops_Zend_View_Helper_AddLink extends Zend_View_Helper_Placeholder_Container_Standalone
{
    /**
     * @var string registry key
     */
    protected $_regKey = 'Xoops_Zend_View_Helper_AddLink';

    /**
     * Constructor
     *
     * Use PHP_EOL as separator
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setSeparator(PHP_EOL);
    }

    /**
     * addLink() - View Helper Method
     *
     * Returns current object instance. Optionally, allows passing array of
     * values to build link.
     *
     * @return Xoops_Zend_View_Helper_AddLink
     */
    public function addLink($rel, $href = '', $attributes = array())
    {
        $arributes['href'] = $href;
        $arributes['rel'] = $rel;
        $this->view->headLink($arributes);
        return $this;
    }
}
