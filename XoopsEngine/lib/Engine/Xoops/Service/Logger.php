<?php
/**
 * XOOPS Log service class
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
 * @package         Xoops_Service
 * @version         $Id$
 */

namespace Engine\Xoops\Service;

class Logger extends \Kernel\Service\Logger
{
    // Enable debug
    protected $silent;
    protected $debugger;

    public function setLogger($logger = null)
    {
        if ($logger instanceof Xoops_Zend_Log) {
            $this->logger = $logger;
        }
    }

    public function silent($flag = null)
    {
        if (!is_null($flag)) {
            $this->silent = (bool) $flag;
            return $this->silent;
        } elseif (!$this->enabled()) {
            $this->silent = true;
            return $this->silent;
        } elseif (!isset($this->silent)) {
            if (isset($this->options['silent'])) {
                $this->silent = $this->options['silent'];
                return $this->silent;
            } else {
                if ($front = \XOOPS::registry('frontController')) {
                    $request = $front->getRequest();
                    if ($request && ($request->isXmlHttpRequest() || $request->isFlashRequest())) {
                        $this->silent = true;
                        return $this->silent;
                    }
                }
                if (\XOOPS::config('environment') == "production") {
                    $this->silent = true;
                    return $this->silent;
                }
            }
        }
        return $this->silent;
    }

    public function getLogger()
    {
        if (!isset($this->logger)) {
            $this->logger = new \Xoops_Zend_Log();
            if (isset($this->options['writers'])) {
                foreach ($this->options['writers'] as $writer) {
                    $writer_class = "Zend_Log_Writer_" . ucfirst($writer);
                    if (class_exists("Xoops_" . $writer_class)) {
                        $writer_class = "Xoops_" . $writer_class;
                        $this->logger->addWriter(new $writer_class);
                    } elseif (class_exists($writer_class)) {
                        $this->logger->addWriter(new $writer_class);
                    }
                }
            }
        }
        if (!isset($this->debugger) && !$this->silent()) {
            $writer_class = "Xoops_Zend_Log_Writer_Debugger";
            $this->debugger = new $writer_class();
            $this->logger->addWriter($this->debugger);
        }

        return $this->logger;
    }

    public function shutdown()
    {
        if (!$this->enabled()) {
            return;
        }

        $this->getLogger()->shutdown();

        if (isset($this->debugger)) {
            $this->debugger->render();
        }
    }
}