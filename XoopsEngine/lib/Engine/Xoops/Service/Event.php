<?php
/**
 * XOOPS event service class
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
 * @package         Xoops_Service
 * @version         $Id$
 */

namespace Engine\Xoops\Service;

class Event extends \Kernel\Service\Event
{
    /**
     * Load observers of an event
     *
     * @param string    $module
     * @param string    $event
     * @return array
     */
    public function loadObservers($module, $event)
    {
        return \Xoops::service('registry')->event->read($module, $event);
    }
}