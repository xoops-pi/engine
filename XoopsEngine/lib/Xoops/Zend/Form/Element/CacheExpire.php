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
 * @package         Form
 * @version         $Id$
 */

class Xoops_Zend_Form_Element_CacheExpire extends Zend_Form_Element_Select
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
        $options = array(
            '-1'        => XOOPS::_('Disable'),
            '0'         => XOOPS::_('No Cache'),
            '30'        => sprintf(XOOPS::_('%d seconds'), 30),
            '60'        => XOOPS::_('1 minute'),
            '300'       => sprintf(XOOPS::_('%d minutes'), 5),
            '1800'      => sprintf(XOOPS::_('%d minutes'), 30),
            '3600'      => XOOPS::_('1 hour'),
            '18000'     => sprintf(XOOPS::_('%d hours'), 5),
            '86400'     => XOOPS::_('1 day'),
            '259200'    => sprintf(XOOPS::_('%d days'), 3),
            '604800'    => XOOPS::_('1 week'),
            '2592000'   => XOOPS::_('1 month')
        );
        $this->setMultiOptions($options);
    }
}
