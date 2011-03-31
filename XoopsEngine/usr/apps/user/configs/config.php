<?php
/**
 * User module config
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

return array(
    "categories"    => array(
        array(
            "name"          => "_USER_AM_ACCOUNT",
            "key"           => "account",
            "order"         => 1,
            "description"   => "",
        ),
        array(
            "name"          => "_USER_AM_LOGIN",
            "key"           => "login",
            "order"         => 2,
            "description"   => "",
        ),
        array(
            "name"          => "_USER_AM_REGISTER",
            "key"           => "register",
            "order"         => 3,
            "description"   => "",
        ),
        array(
            "name"          => "_USER_AM_AVATAR",
            "key"           => "avatar",
            "order"         => 4,
            "description"   => "",
        ),
        array(
            "name"          => "_USER_AM_ADMIN",
            "key"           => "admin",
            "order"         => 5,
            "description"   => "",
        ),
    ),
    "items"         => array(
        // Generic
        array(
            'name'          => "profile_access",
            'title'         => "_USER_AM_PROFILE_ACCESS",
            'description'   => "_USER_AM_PROFILE_ACCESS_DESC",
            'edit'          => "select",
            'filter'        => "string",
            'default'       => "member",
            'options'       => array(
                                "_USER_AM_PROFILE_PUBLIC"       => "public",
                                "_USER_AM_PROFILE_MEMBER"       => "member",
                                "_USER_AM_PROFILE_MODERATOR"    => "moderator",
                                "_USER_AM_PROFILE_CALLBACK"     => "callback")),
        array(
            'name'          => "profile_callback",
            'title'         => "_USER_AM_PROFILE_CALLBACK",
            'description'   => "_USER_AM_PROFILE_CALLBACK_DESC",
            'edit'          => "text",
            'filter'        => "string",
            'default'       => "profile::canView"),
        // Account
        array(
            'category'      => "account",
            'name'          => "delete_enable",
            'title'         => "_USER_AM_DELETE_ENABLE",
            'description'   => "_USER_AM_DELETE_ENABLE_DESC",
            'edit'          => "yesno",
            'filter'        => "number_int",
            'default'       => 0),
        array(
            'category'      => "account",
            'name'          => "uname_format",
            'title'         => "_USER_AM_UNAME_FORMAT",
            'description'   => "_USER_AM_UNAME_FORMAT_DESC",
            'edit'          => "select",
            'filter'        => "string",
            'default'       => "loose",
            'options'       => array(
                                "_USER_AM_UNAME_STRICT" => "strict",
                                "_USER_AM_UNAME_MEDIUM" => "medium",
                                "_USER_AM_UNAME_LOOSE"  => "loose")),
        array(
            'category'      => "account",
            'name'          => "uname_min",
            'title'         => "_USER_AM_UNAME_MIN",
            'description'   => "_USER_AM_UNAME_MIN_DESC",
            'edit'          => "text",
            'filter'        => "number_int",
            'default'       => 3),
        array(
            'category'      => "account",
            'name'          => "uname_max",
            'title'         => "_USER_AM_UNAME_MAX",
            'description'   => "_USER_AM_UNAME_MAX_DESC",
            'edit'          => "text",
            'filter'        => "number_int",
            'default'       => 32),
        array(
            'category'      => "account",
            'name'          => "uname_backlist",
            'title'         => "_USER_AM_UNAME_BACKLIST",
            'description'   => "_USER_AM_UNAME_BACKLIST_DESC",
            'edit'          => "textarea",
            'filter'        => "array",
            'default'       => array(
                                    'webmaster',
                                    '^xoops',
                                    '^admin')),
        array(
            'category'      => "account",
            'name'          => "email_backlist",
            'title'         => "_USER_AM_EMAIL_BACKLIST",
            'description'   => "_USER_AM_EMAIL_BACKLIST_DESC",
            'edit'          => "textarea",
            'filter'        => "array",
            'default'       => array('xoops.org$')),
        array(
            'category'      => "account",
            'name'          => "password_min",
            'title'         => "_USER_AM_PASSWORD_MIN",
            'description'   => "_USER_AM_PASSWORD_MIN_DESC",
            'edit'          => "text",
            'filter'        => "number_int",
            'default'       => 5),

        // Login preferences
        array(
            'category'      => "login",
            'name'          => "identity",
            'title'         => "_USER_AM_LOGIN_IDENTITY",
            'description'   => "_USER_AM_LOGIN_IDENTITY_DESC",
            'edit'          => "select",
            'filter'        => "string",
            'default'       => "",
            'options'       => array(
                                "_USER_AM_IDENTITY_DEFAULT" => "",
                                "_USER_AM_IDENTITY_USERID"  => "userid",
                                "_USER_AM_IDENTITY_EMAIL"   => "email",
                                "_USER_AM_IDENTITY_ANY"     => "any")),
        array(
            'category'      => "login",
            'name'          => "rememberme",
            'title'         => "_USER_AM_REMEMBERME",
            'description'   => "_USER_AM_REMEMBERME_DESC",
            'edit'          => "text",
            'filter'        => "number_int",
            'default'       => 14),
        array(
            'category'      => "login",
            'name'          => "attempts",
            'title'         => "_USER_AM_ATTEMPTS",
            'description'   => "_USER_AM_ATTEMPTS_DESC",
            'edit'          => "text",
            'filter'        => "number_int",
            'default'       => 0),
        /*
        array(
            'category'      => "login",
            'name'          => "multilogin",
            'title'         => "_USER_AM_MULTILOGIN",
            'description'   => "_USER_AM_MULTILOGIN_DESC",
            'edit'          => "yesno",
            'filter'        => "number_int",
            'default'       => 1),
        */
        array(
            'category'      => "login",
            'name'          => "log_onsuccess",
            'title'         => "_USER_AM_LOG_ONSUCCESS",
            'description'   => "_USER_AM_LOG_ONSUCCESS_DESC",
            'edit'          => "yesno",
            'filter'        => "number_int",
            'default'       => 0),
        array(
            'category'      => "login",
            'name'          => "log_onfailure",
            'title'         => "_USER_AM_LOG_ONFAILURE",
            'description'   => "_USER_AM_LOG_ONFAILURE_DESC",
            'edit'          => "yesno",
            'filter'        => "number_int",
            'default'       => 0),
        array(
            'category'      => "login",
            'name'          => "ssl_enable",
            'title'         => "_USER_AM_SSL_ENABLE",
            'description'   => "_USER_AM_SSL_ENABLE_DESC",
            'edit'          => "yesno",
            'filter'        => "number_int",
            'default'       => 0),
        array(
            'category'      => "login",
            'name'          => "ssl_post",
            'title'         => "_USER_AM_SSL_POST",
            'description'   => "_USER_AM_SSL_POST_DESC",
            'edit'          => "text",
            'filter'        => "string",
            'default'       => ""),
        array(
            'category'      => "login",
            'name'          => "ssl_link",
            'title'         => "_USER_AM_SSL_LINK",
            'description'   => "_USER_AM_SSL_LINK_DESC",
            'edit'          => "text",
            'filter'        => "string",
            'default'       => "https://"),

        // Register preferences
        array(
            'category'      => "register",
            'name'          => "enable_register",
            'title'         => "_USER_AM_ENABLE_REGISTER",
            'description'   => "_USER_AM_ENABLE_REGISTER_DESC",
            'edit'          => "yesno",
            'filter'        => "number_int",
            'default'       => 1),
        array(
            'category'      => "register",
            'name'          => "activate_type",
            'title'         => "_USER_AM_ACTIVATE_TYPE",
            'description'   => "_USER_AM_ACTIVATE_TYPE_DESC",
            'edit'          => "select",
            'filter'        => "string",
            'default'       => "auto",
            'options'       => array(
                                "_MD_AM_ACTIVATE_AUTO"  => "auto",
                                "_MD_AM_ACTIVATE_USER"  => "user",
                                "_MD_AM_ACTIVATE_ADMIN" => "admin")),
        array(
            'category'      => "register",
            'name'          => "activate_role",
            'title'         => "_USER_AM_ACTIVATE_ROLE",
            'description'   => "_USER_AM_ACTIVATE_ROLE_DESC",
            'edit'          => "role",
            'filter'        => "number_int",
            'default'       => 1),
        array(
            'category'      => "register",
            'name'          => "notify_roles",
            'title'         => "_USER_AM_NOTIFY_ROLES",
            'description'   => "_USER_AM_NOTIFY_ROLES_DESC",
            //'edit'          => "rolemultiple",
            'edit'          => array(
                "type"      => "role",
                "multiple"  => "multiple",
            ),
            'filter'        => "array",
            'default'       => array()),
        array(
            'category'      => "register",
            'name'          => "activate_expire",
            'title'         => "_USER_AM_ACTIVATE_EXPIRATION",
            'description'   => "_USER_AM_ACTIVATE_EXPIRATION_DESC",
            'edit'          => "text",
            'filter'        => "number_int",
            'default'       => 3),
        array(
            'category'      => "register",
            'name'          => "captcha",
            'title'         => "_USER_AM_CAPTCHA",
            'description'   => "_USER_AM_CAPTCHA_DESC",
            'edit'          => "yesno",
            'filter'        => "number_int",
            'default'       => 1),
        array(
            'category'      => "register",
            'name'          => "disclaim_enable",
            'title'         => "_USER_AM_DISCLAIM_ENABLE",
            'description'   => "_USER_AM_DISCLAIM_ENABLE_DESC",
            'edit'          => "yesno",
            'filter'        => "number_int",
            'default'       => 1),
        array(
            'category'      => "register",
            'name'          => "disclaim_content",
            'title'         => "_USER_AM_DISCLAIM_CONTENT",
            'description'   => "_USER_AM_DISCLAIM_CONTENT_DESC",
            'edit'          => "textarea",
            'filter'        => "string",
            'default'       => _USER_MI_DISCLAIM_CONTENT),
        array(
            'category'      => "register",
            'name'          => "welcome",
            'title'         => "_USER_AM_WELCOME",
            'description'   => "_USER_AM_WELCOME_DESC",
            'edit'          => "yesno",
            'filter'        => "number_int",
            'default'       => 1),
        /*
        array(
            'category'      => "register",
            'name'          => "welcome",
            'title'         => "_USER_AM_WELCOME_CONTENT",
            'description'   => "_USER_AM_WELCOME_CONTENT_DESC",
            'edit'          => "textarea",
            'filter'        => "string",
            'default'       => _USER_MI_WELCOME_CONTENT),
        */
        // Avatar
        array(
            'category'      => "avatar",
            'name'          => "avatar_path",
            'title'         => "_USER_AM_AVATAR_PATH",
            'description'   => "_USER_AM_AVATAR_PATH_DESC",
            'edit'          => "text",
            'filter'        => "string",
            'default'       => "img/user/avatar"),
        array(
            'category'      => "avatar",
            'name'          => "avatar_width",
            'title'         => "_USER_AM_AVATAR_WIDTH",
            'description'   => "_USER_AM_AVATAR_WIDTH_DESC",
            'edit'          => "text",
            'filter'        => "number_int",
            'default'       => 120),
        array(
            'category'      => "avatar",
            'name'          => "avatar_height",
            'title'         => "_USER_AM_AVATAR_HEIGHT",
            'description'   => "_USER_AM_AVATAR_HEIGHT_DESC",
            'edit'          => "text",
            'filter'        => "number_int",
            'default'       => 120),
        array(
            'category'      => "avatar",
            'name'          => "upload_enable",
            'title'         => "_USER_AM_UPLOAD_ENABLE",
            'description'   => "_USER_AM_UPLOAD_ENABLE_DESC",
            'edit'          => "yesno",
            'filter'        => "number_int",
            'default'       => 1),
        // Callback for avatar upload privilege
        array(
            'category'      => "avatar",
            'name'          => "avatar_privilege",
            'title'         => "_USER_AM_AVATAR_PRIVILEGE",
            'description'   => "_USER_AM_AVATAR_PRIVILEGE_DESC",
            'edit'          => "text",
            'filter'        => "string",
            'default'       => "avatar::canUpload"),
        array(
            'category'      => "avatar",
            'name'          => "avatar_upload_width",
            'title'         => "_USER_AM_AVATAR_UPLOAD_WIDTH",
            'description'   => "_USER_AM_AVATAR_UPLOAD_WIDTH_DESC",
            'edit'          => "text",
            'filter'        => "number_int",
            'default'       => 500),
        array(
            'category'      => "avatar",
            'name'          => "avatar_upload_height",
            'title'         => "_USER_AM_AVATAR_UPLOAD_HEIGHT",
            'description'   => "_USER_AM_AVATAR_UPLOAD_HEIGHT_DESC",
            'edit'          => "text",
            'filter'        => "number_int",
            'default'       => 500),

        // Administration
        array(
            'category'      => "admin",
            'name'          => "items_per_page",
            'title'         => "_USER_AM_ITEMS_PER_PAGE",
            'description'   => "_USER_AM_ITEMS_PER_PAGE_DESC",
            'edit'          => "text",
            'filter'        => "number_int",
            'default'       => 50),

    ),
);