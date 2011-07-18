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
 * Helper for setting and retrieving script elements for HTML body foot section
 * @see Zend_View_Helper_HeadScript
 *
 * <code>
 * XOOPS::registry('view')->footScript('file', 'filename', 'append', array(), 'text/javascript');
 * XOOPS::registry('view')->footScript('script', 'source content', 'append', array(), 'text/javascript');
 * </code>
 */

class Xoops_Zend_View_Helper_FootScript extends Xoops_Zend_View_Helper_HeadScript
{
    /**
     * Registry key for placeholder
     * @var string
     */
    protected $_regKey = 'Xoops_Zend_View_Helper_FootScript';

    /**
     * Return footScript object
     *
     * Returns footScript helper object; optionally, allows specifying a script
     * or script file to include.
     *
     * @param  string $mode Script or file
     * @param  string $spec Script/url
     * @param  string $placement Append, prepend, or set
     * @param  array $attrs Array of script attributes
     * @param  string $type Script type and/or array of script attributes
     * @return Xoops_Zend_View_Helper_FootScript
     */
    public function footScript($mode = Zend_View_Helper_HeadScript::FILE, $spec = null, $placement = 'APPEND', array $attrs = array(), $type = 'text/javascript')
    {
        parent::headScript($mode, $spec, $placement, $attrs, $type);
        return $this;
    }
}