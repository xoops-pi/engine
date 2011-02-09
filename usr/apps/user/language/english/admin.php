<?php
// Configuration categories
define("_USER_AM_ACCOUNT", "Account");
define("_USER_AM_LOGIN", "Login");
define("_USER_AM_REGISTER", "Registration");
define("_USER_AM_AVATAR", "Avatar");
define("_USER_AM_ADMIN", "Administration");

// Configuration items
// Generic configurations
define("_USER_AM_PROFILE_ACCESS", "User profile access policy");
define("_USER_AM_PROFILE_ACCESS_DESC", "");
define("_USER_AM_PROFILE_PUBLIC", "Public");
define("_USER_AM_PROFILE_MEMBER", "Members only");
define("_USER_AM_PROFILE_MODERATOR", "Moderators only");

define("_USER_AM_PROFILE_CALLBACK", "Callback for profile");
define("_USER_AM_PROFILE_CALLBACK_DESC", "Use 'class::method'");

// Registration configurations
define("_USER_AM_DELETE_ENABLE", "Enable user self-deletion");
define("_USER_AM_DELETE_ENABLE_DESC", "");

define("_USER_AM_UNAME_FORMAT", "Allowed username format");
define("_USER_AM_UNAME_FORMAT_DESC", "");
define("_USER_AM_UNAME_STRICT", "Strict - alphabet or number only");
define("_USER_AM_UNAME_MEDIUM", "Medium - ASCII characters");
define("_USER_AM_UNAME_LOOSE", "Loose - Multi-byte characters");

define("_USER_AM_UNAME_MIN", "Minimum username length");
define("_USER_AM_UNAME_MIN_DESC", "");

define("_USER_AM_UNAME_MAX", "Maximum username length");
define("_USER_AM_UNAME_MAX_DESC", "");

define("_USER_AM_UNAME_BACKLIST", "Reserved and forbidden username list");
define("_USER_AM_UNAME_BACKLIST_DESC", "Separate each with a <strong>|</strong>");

define("_USER_AM_EMAIL_BACKLIST", "Forbidden email address list");
define("_USER_AM_EMAIL_BACKLIST_DESC", "Separate each with a <strong>|</strong>");

define("_USER_AM_PASSWORD_MIN", "Minimum password length");
define("_USER_AM_PASSWORD_MIN_DESC", "");

// Login configurations
define("_USER_AM_LOGIN_IDENTITY", "Identity for login");
define("_USER_AM_LOGIN_IDENTITY_DESC", "Identity for login");
define("_USER_AM_IDENTITY_DEFAULT", "Default (username)");
define("_USER_AM_IDENTITY_USERID", "Digital user ID");
define("_USER_AM_IDENTITY_EMAIL", "Email");
define("_USER_AM_IDENTITY_ANY", "Any one (user choose)");

define("_USER_AM_REMEMBERME", "Time for 'remember me'");
define("_USER_AM_REMEMBERME_DESC", "In days, 0 for disable");

define("_USER_AM_ATTEMPTS", "Maximum attempts");
define("_USER_AM_ATTEMPTS_DESC", "Allowed times for login attempts in one hour");

//define("_USER_AM_MULTILOGIN", "Allow multiple login for one account");
//define("_USER_AM_MULTILOGIN_DESC", "Multiple simultaneous login.");

define("_USER_AM_LOG_ONSUCCESS", "Log information after login");
define("_USER_AM_LOG_ONSUCCESS_DESC", "IP, time.");

define("_USER_AM_LOG_ONFAILURE", "Log attemps upon failure");
define("_USER_AM_LOG_ONFAILURE_DESC", "attemps, time.");

define("_USER_AM_SSL_ENABLE", "User SSL for login");
define("_USER_AM_SSL_ENABLE_DESC", "");

define("_USER_AM_SSL_POST", "SSL post variable name");
define("_USER_AM_SSL_POST_DESC", "The name of variable used to transfer session value via POST. If you are unsure, set any name that is hard to guess.");

define("_USER_AM_SSL_LINK", "SSL authentication link");
define("_USER_AM_SSL_LINK_DESC", "");

// Registration configurations
define("_USER_AM_ENABLE_REGISTER", "Enable user registration");
define("_USER_AM_ENABLE_REGISTER_DESC", "Set to false for invitation only");

define("_USER_AM_ACTIVATE_TYPE", "Policy for new account activation");
define("_USER_AM_ACTIVATE_TYPE_DESC", "");
define("_MD_AM_ACTIVATE_AUTO", "Automatic");
define("_MD_AM_ACTIVATE_USER", "By user");
define("_MD_AM_ACTIVATE_ADMIN", "By Administrator");

define("_USER_AM_ACTIVATE_ROLE", "Role of users who can activate new accounts");
define("_USER_AM_ACTIVATE_ROLE_DESC", "");

define("_USER_AM_NOTIFY_ROLES", "Roles of users who will receive notifications upon new registraion");
define("_USER_AM_NOTIFY_ROLES_DESC", "");

define("_USER_AM_ACTIVATE_EXPIRATION", "Activation expiration");
define("_USER_AM_ACTIVATE_EXPIRATION_DESC", "In days");

define("_USER_AM_CAPTCHA", "Use CAPTCHA");
define("_USER_AM_CAPTCHA_DESC", "");

define("_USER_AM_DISCLAIM_ENABLE", "Display disclaim message");
define("_USER_AM_DISCLAIM_ENABLE_DESC", "");

define("_USER_AM_DISCLAIM_CONTENT", "Disclaim message content");
define("_USER_AM_DISCLAIM_CONTENT_DESC", "");

define("_USER_AM_WELCOME", "Send welcoming message to new users");
define("_USER_AM_WELCOME_DESC", "");

/*
define("_USER_AM_WELCOME_MODE", "Method of sending welcome message");
define("_USER_AM_WELCOME_MODE_DESC", "");
define("_USER_AM_WELCOME_NO", "Do not send");
define("_USER_AM_WELCOME_EMAIL", "Email");
define("_USER_AM_WELCOME_MESSAGE", "Message");
define("_USER_AM_WELCOME_BOTH", "Both");

define("_USER_AM_WELCOME_CONTENT", "Welcome message to new users");
define("_USER_AM_WELCOME_CONTENT_DESC", "");
*/
// Avatar configurations
define("_USER_AM_AVATAR_PATH", "Avatar file path");
define("_USER_AM_AVATAR_PATH_DESC", "User system path indicators as path prefix: ", implode(", ", array_keys(Xoops::host()->get('paths'))));

define("_USER_AM_AVATAR_WIDTH", "Avatar display width");
define("_USER_AM_AVATAR_WIDTH_DESC", "");

define("_USER_AM_AVATAR_HEIGHT", "Avatar display height");
define("_USER_AM_AVATAR_HEIGHT_DESC", "");

define("_USER_AM_UPLOAD_ENABLE", "Enable user upload");
define("_USER_AM_UPLOAD_ENABLE_DESC", "");

define("_USER_AM_AVATAR_PRIVILEGE", "Callback privilege for user upload");
define("_USER_AM_AVATAR_PRIVILEGE_DESC", "Applicable when upload is enabled");

define("_USER_AM_AVATAR_UPLOAD_WIDTH", "Uploaded avatar width");
define("_USER_AM_AVATAR_UPLOAD_WIDTH_DESC", "");

define("_USER_AM_AVATAR_UPLOAD_HEIGHT", "Uploaded avatar height");
define("_USER_AM_AVATAR_UPLOAD_HEIGHT_DESC", "");

// Administration
define("_USER_AM_ITEMS_PER_PAGE", "Item count on each page");
define("_USER_AM_ITEMS_PER_PAGE_DESC", "");
