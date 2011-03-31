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
 * Helper for adding script elements for HTML head section
 * @see Xoops_Zend_View_Helper_HeadScript
 *
 * <code>
 * XOOPS::registry('view')->addScript('src', array(), 'content');
 * </code>
 */

class Xoops_Zend_View_Helper_AddScript extends Zend_View_Helper_Placeholder_Container_Standalone
{
    /**
     * @var string registry key
     */
    protected $_regKey = 'Xoops_Zend_View_Helper_AddScript';

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
     * addScript() - View Helper Method
     *
     * Returns current object instance. Optionally, allows passing array of
     * values to build link.
     *
     * @return Xoops_Zend_View_Helper_AddMeta
     */
    public function addScript($src = '', $attributes = array(), $content = '')
    {
        $arributes['src'] = $src;
        if (empty($content)) {
            $this->view->headScript()->appendFile($src, 'text/javascript', $attributes);
        } else {
            $this->view->headScript()->appendScript($content, 'text/javascript', $attributes);
        }
        return $this;
    }
}
