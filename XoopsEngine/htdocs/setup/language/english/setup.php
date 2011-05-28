<?php
// $Id$
// _LANGCODE: en
// _CHARSET : UTF-8
// Translator: XOOPS Translation Team

// Titles (main and pages)
define("_INSTALL_WIZARD", "Xoops Engine Setup Wizard");


define("_INSTALL_PAGE_PRESETTING", "Presettings");
define("_INSTALL_PAGE_PRESETTING_DESC", "Presettings and server configuration detection");
define("_INSTALL_PAGE_DIRECTIVE", "Directives");
define("_INSTALL_PAGE_DIRECTIVE_DESC", "Directive settings for website");
define("_INSTALL_PAGE_DATABASE", "Database");
define("_INSTALL_PAGE_DATABASE_DESC", "Database settings");
define("_INSTALL_PAGE_ADMIN", "Administrator");
define("_INSTALL_PAGE_ADMIN_DESC", "Administrator account creation");
define("_INSTALL_PAGE_FINISH", "Finish");
define("_INSTALL_PAGE_FINISH_DESC", "Finishing installation process");

define("_INSTALL_LANGUAGE_LEGEND", "Language Selection");
define("_INSTALL_LANGUAGE_DESC", "Choose the language for the installation and website");

define("_INSTALL_SERVER_LEGEND", "Sever setting detection");
define("_INSTALL_SERVER_DESC", "Check server settings and extensions");

define("_INSTALL_PERSIST", "Persistent data container");
define("_INSTALL_PERSIST_DESC", "Choose the proper backend container for persistent data");
define("_INSTALL_PATHS", "Path settings");
define("_INSTALL_PATHS_DESC", "Path and URL settings");

// Settings (labels and help text)

define("_INSTALL_PATH_WWW_LABEL", "Documents root physical path");
define("_INSTALL_PATH_WWW_HELP", "Physical path to the documents (served) directory without trailing slash");

define("_INSTALL_URL_WWW_LABEL", "Website location (URL)"); // L56
define("_INSTALL_URL_WWW_HELP", "Main URL that will be used to access your XOOPS installation"); // L58

define("_INSTALL_PATH_UPLOAD_LABEL", "Upload directory");
define("_INSTALL_PATH_UPLOAD_HELP", "Physical path to upload directory without trailing slash. A relative path will be allocated in XOOPS root directory");

define("_INSTALL_URL_UPLOAD_LABEL", "URL of upload root");
define("_INSTALL_URL_UPLOAD_HELP", "URL that will be used to access upload directory. A relative URL will be appended to XOOPS root URL.");

define("_INSTALL_PATH_IMG_LABEL", "Static file directory");
define("_INSTALL_PATH_IMG_HELP", "Physical path to static file directory without trailing slash. Upload directory will be used if not set explicitly.");

define("_INSTALL_URL_IMG_LABEL", "URL of static file root directory");
define("_INSTALL_URL_IMG_HELP", "URL that will be used to access static files. Upload URL will be used if static directory is not set explicitly.");

define("_INSTALL_PATH_THEME_LABEL", "Theme directory");
define("_INSTALL_PATH_THEME_HELP", "Physical path to theme directory without trailing slash. 'themes' in img will be used if not set explicitly.");

define("_INSTALL_URL_THEME_LABEL", "URL of theme root directory");
define("_INSTALL_URL_THEME_HELP", "URL that will be used to access themes. 'themes' in img will be used if not set explicitly.");

define("_INSTALL_PATH_LIB_LABEL", "Library directory");
define("_INSTALL_PATH_LIB_HELP", "Physical path to library directory without trailing slash. Locate the folder out of " . _INSTALL_PATH_WWW_LABEL . " to make it secure.");

define("_INSTALL_PATH_VAR_LABEL", "Data file directory");
define("_INSTALL_PATH_VAR_HELP", "Physical path to the data files (writable) directory WITHOUT trailing slash. Locate the folder out of " . _INSTALL_PATH_WWW_LABEL . " to make it secure.");

define("_INSTALL_PATH_USR_LABEL", "Basic directory for user applications");
define("_INSTALL_PATH_USR_HELP", "Physical path to user application directory WITHOUT trailing slash. Locate the folder out of " . _INSTALL_PATH_WWW_LABEL . " to make it secure.");

define("_INSTALL_PATH_APP_LABEL", "Application module directory");
define("_INSTALL_PATH_APP_HELP", "Physical path to application module directory without trailing slash.");

define("_INSTALL_URL_APP_LABEL", "URL of application root directory");
define("_INSTALL_URL_APP_HELP", "URL that will be used to access application directory. For security consideration, it is highly recommended to leave it blank.");

define("_INSTALL_PATH_PLUGIN_LABEL", "Plugin directory");
define("_INSTALL_PATH_PLUGIN_HELP", "Physical path to plugin directory without trailing slash.");

define("_INSTALL_URL_PLUGIN_LABEL", "URL of plugin root directory");
define("_INSTALL_URL_PLUGIN_HELP", "URL that will be used to access plugin directory. For security consideration, it is highly recommended to leave it blank.");

define("_INSTALL_PATH_APPLET_LABEL", "Applet directory");
define("_INSTALL_PATH_APPLET_HELP", "Physical path to applet directory without trailing slash.");

define("_INSTALL_URL_APPLET_LABEL", "URL of applet root directory");
define("_INSTALL_URL_APPLET_HELP", "URL that will be used to access applet directory. For security consideration, it is highly recommended to leave it blank.");

define("_INSTALL_DB_SETUP_LABEL", "Database setup");
define("_INSTALL_DB_SETUP_DESC", "Settings for database");
define("_INSTALL_DB_ADVANCED_LABEL", "Advanced settings");
define("_INSTALL_DB_ADVANCED_DESC", "Extra configurations");

define("_INSTALL_DB_TYPE_LABEL", "Database engine type");
define("_INSTALL_DB_TYPE_HELP",  "Currently only MySQL is officially supported");
define("_INSTALL_DB_HOST_LABEL", "Server hostname");
define("_INSTALL_DB_HOST_HELP",  "Hostname of the database server. If you are unsure, <strong>localhost</strong> works in most cases, or <strong>127.0.0.1</strong>");
define("_INSTALL_DB_USER_LABEL", "User name");
define("_INSTALL_DB_USER_HELP",  "Name of the user account that will be used to connect to the database server");
define("_INSTALL_DB_PASS_LABEL", "Password");
define("_INSTALL_DB_PASS_HELP",  "Password of your database user account");
define("_INSTALL_DB_NAME_LABEL", "Database name");
define("_INSTALL_DB_NAME_HELP",  "The name of database on the host. The installer will attempt to create the database if not exist");
define("_INSTALL_DB_CHARSET_LABEL", "Database character set");
define("_INSTALL_DB_CHARSET_HELP",  "MySQL includes character set support that enables you to store data using a variety of character sets and perform comparisons according to a variety of collations.");
define("_INSTALL_DB_COLLATION_LABEL", "Database collation");
define("_INSTALL_DB_COLLATION_HELP",  "A collation is a set of rules for comparing characters in a character set.");
define("_INSTALL_DB_PREFIX_LABEL", "Table prefix");
define("_INSTALL_DB_PREFIX_HELP",  "This prefix will be added to all new tables created to avoid name conflicts in the database. If you are unsure, just keep the default");
define("_INSTALL_DB_PCONNECT_LABEL", "Use persistent connection");
define("_INSTALL_DB_PCONNECT_HELP",  "Default is 'No'. Leave it blank if you are unsure.");

define("_INSTALL_DB_CHARSET_SELECT", "Choose charset");
define("_INSTALL_DB_COLLATION_SELECT",  "Choose collate");

/*
define("_INSTALL_DB_DATABASE_LABEL", "Database");
define("_INSTALL_DB_SKIP", "Skip Database");
define("_INSTALL_DB_SKIP_DESC", "If database is not utilized, you can skip database settings and the installer is finished.");
*/

define("_INSTALL_LEGEND_ADMIN_ACCOUNT", "Administrator account");
define("_INSTALL_ADMINNAME_LABEL", "Admin login");
define("_INSTALL_ADMINMAIL_LABEL", "Admin email");
define("_INSTALL_ADMINPASS_LABEL", "Admin password");
define("_INSTALL_ADMINPASS2_LABEL", "Confirm password");

// Buttons
define("_INSTALL_BUTTON_PREVIOUS", "Previous");
define("_INSTALL_BUTTON_NEXT", "Next");
define("_INSTALL_BUTTON_RELOAD", "Reload");

// Messages
define("_INSTALL_CHECKING_PERMISSIONS", "Checking file and directory permissions...");
define("_INSTALL_IS_NOT_WRITABLE", "%s is NOT writable.");
define("_INSTALL_IS_WRITABLE", "%s is writable.");

define("_INSTALL_PATH_FOUND", "Path found.");

// %s is table name
/*
define("TABLE_NOT_CREATED", "Unable to create table %s");
define("TABLE_CREATED", "Table %s created.");
define("ROWS_INSERTED", "%d entrie(s) inserted to table %s.");
define("ROWS_FAILED", "Failed inserting %d entrie(s) to table %s.");
define("TABLE_ALTERED", "Table %s updated.");
define("TABLE_NOT_ALTERED", "Failed updating table %s.");
define("TABLE_DROPPED", "Table %s dropped.");
define("TABLE_NOT_DROPPED", "Failed deleting table %s.");
*/

// Error messages
define("_INSTALL_ERR_COULD_NOT_ACCESS", "Could not access the specified folder. Please verify that it exists and is readable by the server.");
//define("ERR_NO_XOOPS_FOUND", "No system installation could be found in the specified folder.");
define("_INSTALL_ERR_INVALID_EMAIL", "Invalid Email");
define("_INSTALL_ERR_REQUIRED", "Information is required.");
define("_INSTALL_ERR_PASSWORD_MATCH", "The two passwords do not match");
define("_INSTALL_ERR_WRITE_CONFIGFILE_LABEL", "Configuration file write error");
define("_INSTALL_ERR_WRITE_CONFIGFILE_DESC", "The configuration file '%s' is not written correctly.");
define("_INSTALL_ERR_NEED_WRITE_ACCESS", "The server must be given write access to the following files and folders<br />(i.e. <strong>chmod 777 directory_name</strong> on a UNIX/LINUX server)<br />If they are not available or not created correctly, please create manually and set proper permissions.");
define("_INSTALL_ERR_COPY_CONFIGFILES_LABEL", "Configuration file copy error");
define("_INSTALL_ERR_COPY_CONFIGFILES_DESC", "The configuration files are not copied correctly or not readable, please create and/or set read permissions for the files manually.");

define("_INSTALL_ERR_NO_DATABASE", "Could not create database. Contact the server administrator for details."); // L31
define("_INSTALL_ERR_NO_DBCONNECTION", "Could not connect to the database server.");
define("_INSTALL_ERR_MYSQL_STRICT_MODE", "MySQL is running in strict mode, you are advised to turn it off. For details, check <a href='http://dev.mysql.com/doc/refman/5.5/en/faqs-sql-modes.html' target='_blank'>MySQL manual</a>.");

define("_INSTALL_ERR_INVALID_DBCHARSET", "The charset '%s' is not supported.");
define("_INSTALL_ERR_INVALID_DBCOLLATION", "The collation '%s' is not supported.");
define("_INSTALL_ERR_CHARSET_NOT_SET", "Default character set is not set for XOOPS database.");


//define("_INSTALL_CHARSET", "UTF-8");

//define("SUPPORT", "Supports");
define("_INSTALL_ADMIN_EXIST", "The administrator account already exists.<br />Press <strong>next</strong> to go to the next step.");

// Settings (labels and help text)
define("_INSTALL_XOOPS_SETTINGS_BASIC", "Basic settings");
define("_INSTALL_XOOPS_SETTINGS_ADVANCED", "Advanced settings");

define("_INSTALL_XOOPS_SETTINGS_BASIC_HELP", "Settings required by system");
define("_INSTALL_XOOPS_SETTINGS_ADVANCED_HELP", "Settings that can help improve security, depolyment flexibility, etc. If you are unsure about it, leave as it is.");

define("_INSTALL_URL_FOUND", "URL is detected.");
define("_INSTALL_ERR_URL_NOT_ACCESS", "URL is not accessible. Please check and try again.");
define("_INSTALL_SYSTEM_ALREADY_INSTALLED", 'System module already exists. Please continue to re-install.');
define("_INSTALL_SYSTEM_TO_INSTALL", 'Please continue to install system module.');
define("_INSTALL_SYSTEM_INSTALLED_SUCCESS", 'System module is installed successfully.');
define("_INSTALL_SYSTEM_INSTALLED_FAILED", 'System module installation is failed. Please continue to try again.');

define("_INSTALL_REQUIREMENT_SYSTEM", "System requirements");
define("_INSTALL_REQUIREMENT_SYSTEM_HELP", "Server settings and system extensions required by Xoops Engine");
define("_INSTALL_REQUIREMENT_VERSION_REQUIRED", "Version %s is required.");
define("_INSTALL_REQUIREMENT_SERVER", "Web server");
define("_INSTALL_REQUIREMENT_SERVER_NGINX", "Make sure you have adequate <a href='http://nginx.net' title='nginx' target='_blank'>nginx</a> knowledge, refer to <a href='http://dev.xoopsengine.org' title='Xoops Engine' target='_blank'>Xoops Engine Dev</a> for nginx settings.");
define("_INSTALL_REQUIREMENT_SERVER_MOD_REWRITE", "Apache \"mod_rewrite\" module is required, check <a href='http://httpd.apache.org/docs/current/mod/mod_rewrite.html' title='mod_rewrite' target='_blank'>mod_rewrite</a> for details.");
define("_INSTALL_REQUIREMENT_SERVER_NOT_SUPPORTED", "The webserver is currently not supported, please use <a href='http://nginx.net' title='nginx' target='_blank'>nginx</a> or <a href='http://www.php.net/manual/en/book.apache.php' target='_blank' title='Apache'>Apache</a>.");
define("_INSTALL_REQUIREMENT_PHP", "PHP");
define("_INSTALL_REQUIREMENT_PDO", "PDO drivers");
define("_INSTALL_REQUIREMENT_PDO_PROMPT", "PHP Data Objects (PDO) extension with MySQL driver is required for regular Xoops Engine instances, check <a href='http://www.php.net/manual/en/book.pdo.php' title='PDO' target='_blank'>PDO manual</a> for details.");
define("_INSTALL_REQUIREMENT_PERSIST", "Persist options");
define("_INSTALL_REQUIREMENT_PERSIST_PROMPT", "There is no recommended persist engine available. One of the following extensions is recommended: %s");
define("_INSTALL_REQUIREMENT_UNKNOWN", "Unknown");
define("_INSTALL_REQUIREMENT_VALID", "Valid");
define("_INSTALL_REQUIREMENT_INVALID", "Invalid");
define("_INSTALL_REQUIREMENT_UPDATE", "Not desired");

define("_INSTALL_REQUIREMENT_EXTENSION", "System extension recommendations");
define("_INSTALL_REQUIREMENT_EXTENSION_HELP", "Extesions recommended for better functionality or performance");
define("_INSTALL_EXTENSION_APC", "APC");
define("_INSTALL_EXTENSION_APC_PROMPT", 'The Alternative PHP Cache (APC) is highly recommended for high-performance senario. Refer to <a href="http://www.php.net/manual/en/intro.apc.php" target="_blank" title="APC introduction">APC introduction</a> for details.');
define("_INSTALL_EXTENSION_REDIS", "Redis");
define("_INSTALL_EXTENSION_REDIS_PROMPT", 'The extension is highly recommended for performance senario and advanced data structure. Refer to <a href="http://redis.io" target="_blank" title="Redis">Redis page</a> for details.');
define("_INSTALL_EXTENSION_MEMCACHED", "Memcached");
define("_INSTALL_EXTENSION_MEMCACHED_PROMPT", 'Memcached is highly recommended for high-performance yet robust distributed senario. Refer to <a href="http://www.php.net/manual/en/intro.memcached.php" target="_blank" title="Memcached introduction">Memcached introduction</a> for details.');
define("_INSTALL_EXTENSION_MEMCACHE", "Memcache");
define("_INSTALL_EXTENSION_MEMCACHE_PROMPT", 'Memcache a widely used cache engine. Refer to <a href="http://www.php.net/manual/en/intro.memcache.php" target="_blank" title="Memcache introduction">Memcache introduction</a> for details.');
define("_INSTALL_EXTENSION_FILE", 'File');
define("_INSTALL_EXTENSION_FILE_PROMPT", 'Caching storage with files is not recommended. You are highly adviced to check recommended extensions to ensure they are installed and configured correctly.');

define("_INSTALL_EXTENSION_MBSTRING", "mbstring");
define("_INSTALL_EXTENSION_MBSTRING_PROMPT", "The extension is required for multibyte string processing, check <a href='http://www.php.net/manual/en/book.mbstring.php' title='Multibyte String' target='_blank'>Multibyte String</a> for details.");
