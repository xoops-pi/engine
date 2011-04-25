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
 * @copyright       Xoops Engine http://www.xoopsengine.org/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         Kernel/Service
 * @version         $Id$
 */

namespace Kernel\Service;

class Logger extends ServiceAbstract
{
    protected $active;
    protected $logger;
    protected $container;

    public function __construct($options = array())
    {
        if ('qa' == \XOOPS::config('environment')) {
            $this->configFile = 'logger';
        }
        parent::__construct($options);

        \XOOPS::registry("logger", $this);
    }

    public function setLogger($logger = null)
    {
        $this->logger = $logger;
    }

    public function enabled($flag = null)
    {
        if (null !== $flag) {
            $this->active = (bool) $flag;
        } elseif (null === $this->active) {
            if (\XOOPS::config('environment') == "production") {
                $this->active = false;
            } elseif (\XOOPS::config('environment') == "qa") {
                if (empty($this->options['ip'])) {
                    $this->active = false;
                } else {
                    $this->active = \Xoops\Security::ip(array('good' => $this->options['ip'])) ? true : false;
                }
            } else {
                $this->active = true;
            }
        }
        return $this->active;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function getLogs()
    {
        return $this->container;
    }

    public function shutdown()
    {
        if (!$this->enabled() || !sef::getLogger()) {
            return;
        }

        $this->getLogger()->shutdown();
    }

    /**
     * Undefined method handler allows a shortcut:
     *   $log->priorityName('message')
     *     instead of
     *   $log->log('message', Zend_Log::PRIORITY_NAME)
     *
     * @param  string  $method  priority name
     * @param  string  $params  message to log
     * @return void
     */
    public function __call($method, $params)
    {
        if (!$this->enabled()) {
            return;
        }

        $this->log(array_shift($params), $method, array_shift($params));
    }

    /**
     * Log a message at a priority
     *
     * @param  string   $message   Message to log
     * @param  integer|string  $priority  Priority of message
     * @return void
     */
    public function log($message, $priority = null, $category = "debug", $time = null)
    {
        if (!$this->enabled()) {
            return;
        }
        $this->container[] = array( "message"   => $message,
                                    "priority"  => $priority,
                                    "category"  => $category,
                                    "time"      => is_null($time) ? microtime(true) : $time);
    }
}