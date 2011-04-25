<?php
// $Id: preferences.php 1507 2008-04-26 12:08:47Z phppp $
//%%%%%%    Admin Module Name  AdminGroup   %%%%%
//define("_AM_DBUPDATED",_SYSTEM_AM_DBUPDATED);

define("_SYSTEM_AM_SITEPREF", "Site Preferences");
define("_SYSTEM_AM_SITENAME", "Site name");
define("_SYSTEM_AM_SLOGAN", "Slogan for your site");
define("_SYSTEM_AM_ADMINML", "Admin mail address");
define("_SYSTEM_AM_LANGUAGE", "Default language");
define("_SYSTEM_AM_LANGUAGEDSC", "");
define("_SYSTEM_AM_STARTPAGE", "Module for your start page");
define("_SYSTEM_AM_NONE", "None");
define("_SYSTEM_AM_SERVERTZ", "Server timezone");
define("_SYSTEM_AM_DEFAULTTZ", "Default timezone");
define("_SYSTEM_AM_DTHEME", "Default theme");
define("_SYSTEM_AM_THEMESET", "Theme Set");
define("_SYSTEM_AM_ANONNAME", "Username for anonymous users");
define("_SYSTEM_AM_MINPASS", "Minimum length of password required");
define("_SYSTEM_AM_NEWUNOTIFY", "Notify by mail when a new user is registered?");
define("_SYSTEM_AM_SELFDELETE", "Allow users to delete own account?");
define("_SYSTEM_AM_LOADINGIMG", "Display loading... image?");
define("_SYSTEM_AM_USEGZIP", "Use gzip compression?");
define("_SYSTEM_AM_UNAMELVL", "Select the level of strictness for username filtering");
define("_SYSTEM_AM_STRICT", "Strict (only alphabets and numbers)");
define("_SYSTEM_AM_MEDIUM", "Medium");
define("_SYSTEM_AM_LIGHT", "Light (recommended for multi-byte chars)");
define("_SYSTEM_AM_USERCOOKIE", "Name for user cookies.");
define("_SYSTEM_AM_USERCOOKIEDSC", "If the cookie name is set, 'Remember me' will be enabled for user login. If a user has chosen 'Remember me', he will be logged in automatically. The expiration for the cookie is one year.");
define("_SYSTEM_AM_USEMYSESS", "Use custom session");
define("_SYSTEM_AM_USEMYSESSDSC", "Select yes to customise session related values.");
define("_SYSTEM_AM_SESSNAME", "Session name");
define("_SYSTEM_AM_SESSNAMEDSC", "The name of session (Valid only when 'use custom session' is enabled)");
define("_SYSTEM_AM_SESSEXPIRE", "Session expiration");
define("_SYSTEM_AM_SESSEXPIREDSC", "Maximum duration of session idle time in minutes (Valid only when 'use custom session' is enabled. Works only when you are using PHP4.2.0 or later.)");
define("_SYSTEM_AM_BANNERS", "Activate banner ads?");
define("_SYSTEM_AM_MYIP", "Your IP address");
define("_SYSTEM_AM_MYIPDSC", "This IP will not count as an impression for banners");
define("_SYSTEM_AM_ALWDHTML", "HTML tags allowed in all posts.");
define("_SYSTEM_AM_INVLDMINPASS", "Invalid value for minimum length of password.");
define("_SYSTEM_AM_INVLDUCOOK", "Invalid value for usercookie name.");
define("_SYSTEM_AM_INVLDSCOOK", "Invalid value for sessioncookie name.");
define("_SYSTEM_AM_INVLDSEXP", "Invalid value for session expiration time.");
define("_SYSTEM_AM_ADMNOTSET", "Admin mail is not set.");
define("_SYSTEM_AM_YES", "Yes");
define("_SYSTEM_AM_NO", "No");
define("_SYSTEM_AM_DONTCHNG", "Don't change!");
define("_SYSTEM_AM_REMEMBER", "Remember to chmod 666 this file in order to let the system write to it properly.");
define("_SYSTEM_AM_IFUCANT", "If you can't change the permissions you can edit the rest of this file by hand.");


define("_SYSTEM_AM_COMMODE", "Default Comment Display Mode");
define("_SYSTEM_AM_COMMODE_NESTED", "Nested");
define("_SYSTEM_AM_COMMODE_FLAT", "Flat");
define("_SYSTEM_AM_COMMODE_THREADED", "Threaded");
define("_SYSTEM_AM_COMORDER", "Default Comments Display Order");
define("_SYSTEM_AM_COMORDER_OLDESTFIRST", "Old first");
define("_SYSTEM_AM_COMORDER_NEWESTFIRST", "New first");
define("_SYSTEM_AM_ALLOWHTML", "Allow HTML tags in user comments?");
//define("_SYSTEM_AM_DEBUGMODE", "Debug mode");
//define("_SYSTEM_AM_DEBUGMODEDSC", "Several debug options. A running website should have this turned off.");
define("_SYSTEM_AM_AVATARALLOW", "Allow custom avatar upload?");
define("_SYSTEM_AM_AVATARMP", "Minimum posts required");
define("_SYSTEM_AM_AVATARMPDSC", "Enter the minimum number of posts required to upload a custom avatar");
define("_SYSTEM_AM_AVATARW", "Avatar image max width (pixel)");
define("_SYSTEM_AM_AVATARH", "Avatar image max height (pixel)");
define("_SYSTEM_AM_AVATARMAX", "Avatar image max filesize (byte)");
define("_SYSTEM_AM_AVATARCONF", "Custom avatar settings");
define("_SYSTEM_AM_CHNGUTHEME", "Change all users' theme");
define("_SYSTEM_AM_NOTIFYTO", "Select group to which new user notification mail will be sent");
define("_SYSTEM_AM_ALLOWTHEME", "Allow users to select theme?");
define("_SYSTEM_AM_ALLOWIMAGE", "Allow users to display image files in posts?");

define("_SYSTEM_AM_USERACTV", "Requires activation by user (recommended)");
define("_SYSTEM_AM_AUTOACTV", "Activate automatically");
define("_SYSTEM_AM_ADMINACTV", "Activation by administrators");
define("_SYSTEM_AM_ACTVTYPE", "Select activation type of newly registered users");
define("_SYSTEM_AM_ACTVGROUP", "Select group to which activation mail will be sent");
define("_SYSTEM_AM_ACTVGROUPDSC", "Valid only when 'Activation by administrators' is selected");
define("_SYSTEM_AM_USESSL", "Use SSL for login?");
define("_SYSTEM_AM_SSLPOST", "SSL Post variable name");
define("_SYSTEM_AM_SSLPOSTDSC", "The name of variable used to transfer session value via POST. If you are unsure, set any name that is hard to guess.");
define("_SYSTEM_AM_GENERAL", "General Settings");
//define("_SYSTEM_AM_DEBUGMODE0", "Off");
//define("_SYSTEM_AM_DEBUGMODE1", "Enable debug (inline mode)");
//define("_SYSTEM_AM_DEBUGMODE2", "Enable debug (popup mode)");
//define("_SYSTEM_AM_DEBUGMODE3", "Smarty Templates Debug");
/*
define("_SYSTEM_AM_MINUNAME", "Minimum length of username required");
define("_SYSTEM_AM_MAXUNAME", "Maximum length of username");
define("_SYSTEM_AM_USERSETTINGS", "User Info Settings");
define("_SYSTEM_AM_ALLWCHGMAIL", "Allow users to change email address?");
define("_SYSTEM_AM_ALLWCHGMAILDSC", "");
define("_SYSTEM_AM_IPBAN", "IP Banning");
define("_SYSTEM_AM_BADEMAILS", "Enter emails that should not be used in user profile");
define("_SYSTEM_AM_BADEMAILSDSC", "Separate each with a '|', case insensitive, regex enabled.");
define("_SYSTEM_AM_BADUNAMES", "Enter names that should not be selected as username");
define("_SYSTEM_AM_BADUNAMESDSC", "Separate each with a '|', case insensitive, regex enabled.");
define("_SYSTEM_AM_DOBADIPS", "Enable IP bans?");
define("_SYSTEM_AM_DOBADIPSDSC", "Users from specified IP addresses will not be able to view your site");
define("_SYSTEM_AM_BADIPS", "Enter IP addresses that should be banned from the site.");
define("_SYSTEM_AM_BADIPSDSC", "Separate each with a '|', case insensitive, regex enabled. '^aaa.bbb.ccc' will disallow visitors with an IP that starts with 'aaa.bbb.ccc'; 'aaa.bbb.ccc$' will disallow visitors with an IP that ends with 'aaa.bbb.ccc'; 'aaa.bbb.ccc' will disallow visitors with an IP that contains 'aaa.bbb.ccc'.");
*/
define("_SYSTEM_AM_PREFMAIN", "Preferences Main");
define("_SYSTEM_AM_METAKEY", "Meta Keywords");
define("_SYSTEM_AM_METAKEYDSC", "The keywords meta tag is a series of keywords that represents the content of your site. Type in keywords with each separated by a comma or a space in between. (Ex. XOOPS, PHP, mySQL, portal system)");
define("_SYSTEM_AM_METARATING", "Meta Rating");
define("_SYSTEM_AM_METARATINGDSC", "The rating meta tag defines your site age and content rating");
define("_SYSTEM_AM_METAOGEN", "General");
define("_SYSTEM_AM_METAO14YRS", "14 years");
define("_SYSTEM_AM_METAOREST", "Restricted");
define("_SYSTEM_AM_METAOMAT", "Mature");
define("_SYSTEM_AM_METAROBOTS", "Meta Robots");
define("_SYSTEM_AM_METAROBOTSDSC", "The Robots Tag declares to search engines what content to index and spider");
define("_SYSTEM_AM_INDEXFOLLOW", "Index, Follow");
define("_SYSTEM_AM_NOINDEXFOLLOW", "No Index, Follow");
define("_SYSTEM_AM_INDEXNOFOLLOW", "Index, No Follow");
define("_SYSTEM_AM_NOINDEXNOFOLLOW", "No Index, No Follow");
define("_SYSTEM_AM_METAAUTHOR", "Meta Author");
define("_SYSTEM_AM_METAAUTHORDSC", "The author meta tag defines the name of the author of the document being read. Supported data formats include the name, email address of the webmaster, company name or URL.");
define("_SYSTEM_AM_METACOPYR", "Meta Copyright");
define("_SYSTEM_AM_METACOPYRDSC", "The copyright meta tag defines any copyright statements you wish to disclose about your web page documents.");
define("_SYSTEM_AM_METADESC", "Meta Description");
define("_SYSTEM_AM_METADESCDSC", "The description meta tag is a general description of what is contained in your web page");
define("_SYSTEM_AM_METAFOOTER", "Meta Tags and Footer");
define("_SYSTEM_AM_FOOTER", "Footer");
define("_SYSTEM_AM_FOOTERDSC", "Be sure to type links in full path starting from http://, otherwise the links will not work correctly in modules pages.");
define("_SYSTEM_AM_CENSOR", "Word Censoring Options");
define("_SYSTEM_AM_DOCENSOR", "Enable censoring of unwanted words?");
define("_SYSTEM_AM_DOCENSORDSC", "Words will be censored if this option is enabled. This option may be turned off for enhanced site speed.");
define("_SYSTEM_AM_CENSORWRD", "Words to censor");
define("_SYSTEM_AM_CENSORWRDDSC", "Enter words that should be censored in user posts. Separate each with a '|', case insensitive.");
define("_SYSTEM_AM_CENSORRPLC", "Bad words will be replaced with:");
define("_SYSTEM_AM_CENSORRPLCDSC", "Censored words will be replaced with the characters entered in this textbox");

define("_SYSTEM_AM_SEARCH", "Search Options");
define("_SYSTEM_AM_DOSEARCH", "Enable global searches?");
define("_SYSTEM_AM_DOSEARCHDSC", "Allow searching for posts/items within your site.");
define("_SYSTEM_AM_MINSEARCH", "Minimum keyword length");
define("_SYSTEM_AM_MINSEARCHDSC", "Enter the minimum keyword length that users are required to enter to perform search");
define("_SYSTEM_AM_MODCONFIG", "Module Config Options");
define("_SYSTEM_AM_DSPDSCLMR", "Display disclaimer?");
define("_SYSTEM_AM_DSPDSCLMRDSC", "Select yes to display disclaimer in registration page");
define("_SYSTEM_AM_REGDSCLMR", "Registration disclaimer");
define("_SYSTEM_AM_REGDSCLMRDSC", "Enter text to be displayed as registration disclaimer");
define("_SYSTEM_AM_ALLOWREG", "Allow new user registration?");
define("_SYSTEM_AM_ALLOWREGDSC", "Select yes to accept new user registration");
define("_SYSTEM_AM_THEMEFILE", "Check templates for modifications ?");
define("_SYSTEM_AM_THEMEFILEDSC", "If this option is enabled, modified templates will be automatically recompiled when they are displayed. You must turn this option off on a production site.");
define("_SYSTEM_AM_CLOSESITE", "Turn your site off?");
define("_SYSTEM_AM_CLOSESITEDSC", "Select yes to turn your site off so that only users in selected groups have access to the site. ");
define("_SYSTEM_AM_CLOSESITEOK", "Select groups that are allowed to access while the site is turned off.");
define("_SYSTEM_AM_CLOSESITEOKDSC", "Users in the default webmasters group are always granted access.");
define("_SYSTEM_AM_CLOSESITETXT", "Reason for turning off the site");
define("_SYSTEM_AM_CLOSESITETXTDSC", "The text that is presented when the site is closed.");
define("_SYSTEM_AM_SITECACHE", "Site-wide Cache");
define("_SYSTEM_AM_SITECACHEDSC", "Caches whole contents of the site for a specified amount of time to enhance performance. Setting site-wide cache will override module-level cache, block-level cache, and module item level cache if any.");
define("_SYSTEM_AM_MODCACHE", "Module-wide Cache");
define("_SYSTEM_AM_MODCACHEDSC", "Caches module contents for a specified amount of time to enhance performance. Setting module-wide cache will override module item level cache if any.");
define("_SYSTEM_AM_NOMODULE", "There is no module that can be cached.");
define("_SYSTEM_AM_DTPLSET", "Default template set");
define("_SYSTEM_AM_SSLLINK", "URL where SSL login page is located");

// added for mailer
define("_SYSTEM_AM_MAILER", "Mail Setup");
define("_SYSTEM_AM_MAILER_MAIL", "");
define("_SYSTEM_AM_MAILER_SENDMAIL", "");
define("_SYSTEM_AM_MAILER_", "");
define("_SYSTEM_AM_MAILFROM", "FROM address");
define("_SYSTEM_AM_MAILFROMDESC", "");
define("_SYSTEM_AM_MAILFROMNAME", "FROM name");
define("_SYSTEM_AM_MAILFROMNAMEDESC", "");
// RMV-NOTIFY
define("_SYSTEM_AM_MAILFROMUID", "FROM user");
define("_SYSTEM_AM_MAILFROMUIDDESC", "When the system sends a private message, which user should appear to have sent it?");
define("_SYSTEM_AM_MAILERMETHOD", "Mail delivery method");
define("_SYSTEM_AM_MAILERMETHODDESC", "Method used to deliver mail. Default is \"mail\", use others only if that makes trouble.");
define("_SYSTEM_AM_SMTPHOST", "SMTP host(s)");
define("_SYSTEM_AM_SMTPHOSTDESC", "List of SMTP servers to try to connect to.");
define("_SYSTEM_AM_SMTPUSER", "SMTPAuth username");
define("_SYSTEM_AM_SMTPUSERDESC", "Username to connect to an SMTP host with SMTPAuth.");
define("_SYSTEM_AM_SMTPPASS", "SMTPAuth password");
define("_SYSTEM_AM_SMTPPASSDESC", "Password to connect to an SMTP host with SMTPAuth.");
define("_SYSTEM_AM_SENDMAILPATH", "Path to sendmail");
define("_SYSTEM_AM_SENDMAILPATHDESC", "Path to the sendmail program (or substitute) on the webserver.");
define("_SYSTEM_AM_THEMEOK", "Selectable themes");
define("_SYSTEM_AM_THEMEOKDSC", "Choose themes that users can select as the default theme");


// Xoops Authentication constants
/*
define("_SYSTEM_AM_AUTH_CONFOPTION_XOOPS", "XOOPS Database");
define("_SYSTEM_AM_AUTH_CONFOPTION_LDAP", "Standard LDAP Directory");
define("_SYSTEM_AM_AUTH_CONFOPTION_AD", "Microsoft Active Directory &copy");
define("_SYSTEM_AM_AUTHENTICATION", "Authentication Options");
define("_SYSTEM_AM_AUTHMETHOD", "Authentication Method");
define("_SYSTEM_AM_AUTHMETHODDESC", "Which authentication method would you like to use for signing on users.");
define("_SYSTEM_AM_LDAP_MAIL_ATTR", "LDAP - Mail Field Name");
define("_SYSTEM_AM_LDAP_MAIL_ATTR_DESC", "The name of the E-Mail attribute in your LDAP directory tree.");
define("_SYSTEM_AM_LDAP_NAME_ATTR", "LDAP - Common Name Field Name");
define("_SYSTEM_AM_LDAP_NAME_ATTR_DESC", "The name of the Common Name attribute in your LDAP directory.");
define("_SYSTEM_AM_LDAP_SURNAME_ATTR", "LDAP - Surname Field Name");
define("_SYSTEM_AM_LDAP_SURNAME_ATTR_DESC", "The name of the Surname attribute in your LDAP directory.");
define("_SYSTEM_AM_LDAP_GIVENNAME_ATTR", "LDAP - Given Name Field Name");
define("_SYSTEM_AM_LDAP_GIVENNAME_ATTR_DSC", "The name of the Given Name attribute in your LDAP directory.");
define("_SYSTEM_AM_LDAP_BASE_DN", "LDAP - Base DN");
define("_SYSTEM_AM_LDAP_BASE_DN_DESC", "The base DN (Distinguished Name) of your LDAP directory tree.");
define("_SYSTEM_AM_LDAP_PORT", "LDAP - Port Number");
define("_SYSTEM_AM_LDAP_PORT_DESC", "The port number needed to access your LDAP directory server.");
define("_SYSTEM_AM_LDAP_SERVER", "LDAP - Server Name");
define("_SYSTEM_AM_LDAP_SERVER_DESC", "The name of your LDAP directory server.");

define("_SYSTEM_AM_LDAP_MANAGER_DN", "DN of the LDAP manager");
define("_SYSTEM_AM_LDAP_MANAGER_DN_DESC", "The DN of the user allow to make search (eg manager)");
define("_SYSTEM_AM_LDAP_MANAGER_PASS", "Password of the LDAP manager");
define("_SYSTEM_AM_LDAP_MANAGER_PASS_DESC", "The password of the user allow to make search");
define("_SYSTEM_AM_LDAP_VERSION", "LDAP Version protocol");
define("_SYSTEM_AM_LDAP_VERSION_DESC", "The LDAP Version protocol : 2 or 3");
define("_SYSTEM_AM_LDAP_USERS_BYPASS", "Users allowed to bypass LDAP authentication");
define("_SYSTEM_AM_LDAP_USERS_BYPASS_DESC", "Users to be authenticated with native XOOPS method");

define("_SYSTEM_AM_LDAP_USETLS", " Use TLS connection");
define("_SYSTEM_AM_LDAP_USETLS_DESC", "Use a TLS (Transport Layer Security) connection. TLS use standard 389 port number<BR>" .
                                  " and the LDAP version must be set to 3.");

define("_SYSTEM_AM_LDAP_LOGINLDAP_ATTR", "LDAP Attribute use to search the user");
define("_SYSTEM_AM_LDAP_LOGINLDAP_ATTR_D", "When Login name use in the DN option is set to yes, must correspond to the login name XOOPS");
define("_SYSTEM_AM_LDAP_LOGINNAME_ASDN", "Login name use in the DN");
define("_SYSTEM_AM_LDAP_LOGINNAME_ASDN_D", "The XOOPS login name is used in the LDAP DN (eg : uid=<loginname>,dc=xoops,dc=org)<br>The entry is directly read in the LDAP Server without search");

define("_SYSTEM_AM_LDAP_FILTER_PERSON", "The search filter LDAP query to find user");
define("_SYSTEM_AM_LDAP_FILTER_PERSON_DESC", "Special LDAP Filter to find user. @@loginname@@ is replace by the users's login name<br> MUST BE BLANK IF YOU DON'T KNOW WHAT YOU DO' !" .
        "<br />Ex : (&(objectclass=person)(samaccountname=@@loginname@@)) for AD" .
        "<br />Ex : (&(objectclass=inetOrgPerson)(uid=@@loginname@@)) for LDAP");

define("_SYSTEM_AM_LDAP_DOMAIN_NAME", "The domain name");
define("_SYSTEM_AM_LDAP_DOMAIN_NAME_DESC", "Windows domain name. for ADS and NT Server only");

define("_SYSTEM_AM_LDAP_PROVIS", "Automatic xoops account provisionning");
define("_SYSTEM_AM_LDAP_PROVIS_DESC", "Create xoops user database if not exists");

define("_SYSTEM_AM_LDAP_PROVIS_GROUP", "Default affect group");
define("_SYSTEM_AM_LDAP_PROVIS_GROUP_DSC", "The new user is assign to these groups");

define("_SYSTEM_AM_LDAP_FIELD_MAPPING_ATTR", "Xoops-Auth server fields mapping");
define("_SYSTEM_AM_LDAP_FIELD_MAPPING_DESC", "Describe here the mapping between the Xoops database field and the LDAP Authentication system field." .
        "<br /><br />Format [Xoops Database field]=[Auth system LDAP attribute]" .
        "<br />for example : email=mail" .
        "<br />Separate each with a |" .
        "<br /><br />!! For advanced users !!");

define("_SYSTEM_AM_LDAP_PROVIS_UPD", "Maintain xoops account provisionning");
define("_SYSTEM_AM_LDAP_PROVIS_UPD_DESC", "The Xoops User account is always synchronized with the Authentication Server");

define("_SYSTEM_AM_CPANEL", "Control Panel GUI");
define("_SYSTEM_AM_CPANELDSC", "For backend");

define("_SYSTEM_AM_WELCOMETYPE", "Sending welcoming message");
define("_SYSTEM_AM_WELCOMETYPE_DESC", "The way of sending out a welcoming message to a user upon his successful registration.");
define("_SYSTEM_AM_WELCOMETYPE_EMAIL", "Email");
define("_SYSTEM_AM_WELCOMETYPE_PM", "Message");
define("_SYSTEM_AM_WELCOMETYPE_BOTH", "Email and message");
*/

define("_SYSTEM_AM_MODULEPREF", "Module Preferences");

define("_SYSTEM_AM_LOCALE", "Site Locale");
define("_SYSTEM_AM_LOCALE_DESC", "Site language and charset");

define("_SYSTEM_AM_FRONTNAVIGATION", "Site front navigation");
define("_SYSTEM_AM_FRONTNAVIGATION_DESC", "");

define("_SYSTEM_AM_ROOT", "Root users");
define("_SYSTEM_AM_ROOT_DESC", "");

define("_SYSTEM_AM_ROOT_ATTEMPTS", "Maximum attemps for root user login");
define("_SYSTEM_AM_ROOT_ATTEMPTS_DESC", "");

define("_SYSTEM_AM_CAPTCHA", "Enable CAPTCHA for root user login");
define("_SYSTEM_AM_CAPTCHA_DESC", "");

define("_SYSTEM_AM_ROOT_IPS", "Root user IPs");
define("_SYSTEM_AM_ROOT_IPS_DESC", "Allowed IPs '1.1.1.*|2.2.2.3' or blocked IPs '-3.3.3.3|-4.5.6.*'");

define("_SYSTEM_AM_ENVIRONMENT", "Application environment");
define("_SYSTEM_AM_ENVIRONMENT_DESC", "");
define("_SYSTEM_AM_ENVIRONMENT_PRODUCTION", "Production");
define("_SYSTEM_AM_ENVIRONMENT_QA", "QA");
define("_SYSTEM_AM_ENVIRONMENT_DEBUG", "Debug");
define("_SYSTEM_AM_ENVIRONMENT_DEVELOPMENT", "Development");

define("_SYSTEM_AM_TEXT", "Text processing");
define("_SYSTEM_AM_TEXT_DESC", "");

define("_SYSTEM_AM_TEXT_EDITOR", "WYSIWYG Editor");
define("_SYSTEM_AM_TEXT_EDITOR_DESC", "Default editor for text processing");

/*
define("_SYSTEM_AM_SECURITY", "Security policy");
define("_SYSTEM_AM_SECURITY_DESC", "");
*/

define("_SYSTEM_AM_CPANEL", "Control panel");
define("_SYSTEM_AM_CPANEL_DESC", "");
