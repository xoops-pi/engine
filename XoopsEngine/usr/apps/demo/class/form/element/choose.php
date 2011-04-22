<?php
/**
 * Use theme selection element as an example for block custom option field
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
 * @package         Demo
 * @version         $Id$
 */

class App_Demo_Form_Element_Choose extends Zend_Form_Element_Select
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
        // Get current module, nothing more than demonstrating the use of module dirname
        $module = $this->getAttrib('module');

        $themes = XOOPS::service("registry")->theme->read();
        foreach ($themes as $key => &$theme) {
            $theme = $theme["name"] . " ({$key}) [{$module}]";
        }
        $this->setMultiOptions($themes);
    }
}