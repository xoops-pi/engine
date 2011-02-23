<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/**
 * TextSanitizer extension
 *
 * @copyright   The XOOPS project http://www.xoops.org/
 * @license     http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package     class
 * @since       2.3.0
 * @author      Kazumi Ono (http://www.myweb.ne.jp/, http://jp.xoops.org/)
 * @author      Goghs Cheng (http://www.eqiao.com, http://www.devbeez.com/)
 * @author      Taiwen Jiang <phppp@users.sourceforge.net>
 * @version     $Id: module.textsanitizer.php 2781 2009-02-09 04:02:45Z phppp $
 */

/**
 * Abstract class for extensions
 *
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @copyright       The Xoops Project
 */
class MyTextSanitizerExtension
{
    var $instance;
    var $ts;
    var $config;
    var $image_path;

    function __construct(&$ts)
    {
        $this->ts = $ts;
        $this->image_path = Xoops::url('www') . '/images/form';
    }

    /*
    function &getInstance($myts)
    {
        if (!isset(self::$instance)) {
            $class = __CLASS__;
            self::$instance = new $class($myts);
        }
        return self::$instance;
    }

    function load($myts, $config = null)
    {
        $instance = self::getInstance($myts);
        if ($config) {
            $this->config = $instance->loadConfig($config);
        }
    }
    */

    function loadConfig($path = null)
    {
        $ts =& MyTextSanitizer::getInstance();
        $path = str_replace( DIRECTORY_SEPARATOR, "/", $path );
        if (false === strpos($path, "/")) {
            if (is_dir("{$ts->path_basic}/{$path}/")) {
                $path = "{$ts->path_basic}/{$path}/";
            } else {
                $path = "{$ts->path_plugin}/{$path}/";
            }
        }
        $config_default = include "{$path}/config.php";
        if (file_exists("{$path}/config.custom.php")) {
            $config_custom = include "{$path}/config.custom.php";
        } else {
            $config_custom = array();
        }
        return  $this->mergeConfig($config_default, $config_custom);
    }

    function mergeConfig( $config_default, $config_custom ) {
        if (is_array($config_custom)) {
            foreach ($config_custom as $key => $val) {
                if (is_array($config_default[$key])) {
                    $config_default[$key] = $this->mergeConfig($config_default[$key], $config_custom[$key]);
                } else {
                    $config_default[$key] = $val;
                }
            }
        }
        return $config_default;
    }

    function encode($textarea_id)
    {
        return array();
    }

    function decode()
    {
        return null;
    }
}

/**
 * Class to "clean up" text for various uses
 *
 * <strong>Singleton</strong>
 *
 * @package         kernel
 * @subpackage      core
 *
 * @author          Kazumi Ono     <onokazu@xoops.org>
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @author          Goghs Cheng
 * @copyright       (c) 2000-2003 The Xoops Project - www.xoops.org
 */
class MyTextSanitizer
{
    /**
     * @var    array
     */
    var $smileys = array();

    /**
     *
     */
    var $censorConf;

    /**
     * @var    holding reference to text
     */
    var $text = "";
    var $patterns = array();
    var $replacements = array();

    var $path_basic;
    var $path_plugin;

    var $config;


    function __construct()
    {
        $this->path_basic = XOOPS_ROOT_PATH . "/class/textsanitizer";
        $this->path_plugin = XOOPS_ROOT_PATH . "/Frameworks/textsanitizer";
        $this->config = $this->loadConfig();
    }

    function loadConfig($name = null)
    {
        if (!empty($name)) {
            return MyTextSanitizerExtension::loadConfig($name);
        }

        $config_default = include "{$this->path_basic}/config.php";
        $config_custom = @include "{$this->path_basic}/config.custom.php";
        return $this->mergeConfig($config_default, $config_custom);
    }

    function mergeConfig( $config_default, $config_custom )
    {
        if (is_array($config_custom)) {
            foreach ($config_custom as $key => $val) {
                if (is_array($config_default[$key])) {
                    $config_default[$key] = $this->mergeConfig($config_default[$key], $config_custom[$key]);
                } else {
                    $config_default[$key] = $val;
                }
            }
        }
        return $config_default;
    }

    /**
     * Access the only instance of this class
     *
     * @return    object
     *
     * @static
     * @staticvar   object
     */
    public static function &getInstance()
    {
        static $instance;
        if (!isset($instance)) {
            $instance = new MyTextSanitizer();
        }
        return $instance;
    }

    /**
     * Get the smileys
     *
     * @param    boole  $isAll TRUE for all smileys, FALSE for smileys with display = 1
     * @return    array
     */
    function getSmileys( $isAll = TRUE )
    {
        // TODO: move smileys specs from datatbase to ini file
        /*
        if (null === $this->smileys) {
            Xoops::service('translate')->loadTranslation('smiley');
            $this->smileys = Xoops::loadConfig('smiley');
        }
        */
        if (null === $this->smileys) {
            if ($getsmiles = $GLOBALS["xoopsDB"]->query("SELECT * FROM " . $GLOBALS["xoopsDB"]->prefix("smiles"))) {
                while ($smiles = $GLOBALS["xoopsDB"]->fetchArray($getsmiles)) {
                    array_push($this->smileys, $smiles);
                }
            }
        }
        if ($isAll) {
            return $this->smileys;
        }

        $smileys = array();
        foreach ($this->smileys as $smile) {
            if (empty($smile['display'])) continue;
            $smileys[] = $smile;
        }
        return $smileys;
    }

    /**
     * Replace emoticons in the message with smiley images
     *
     * @param    string  $message
     *
     * @return    string
     */
    function smiley($message)
    {
        $smileys = $this->getSmileys();
        foreach ($smileys as $smile) {
            $message = str_replace($smile['code'], '<img src="' . XOOPS_UPLOAD_URL . '/' . htmlspecialchars($smile['smile_url']) . '" alt="" />', $message);
        }
        return $message;
    }

    /**
     * Make links in the text clickable
     *
     * @param   string  $text
     * @return  string
     **/
    function makeClickable(&$text)
    {
        $valid_chars = "a-z0-9\/\-_+=.~!%@?#&;:$\|";
        $patterns = array(
                        "/(^|[^]_a-z0-9-=\"'\/])([a-z]+?):\/\/([{$valid_chars}]+)/ei",
                        "/(^|[^]_a-z0-9-=\"'\/])www\.([a-z0-9\-]+)\.([{$valid_chars}]+)/ei",
                        "/(^|[^]_a-z0-9-=\"'\/])ftp\.([a-z0-9\-]+)\.([{$valid_chars}]+)/ei",
                        "/(^|[^]_a-z0-9-=\"'\/:\.])([a-z0-9\-_\.]+?)@([{$valid_chars}]+)/ei");
        $replacements = array(
                        "'\\1<a href=\"\\2://\\3\" title=\"\\2://\\3\" rel=\"external\">\\2://'.MyTextSanitizer::truncate( '\\3' ).'</a>'",
                        "'\\1<a href=\"http://www.\\2.\\3\" title=\"www.\\2.\\3\" rel=\"external\">'.MyTextSanitizer::truncate( 'www.\\2.\\3' ).'</a>'",
                        "'\\1<a href=\"ftp://ftp.\\2.\\3\" title=\"ftp.\\2.\\3\" rel=\"external\">'.MyTextSanitizer::truncate( 'ftp.\\2.\\3' ).'</a>'",
                        "'\\1<a href=\"mailto:\\2@\\3\" title=\"\\2@\\3\">'.MyTextSanitizer::truncate( '\\2@\\3' ).'</a>'");
        return preg_replace($patterns, $replacements, $text);
    }

    function truncate($text)
    {
        $instance = MyTextSanitizer::getInstance();
        if ( empty($text) || empty($instance->config['truncate_length']) || strlen($text) < $instance->config['truncate_length'] ) return $text;
        $len = floor( $instance->config['truncate_length'] / 2 );
        $ret = substr($text, 0, $len) . " ... ". substr($text, 5 - $len);
        return $ret;
    }

    /**
     * Replace XoopsCodes with their equivalent HTML formatting
     *
     * @param   string  $text
     * @param   bool    $allowimage Allow images in the text?
     *                              On FALSE, uses links to images.
     * @return  string
     **/
    function &xoopsCodeDecode(&$text, $allowimage = 1)
    {
        $patterns = array();
        $replacements = array();
        $patterns[] = "/\[siteurl=(['\"]?)([^\"'<>]*)\\1](.*)\[\/siteurl\]/sU";
        $replacements[] = '<a href="' . XOOPS_URL . '/\\2">\\3</a>';
        $patterns[] = "/\[url=(['\"]?)(http[s]?:\/\/[^\"'<>]*)\\1](.*)\[\/url\]/sU";
        $replacements[] = '<a href="\\2" rel="external" title="">\\3</a>';
        $patterns[] = "/\[url=(['\"]?)(ftp?:\/\/[^\"'<>]*)\\1](.*)\[\/url\]/sU";
        $replacements[] = '<a href="\\2" rel="external" title="">\\3</a>';
        $patterns[] = "/\[url=(['\"]?)([^'\"<>]*)\\1](.*)\[\/url\]/sU";
        $replacements[] = '<a href="http://\\2" rel="external" title="">\\3</a>';
        $patterns[] = "/\[color=(['\"]?)([a-zA-Z0-9]*)\\1](.*)\[\/color\]/sU";
        $replacements[] = '<span style="color: #\\2;">\\3</span>';
        $patterns[] = "/\[size=(['\"]?)([a-z0-9-]*)\\1](.*)\[\/size\]/sU";
        $replacements[] = '<span style="font-size: \\2;">\\3</span>';
        $patterns[] = "/\[font=(['\"]?)([^;<>\*\(\)\"']*)\\1](.*)\[\/font\]/sU";
        $replacements[] = '<span style="font-family: \\2;">\\3</span>';
        $patterns[] = "/\[email]([^;<>\*\(\)\"']*)\[\/email\]/sU";
        $replacements[] = '<a href="mailto:\\1">\\1</a>';
        $patterns[] = "/\[b](.*)\[\/b\]/sU";
        $replacements[] = '<strong>\\1</strong>';
        $patterns[] = "/\[i](.*)\[\/i\]/sU";
        $replacements[] = '<i>\\1</i>';
        $patterns[] = "/\[u](.*)\[\/u\]/sU";
        $replacements[] = '<u>\\1</u>';
        $patterns[] = "/\[d](.*)\[\/d\]/sU";
        $replacements[] = '<del>\\1</del>';
        $patterns[] = "/\[center](.*)\[\/center\]/sU";
        $replacements[] = '<div style="text-align: center;">\\1</div>';
        $patterns[] = "/\[left](.*)\[\/left\]/sU";
        $replacements[] = '<div style="text-align: left;">\\1</div>';
        $patterns[] = "/\[right](.*)\[\/right\]/sU";
        $replacements[] = '<div style="text-align: right;">\\1</div>';
        $patterns[] = "/\[quote]/sU";
        $replacements[] = _QUOTEC . '<div class="xoopsQuote"><blockquote>';
        $patterns[] = "/\[\/quote]/sU";
        $replacements[] = '</blockquote></div>';

        $this->text = $text;
        $this->patterns = $patterns;
        $this->replacements = $replacements;

        $this->config['allowimage'] = $allowimage;
        $this->executeExtensions();

        $text = preg_replace($this->patterns, $this->replacements, $this->text);
        return $text;
    }

    /**
     * A quick solution for filtering XSS scripts
     *
     * @TODO: To be improved
     */
    function filterXss($text)
    {
        $patterns = array();
        $replacements = array();

        $text = str_replace( "\x00", "", $text );
        $c = "[\x01-\x1f]*";
        $patterns[] = "/\bj{$c}a{$c}v{$c}a{$c}s{$c}c{$c}r{$c}i{$c}p{$c}t{$c}[\s]*:/si";
        $replacements[] = "javascript;";
        $patterns[] = "/\ba{$c}b{$c}o{$c}u{$c}t{$c}[\s]*:/si";
        $replacements[] = "about;";
        $patterns[] = "/\bx{$c}s{$c}s{$c}[\s]*:/si";
        $replacements[] = "xss;";

        $text = preg_replace($patterns, $replacements, $text);

        return $text;
    }

    /**
     * Convert linebreaks to <br /> tags
     *
     * @param    string  $text
     *
     * @return    string
     */
    function nl2Br($text)
    {
        return preg_replace("/(\015\012)|(\015)|(\012)/", "<br />", $text);
    }

    /**
     * Add slashes to the text if magic_quotes_gpc is turned off.
     *
     * @param   string  $text
     * @return  string
     **/
    function addSlashes($text)
    {
        if (!get_magic_quotes_gpc()) {
            $text = addslashes($text);
        }
        return $text;
    }

    /*
    * if magic_quotes_gpc is on, stirip back slashes
    *
    * @param    string  $text
    *
    * @return    string
    */
    function stripSlashesGPC($text)
    {
        if (get_magic_quotes_gpc()) {
            $text = stripslashes($text);
        }
        return $text;
    }

    /**
     * Convert special characters to HTML entities
     *
     * @param   string  $text           string being converted
     * @param   int     $quote_style
     * @param   string  $charset        character set used in conversion
     * @param   bool    $double_encode
     *
     * @return    string
     */
    function htmlSpecialChars( $text, $quote_style = ENT_QUOTES, $charset = "ISO-8859-1", $double_encode = true )
    {
        //return preg_replace("/&amp;/i", '&', htmlspecialchars($text, ENT_QUOTES));
        if ( version_compare( phpversion(), "5.2.3", ">=" ) ) {
            $text = htmlspecialchars( $text, $quote_style, $charset, $double_encode );
        } else {
            $text = htmlspecialchars( $text, $quote_style);
        }
        return preg_replace(
                            array("/&amp;/i", "/&nbsp;/i"),
                            array('&', '&amp;nbsp;'),
                            $text);
    }

    /**
     * Reverses {@link htmlSpecialChars()}
     *
     * @param   string  $text
     * @return  string
     **/
    function undoHtmlSpecialChars( $text )
    {
        return preg_replace(
                            array("/&gt;/i", "/&lt;/i", "/&quot;/i", "/&#039;/i", '/&amp;nbsp;/i'),
                            array(">", "<", "\"", "'", "&nbsp;"),
                            $text);
    }

    /**
     * Filters textarea form data in DB for display
     *
     * @param   string  $text
     * @param   bool    $html   allow html?
     * @param   bool    $smiley allow smileys?
     * @param   bool    $xcode  allow xoopscode?
     * @param   bool    $image  allow inline images?
     * @param   bool    $br     convert linebreaks?
     * @return  string
     **/
    function &displayTarea( $text, $html = 0, $smiley = 1, $xcode = 1, $image = 1, $br = 1)
    {
        if ($html != 1) {
            // html not allowed
            $text = $this->htmlSpecialChars($text);
        }
        $text = $this->codePreConv($text, $xcode); // Ryuji_edit(2003-11-18)
        if ($smiley != 0) {
            // process smiley
            $text = $this->smiley($text);
        }

        if ($xcode != 0) {
            // decode xcode
            if ($image != 0) {
                // image allowed
                $text = $this->xoopsCodeDecode($text);
            } else {
                // image not allowed
                $text = $this->xoopsCodeDecode($text, 0);
            }
        }
        if ($br != 0) {
            $text = $this->nl2Br($text);
        }
        $text = $this->codeConv($text, $xcode);
        $text = $this->makeClickable($text);
        if (!empty($this->config['filterxss_on_display'])) {
            $text = $this->filterXss($text);
        }
        return $text;
    }

    /**
     * Filters textarea form data submitted for preview
     *
     * @param   string  $text
     * @param   bool    $html   allow html?
     * @param   bool    $smiley allow smileys?
     * @param   bool    $xcode  allow xoopscode?
     * @param   bool    $image  allow inline images?
     * @param   bool    $br     convert linebreaks?
     * @return  string
     **/
    function &previewTarea( $text, $html = 0, $smiley = 1, $xcode = 1, $image = 1, $br = 1)
    {
        $text = $this->stripSlashesGPC($text);
        $text = $this->displayTarea( $text, $html, $smiley, $xcode, $image, $br );
        return $text;
    }

    /**
     * Replaces banned words in a string with their replacements
     *
     * @param   string $text
     * @return  string
     *
     * @deprecated
     **/
    function &censorString(&$text)
    {
        $ret = $this->executeExtension('censor', $text);
        if ($ret === false) {
            return $text;
        }
        return $ret;
    }

    /**#@+
     * Sanitizing of [code] tag
     */
    function codePreConv($text, $xcode = 1)
    {
        if ($xcode != 0) {
            $patterns = "/\[code([^\]]*?)\](.*)\[\/code\]/esU";
            $replacements = "'[code\\1]'.base64_encode('\\2').'[/code]'";
            $text =  preg_replace($patterns, $replacements, $text);
        }
        return $text;
    }

    function codeConv($text, $xcode = 1)
    {
        if (empty($xcode)) return $text;
        $patterns = "/\[code([^\]]*?)\](.*)\[\/code\]/esU";
        $replacements = "'<div class=\"xoopsCode\"><code>'.\$this->executeExtension('syntaxhighlight', \$this->htmlSpecialChars(str_replace('\\\"', '\"', base64_decode('$2'))), '$1').'</code></div>'";
        $text =  preg_replace($patterns, $replacements, $text);
        return $text;
    }
    /**#@-*/

    function executeExtensions()
    {
        $extensions = array_filter($this->config['extensions']);
        if (empty($extensions)) return true;
        foreach (array_keys($extensions) as $extension) {
            $this->executeExtension($extension);
        }
    }

    function loadExtension($name)
    {
        if (! include_once "{$this->path_basic}/{$name}/{$name}.php" ) {
            if (! include_once "{$this->path_plugin}/{$name}/{$name}.php" ) {
                return false;
            }
        }
        $class = "Myts" . ucfirst($name);
        if (! class_exists($class) ) {
            trigger_error("Extension {$name} not exist", E_USER_WARNING);
            return false;
        }
        $extension = new $class($this);
        return $extension;
    }

    function executeExtension($name)
    {
        $extension = $this->loadExtension($name);
        $args = array_slice(func_get_args(), 1);

        return call_user_func_array(array($extension, 'load'), array_merge( array(&$this), $args));
    }

    /*
     * Filter out possible malicious text
     * kses project at SF could be a good solution to check
     *
     * @param string    $text     text to filter
     * @param bool        $force     force filtering
     * @return string     filtered text
     */
    function textFilter($text, $force = false)
    {
        $ret = $this->executeExtension('textfilter', $text, $force);
        if ($ret === false) {
            return $text;
        }
        return $ret;
    }


##################### Deprecated Methods ######################

    /**#@+
     * @deprecated
     */

    function codeSanitizer($str, $image = 1)
    {
        trigger_error(__CLASS__."::".__FUNCTION__.' is deprecated', E_USER_WARNING);
        $str =  $this->htmlSpecialChars(str_replace('\"', '"', base64_decode($str)));
        $str = $this->xoopsCodeDecode($str, $image);
        return $str;
    }

    function sanitizeForDisplay($text, $allowhtml = 0, $smiley = 1, $bbcode = 1)
    {
        trigger_error(__CLASS__."::".__FUNCTION__.' is deprecated', E_USER_WARNING);
        if ( $allowhtml == 0 ) {
            $text = $this->htmlSpecialChars($text);
        } else {
            //$config =& $GLOBALS['xoopsConfig'];
            //$allowed = $config['allowed_html'];
            //$text = strip_tags($text, $allowed);
            $text = $this->makeClickable($text);
        }
        if ( $smiley == 1 ) {
            $text = $this->smiley($text);
        }
        if ( $bbcode == 1 ) {
            $text = $this->xoopsCodeDecode($text);
        }
        $text = $this->nl2Br($text);
        return $text;
    }

    function sanitizeForPreview($text, $allowhtml = 0, $smiley = 1, $bbcode = 1)
    {
        trigger_error(__CLASS__."::".__FUNCTION__.' is deprecated', E_USER_WARNING);
        $text = $this->oopsStripSlashesGPC($text);
        if ( $allowhtml == 0 ) {
            $text = $this->htmlSpecialChars($text);
        } else {
            //$config =& $GLOBALS['xoopsConfig'];
            //$allowed = $config['allowed_html'];
            //$text = strip_tags($text, $allowed);
            $text = $this->makeClickable($text);
        }
        if ( $smiley == 1 ) {
            $text = $this->smiley($text);
        }
        if ( $bbcode == 1 ) {
            $text = $this->xoopsCodeDecode($text);
        }
        $text = $this->nl2Br($text);
        return $text;
    }

    function makeTboxData4Save($text)
    {
        trigger_error(__CLASS__."::".__FUNCTION__.' is deprecated', E_USER_WARNING);
        //$text = $this->undoHtmlSpecialChars($text);
        return $this->addSlashes($text);
    }

    function makeTboxData4Show($text, $smiley=0)
    {
        trigger_error(__CLASS__."::".__FUNCTION__.' is deprecated', E_USER_WARNING);
        $text = $this->htmlSpecialChars($text);
        return $text;
    }

    function makeTboxData4Edit($text)
    {
        trigger_error(__CLASS__."::".__FUNCTION__.' is deprecated', E_USER_WARNING);
        return $this->htmlSpecialChars($text);
    }

    function makeTboxData4Preview($text, $smiley=0)
    {
        trigger_error(__CLASS__."::".__FUNCTION__.' is deprecated', E_USER_WARNING);
        $text = $this->stripSlashesGPC($text);
        $text = $this->htmlSpecialChars($text);
        return $text;
    }

    function makeTboxData4PreviewInForm($text)
    {
        trigger_error(__CLASS__."::".__FUNCTION__.' is deprecated', E_USER_WARNING);
        $text = $this->stripSlashesGPC($text);
        return $this->htmlSpecialChars($text);
    }

    function makeTareaData4Save($text)
    {
        trigger_error(__CLASS__."::".__FUNCTION__.' is deprecated', E_USER_WARNING);
        return $this->addSlashes($text);
    }

    function &makeTareaData4Show(&$text, $html=1, $smiley=1, $xcode=1)
    {
        trigger_error(__CLASS__."::".__FUNCTION__.' is deprecated', E_USER_WARNING);
        $text = $this->displayTarea($text, $html, $smiley, $xcode);
        return $text;
    }

    function makeTareaData4Edit($text)
    {
        trigger_error(__CLASS__."::".__FUNCTION__.' is deprecated', E_USER_WARNING);
        return $this->htmlSpecialChars($text);
    }

    function &makeTareaData4Preview(&$text, $html=1, $smiley=1, $xcode=1)
    {
        trigger_error(__CLASS__."::".__FUNCTION__.' is deprecated', E_USER_WARNING);
        $text = $this->previewTarea($text, $html, $smiley, $xcode);
        return $text;
    }

    function makeTareaData4PreviewInForm($text)
    {
        trigger_error(__CLASS__."::".__FUNCTION__.' is deprecated', E_USER_WARNING);
        //if magic_quotes_gpc is on, do stipslashes
        $text = $this->stripSlashesGPC($text);
        return $this->htmlSpecialChars($text);
    }

    function makeTareaData4InsideQuotes($text)
    {
        trigger_error(__CLASS__."::".__FUNCTION__.' is deprecated', E_USER_WARNING);
        return $this->htmlSpecialChars($text);
    }

    function oopsStripSlashesGPC($text)
    {
        trigger_error(__CLASS__."::".__FUNCTION__.' is deprecated', E_USER_WARNING);
        return $this->stripSlashesGPC($text);
    }

    function oopsStripSlashesRT($text)
    {
        trigger_error(__CLASS__."::".__FUNCTION__.' is deprecated', E_USER_WARNING);
        if (get_magic_quotes_runtime()) {
            $text = stripslashes($text);
        }
        return $text;
    }

    function oopsAddSlashes($text)
    {
        trigger_error(__CLASS__."::".__FUNCTION__.' is deprecated', E_USER_WARNING);
        return $this->addSlashes($text);
    }

    function oopsHtmlSpecialChars($text)
    {
        trigger_error(__CLASS__."::".__FUNCTION__.' is deprecated', E_USER_WARNING);
        return $this->htmlSpecialChars($text);
    }

    function oopsNl2Br($text)
    {
        trigger_error(__CLASS__."::".__FUNCTION__.' is deprecated', E_USER_WARNING);
        return $this->nl2br($text);
    }
    /**#@-*/
}
?>
