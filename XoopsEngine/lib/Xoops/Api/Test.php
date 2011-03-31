<?php
/**
 * XOOPS Test API
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
 * @package         Xoops_Api
 * @version         $Id$
 */

class Xoops_Api_Test extends Xoops_Api
{
    public function put($args = array())
    {
        Debug::display(__METHOD__ . " called with arguments:");
        Debug::display$args);
    }

    public function post($args = array())
    {
        Debug::display(__METHOD__ . " called with arguments:");
        Debug::display$args);
    }

    public function get($args = array())
    {
        Debug::display(__METHOD__ . " called with arguments:");
        Debug::display$args);
    }

    public function delete($args = array())
    {
        Debug::display(__METHOD__ . " called with arguments:");
        Debug::display$args);
    }

    public function test($args)
    {
        Debug::display("Called in test:");
        Debug::display$args);
    }
}