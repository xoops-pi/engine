<?php
/**
 * Mail handler for Xoops Engine
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
 * @package         Xoops_Core
 * @since           3.0
 * @version         $Id$
 * @uses            Xoops_Zend_Mail_Locale
 * @uses            Xoops_Zend_Mail_Template
 * @uses            Zend_Mail_Transport_Abstract
 * @uses            Zend_Mail_Transport_Smtp
 * @uses            Zend_Mail_Transport_Sendmail
 * @uses            Zend_Mail_Exception
 */

class Xoops_Mail
{
    /**
     * Mail handler
     */
    protected $mailer;
    protected $locale;
    protected $charset;
    protected $template;
    protected $domain;
    protected static $initialized = false;

    /**
     * Public constructor
     *
     * @param string $charset
     */
    public function __construct($charset = null, $initialize = true)
    {
        $this->charset = $charset;
        if ($initialize) {
            static::initialize();
        }
    }

    public function setMailer($mailer)
    {
        if ($mailer instanceof Xoops_Zend_Mail_Locale) {
            $this->mailer = $mailer;
        }

        return $this;
    }

    public function getMailer($locale = null)
    {
        $mailer = null;

        $localeName = $locale ?: XOOPS::service("translate")->getAdapter()->getLocale();
        //$locale = $locale ?: XOOPS::registry("locale")->getLocale();
        $className = "xoops_" . $localeName . "_mail";
        if ($this->mailer && strtolower(get_class($this->mailer)) == $className) {
            return $this->mailer;
        }
        if (!class_exists($className, false)) {
            $path = XOOPS::service("translate")->getPath();
            if (!empty($path)) {
                $classFile = $path . "/class/mail.php";
                include XOOPS::path($classFile);
            }
        }
        if (!class_exists($className, false)) {
            $className = "Xoops_Zend_Mail_Locale";
        }

        $mailer = new $className();
        if (!empty($this->charset)) {
            $mailer->setCharset($this->charset);
        }
        if (!empty($locale)) {
            $mailer->setLocale($locale);
        }

        if (!$this->mailer) {
            $this->mailer = $mailer;
        }

        return $mailer;
    }

    /**
     * Sends this email using the given transport or a previously
     * set DefaultTransport or the internal mail function if no
     * default transport had been set.
     *
     * @param  Zend_Mail_Transport_Abstract $transport
     * @return Zend_Mail                    Provides fluent interface
     */
    public function send($transport = null)
    {
        $status = true;

        if (!empty($this->template)) {
            $this->template->render();
        }

        try {
            $this->getMailer()->send($transport);
        } catch (Zend_Mail_Exception $e) {
            XOOPS::service("logger")->log("Mail error: [" . get_class($e) . "] " . $e->getMessage(), "WARN");
            $status = false;
        }

        return $status;
    }

    public function __call($method, $args)
    {
        call_user_func_array(array($this->getMailer(), $method), $args);
        return $this;
    }

    /**
     * Sets the default mail transport for all following uses of
     * Zend_Mail::send();
     *
     * @todo Allow passing a string to indicate the transport to load
     * @todo Allow passing in optional options for the transport to load
     * @param  Zend_Mail_Transport_Abstract $transport
     */
    public static function setDefaultTransport(Zend_Mail_Transport_Abstract $transport)
    {
        Xoops_Zend_Mail_Locale::setDefaultTransport($transport);
    }

    /**
     * Gets the default mail transport for all following uses of
     * unittests
     *
     * @todo Allow passing a string to indicate the transport to load
     * @todo Allow passing in optional options for the transport to load
     */
    public static function getDefaultTransport()
    {
        return Xoops_Zend_Mail_Locale::getDefaultTransport();
    }

    /**
     * Clear the default transport property
     */
    public static function clearDefaultTransport()
    {
        Xoops_Zend_Mail_Locale::clearDefaultTransport();
    }

    /**
     * Sets Default From-email and name of the message
     *
     * @param  string               $email
     * @param  string    Optional   $name
     * @return void
     */
    public static function setDefaultFrom($email, $name = null)
    {
        Xoops_Zend_Mail_Locale::setDefaultFrom($email, $name);
    }

    /**
     * Returns the default sender of the mail
     *
     * @return null|array   Null if none was set.
     */
    public static function getDefaultFrom()
    {
        return Xoops_Zend_Mail_Locale::getDefaultFrom();
    }

    /**
     * Clears the default sender from the mail
     *
     * @return void
     */
    public static function clearDefaultFrom()
    {
        Xoops_Zend_Mail_Locale::clearDefaultFrom();
    }

    /**
     * Sets Default ReplyTo-address and -name of the message
     *
     * @param  string               $email
     * @param  string    Optional   $name
     * @return void
     */
    public static function setDefaultReplyTo($email, $name = null)
    {
        Xoops_Zend_Mail_Locale::setDefaultReplyTo($email, $name);
    }

    /**
     * Returns the default Reply-To Address and Name of the mail
     *
     * @return null|array   Null if none was set.
     */
    public static function getDefaultReplyTo()
    {
        return Xoops_Zend_Mail_Locale::getDefaultReplyTo();
    }

    /**
     * Clears the default ReplyTo-address and -name from the mail
     *
     * @return void
     */
    public static function clearDefaultReplyTo()
    {
        Xoops_Zend_Mail_Locale::clearDefaultReplyTo();
    }

    public static function initialize()
    {
        if (static::$initialized) return;
        static::$initialized = 1;

        $configs = XOOPS::service("registry")->config->read("", "mail");
        $email = !empty($configs['from']) ? $configs['from'] : XOOPS::config('adminmail');
        $name = !empty($configs['fromname']) ? $configs['fromname'] : XOOPS::config('sitename');

        static::setDefaultFrom($email, $name);
        static::setDefaultReplyTo($email, $name);

        switch($configs['mailmethod']) {
            case "smtpauth":
            case "smtp":
                $hosts = explode(';', $configs['smtphost']);
                $host = array_pop($hosts);
                $port = null;
                if (false !== strpos($host, ":")) {
                    list($host, $port) = explode(":", $host);
                }
                $options = array(
                    "auth"      => (empty($configs["smtpuser"]) || empty($configs["smtppass"])) ? null : "login",
                    "username"  => empty($configs["smtpuser"]) ? null : $configs["smtpuser"],
                    "password"  => empty($configs["smtppass"]) ? null : $configs["smtppass"],
                    "port"      => empty($port) ? null : $port,
                );
                $transport = new Zend_Mail_Transport_Smtp($host, $options);
                break;
            case "sendmail":
            case "mail":
            default:
                $replyTo = static::getDefaultReplyTo();
                $transport = new Zend_Mail_Transport_Sendmail('-f' . $replyTo["email"]);
                break;
        }
        static::setDefaultTransport($transport);
    }

    public function setTemplate($template, $domain = "")
    {
        $this->getTemplate()->setTemplate($template, $domain);

        return $this;
    }

    public function getTemplate()
    {
        if (!isset($this->template)) {
            $this->template = new Xoops_Zend_Mail_Template($this->getMailer());
            //$this->template->setMailer($this->getMailer());
        }
        return $this->template;
    }

    public function assign($var, $val = null)
    {
        $this->getTemplate()->assign($var, $val);
        return $this;
    }
}