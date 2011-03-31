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
 * @package         Application
 * @subpackage      Resource
 * @version         $Id$
 */

class Xoops_Zend_Application_Resource_Modules extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var ArrayObject
     */
    protected $_bootstraps;

    /**
     * Constructor
     *
     * @param  mixed $options
     * @return void
     */
    public function __construct($options = null)
    {
        $this->_bootstraps = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
        parent::__construct($options);
    }

    /**
     * Initialize modules
     *
     * @return array
     * @throws Zend_Application_Resource_Exception When bootstrap class was not found
     */
    public function init()
    {
        $bootstraps = XOOPS::service('registry')->bootstrap->read();
        if (empty($bootstraps)) {
            return;
        }
        $bootstrap = $this->getBootstrap();
        foreach ($bootstraps as $bootstrapClass) {
            if (!class_exists($bootstrapClass)) {
                continue;
            }

            $moduleBootstrap = new $bootstrapClass($bootstrap);
            $moduleBootstrap->bootstrap();
            $this->_bootstraps[] = $moduleBootstrap;
        }

        return $this->_bootstraps;
    }

    /**
     * Get bootstraps that have been run
     *
     * @return ArrayObject
     */
    public function getExecutedBootstraps()
    {
        return $this->_bootstraps;
    }
}
