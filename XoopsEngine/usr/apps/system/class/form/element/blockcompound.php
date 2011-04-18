<?php
/**
 * Block compound renderer selection
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
 * @category        Xoops_Module
 * @package         System
 * @version         $Id$
 */

class App_System_Form_Element_Blockcompound extends Zend_Form_Element_Select
{
    /**
     * Constructor
     *
     * @param  string|array|Zend_Config $spec Element name or configuration
     * @param  string|array|Zend_Config $options Element value or configuration
     * @return void
     */
    public function __construct($spec, $options = null)
    {
        parent::__construct($spec, $options);
        $this->setServiceOptions();
    }

    protected function setServiceOptions()
    {
        // Available renderers
        $iterator = new DirectoryIterator(Xoops::path('app/system/class/block'));
        // Container for available renderers
        $list = array();
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isFile()) {
                continue;
            }
            $filename = $fileinfo->getFilename();
            if ('.php' === substr($filename, -4)) {
                $filename = substr($filename, 0, -4);
                $list[$filename] = $filename;
            }
        }

        $this->setMultiOptions($list);
    }
}