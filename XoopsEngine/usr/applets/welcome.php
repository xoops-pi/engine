<?php
/**
 * Welcome applet Xoops Engine
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
 * @category        Applications
 * @package         Applet
 * @version         $Id$
 */

namespace Applet;

class Welcome extends \Application\Applet
{
    /**
     * Renders content
     *
     * @return string
     */
    public function render()
    {
        if ($name = \XOOPS::registry("user")->name) {
            $message = "Dear " . $name . ", welcome back!";
        } else {
            $message = "Oh dear, let's pick up an account!";
        }

        return $message;
    }
}