<?php
/**
 * System module ACL config
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
 * @category        Xoops_Module
 * @package         System
 * @version         $Id$
 */

/**
 * ACL specs
 *
 *  return array(
 *      "roles" => array(
 *          "roleName"  => array(
 *              "title"     => "Title",
 *              "parents"   => array("parent")
 *          ),
 *          ...
 *      ),
 *      "resources" => array(
 *          "module" => array(
 *              array(
 *                  "name"          => "category",
 *                  "title"         => "Category Title",
 *                  "parent"        => "parentCategory"
 *                  "rules"         => array(
 *                      "guest"     => 1,
 *                      "member"    => 1
 *                  ),
 *                  "privileges"    => array(
 *                      "read"      => array(
 *                          "title" => "Read articles",
 *                      ),
 *                      "post"      => array(
 *                          "title" => "Post articles",
 *                          "rules" => array(
 *                              "guest"     => 0,
 *                          ),
 *                      ),
 *                      "delete"    => array(
 *                          "title" => "Post articles",
 *                          "rules" => array(
 *                              "guest"     => 0,
 *                              "member"    => 0,
 *                          ),
 *                      ),
 *                  ),
 *              ),
 *              ...
 *          ),
 *          "front" => array(
 *              array(
 *                  "name"          => "Name",
 *                  "controller"    => "controllerName",
 *                  "title"         => "Title",
 *                  "parent"        => "parentName"
 *              ),
 *              ...
 *          ),
 *          "admin"  => array(
 *              array(
 *                  "name"          => "Name",
 *                  "controller"    => "controllerName",
 *                  "title"         => "Title",
 *                  "parent"        => "parentName"
 *                  "rules"         => array(
 *                      "roleA" => 1,
 *                      "roleB" => 0
 *                  ),
 *                  "privileges"    => array(
 *                      "nameA"     => array(
 *                          "title" => "privilegeName",
 *                          "rules" => array(
 *                              "roleA" => 1,
 *                              "roleB" => 0
 *                          ),
 *                      ),
 *                  ),
 *              ),
 *              ...
 *          ),
 *          ...
 *      ),
 *  );
 */

// System settings, don't change
return array(
    "roles" => array(
        // System administrator or webmaster
        "admin"     => array("title"    => _SYSTEM_MI_ACL_ROLE_ADMIN),
        // User
        "member"    => array("title"    => _SYSTEM_MI_ACL_ROLE_MEMBER),
        // Visitor
        "guest"     => array("title"    => _SYSTEM_MI_ACL_ROLE_GUEST),
        // Inactive user
        "inactive"  => array("title"    => _SYSTEM_MI_ACL_ROLE_INACTIVE),
        // Banned user
        "banned"    => array("title"    => _SYSTEM_MI_ACL_ROLE_BANNED),
        // Module/section moderator or administrator
        "moderator" => array(
            "title"     => _SYSTEM_MI_ACL_ROLE_MODERATOR,
            "parents"   => array("member")
        ),
    ),
    "resources" => array(
        // Front section
        "front" => array(
            // global public
            array(
                "module"        => "system",
                "name"          => "public",
                "title"         => _SYSTEM_MI_ACL_RESOURCE_PUBLIC,
                "access"        => array(
                    "guest"     => 1,
                    "member"    => 1,
                ),
            ),
            // global guest
            array(
                "module"        => "system",
                "name"          => "guest",
                "title"         => _SYSTEM_MI_ACL_RESOURCE_GUEST,
                "access"        => array(
                    "guest"     => 1,
                    "member"    => 0,
                ),
            ),
            // global member
            array(
                "module"        => "system",
                "name"          => "member",
                "title"         => _SYSTEM_MI_ACL_RESOURCE_MEMBER,
                "access"        => array(
                    "guest"     => 0,
                    "member"    => 1,
                ),
            ),
            // global moderate
            array(
                "module"        => "system",
                "name"          => "moderate",
                "title"         => _SYSTEM_MI_ACL_RESOURCE_MODERATE,
                "access"        => array(
                    "guest"     => 0,
                    "moderator" => 1,
                ),
            ),
        ),
        // Admin section
        "admin" => array(
            // Basic admin resource
            array("name"        => "admin",
                "title"         => _SYSTEM_MI_ACL_RESOURCE_ADMIN),

            // System specs
            // preferences
            array("name"        => "preference",
                "title"         => _SYSTEM_MI_ACL_RESOURCE_PREFERENCE),
            // appearance
            array("name"        => "appearance",
                "title"         => _SYSTEM_MI_ACL_RESOURCE_APPEARANCE),
            // permissions
            array("name"        => "permission",
                "title"         => _SYSTEM_MI_ACL_RESOURCE_PERMISSION),
            // modules
            array("name"        => "module",
                "title"         => _SYSTEM_MI_ACL_RESOURCE_MODULE),
            // plugins
            array("name"        => "plugin",
                "title"         => _SYSTEM_MI_ACL_RESOURCE_PLUGIN),
            // themes
            array("name"        => "theme",
                "title"         => _SYSTEM_MI_ACL_RESOURCE_THEME),
            // comment
            array("name"        => "comment",
                "title"         => _SYSTEM_MI_ACL_RESOURCE_COMMENT),
            // notification
            array("name"        => "notification",
                "title"         => _SYSTEM_MI_ACL_RESOURCE_NOTIFICATION),
            // event
            array("name"        => "event",
                "title"         => _SYSTEM_MI_ACL_RESOURCE_EVENT),
            // toolkit
            array("name"        => "toolkit",
                "title"         => _SYSTEM_MI_ACL_RESOURCE_TOOLKIT),
        ),
        // Module resources
        "module"    => array(
            // test
            array(
                "name"          => "test",
                "title"         => XOOPS::_("Test resource"),
                /*
                "access"    => array(
                    "guest"     => 1,
                    "member"    => 1,
                ),
                */
                "privileges"    => array(
                    "read"  => array(
                        "title" => XOOPS::_("Read privilege"),
                        "access"    => array(
                            "guest"     => 1,
                            "member"    => 1,
                        )
                    ),
                    "write"  => array(
                        "title" => XOOPS::_("Write privilege"),
                        "access"    => array(
                            "guest"     => 0,
                            "member"    => 1,
                        )
                    ),
                    "manage"  => array(
                        "title" => XOOPS::_("Management privilege"),
                        "access"    => array(
                            "guest"     => 0,
                            "moderator" => 1,
                        )
                    ),
                )
            ),
            // second test
            array(
                "name"          => "test2",
                "title"         => XOOPS::_("Test resource 2"),
                "privileges"    => array(
                    "read"  => array(
                        "title" => XOOPS::_("Read privilege 2"),
                        "access"    => array(
                            "guest"     => 0,
                            "member"    => 1,
                        )
                    ),
                    "write"  => array(
                        "title" => XOOPS::_("Write privilege 2"),
                        "access"    => array(
                            "guest"     => 0,
                        )
                    ),
                    "manage"  => array(
                        "title" => XOOPS::_("Management privilege 2"),
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