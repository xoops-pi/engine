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
 * @copyright       Xoops Engine http://www.xoopsengine.org/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Log
 * @version         $Id$
 */

class Xoops_Zend_Log extends Zend_Log
{
    private static $logFlushed;
    private static $errorFlushed;
    private static $ProfilerFlushed;

    /**
     * Shutdown log writers
     *
     * @return void
     */
    public function shutdown()
    {
        $this->flushLog();
        $this->flushError();
        $this->flushProfiler();

        foreach($this->_writers as $writer) {
            $writer->shutdown();
        }
    }

    /**
     * Class destructor.  To overwrite parent __destruct
     *
     * @return void
     */
    public function __destruct()
    {
    }

    /**
     * Flush log handler
     *
     * @return void
     */
    public function flushLog()
    {
        if (!empty(self::$logFlushed)) return;

        self::$logFlushed = true;
        if (!$logHandler = XOOPS::registry("logger")) {
            return;
        }

        $priority_map = array_flip($this->_priorities);
        foreach ($logHandler->getLogs() as $log) {
            $log['priority'] = (null === $log['priority']) ? self::WARN : $log['priority'];
            if (!is_integer($log['priority'])) {
                $log['priority'] = $priority_map[strtoupper($log['priority'])];
            }
            $this->log($log['message'], $log['priority'], $log['category'], $log['time']);
        }
    }

    /**
     * Flush error handler
     *
     * @return void
     */
    public function flushError()
    {
        if (!empty(self::$errorFlushed)) return;

        self::$errorFlushed = true;
        if ($errorHandler = XOOPS::registry("error")) {
            foreach ($errorHandler->getErrors() as $error) {
                switch ($error['code']) {
                    case E_ERROR:
                    case E_CORE_ERROR:
                    case E_COMPILE_ERROR :
                    case E_USER_ERROR:
                    case E_PARSE:
                    case E_RECOVERABLE_ERROR:
                        $priority = self::ERR;
                        break;
                    case E_WARNING:
                    case E_CORE_WARNING:
                    case E_COMPILE_WARNING:
                    case E_USER_WARNING:
                        $priority = self::WARN;
                        break;
                    case E_NOTICE:
                    case E_USER_NOTICE:
                        $priority = self::NOTICE;
                        break;
                    case E_STRICT:
                    case E_DEPRECATED:
                    case E_USER_DEPRECATED:
                        $priority = self::INFO;
                        break;
                    default:
                        $priority = self::DEBUG;
                        break;
                }
                $this->log($error['message'], $priority, 'errors', $error['time']);
            }
        }
    }

    /**
     * Flush profiler data
     *
     * @return void
     */
    public function flushProfiler()
    {
        if (!empty(self::$ProfilerFlushed)) return;

        self::$ProfilerFlushed = true;
        if (!$profiler = XOOPS::registry("profiler")) {
            return;
        }
        $priority = self::INFO;
        $timers = $profiler->timers();
        $database = $profiler->database();

        foreach ($timers as $name => $timer) {
            $message = $name . ' - time: ' . sprintf("%.4f", $timer['sum']['time']);
            if (isset($timer['sum']['realmem'])) {
                 $message .= ' - realmem: ' . $timer['sum']['realmem'] . ' - emalloc: ' . $timer['sum']['emalloc'];
            }
            $this->log($message, $priority, 'timers', $timer['time']['start']);
        }

        if (!empty($database)) {
            $message = sprintf('Executed %d queries in %.4f seconds', $database['queryCount'], $database['totalTime']);
            $message .= "<br />" . sprintf('Longest query takes %.4f seconds: %s', $database['longestQuery']['time'], $database['longestQuery']['query']);
            $this->log($message, $priority, 'queries');

            foreach ($database['queries'] as $query) {
                $message = sprintf("%.4f", $query['time']) . ": " . $query['query'];
                if (!empty($query["exception"])) {
                    $message .= "<div class='exception'>" . $query["exception"]. "</div>";
                }
                $this->log($message, $priority, 'queries', $query['start']);
            }
        }
    }

    /**
     * Log a message at a priority
     *
     * @param  string   $message   Message to log
     * @param  integer  $priority  Priority of message
     * @return void
     * @throws Zend_Log_Exception
     */
    public function log($message, $priority = null, $category = "debug", $time = null)
    {
        // Debugger writer might be disabled upon silent mode
        if (empty($this->_writers)) {
            return;
        }
        try {
            $priority = (null === $priority) ? self::WARN : $priority;
            parent::log($message, $priority,
                array(
                    'category'  => $category,
                    'timestamp' => isset($time) ? $time : microtime(true),
                    )
            );
        } catch (Exception $e) {
            trigger_error($e->getMessage() . ': ' . $priority);
        }
    }
}
