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

class Xoops_Zend_Form_Element_Timezone extends Zend_Form_Element_Select
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
        $timezoneList = DateTimeZone::listIdentifiers();
        $timezoneLocale = Xoops_Zend_Locale::getTranslationList("CityToTimezone");
        $selectList = array();
        $misc = array();
        foreach ($timezoneList as $zone) {
            $element = isset($timezoneLocale[$zone]) ? $timezoneLocale[$zone] : $zone;;
            if (preg_match('/^([^\/]+)\//', $zone, $matches)) {
                $optKey = $matches[1];
                $selectList[$optKey][$zone] = $element;
            } else {
                $misc[$zone] = $element;
            }
        }
        if (!empty($misc)) {
            $selectList["Others"] = $misc;
        }
        $this->setMultiOptions($selectList);
    }
}
