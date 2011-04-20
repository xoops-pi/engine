<?php
/**
 * XOOPS monitor registry
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
 * @package         Xoops_Core
 * @subpackage      Registry
 * @version         $Id$
 */

namespace Engine\Xoops\Registry;

class Monitor extends \Kernel\Registry
{
    /**
     * Load raw data
     *
     * @param   array   $options not used
     * @return  array   keys: dirname => callback
     */
    protected function loadDynamic($options = null)
    {
        $model = \Xoops::getModel("monitor");

        $select = $model->select()->from($model, array("module", "callback"));
        $modules = $model->getAdapter()->fetchPairs($select);

        return $modules;
    }

    public function read()
    {
        return $this->loadData();
    }

    public function create()
    {
        self::delete();
        self::read();
        return true;
    }

    public function delete()
    {
        return $this->cache->clean('matchingTag', self::createTags());
    }

    public function flush()
    {
        return self::delete();
    }
}