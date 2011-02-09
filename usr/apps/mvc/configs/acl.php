<?php
/**
 * Mvc module ACL config
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
 * @category        Xoops_Module
 * @package         Mvc
 * @version         $Id$
 */

return array(
    "resources" => array(
        "module"    => array(
            // test
            array(
                "name"          => "test",
                "title"         => XOOPS::_("MVC test resource"),
                "privileges"    => array(
                    "read"  => array(
                        "title"     => XOOPS::_("MVC read privilege"),
                        "access"    => array(
                            "guest"     => 1,
                            "member"    => 1,
                        )
                    ),
                    "write"  => array(
                        "title" => XOOPS::_("MVC write privilege"),
                        "access"    => array(
                            "guest"     => 0,
                            "member"    => 1,
                        )
                    ),
                    "manage"  => array(
                        "title" => XOOPS::_("MVC management privilege"),
                        "access"    => array(
                            "guest"     => 0,
                            "moderator" => 1,
                        )
                    ),
                )
            ),
        ),
    ),
);