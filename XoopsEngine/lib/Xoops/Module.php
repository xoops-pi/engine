<?php
/**
 * XOOPS module handler
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
 * @version         $Id$
 */

class Xoops_Module extends \Kernel\Module
{
    /**
     * Load model data
     *
     * @return
     */
    public function loadModel()
    {
        $this->model = XOOPS::getModel("module")->load($this->dirname);
        return $this->model;
    }

    /**
     * Read config data from storage
     */
    public function readConfig($category)
    {
        return XOOPS::service('registry')->config->read($this->dirname, $category);
    }
}