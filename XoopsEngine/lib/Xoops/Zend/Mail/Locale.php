<?php
/**
 * Zend Framework for Xoops Engine
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
 * @category        Xoops_Zend
 * @package         Mail
 * @version         $Id$
 */

class Xoops_Zend_Mail_Locale extends Zend_Mail
{
    protected static $instance;

    /**
     * Locale handler
     */
    protected static $locale;

    /**
     * Public constructor
     *
     * @param string $charset
     */
    public function __construct($charset = null)
    {
        if (is_null($charset)) {
            $charset = XOOPS::config('charset');
        }
        $this->_charset = $charset;
    }

    public function instance($charset = null)
    {
        if (!static::$instance) {
            $class = __CLASS__;
            static::$instance = new $class($charset);
        }
        return static::$instance;
    }

    public function setLocale($locale)
    {
        //if ($locale instanceof Xoops_Zend_Locale) {
            static::$locale = $locale;
        //}

        return $this;
    }

    public function getLocale()
    {
        if (!static::$locale) {
            static::$locale = XOOPS::registry("locale");
        }
        return static::$locale;
    }

    protected function convert(&$text)
    {
        return;

        if ($this->getLocale()->getCharset() == $this->getCharset()) {
            return;
        }
        if (!function_exists("mb_convert_encoding")) {
            return;
        }
        $text = mb_convert_encoding($text, $this->getCharset(), $this->getLocale()->getCharset());
    }

    /**
     * Sets the text body for the message.
     *
     * @param  string $txt
     * @param  string $charset
     * @param  string $encoding
     * @return Zend_Mail Provides fluent interface
    */
    public function setBodyText($txt, $charset = null, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE)
    {
        $this->convert($txt);
        return parent::setBodyText($txt, $charset, $encoding);
    }

    /**
     * Sets the HTML body for the message
     *
     * @param  string    $html
     * @param  string    $charset
     * @param  string    $encoding
     * @return Zend_Mail Provides fluent interface
     */
    public function setBodyHtml($html, $charset = null, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE)
    {
        $this->convert($html);
        return parent::setBodyHtml($html, $charset, $encoding);
    }

    /**
     * Adds To-header and recipient, $email can be an array, or a single string address
     *
     * @param  string|array $email
     * @param  string $name
     * @return Zend_Mail Provides fluent interface
     */
    public function addTo($email, $name='')
    {
        if (!is_array($email)) {
            $email = array($name => $email);
        }

        $emailConverted = array();
        foreach ($email as $n => $recipient) {
            $this->convert($n);
            $emailConverted[$n] = $recipient;
        }

        return parent::addTo($emailConverted);
    }

    /**
     * Adds Cc-header and recipient, $email can be an array, or a single string address
     *
     * @param  string|array    $email
     * @param  string    $name
     * @return Zend_Mail Provides fluent interface
     */
    public function addCc($email, $name='')
    {
        if (!is_array($email)) {
            $email = array($name => $email);
        }

        $emailConverted = array();
        foreach ($email as $n => $recipient) {
            $this->convert($n);
            $emailConverted[$n] = $recipient;
        }

        return parent::addCc($emailConverted);
    }


    /**
     * Sets From-header and sender of the message
     *
     * @param  string    $email
     * @param  string    $name
     * @return Zend_Mail Provides fluent interface
     * @throws Zend_Mail_Exception if called subsequent times
     */
    public function setFrom($email, $name = null)
    {
        $this->convert($name);
        return parent::setFrom($email, $name);
    }

    /**
     * Set Reply-To Header
     *
     * @param string $email
     * @param string $name
     * @return Zend_Mail
     * @throws Zend_Mail_Exception if called more than one time
     */
    public function setReplyTo($email, $name = null)
    {
        $this->convert($name);
        return parent::setReplyTo($email, $name);
    }

    /**
     * Sets the subject of the message
     *
     * @param   string    $subject
     * @return  Zend_Mail Provides fluent interface
     * @throws  Zend_Mail_Exception
     */
    public function setSubject($subject)
    {
        $this->convert($subject);
        return parent::setSubject($subject);
    }

    /**
     * Add a custom header to the message
     *
     * @param  string              $name
     * @param  string              $value
     * @param  boolean             $append
     * @return Zend_Mail           Provides fluent interface
     * @throws Zend_Mail_Exception on attempts to create standard headers
     */
    public function addHeader($name, $value, $append = false)
    {
        $this->convert($value);
        return parent::addHeader($name, $value, $append);
    }

}
