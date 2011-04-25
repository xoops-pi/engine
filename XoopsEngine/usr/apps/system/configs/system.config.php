<?php
/**
 * System config
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

$config = array();

// Config categories
$config["categories"] = array(
        array("key"     => 'general',
            "name"      => '_SYSTEM_AM_GENERAL',
            "description"      => ''),
        /*
        array("key"     => 'user',
            "name"      => '_SYSTEM_AM_USERSETTINGS',
            "description"      => ''),
        */
        array("key"     => 'meta',
            "name"      => '_SYSTEM_AM_METAFOOTER',
            "description"      => ''),
        /*
        array("key"     => 'censor',
            "name"      => '_SYSTEM_AM_CENSOR',
            "description"      => ''),
        array("key"     => 'search',
            "name"      => '_SYSTEM_AM_SEARCH',
            "description"      => ''),
        */
        array("key"     => 'mail',
            "name"      => '_SYSTEM_AM_MAILER',
            "description"      => ''),
        array("key"     => 'text',
            "name"      => '_SYSTEM_AM_TEXT',
            "description"      => ''),
        /*
        array("key"     => 'auth',
            "name"      => '_SYSTEM_AM_AUTHENTICATION',
            "description"      => ''),
        */
        array("key"     => 'root',
            "name"      => '_SYSTEM_AM_ROOT',
            "description"      => ''),
);

// Config items

// General section
$i = 0;
$config['items'][$i]['name'] = 'sitename';
$config['items'][$i]['title'] = '_SYSTEM_AM_SITENAME';
//$config['items'][$i]['description'] = '_SYSTEM_AM_SITENAMEDSC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = "Web Applications";
$config['items'][$i]['category'] = 'general';

$i++;
$config['items'][$i]['name'] = 'slogan';
$config['items'][$i]['title'] = '_SYSTEM_AM_SLOGAN';
//$config['items'][$i]['description'] = '_SYSTEM_AM_SLOGANDSC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = "Powered by Xoops Engine.";
$config['items'][$i]['category'] = 'general';

$i++;
$config['items'][$i]['name'] = 'adminmail';
$config['items'][$i]['title'] = '_SYSTEM_AM_ADMINML';
//$config['items'][$i]['description'] = '_SYSTEM_AM_ADMINMLDSC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'email';
$config['items'][$i]['default'] = "";
$config['items'][$i]['category'] = 'general';

$i++;
$config['items'][$i]['name'] = 'language';
$config['items'][$i]['title'] = '_SYSTEM_AM_LANGUAGE';
$config['items'][$i]['description'] = '_SYSTEM_AM_LANGUAGEDSC';
$config['items'][$i]['edit'] = 'language';
$config['items'][$i]['filter'] = '';
$config['items'][$i]['default'] = Xoops::config('language'); //empty($GLOBALS['setup_system_language']) ? 'english' : $GLOBALS['setup_system_language'];
$config['items'][$i]['category'] = 'general';

$i++;
$config['items'][$i]['name'] = 'locale';
$config['items'][$i]['title'] = '_SYSTEM_AM_LOCALE';
$config['items'][$i]['description'] = '_SYSTEM_AM_LOCALE_DESC';
$config['items'][$i]['edit'] = 'none';
$config['items'][$i]['filter'] = '';
$config['items'][$i]['default'] = Xoops::config('locale'); //empty($GLOBALS['setup_system_locale']) ? 'en' : $GLOBALS['setup_system_locale'];
$config['items'][$i]['category'] = 'general';

$i++;
$config['items'][$i]['name'] = 'charset';
$config['items'][$i]['title'] = '_SYSTEM_AM_CHARSET';
$config['items'][$i]['description'] = '_SYSTEM_AM_CHARSET_DESC';
$config['items'][$i]['edit'] = 'none';
$config['items'][$i]['filter'] = '';
$config['items'][$i]['default'] = Xoops::config('charset'); //empty($GLOBALS['setup_system_charset']) ? 'UTF-8' : $GLOBALS['setup_system_charset'];
$config['items'][$i]['category'] = 'general';

/*
$i++;
$config['items'][$i]['name'] = 'navigation';
$config['items'][$i]['title'] = '_SYSTEM_AM_FRONTNAVIGATION';
$config['items'][$i]['description'] = '_SYSTEM_AM_FRONTNAVIGATION_DESC';
$config['items'][$i]['edit'] = 'navigation';
$config['items'][$i]['filter'] = '';
$config['items'][$i]['default'] = 'front';
$config['items'][$i]['category'] = 'general';
*/

$i++;
$config['items'][$i]['name'] = 'startpage';
$config['items'][$i]['title'] = '_SYSTEM_AM_STARTPAGE';
//$config['items'][$i]['description'] = '_SYSTEM_AM_STARTPAGEDSC';
$config['items'][$i]['edit'] = array('module' => 'system', 'type' => 'startpage');
$config['items'][$i]['filter'] = '';
$config['items'][$i]['default'] = '';
$config['items'][$i]['category'] = 'general';

$i++;
$config['items'][$i]['name'] = 'server_TZ';
$config['items'][$i]['title'] = '_SYSTEM_AM_SERVERTZ';
//$config['items'][$i]['description'] = '_SYSTEM_AM_SERVERTZDSC';
$config['items'][$i]['edit'] = 'timezone';
$config['items'][$i]['filter'] = '';
//$config['items'][$i]['default'] = "";
$config['items'][$i]['category'] = 'general';

$i++;
$config['items'][$i]['name'] = 'default_TZ';
$config['items'][$i]['title'] = '_SYSTEM_AM_DEFAULTTZ';
//$config['items'][$i]['description'] = '_SYSTEM_AM_DEFAULTTZDSC';
$config['items'][$i]['edit'] = 'timezone';
$config['items'][$i]['filter'] = '';
//$config['items'][$i]['default'] = "";
$config['items'][$i]['category'] = 'general';

$i++;
$config['items'][$i]['name'] = 'theme_set';
$config['items'][$i]['title'] = '_SYSTEM_AM_DTHEME';
//$config['items'][$i]['description'] = '_SYSTEM_AM_DTHEMEDSC';
$config['items'][$i]['edit'] = 'theme';
$config['items'][$i]['filter'] = '';
$config['items'][$i]['default'] = "default";
$config['items'][$i]['category'] = 'general';

$i++;
$config['items'][$i]['name'] = 'cpanel';
$config['items'][$i]['title'] = '_SYSTEM_AM_CPANEL';
//$config['items'][$i]['description'] = '_SYSTEM_AM_CPANEL_DESC';
$config['items'][$i]['edit'] = array('module' => 'system', 'type' => 'cpanel');
$config['items'][$i]['filter'] = '';
$config['items'][$i]['default'] = "default";
$config['items'][$i]['category'] = 'general';

/*
$i++;
$config['items'][$i]['name'] = 'theme_fromfile';
$config['items'][$i]['title'] = '_SYSTEM_AM_THEMEFILE';
$config['items'][$i]['description'] = '_SYSTEM_AM_THEMEFILEDSC';
$config['items'][$i]['edit'] = 'yesno';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = "0";
$config['items'][$i]['category'] = 'general';

$i++;
$config['items'][$i]['name'] = 'theme_set_allowed';
$config['items'][$i]['title'] = '_SYSTEM_AM_THEMEOK';
$config['items'][$i]['description'] = '_SYSTEM_AM_THEMEOKDSC';
$config['items'][$i]['edit'] = array('type' => 'theme', 'multiple' => 'multiple');
$config['items'][$i]['filter'] = 'array';
$config['items'][$i]['default'] = array('default');
$config['items'][$i]['category'] = 'general';

$i++;
$config['items'][$i]['name'] = 'auth_method';
$config['items'][$i]['title'] = '_SYSTEM_AM_AUTHMETHOD';
$config['items'][$i]['description'] = '_SYSTEM_AM_AUTHMETHODDESC';
$config['items'][$i]['edit'] = array('type' => 'authentication', 'module' => 'system');
$config['items'][$i]['default'] = "xoops";
$config['items'][$i]['category'] = 'auth';

$i++;
$config['items'][$i]['name'] = 'template_set';
$config['items'][$i]['title'] = '_SYSTEM_AM_DTPLSET';
$config['items'][$i]['description'] = '_SYSTEM_AM_DTPLSETDSC';
$config['items'][$i]['edit'] = 'tplset';
$config['items'][$i]['filter'] = '';
$config['items'][$i]['default'] = "default";
$config['items'][$i]['category'] = 'general';

$i++;
$config['items'][$i]['name'] = 'cpanel';
$config['items'][$i]['title'] = '_SYSTEM_AM_CPANEL';
$config['items'][$i]['description'] = '_SYSTEM_AM_CPANELDSC';
$config['items'][$i]['edit'] = 'cpanel';
$config['items'][$i]['filter'] = '';
$config['items'][$i]['default'] = 'default';
$config['items'][$i]['category'] = 'general';

$i++;
$config['items'][$i]['name'] = 'anonymous';
$config['items'][$i]['title'] = '_SYSTEM_AM_ANONNAME';
//$config['items'][$i]['description'] = '_SYSTEM_AM_ANONNAMEDSC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = _SYSTEM_MI_ANONYMOUS;
$config['items'][$i]['category'] = 'general';
*/

$i++;
$config['items'][$i]['name'] = 'gzip_compression';
$config['items'][$i]['title'] = '_SYSTEM_AM_USEGZIP';
//$config['items'][$i]['description'] = '_SYSTEM_AM_USEGZIPDSC';
$config['items'][$i]['edit'] = 'yesno';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = "0";
$config['items'][$i]['category'] = 'general';

/*
$i++;
$config['items'][$i]['name'] = 'usercookie';
$config['items'][$i]['title'] = '_SYSTEM_AM_USERCOOKIE';
$config['items'][$i]['description'] = '_SYSTEM_AM_USERCOOKIEDSC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = "xoops_user";
$config['items'][$i]['category'] = 'general';

$i++;
$config['items'][$i]['name'] = 'use_mysession';
$config['items'][$i]['title'] = '_SYSTEM_AM_USEMYSESS';
$config['items'][$i]['description'] = '_SYSTEM_AM_USEMYSESSDSC';
$config['items'][$i]['edit'] = 'yesno';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = "0";
$config['items'][$i]['category'] = 'general';

$i++;
$config['items'][$i]['name'] = 'session_expire';
$config['items'][$i]['title'] = '_SYSTEM_AM_SESSEXPIRE';
$config['items'][$i]['description'] = '_SYSTEM_AM_SESSEXPIREDSC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = "15";
$config['items'][$i]['category'] = 'general';

$i++;
$config['items'][$i]['name'] = 'session_name';
$config['items'][$i]['title'] = '_SYSTEM_AM_SESSNAME';
$config['items'][$i]['description'] = '_SYSTEM_AM_SESSNAMEDSC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = "xoops_session";
$config['items'][$i]['category'] = 'general';

$i++;
$config['items'][$i]['name'] = 'debug_mode';
$config['items'][$i]['title'] = '_SYSTEM_AM_DEBUGMODE';
$config['items'][$i]['description'] = '_SYSTEM_AM_DEBUGMODEDSC';
$config['items'][$i]['edit'] = 'select_multi';
$config['items'][$i]['filter'] = 'array';
$config['items'][$i]['default'] = array(1);
$config['items'][$i]['options'] = array("_SYSTEM_AM_DEBUGMODE0" => 0,
                                        "_SYSTEM_AM_DEBUGMODE1" => 1,
                                        "_SYSTEM_AM_DEBUGMODE2" => 2);
$config['items'][$i]['category'] = 'general';

$i++;
$config['items'][$i]['name'] = 'environment';
$config['items'][$i]['title'] = '_SYSTEM_AM_ENVIRONMENT';
$config['items'][$i]['description'] = '_SYSTEM_AM_ENVIRONMENT_DESC';
$config['items'][$i]['edit'] = 'select';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = "debug";
$config['items'][$i]['options'] = array("_SYSTEM_AM_ENVIRONMENT_PRODUCTION"     => "production",
                                        "_SYSTEM_AM_ENVIRONMENT_QA"             => "qa",
                                        "_SYSTEM_AM_ENVIRONMENT_DEBUG"          => "debug",
                                        "_SYSTEM_AM_ENVIRONMENT_DEVELOPMENT"    => "development");
$config['items'][$i]['category'] = 'general';
*/

/*
$i++;
$config['items'][$i]['name'] = 'banners';
$config['items'][$i]['title'] = '_SYSTEM_AM_BANNERS';
$config['items'][$i]['description'] = '_SYSTEM_AM_BANNERSDSC';
$config['items'][$i]['edit'] = 'yesno';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = "1";
$config['items'][$i]['category'] = 'general';
*/

/*
$i++;
$config['items'][$i]['name'] = 'closesite';
$config['items'][$i]['title'] = '_SYSTEM_AM_CLOSESITE';
$config['items'][$i]['description'] = '_SYSTEM_AM_CLOSESITEDSC';
$config['items'][$i]['edit'] = 'yesno';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = "0";
$config['items'][$i]['category'] = 'general';

$i++;
$config['items'][$i]['name'] = 'closesite_okgrp';
$config['items'][$i]['title'] = '_SYSTEM_AM_CLOSESITEOK';
$config['items'][$i]['description'] = '_SYSTEM_AM_CLOSESITEOKDSC';
$config['items'][$i]['edit'] = array('type' => 'role', 'multiple' => 'multiple');
$config['items'][$i]['filter'] = 'array';
$config['items'][$i]['default'] = array('1');
$config['items'][$i]['category'] = 'general';

$i++;
$config['items'][$i]['name'] = 'closesite_text';
$config['items'][$i]['title'] = '_SYSTEM_AM_CLOSESITETXT';
$config['items'][$i]['description'] = '_SYSTEM_AM_CLOSESITETXTDSC';
$config['items'][$i]['edit'] = 'textarea';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = _SYSTEM_MI_SITECLOSEDMSG;
$config['items'][$i]['category'] = 'general';
*/

$i++;
$config['items'][$i]['name'] = 'use_ssl';
$config['items'][$i]['title'] = '_SYSTEM_AM_USESSL';
//$config['items'][$i]['description'] = '_SYSTEM_AM_USESSLDSC';
$config['items'][$i]['edit'] = 'yesno';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = "0";
$config['items'][$i]['category'] = 'general';

$i++;
$config['items'][$i]['name'] = 'sslpost_name';
$config['items'][$i]['title'] = '_SYSTEM_AM_SSLPOST';
$config['items'][$i]['description'] = '_SYSTEM_AM_SSLPOSTDSC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = "xoops_ssl";
$config['items'][$i]['category'] = 'general';

$i++;
$config['items'][$i]['name'] = 'sslloginlink';
$config['items'][$i]['title'] = '_SYSTEM_AM_SSLLINK';
//$config['items'][$i]['description'] = '_SYSTEM_AM_SSLLINKDSC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = "https://";
$config['items'][$i]['category'] = 'general';

$i++;
$config['items'][$i]['name'] = 'com_mode';
$config['items'][$i]['title'] = '_SYSTEM_AM_COMMODE';
//$config['items'][$i]['description'] = '_SYSTEM_AM_COMMODEDSC';
$config['items'][$i]['edit'] = 'select';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = "nest";
$config['items'][$i]['category'] = 'general';
$config['items'][$i]['options'] = array("_SYSTEM_AM_COMMODE_NESTED"     => "nest",
                                        "_SYSTEM_AM_COMMODE_FLAT"       => "flat",
                                        "_SYSTEM_AM_COMMODE_THREADED"   => "thread");

$i++;
$config['items'][$i]['name'] = 'com_order';
$config['items'][$i]['title'] = '_SYSTEM_AM_COMORDER';
//$config['items'][$i]['description'] = '_SYSTEM_AM_COMORDERDSC';
$config['items'][$i]['edit'] = 'select';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = "0";
$config['items'][$i]['category'] = 'general';
$config['items'][$i]['options'] = array("_SYSTEM_AM_COMORDER_OLDESTFIRST"   => 0,
                                        "_SYSTEM_AM_COMORDER_NEWESTFIRST"   => 1);

/*
$i++;
$config['items'][$i]['name'] = 'module_cache';
$config['items'][$i]['title'] = '_SYSTEM_AM_MODCACHE';
$config['items'][$i]['description'] = '_SYSTEM_AM_MODCACHEDSC';
$config['items'][$i]['edit'] = 'module_cache';
$config['items'][$i]['filter'] = 'array';
$config['items'][$i]['default'] = array();
$config['items'][$i]['category'] = 'general';
*/

/*
// User section

$i++;
$config['items'][$i]['name'] = 'minpass';
$config['items'][$i]['title'] = '_SYSTEM_AM_MINPASS';
$config['items'][$i]['description'] = '_SYSTEM_AM_MINPASSDSC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = '5';
$config['items'][$i]['category'] = 'user';

$i++;
$config['items'][$i]['name'] = 'minuname';
$config['items'][$i]['title'] = '_SYSTEM_AM_MINUNAME';
$config['items'][$i]['description'] = '_SYSTEM_AM_MINUNAMEDSC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = '3';
$config['items'][$i]['category'] = 'user';

$i++;
$config['items'][$i]['name'] = 'new_user_notify';
$config['items'][$i]['title'] = '_SYSTEM_AM_NEWUNOTIFY';
$config['items'][$i]['description'] = '_SYSTEM_AM_NEWUNOTIFYDSC';
$config['items'][$i]['edit'] = 'yesno';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = '1';
$config['items'][$i]['category'] = 'user';

$i++;
$config['items'][$i]['name'] = 'new_user_notify_group';
$config['items'][$i]['title'] = '_SYSTEM_AM_NOTIFYTO';
$config['items'][$i]['description'] = '_SYSTEM_AM_NOTIFYTODSC';
$config['items'][$i]['edit'] = 'role';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = 'admin';
$config['items'][$i]['category'] = 'user';

$i++;
$config['items'][$i]['name'] = 'activation_type';
$config['items'][$i]['title'] = '_SYSTEM_AM_ACTVTYPE';
$config['items'][$i]['description'] = '_SYSTEM_AM_ACTVTYPEDSC';
$config['items'][$i]['edit'] = 'select';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = '1';
$config['items'][$i]['category'] = 'user';
$config['items'][$i]['options'] = array("_SYSTEM_AM_USERACTV"   => 0,
                                        "_SYSTEM_AM_AUTOACTV"   => 1,
                                        "_SYSTEM_AM_ADMINACTV"  => 2);

$i++;
$config['items'][$i]['name'] = 'activation_group';
$config['items'][$i]['title'] = '_SYSTEM_AM_ACTVGROUP';
$config['items'][$i]['description'] = '_SYSTEM_AM_ACTVGROUPDSC';
$config['items'][$i]['edit'] = 'role';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = 'admin';
$config['items'][$i]['category'] = 'user';

$i++;
$config['items'][$i]['name'] = 'uname_test_level';
$config['items'][$i]['title'] = '_SYSTEM_AM_UNAMELVL';
$config['items'][$i]['description'] = '_SYSTEM_AM_UNAMELVLDSC';
$config['items'][$i]['edit'] = 'select';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = '0';
$config['items'][$i]['category'] = 'user';
$config['items'][$i]['options'] = array("_SYSTEM_AM_STRICT" => 0,
                                        "_SYSTEM_AM_MEDIUM" => 1,
                                        "_SYSTEM_AM_LIGHT"  => 2);


$i++;
$config['items'][$i]['name'] = 'avatar_allow_upload';
$config['items'][$i]['title'] = '_SYSTEM_AM_AVATARALLOW';
$config['items'][$i]['description'] = '_SYSTEM_AM_AVATARALWDSC';
$config['items'][$i]['edit'] = 'yesno';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = '0';
$config['items'][$i]['category'] = 'user';

$i++;
$config['items'][$i]['name'] = 'avatar_width';
$config['items'][$i]['title'] = '_SYSTEM_AM_AVATARW';
$config['items'][$i]['description'] = '_SYSTEM_AM_AVATARWDSC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = '120';
$config['items'][$i]['category'] = 'user';

$i++;
$config['items'][$i]['name'] = 'avatar_height';
$config['items'][$i]['title'] = '_SYSTEM_AM_AVATARH';
$config['items'][$i]['description'] = '_SYSTEM_AM_AVATARHDSC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = '120';
$config['items'][$i]['category'] = 'user';

$i++;
$config['items'][$i]['name'] = 'avatar_maxsize';
$config['items'][$i]['title'] = '_SYSTEM_AM_AVATARMAX';
$config['items'][$i]['description'] = '_SYSTEM_AM_AVATARMAXDSC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = '35000';
$config['items'][$i]['category'] = 'user';

$i++;
$config['items'][$i]['name'] = 'self_delete';
$config['items'][$i]['title'] = '_SYSTEM_AM_SELFDELETE';
$config['items'][$i]['description'] = '_SYSTEM_AM_SELFDELETEDSC';
$config['items'][$i]['edit'] = 'yesno';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = '0';
$config['items'][$i]['category'] = 'user';

$i++;
$config['items'][$i]['name'] = 'bad_unames';
$config['items'][$i]['title'] = '_SYSTEM_AM_BADUNAMES';
$config['items'][$i]['description'] = '_SYSTEM_AM_BADUNAMESDSC';
$config['items'][$i]['edit'] = 'textarea';
$config['items'][$i]['filter'] = 'array';
$config['items'][$i]['default'] = array('webmaster', '^xoops', '^admin');
$config['items'][$i]['category'] = 'user';

$i++;
$config['items'][$i]['name'] = 'bad_emails';
$config['items'][$i]['title'] = '_SYSTEM_AM_BADEMAILS';
$config['items'][$i]['description'] = '_SYSTEM_AM_BADEMAILSDSC';
$config['items'][$i]['edit'] = 'textarea';
$config['items'][$i]['filter'] = 'array';
$config['items'][$i]['default'] = array('xoops.org$');
$config['items'][$i]['category'] = 'user';

$i++;
$config['items'][$i]['name'] = 'maxuname';
$config['items'][$i]['title'] = '_SYSTEM_AM_MAXUNAME';
$config['items'][$i]['description'] = '_SYSTEM_AM_MAXUNAMEDSC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = "10";
$config['items'][$i]['category'] = 'user';

$i++;
$config['items'][$i]['name'] = 'avatar_minposts';
$config['items'][$i]['title'] = '_SYSTEM_AM_AVATARMP';
$config['items'][$i]['description'] = '_SYSTEM_AM_AVATARMPDSC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = "0";
$config['items'][$i]['category'] = 'user';

$i++;
$config['items'][$i]['name'] = 'allow_chgmail';
$config['items'][$i]['title'] = '_SYSTEM_AM_ALLWCHGMAIL';
$config['items'][$i]['description'] = '_SYSTEM_AM_ALLWCHGMAILDSC';
$config['items'][$i]['edit'] = 'yesno';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = "0";
$config['items'][$i]['category'] = 'user';

$i++;
$config['items'][$i]['name'] = 'reg_dispdsclmr';
$config['items'][$i]['title'] = '_SYSTEM_AM_DSPDSCLMR';
$config['items'][$i]['description'] = '_SYSTEM_AM_DSPDSCLMRDSC';
$config['items'][$i]['edit'] = 'yesno';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = "1";
$config['items'][$i]['category'] = 'user';

$i++;
$config['items'][$i]['name'] = 'reg_disclaimer';
$config['items'][$i]['title'] = '_SYSTEM_AM_REGDSCLMR';
$config['items'][$i]['description'] = '_SYSTEM_AM_REGDSCLMRDSC';
$config['items'][$i]['edit'] = 'textarea';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = _INSTALL_DISCLMR;
$config['items'][$i]['category'] = 'user';

$i++;
$config['items'][$i]['name'] = 'allow_register';
$config['items'][$i]['title'] = '_SYSTEM_AM_ALLOWREG';
$config['items'][$i]['description'] = '_SYSTEM_AM_ALLOWREGDSC';
$config['items'][$i]['edit'] = 'yesno';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = "1";
$config['items'][$i]['category'] = 'user';

$i++;
$config['items'][$i]['name'] = 'welcome_type';
$config['items'][$i]['title'] = '_SYSTEM_AM_WELCOMETYPE';
$config['items'][$i]['description'] = '_SYSTEM_AM_WELCOMETYPE_DESC';
$config['items'][$i]['edit'] = 'select';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = "1";
$config['items'][$i]['category'] = 'user';
$config['items'][$i]['options'] = array("_NO" => 0,
                                        "_SYSTEM_AM_WELCOMETYPE_EMAIL"  => 1,
                                        "_SYSTEM_AM_WELCOMETYPE_PM"     => 2,
                                        "_SYSTEM_AM_WELCOMETYPE_BOTH"   => 3);

*/
//Meta section
$i++;
$config['items'][$i]['name'] = 'meta_keywords';
$config['items'][$i]['title'] = '_SYSTEM_AM_METAKEY';
$config['items'][$i]['description'] = '_SYSTEM_AM_METAKEYDSC';
$config['items'][$i]['edit'] = 'textarea';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = _SYSTEM_MI_META_KEYWORDS_DEFAULT;
$config['items'][$i]['category'] = 'meta';

$i++;
$config['items'][$i]['name'] = 'footer';
$config['items'][$i]['title'] = '_SYSTEM_AM_FOOTER';
$config['items'][$i]['description'] = '_SYSTEM_AM_FOOTERDSC';
$config['items'][$i]['edit'] = 'textarea';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = "Powered by Xoops Engine &copy; 2001-" . date('Y', time()) . " <a href=\"http://www.xoopsengine.org/\" rel=\"external\">Xoops Engine</a>";
$config['items'][$i]['category'] = 'meta';

/*
$i++;
$config['items'][$i]['name'] = 'meta_rating';
$config['items'][$i]['title'] = '_SYSTEM_AM_METARATING';
$config['items'][$i]['description'] = '_SYSTEM_AM_METARATINGDSC';
$config['items'][$i]['edit'] = 'select';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = 'general';
$config['items'][$i]['category'] = 'meta';
$config['items'][$i]['options'] = array(
    "_SYSTEM_AM_METAOGEN"   => "general",
    "_SYSTEM_AM_METAO14YRS" => "14 years",
    "_SYSTEM_AM_METAOREST"  => "restricted",
    "_SYSTEM_AM_METAOMAT"   => "mature"
);
*/

$i++;
$config['items'][$i]['name'] = 'meta_author';
$config['items'][$i]['title'] = '_SYSTEM_AM_METAAUTHOR';
$config['items'][$i]['description'] = '_SYSTEM_AM_METAAUTHORDSC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = 'Xoops Engine';
$config['items'][$i]['category'] = 'meta';

$i++;
$config['items'][$i]['name'] = 'meta_copyright';
$config['items'][$i]['title'] = '_SYSTEM_AM_METACOPYR';
$config['items'][$i]['description'] = '_SYSTEM_AM_METACOPYRDSC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = 'Copyright &copy; 2001-' . date("Y");
$config['items'][$i]['category'] = 'meta';

$i++;
$config['items'][$i]['name'] = 'meta_description';
$config['items'][$i]['title'] = '_SYSTEM_AM_METADESC';
$config['items'][$i]['description'] = '_SYSTEM_AM_METADESCDSC';
$config['items'][$i]['edit'] = 'textarea';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = _SYSTEM_MI_META_DESCRIPTION_DEFAULT;
$config['items'][$i]['category'] = 'meta';

/*
$i++;
$config['items'][$i]['name'] = 'meta_robots';
$config['items'][$i]['title'] = '_SYSTEM_AM_METAROBOTS';
$config['items'][$i]['description'] = '_SYSTEM_AM_METAROBOTSDSC';
$config['items'][$i]['edit'] = 'select';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = 'index,follow';
$config['items'][$i]['category'] = 'meta';
$config['items'][$i]['options'] = array("_SYSTEM_AM_INDEXFOLLOW"        => "index,follow",
                                        "_SYSTEM_AM_NOINDEXFOLLOW"      => "noindex,follow",
                                        "_SYSTEM_AM_INDEXNOFOLLOW"      => "index,nofollow",
                                        "_SYSTEM_AM_NOINDEXNOFOLLOW"    => "noindex,nofollow");
*/


/*
// Search section
$i++;
$config['items'][$i]['name'] = 'enable_search';
$config['items'][$i]['title'] = '_SYSTEM_AM_DOSEARCH';
$config['items'][$i]['description'] = '_SYSTEM_AM_DOSEARCHDSC';
$config['items'][$i]['edit'] = 'yesno';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = '1';
$config['items'][$i]['category'] = 'search';

$i++;
$config['items'][$i]['name'] = 'keyword_min';
$config['items'][$i]['title'] = '_SYSTEM_AM_MINSEARCH';
$config['items'][$i]['description'] = '_SYSTEM_AM_MINSEARCHDSC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = '5';
$config['items'][$i]['category'] = 'search';
*/

// Mail section
$i++;
$config['items'][$i]['name'] = 'mailmethod';
$config['items'][$i]['title'] = '_SYSTEM_AM_MAILERMETHOD';
$config['items'][$i]['description'] = '_SYSTEM_AM_MAILERMETHODDESC';
$config['items'][$i]['edit'] = 'select';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = 'mail';
$config['items'][$i]['category'] = 'mail';
$config['items'][$i]['options'] = array("PHP mail()"    => "mail",
                                        "sendmail"      => "sendmail",
                                        "SMTP"          => "smtp",
                                        "SMTPAuth"      => "smtpauth");
/*
$i++;
$config['items'][$i]['name'] = 'sendmailpath';
$config['items'][$i]['title'] = '_SYSTEM_AM_SENDMAILPATH';
$config['items'][$i]['description'] = '_SYSTEM_AM_SENDMAILPATHDESC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = '/usr/sbin/sendmail';
$config['items'][$i]['category'] = 'mail';
*/

$i++;
$config['items'][$i]['name'] = 'smtphost';
$config['items'][$i]['title'] = '_SYSTEM_AM_SMTPHOST';
$config['items'][$i]['description'] = '_SYSTEM_AM_SMTPHOSTDESC';
$config['items'][$i]['edit'] = 'textarea';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = "";
$config['items'][$i]['category'] = 'mail';

$i++;
$config['items'][$i]['name'] = 'smtpuser';
$config['items'][$i]['title'] = '_SYSTEM_AM_SMTPUSER';
$config['items'][$i]['description'] = '_SYSTEM_AM_SMTPUSERDESC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = '';
$config['items'][$i]['category'] = 'mail';

$i++;
$config['items'][$i]['name'] = 'smtppass';
$config['items'][$i]['title'] = '_SYSTEM_AM_SMTPPASS';
$config['items'][$i]['description'] = '_SYSTEM_AM_SMTPPASSDESC';
$config['items'][$i]['edit'] = 'password';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = '';
$config['items'][$i]['category'] = 'mail';

$i++;
$config['items'][$i]['name'] = 'from';
$config['items'][$i]['title'] = '_SYSTEM_AM_MAILFROM';
$config['items'][$i]['description'] = '_SYSTEM_AM_MAILFROMDESC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = '';
$config['items'][$i]['category'] = 'mail';

$i++;
$config['items'][$i]['name'] = 'fromname';
$config['items'][$i]['title'] = '_SYSTEM_AM_MAILFROMNAME';
$config['items'][$i]['description'] = '_SYSTEM_AM_MAILFROMNAMEDESC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = '';
$config['items'][$i]['category'] = 'mail';

$i++;
$config['items'][$i]['name'] = 'fromuid';
$config['items'][$i]['title'] = '_SYSTEM_AM_MAILFROMUID';
$config['items'][$i]['description'] = '_SYSTEM_AM_MAILFROMUIDDESC';
$config['items'][$i]['edit'] = 'user';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = '1';
$config['items'][$i]['category'] = 'mail';

/*
// Authentication section
$i++;
$config['items'][$i]['name'] = 'auth_method';
$config['items'][$i]['title'] = '_SYSTEM_AM_AUTHMETHOD';
$config['items'][$i]['description'] = '_SYSTEM_AM_AUTHMETHODDESC';
$config['items'][$i]['edit'] = 'select';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = "xoops";
$config['items'][$i]['category'] = 'auth';
$config['items'][$i]['options'] = array("_SYSTEM_AM_AUTH_CONFOPTION_XOOPS"  => "xoops",
                                        "_SYSTEM_AM_AUTH_CONFOPTION_LDAP"   => "ldap",
                                        "_SYSTEM_AM_AUTH_CONFOPTION_AD"     => "ads");

$i++;
$config['items'][$i]['name'] = 'ldap_port';
$config['items'][$i]['title'] = '_SYSTEM_AM_LDAP_PORT';
$config['items'][$i]['description'] = '_SYSTEM_AM_LDAP_PORT';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = "389";
$config['items'][$i]['category'] = 'auth';

$i++;
$config['items'][$i]['name'] = 'ldap_server';
$config['items'][$i]['title'] = '_SYSTEM_AM_LDAP_SERVER';
$config['items'][$i]['description'] = '_SYSTEM_AM_LDAP_SERVER_DESC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = "your directory server";
$config['items'][$i]['category'] = 'auth';

$i++;
$config['items'][$i]['name'] = 'ldap_base_dn';
$config['items'][$i]['title'] = '_SYSTEM_AM_LDAP_BASE_DN';
$config['items'][$i]['description'] = '_SYSTEM_AM_LDAP_BASE_DN_DESC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = "ou=Employees,o=Company";
$config['items'][$i]['category'] = 'auth';

$i++;
$config['items'][$i]['name'] = 'ldap_manager_dn';
$config['items'][$i]['title'] = '_SYSTEM_AM_LDAP_MANAGER_DN';
$config['items'][$i]['description'] = '_SYSTEM_AM_LDAP_MANAGER_DN_DESC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = "manager_dn";
$config['items'][$i]['category'] = 'auth';

$i++;
$config['items'][$i]['name'] = 'ldap_manager_pass';
$config['items'][$i]['title'] = '_SYSTEM_AM_LDAP_MANAGER_PASS';
$config['items'][$i]['description'] = '_SYSTEM_AM_LDAP_MANAGER_PASS_DESC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = "manager_pass";
$config['items'][$i]['category'] = 'auth';

$i++;
$config['items'][$i]['name'] = 'ldap_version';
$config['items'][$i]['title'] = '_SYSTEM_AM_LDAP_VERSION';
$config['items'][$i]['description'] = '_SYSTEM_AM_LDAP_VERSION_DESC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = "3";
$config['items'][$i]['category'] = 'auth';

$i++;
$config['items'][$i]['name'] = 'ldap_users_bypass';
$config['items'][$i]['title'] = '_SYSTEM_AM_LDAP_USERS_BYPASS';
$config['items'][$i]['description'] = '_SYSTEM_AM_LDAP_USERS_BYPASS_DESC';
$config['items'][$i]['edit'] = 'textarea';
$config['items'][$i]['filter'] = 'array';
$config['items'][$i]['default'] = array('admin');
$config['items'][$i]['category'] = 'auth';

$i++;
$config['items'][$i]['name'] = 'ldap_loginname_asdn';
$config['items'][$i]['title'] = '_SYSTEM_AM_LDAP_LOGINNAME_ASDN';
$config['items'][$i]['description'] = '_SYSTEM_AM_LDAP_LOGINNAME_ASDN_D';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = 'uid_asdn';
$config['items'][$i]['category'] = 'auth';

$i++;
$config['items'][$i]['name'] = 'ldap_loginldap_attr';
$config['items'][$i]['title'] = '_SYSTEM_AM_LDAP_LOGINLDAP_ATTR';
$config['items'][$i]['description'] = '_SYSTEM_AM_LDAP_LOGINLDAP_ATTR_D';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = 'uid';
$config['items'][$i]['category'] = 'auth';

$i++;
$config['items'][$i]['name'] = 'ldap_filter_person';
$config['items'][$i]['title'] = '_SYSTEM_AM_LDAP_FILTER_PERSON';
$config['items'][$i]['description'] = '_SYSTEM_AM_LDAP_FILTER_PERSON_DESC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = '';
$config['items'][$i]['category'] = 'auth';

$i++;
$config['items'][$i]['name'] = 'ldap_domain_name';
$config['items'][$i]['title'] = '_SYSTEM_AM_LDAP_DOMAIN_NAME';
$config['items'][$i]['description'] = '_SYSTEM_AM_LDAP_DOMAIN_NAME_DESC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = 'mydomain';
$config['items'][$i]['category'] = 'auth';

$i++;
$config['items'][$i]['name'] = 'ldap_provisionning';
$config['items'][$i]['title'] = '_SYSTEM_AM_LDAP_PROVIS';
$config['items'][$i]['description'] = '_SYSTEM_AM_LDAP_PROVIS_DESC';
$config['items'][$i]['edit'] = 'yesno';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = '0';
$config['items'][$i]['category'] = 'auth';

$i++;
$config['items'][$i]['name'] = 'ldap_provisionning_group';
$config['items'][$i]['title'] = '_SYSTEM_AM_LDAP_PROVIS_GROUP';
$config['items'][$i]['description'] = '_SYSTEM_AM_LDAP_PROVIS_GROUP_DSC';
$config['items'][$i]['edit'] = array('type' => 'role', 'multiple' => 'multiple');
$config['items'][$i]['filter'] = 'array';
$config['items'][$i]['default'] = array("member");
$config['items'][$i]['category'] = 'auth';

$i++;
$config['items'][$i]['name'] = 'ldap_mail_attr';
$config['items'][$i]['title'] = '_SYSTEM_AM_LDAP_MAIL_ATTR';
$config['items'][$i]['description'] = '_SYSTEM_AM_LDAP_MAIL_ATTR_DESC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = 'mail';
$config['items'][$i]['category'] = 'auth';

$i++;
$config['items'][$i]['name'] = 'ldap_givenname_attr';
$config['items'][$i]['title'] = '_SYSTEM_AM_LDAP_GIVENNAME_ATTR';
$config['items'][$i]['description'] = '_SYSTEM_AM_LDAP_GIVENNAME_ATTR_DSC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = "givenname";
$config['items'][$i]['category'] = 'auth';

$i++;
$config['items'][$i]['name'] = 'ldap_surname_attr';
$config['items'][$i]['title'] = '_SYSTEM_AM_LDAP_SURNAME_ATTR';
$config['items'][$i]['description'] = '_SYSTEM_AM_LDAP_SURNAME_ATTR_DESC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = "sn";
$config['items'][$i]['category'] = 'auth';

$i++;
$config['items'][$i]['name'] = 'ldap_field_mapping';
$config['items'][$i]['title'] = '_SYSTEM_AM_LDAP_FIELD_MAPPING_ATTR';
$config['items'][$i]['description'] = '_SYSTEM_AM_LDAP_FIELD_MAPPING_DESC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = "email=mail|name=displayname";
$config['items'][$i]['category'] = 'auth';

$i++;
$config['items'][$i]['name'] = 'ldap_provisionning_upd';
$config['items'][$i]['title'] = '_SYSTEM_AM_LDAP_PROVIS_UPD';
$config['items'][$i]['description'] = '_SYSTEM_AM_LDAP_PROVIS_UPD_DESC';
$config['items'][$i]['edit'] = 'yesno';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = "1";
$config['items'][$i]['category'] = 'auth';

$i++;
$config['items'][$i]['name'] = 'ldap_use_TLS';
$config['items'][$i]['title'] = '_SYSTEM_AM_LDAP_USETLS';
$config['items'][$i]['description'] = '_SYSTEM_AM_LDAP_USETLS_DESC';
$config['items'][$i]['edit'] = 'yesno';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = "0";
$config['items'][$i]['category'] = 'auth';
*/

/*
// Security category
$i++;
$config['items'][$i]['name'] = 'my_ip';
$config['items'][$i]['title'] = '_SYSTEM_AM_MYIP';
$config['items'][$i]['description'] = '_SYSTEM_AM_MYIPDSC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = "127.0.0.1";
$config['items'][$i]['category'] = 'security';

$i++;
$config['items'][$i]['name'] = 'enable_badips';
$config['items'][$i]['title'] = '_SYSTEM_AM_DOBADIPS';
$config['items'][$i]['description'] = '_SYSTEM_AM_DOBADIPSDSC';
$config['items'][$i]['edit'] = 'yesno';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = "0";
$config['items'][$i]['category'] = 'security';

$i++;
$config['items'][$i]['name'] = 'bad_ips';
$config['items'][$i]['title'] = '_SYSTEM_AM_BADIPS';
$config['items'][$i]['description'] = '_SYSTEM_AM_BADIPSDSC';
$config['items'][$i]['edit'] = 'textarea';
$config['items'][$i]['filter'] = 'array';
$config['items'][$i]['default'] = array('127.0.0.1');
$config['items'][$i]['category'] = 'security';
*/

// Text category
$i++;
$config['items'][$i] = array(
    'category'      => "text",
    'name'          => "editor",
    'title'         => "_SYSTEM_AM_TEXT_EDITOR",
    'description'   => "_SYSTEM_AM_TEXT_EDITOR_DESC",
    'edit'          => "editor",
    'filter'        => "string",
    'default'       => 'xoops'
);

$i++;
$config['items'][$i]['name'] = 'censor_enable';
$config['items'][$i]['title'] = '_SYSTEM_AM_DOCENSOR';
$config['items'][$i]['description'] = '_SYSTEM_AM_DOCENSORDSC';
$config['items'][$i]['edit'] = 'yesno';
$config['items'][$i]['filter'] = 'number_int';
$config['items'][$i]['default'] = 0;
$config['items'][$i]['category'] = 'text';

$i++;
$config['items'][$i]['name'] = 'censor_words';
$config['items'][$i]['title'] = '_SYSTEM_AM_CENSORWRD';
$config['items'][$i]['description'] = '_SYSTEM_AM_CENSORWRDDSC';
$config['items'][$i]['edit'] = 'textarea';
$config['items'][$i]['filter'] = 'array';
$config['items'][$i]['default'] = array('fuck', 'shit');
$config['items'][$i]['category'] = 'text';

$i++;
$config['items'][$i]['name'] = 'censor_replace';
$config['items'][$i]['title'] = '_SYSTEM_AM_CENSORRPLC';
$config['items'][$i]['description'] = '_SYSTEM_AM_CENSORRPLCDSC';
$config['items'][$i]['edit'] = 'text';
$config['items'][$i]['filter'] = 'string';
$config['items'][$i]['default'] = "#OOPS#";
$config['items'][$i]['category'] = 'text';

// Root category
$i++;
$config['items'][$i] = array(
    'category'      => "root",
    'name'          => "attempts",
    'title'         => "_SYSTEM_AM_ROOT_ATTEMPTS",
    'description'   => "_SYSTEM_AM_ROOT_ATTEMPTS_DESC",
    'edit'          => "text",
    'filter'        => "number_int",
    'default'       => 5
);

$i++;
$config['items'][$i] = array(
    'category'      => "root",
    'name'          => "captcha",
    'title'         => "_SYSTEM_AM_CAPTCHA",
    'description'   => "_SYSTEM_AM_CAPTCHA_DESC",
    'edit'          => "yesno",
    'filter'        => "number_int",
    'default'       => 1
);

$i++;
$config['items'][$i] = array(
    'category'      => "root",
    'name'          => "ips",
    'title'         => "_SYSTEM_AM_ROOT_IPS",
    'description'   => "_SYSTEM_AM_ROOT_IPS_DESC",
    'edit'          => "textarea",
    'filter'        => "array",
    'default'       => array(),
);

return $config;