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
 * @copyright       Xoops Engine http://www.xoopsengine.org/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Config
 * @version         $Id$
 */

class Xoops_Zend_Config_Writer_Ini extends Zend_Config_Writer_Ini
{

    /**
     * Render a Zend_Config into a INI config string.
     *
     * @since 1.10
     * @return string
     */
    public function render()
    {
        $iniString = parent::render();
        $suffix = strtolower(pathinfo($this->_filename, PATHINFO_EXTENSION));
        if ('php' === $suffix) {
            $iniString = ';<?php __halt_compiler();' . PHP_EOL . PHP_EOL . $iniString;
        }

        return $iniString;
    }
}
