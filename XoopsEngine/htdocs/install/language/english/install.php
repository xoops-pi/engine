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
define("BUTTON_RELOAD", "Reload");

// Messages
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
define("ERR_COPY_CONFIGFILES", "The configuration files are not copied correctly or not readable, please create and/or set read permissions for the files manually.");

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
define("_INSTALL_PAGE_REQUIREMENT", "System requirements");
define("_INSTALL_PAGE_REQUIREMENT_TITLE", "Check server settings and extensions");
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
