<?php
/**
 * Lite event service class
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       The Xoops Engine http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         Xoops_Service
 * @version         $Id$
 */

namespace Engine\Lite\Service;

class Event extends \Engine\Xoops\Service\Event
{
    /**
     * Trigger (or notify) callbacks registered to an event
     *
     * @param string        $event  event name
     * @param object|array  $object object or array
     * @return boolean
     */
    public function trigger($event, $object = null, $module = null)
    {
        // Not implemented yet;
    }
}