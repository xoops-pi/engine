<?php
/**
 * Kernel service
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
 * @package         Kernel/Service
 * @version         $Id$
 */

namespace Kernel\Service;

class Translate extends ServiceAbstract
{
    //const   HANDLER_CLASS;
    protected $handler;

    /**
     * Constructor
     *
     * @param array     $options    Parameters to send to the service during instanciation
     */
    public function __construct($options = array())
    {
        parent::__construct($options);
        $this->loadHandler($options);
    }

    public function loadHandler($options)
    {
        throw new \Exception('The abstract method can not be accessed directly');
    }

    public function setHandler($handler)
    {
        $this->handler = $handler;
        return $this;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getAdapter()
    {
        return $this->handler->getAdapter();
    }

    /**
     * Get translation data path
     *
     */
    public function getPath($domain = "", $locale = null)
    {
        return $this->handler->getPath($domain, $locale);
    }

    /**
     * Load translation data
     *
     * @param  string       $data    Translation data
     * @param  string       $domain  global, module, theme, plugin, etc.
     * @param  string       $locale  (optional) Locale/Language to add data for
     * @param  array        $options (optional) Option for this Adapter
     * @return
     */
    public function loadTranslation($data, $domain = "", $locale = null, $options = array())
    {
        $this->handler->loadTranslation($data, $domain, $locale, $options);
        return $this;
    }

    /**
     * Translates the given string
     * returns the translation
     *
     * @param  string             $message Translation string
     * @param  string               $locale    (optional) Locale/Language to use
     * @return string
     */
    public function _($message, $locale = null)
    {
        return $this->handler->_($message, $locale);
    }

}