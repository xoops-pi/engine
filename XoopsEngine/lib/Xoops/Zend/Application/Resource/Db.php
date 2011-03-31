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

class Xoops_Zend_Application_Resource_Db extends Zend_Application_Resource_Db
{
    const DEFAULT_REGISTRY_KEY = 'db';

    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return Zend_Db_Adapter_Abstract|null
     */
    public function init()
    {
        $options = $this->getOptions();

        if (!isset($options['profiler']['class'])) {
            $options['profiler']['class'] = "Xoops_Zend_Db_Profiler";
        }
        if (!isset($options['profiler']['enabled'])) {
            $options['profiler']['enabled'] = (XOOPS::config('environment') == "production") ? false : true;
        }
        if (empty($options['profiler']['enabled'])) {
            $options['profiler'] = false;
        }
        $db = Xoops_Zend_Db::factory($options['adapter'], $options);
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
        Xoops_Zend_Db_Table::setDefaultMetadataCache(XOOPS::persist()->getHandler());

        $key = (isset($options['registry_key']) && !is_numeric($options['registry_key']))
            ? $options['registry_key']
            : 'db';
        XOOPS::registry($key, $db);

        return $db;
    }
}