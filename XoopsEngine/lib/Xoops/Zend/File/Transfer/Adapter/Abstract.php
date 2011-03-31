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
 * @package         File
 * @version         $Id$
 */

abstract class Xoops_Zend_File_Transfer_Adapter_Abstract extends Zend_File_Transfer_Adapter_Abstract
{
    /**
     * Retrieve plugin loader for validator or filter chain
     *
     * Instantiates with default rules if none available for that type. Use
     * 'filter' or 'validate' for $type.
     *
     * @param  string $type
     * @return Zend_Loader_PluginLoader
     * @throws Zend_File_Transfer_Exception on invalid type.
     */
    public function getPluginLoader($type)
    {
        $type = strtoupper($type);
        switch ($type) {
            case self::FILTER:
            case self::VALIDATE:
                $prefixSegment = ucfirst(strtolower($type));
                $pathSegment   = $prefixSegment;
                if (!isset($this->_loaders[$type])) {
                    $paths         = array(
                        'Zend_' . $prefixSegment . '_'              => 'Zend/' . $pathSegment . '/',
                        'Zend_' . $prefixSegment . '_File'          => 'Zend/' . $pathSegment . '/File',
                        'Xoops_Zend_' . $prefixSegment . '_'        => 'Xoops/Zend/' . $pathSegment . '/',
                        'Xoops_Zend_' . $prefixSegment . '_File'    => 'Xoops/Zend/' . $pathSegment . '/File',
                    );

                    $this->_loaders[$type] = new Zend_Loader_PluginLoader($paths);
                } else {
                    /*
                    $loader = $this->_loaders[$type];
                    $prefix = 'Zend_' . $prefixSegment . '_File_';
                    if (!$loader->getPaths($prefix)) {
                        $loader->addPrefixPath($prefix, str_replace('_', '/', $prefix));
                    }
                    */
                }
                return $this->_loaders[$type];
            default:
                throw new Zend_File_Transfer_Exception(sprintf('Invalid type "%s" provided to getPluginLoader()', $type));
        }
    }
}
