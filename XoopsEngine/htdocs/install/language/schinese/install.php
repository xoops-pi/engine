<?php
// $Id$
// _LANGCODE: en
// _CHARSET : UTF-8
// Translator: XOOPS Translation Team

define("SHOW_HIDE_HELP", "Show/hide help text");

define("SUCCESS", "Success");
define("WARNING", "Warning");
define("FAILED", "Failed");

// Titles (main and pages)
define("XOOPS_INSTALL_WIZARD", "Xoops Engine Setup Wizard");

define("LEGEND_LANGUAGE", "Language Selection");
define("LANGUAGE_LABEL", "Language");
define("LANGUAGE_HELP", "Choose the language to be used");

define("LEGEND_LOCALE", "Locale Setting");
define("LOCALE_LANG_LABEL", "Language code");
define("LOCALE_LANG_HELP", "Language code to be used for output");
define("LOCALE_CHARSET_LABEL", "Character set");
define("LOCALE_CHARSET_HELP", "Charset to be used for output");

// Settings (labels and help text)

define("XOOPS_ROOT_PATH_LABEL", "Documents root physical path");
define("XOOPS_ROOT_PATH_HELP", "Physical path to the documents (served) directory without trailing slash");

define("XOOPS_URL_LABEL", "Website location (URL)"); // L56
define("XOOPS_URL_HELP", "Main URL that will be used to access your XOOPS installation"); // L58

define("XOOPS_LIB_PATH_LABEL", "Library directory");
define("XOOPS_LIB_PATH_HELP", "Physical path to library directory without trailing slash. Locate the folder out of " . XOOPS_ROOT_PATH_LABEL . " to make it secure.");

define("XOOPS_DATA_PATH_LABEL", "Data file directory");
define("XOOPS_DATA_PATH_HELP", "Physical path to the data files (writable) directory WITHOUT trailing slash. Locate the folder out of " . XOOPS_ROOT_PATH_LABEL . " to make it secure.");

define("XOOPS_USR_PATH_LABEL", "Basic directory for user applications");
define("XOOPS_USR_PATH_HELP", "Physical path to user application directory WITHOUT trailing slash. Locate the folder out of " . XOOPS_ROOT_PATH_LABEL . " to make it secure.");

define("XOOPS_APP_PATH_LABEL", "Application module directory");
define("XOOPS_APP_PATH_HELP", "Physical path to application module directory without trailing slash.");

define("XOOPS_APP_URL_LABEL", "URL of application root directory");
define("XOOPS_APP_URL_HELP", "URL that will be used to access application directory. For security consideration, it is highly recommended to leave it blank.");

define("XOOPS_PLUGIN_PATH_LABEL", "Plugin directory");
define("XOOPS_PLUGIN_PATH_HELP", "Physical path to plugin directory without trailing slash.");

define("XOOPS_PLUGIN_URL_LABEL", "URL of plugin root directory");
define("XOOPS_PLUGIN_URL_HELP", "URL that will be used to access plugin directory. For security consideration, it is highly recommended to leave it blank.");

define("XOOPS_APPLET_PATH_LABEL", "Applet directory");
define("XOOPS_APPLET_PATH_HELP", "Physical path to applet directory without trailing slash.");

define("XOOPS_APPLET_URL_LABEL", "URL of applet root directory");
define("XOOPS_APPLET_URL_HELP", "URL that will be used to access applet directory. For security consideration, it is highly recommended to leave it blank.");

define("XOOPS_UPLOAD_PATH_LABEL", "Upload directory");
define("XOOPS_UPLOAD_PATH_HELP", "Physical path to upload directory without trailing slash. A relative path will be allocated in XOOPS root directory");

define("XOOPS_UPLOAD_URL_LABEL", "URL of upload root");
define("XOOPS_UPLOAD_URL_HELP", "URL that will be used to access upload directory. A relative URL will be appended to XOOPS root URL.");

define("XOOPS_IMG_PATH_LABEL", "Static file directory");
define("XOOPS_IMG_PATH_HELP", "Physical path to static file directory without trailing slash. Upload directory will be used if not set explicitly.");

define("XOOPS_IMG_URL_LABEL", "URL of static file root directory");
define("XOOPS_IMG_URL_HELP", "URL that will be used to access static files. Upload URL will be used if static directory is not set explicitly.");

define("XOOPS_THEME_PATH_LABEL", "Theme directory");
define("XOOPS_THEME_PATH_HELP", "Physical path to theme directory without trailing slash. 'themes' in img will be used if not set explicitly.");

define("XOOPS_THEME_URL_LABEL", "URL of theme root directory");
define("XOOPS_THEME_URL_HELP", "URL that will be used to access themes. 'themes' in img will be used if not set explicitly.");


define("LEGEND_CONNECTION", "Server connection");
define("LEGEND_DATABASE", "Database");
define("DB_HOST_LABEL", "Server hostname");
define("DB_HOST_HELP",  "Hostname of the database server. If you are unsure, <strong>localhost</strong> works in most cases, or <strong>127.0.0.1</strong>");
define("DB_USER_LABEL", "User name");
define("DB_USER_HELP",  "Name of the user account that will be used to connect to the database server");
define("DB_PASS_LABEL", "Password");
define("DB_PASS_HELP",  "Password of your database user account");
define("DB_NAME_LABEL", "Database name");
define("DB_NAME_HELP",  "The name of database on the host. The installer will attempt to create the database if not exist");
define("DB_CHARSET_LABEL", "Database character set");
define("DB_CHARSET_HELP",  "MySQL includes character set support that enables you to store data using a variety of character sets and perform comparisons according to a variety of collations.");
define("DB_COLLATION_LABEL", "Database collation");
define("DB_COLLATION_HELP",  "A collation is a set of rules for comparing characters in a character set.");
define("DB_PREFIX_LABEL", "Table prefix");
define("DB_PREFIX_HELP",  "This prefix will be added to all new tables created to avoid name conflicts in the database. If you are unsure, just keep the default");
define("DB_PCONNECT_LABEL", "Use persistent connection");
define("DB_PCONNECT_HELP",  "Default is 'No'. Leave it blank if you are unsure.");
define("DB_DATABASE_LABEL", "Database");

define("LEGEND_ADMIN_ACCOUNT", "Administrator account");
define("ADMIN_LOGIN_LABEL", "Admin login");
define("ADMIN_EMAIL_LABEL", "Admin email");
define("ADMIN_PASS_LABEL", "Admin password");
define("ADMIN_CONFIRMPASS_LABEL", "Confirm password");

// Buttons
define("BUTTON_PREVIOUS", "Previous");
define("BUTTON_NEXT", "Next");

// Messages
//define("XOOPS_FOUND", "%s found");
define("CHECKING_PERMISSIONS", "Checking file and directory permissions...");
define("IS_NOT_WRITABLE", "%s is NOT writable.");
define("IS_WRITABLE", "%s is writable.");

define("XOOPS_PATH_FOUND", "Path found.");

// %s is table name
define("TABLE_NOT_CREATED", "Unable to create table %s");
define("TABLE_CREATED", "Table %s created.");
define("ROWS_INSERTED", "%d entrie(s) inserted to table %s.");
define("ROWS_FAILED", "Failed inserting %d entrie(s) to table %s.");
define("TABLE_ALTERED", "Table %s updated.");
define("TABLE_NOT_ALTERED", "Failed updating table %s.");
define("TABLE_DROPPED", "Table %s dropped.");
define("TABLE_NOT_DROPPED", "Failed deleting table %s.");

// Error messages
define("ERR_COULD_NOT_ACCESS", "Could not access the specified folder. Please verify that it exists and is readable by the server.");
define("ERR_NO_XOOPS_FOUND", "No system installation could be found in the specified folder.");
define("ERR_INVALID_EMAIL", "Invalid Email");
define("ERR_REQUIRED", "Information is required.");
define("ERR_PASSWORD_MATCH", "The two passwords do not match");
define("ERR_WRITE_CONFIGFILE", "The configuration file '%s' is not written correctly.");
define("ERR_NEED_WRITE_ACCESS", "The server must be given write access to the following files and folders<br />(i.e. <strong>chmod 777 directory_name</strong> on a UNIX/LINUX server)<br />If they are not available or not created correctly, please create manually and set proper permissions.");
define("ERR_NO_DATABASE", "Could not create database. Contact the server administrator for details."); // L31
define("ERR_NO_DBCONNECTION", "Could not connect to the database server.");
define("ERR_MYSQL_STRICT_MODE", "MySQL is running in strict mode, you are advised to turn it off. For details, check <a href='http://dev.mysql.com/doc/refman/5.5/en/faqs-sql-modes.html' target='_blank'>MySQL manual</a>.");

define("ERR_INVALID_DBCHARSET", "The charset '%s' is not supported.");
define("ERR_INVALID_DBCOLLATION", "The collation '%s' is not supported.");
define("ERR_CHARSET_NOT_SET", "Default character set is not set for XOOPS database.");


//define("_INSTALL_CHARSET", "UTF-8");

define("SUPPORT", "Supports");
define("ADMIN_EXIST", "The administrator account already exists.<br />Press <strong>next</strong> to go to the next step.");

// Settings (labels and help text)
define("XOOPS_SETTINGS_BASIC", "Basic settings");
define("XOOPS_SETTINGS_ADVANCED", "Advanced settings");

define("XOOPS_SETTINGS_BASIC_HELP", "Settings required by system");
define("XOOPS_SETTINGS_ADVANCED_HELP", "Settings that can help improve security, depolyment flexibility, etc. If you are unsure about it, leave as it is.");

define("XOOPS_URL_FOUND", "URL is detected.");
define("ERR_URL_NOT_ACCESS", "URL is not accessible. Please check and try again.");
define("_INSTALL_SYSTEM_ALREADY_INSTALLED", 'System module already exists. Please continue to re-install.');
define("_INSTALL_SYSTEM_TO_INSTALL", 'Please continue to install system module.');
define("_INSTALL_SYSTEM_INSTALLED_SUCCESS", 'System module is installed successfully.');
define("_INSTALL_SYSTEM_INSTALLED_FAILED", 'System module installation is failed. Please continue to try again.');

define("_INSTALL_PAGE_LOCALE", "Language selection");
define("_INSTALL_PAGE_LOCALE_TITLE", "Choose system language and charset");
define("_INSTALL_PAGE_PERSIST", "Persistent data container");
define("_INSTALL_PAGE_PERSIST_TITLE", "Choose the proper backend container for persistent data");
define("_INSTALL_PAGE_PATHS", "Paths settings");
define("_INSTALL_PAGE_PATHS_TITLE", "Paths settings");
define("_INSTALL_PAGE_DATABASE_CONNECTION", "Database connection");
define("_INSTALL_PAGE_DATABASE_CONNECTION_TITLE", "Database connection");
define("_INSTALL_PAGE_DATABASE_SETUP", "Database configuration");
define("_INSTALL_PAGE_DATABASE_SETUP_TITLE", "Database creation and configuration");
define("_INSTALL_PAGE_SYSTEM_SETUP", "System module");
define("_INSTALL_PAGE_SYSTEM_SETUP_TITLE", "System module installation");
define("_INSTALL_PAGE_ADMIN_ACCOUNT", "Administrator account");
define("_INSTALL_PAGE_ADMIN_ACCOUNT_TITLE", "Administrator account creation");
define("_INSTALL_PAGE_FINISH", "Finish");
define("_INSTALL_PAGE_FINISH_TITLE", "Finish installation and take a tour of XOOPS website");
