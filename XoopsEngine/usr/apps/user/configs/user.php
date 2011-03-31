<?php
/**
 * User module profile meta config
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
 * @package         User
 * @version         $Id$
 */

/**
 * schema
 *
 *  category: false - not present in edit or display, used for custom, like avatar
 *  title
 *  attribute: for column in profile table
 *  view: display callback => method in module profile class
 *  edit: form element for user edit
 *  admin: form element for admin edit. Use "edit" if not specified
 *  search: form element for search input. Use "edit" if not specified; use "admin" if "edit" not specified
 *  options: used for view and edit
 *  updated: indicates if the meta should be recreated
 */
return array(
    // Basic
    "location"  => array(
        "updated"   => false,
        "category"  => "basic",
        "title"     => "Location",
        "attribute" => "varchar(255) default NULL",
    ),
    "rank"  => array(
        "updated"   => false,
        "category"  => "basic",
        "title"     => "Rank",
        "attribute" => "smallint(5) unsigned default NULL",
        "view"      => "rank",
        "edit"      => false,
        "admin"     => array(
            "type"      => "rank",
            "module"    => 1,
        ),
        "search"    => array(
            "type"      => "ranksearch",
            "module"    => 1,
        ),
    ),
    "gender"        => array(
        "category"  => "basic",
        "title"     => "Gender",
        "attribute" => "ENUM('male', 'female') default NULL",
        "view"      => "gender",
        "edit"      => "gender",
    ),
    "birthday"  => array(
        "category"  => "basic",
        "title"     => "Birthday",
        "attribute" => "DATE default NULL",
        "view"      => "birthday",
        "edit"      => "date",
    ),
    "bio"   => array(
        "category"  => "basic",
        "title"     => "Bio",
        "attribute" => "tinytext default NULL",
        "edit"      => array(
            "type"      => "textarea",
            "options"   => array(
                "rows"  => 2,
                "cols"  => 50
            ),
        ),
        "search"    => "text",
    ),
    "interest"  => array(
        "category"  => "basic",
        "title"     => "Interests",
        "attribute" => "varchar(255) default NULL",
    ),
    "occupation"    => array(
        "category"  => "basic",
        "title"     => "Occupation",
        "attribute" => "varchar(255) default NULL",
    ),
    "signature" => array(
        "category"  => "basic",
        "title"     => "Signature",
        "attribute" => "tinytext default NULL",
        "view"      => "signature",
        "edit"      => array(
            "type"      => "textarea",
            "options"   => array(
                "rows"  => 2,
                "cols"  => 50
            ),
        ),
        "search"    => "text",
    ),

    // Avatar: upload/aaa.jpg => upload/avatar/aaa.jpg or img/aaa.jpg => img/avatar/aaa.jpg
    "avatar"    => array(
        "category"  => false,
        "title"     => "Avatar",
        "attribute" => "varchar(255) NOT NULL default ''",
        "edit"      => false,
        "admin"     => "text",
        "view"      => "avatar",
        "search"    => false,
   ),

    // Contact
    "msn" => array(
        "category"  => "contact",
        "title"     => "MSN",
        "attribute" => "varchar(64) default NULL",
    ),
    "aim" => array(
        "category"  => "contact",
        "title"     => "AOL messenger",
        "attribute" => "varchar(64) default NULL",
    ),
    "yim" => array(
        "category"  => "contact",
        "title"     => "Yahoo messenger",
        "attribute" => "varchar(64) default NULL",
    ),
    "gtalk" => array(
        "category"  => "contact",
        "title"     => "Google Talk",
        "attribute" => "varchar(64) default NULL",
    ),
    "skype" => array(
        "category"  => "contact",
        "title"     => "Skype",
        "attribute" => "varchar(64) default NULL",
    ),
    "qq" => array(
        "category"  => "contact",
        "title"     => "QQ",
        "attribute" => "varchar(64) default NULL",
    ),
    "homepage"  => array(
        "category"  => "contact",
        "title"     => "Website",
        "attribute" => "varchar(64) default NULL",
        "view"      => "link",
        "edit"      => "link",
        "search"    => "text",
    ),

    // Preference
    "theme" => array(
        "category"  => "preference",
        "title"     => "Theme",
        "attribute" => "varchar(64) default NULL",
        "edit"      => "theme"
    ),
    "timezone"  => array(
        "category"  => "preference",
        "title"     => "Timezone",
        "attribute" => "varchar(64) default NULL",
        "view"      => "timezone",
        "edit"      => "timezone"
    ),
    "comment_mode"  => array(
        "category"  => "preference",
        "title"     => "Comment display mode",
        "attribute" => "varchar(64) NOT NULL default 'flat'",
        "options"   => array(
            "nest"      => "Nested",
            "flat"      => "Flat",
            "thread"    => "threaded",
        ),
        "edit"      => array(
            "type"          => "select",
            "options"       => array(
                "value"         => "flat",
            ),
        ),
    ),
    "comment_order"  => array(
        "category"  => "preference",
        "title"     => "Comment display order",
        "attribute" => "tinyint(1) unsigned NOT NULL default '0'",
        "options"   => array(
            "0"     => "Oldest first",
            "1"     => "Newest first",
        ),
        "edit"      => "select",
    ),
    "notify_method" => array(
        "category"  => "preference",
        "title"     => "Notifying method",
        "attribute" => "tinyint(1) unsigned NOT NULL default '1'",
        "options"   => array(
            "0"     => "Disable",
            "1"     => "Message",
            "2"     => "Email",
        ),
        "edit"      => array(
            "type"          => "select",
            "options"       => array(
                "value"         => 1,
            ),
        ),
    ),
    "notify_mode"  => array(
        "category"  => "preference",
        "title"     => "Notification sending mode",
        "attribute" => "tinyint(1) unsigned NOT NULL default '0'",
        "options"   => array(
            "0"     => "Always send",
            "1"     => "Send once",
            "2"     => "Send once and wait for next login",
        ),
        "edit"      => "select",
    ),
    "accept_email" => array(
        "category"  => "preference",
        "title"     => "Accept emails",
        "attribute" => "tinyint(1) unsigned NOT NULL default '1'",
        "options"   => array(
            "0"     => "No",
            "1"     => "Yes",
        ),
        "edit"      => array(
            "type"          => "radio",
            "options"       => array(
                "value"         => 1,
                "separator"     => " ",
            ),
        ),
    ),

    // Stats
    "create_time"   => array(
        "category"  => "stats",
        "title"     => "Time on creation",
        "attribute" => "int(10) default NULL",
        "view"      => "time",
        "edit"      => false
    ),
    "create_ip"   => array(
        "category"  => "stats",
        "title"     => "IP for creation",
        "attribute" => "char(15) default NULL",
        "edit"      => false
    ),
    "posts"     => array(
        "category"  => "stats",
        "title"     => "Count of posting",
        "attribute" => "mediumint(8) unsigned NOT NULL default '0'",
        "edit"      => false,
        "admin"     => "Text"
    ),

    // Education, work
);