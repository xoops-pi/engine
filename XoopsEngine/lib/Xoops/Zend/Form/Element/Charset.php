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

class Xoops_Zend_Form_Element_Charset extends Zend_Form_Element_Select
{
    protected $charset;

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
        // Available translations
        $iterator = new DirectoryIterator(Xoops::path('language'));
        // Container for available locales
        $localeList = array();
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                continue;
            }

            $localeFile = $fileinfo->getPathname() . '/locale.ini.php';
            if (!file_exists($localeFile)) {
                continue;
            }

            $locale = parse_ini_file($localeFile);
            $charset = $locale['charset'];
            $localeList[$charset] = $charset;
        }

        $this->setMultiOptions($localeList);
    }

    /**
     * Set value
     *
     * @param  string $value
     * @return Xoops_Zend_Form_Element_Charset
     */
    public function setValue($value)
    {
        $this->charset = $value;
        return $this;
    }

    /**
     * Retrieve element values
     *
     * @return string
     */
    public function getValue($suppressArrayNotation = false)
    {
        $value = $this->charset;

        return $value;
    }
}