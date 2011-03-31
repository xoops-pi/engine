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

class Profiler extends ServiceAbstract
{
    /**
     * Available timers
     * @var array
     */
    protected $timers = array();

    /**
     * database query profiler
     * @var array
     */
    protected $queries;

    public function __construct($options = array())
    {
        parent::__construct($options);
        $enable = isset($this->options['enable']) ? $this->options['enable'] : null;
        $this->enable($enable);
        //$GLOBALS['xoopsLogger'] = $this;
        \XOOPS::registry("profiler", $this);

        $this->start();
    }

    public function enable($enable = null)
    {
        $this->active = (null === $enable) ? (\XOOPS::config('environment') != "production") : (bool) $enable;
    }

    public function shutdown()
    {
        if (!$this->active) return;

        $this->timers();
        $this->database();
    }

    public function timers()
    {
        foreach (array_keys($this->timers) as $k) {
            $this->stop($k);
        }
        return $this->timers;
    }

    public function timer($name = 'XOOPS')
    {
        $this->stop($name);
        return $this->timers[$name]['sum'];
    }

    /**
     * Starts a timer
     * @param    string  $name   name of the timer
     */
    public function start($name = 'XOOPS')
    {
        if (!$this->active) {
            return $this;
        }

        if (!empty($this->timers[$name]['start']['time'])) {
            $this->stop($name);
        }
        $this->timers[$name]['start']['time'] = microtime(true);
        $this->timers[$name]['start']['realmem'] = memory_get_usage(true);
        $this->timers[$name]['start']['emalloc'] = memory_get_usage();
        if (empty($this->timers[$name]['time']['start'])) {
            $this->timers[$name]['time']['start'] = $this->timers[$name]['start']['time'];
        }
        return $this;
    }

    public function stop($name = 'XOOPS')
    {
        if (!$this->active) {
            return $this;
        }

        if (!empty($this->timers[$name]['start']['time'])) {
            if (!isset($this->timers[$name]['sum'])) {
                $this->timers[$name]['sum'] = array('time' => 0, 'realmem' => 0, 'emalloc' => 0);
            }
            $this->timers[$name]['sum']['time'] += microtime(true) - $this->timers[$name]['start']['time'];
            $this->timers[$name]['sum']['realmem'] += memory_get_usage(true) - $this->timers[$name]['start']['realmem'];
            $this->timers[$name]['sum']['emalloc'] += memory_get_usage() - $this->timers[$name]['start']['emalloc'];
        }
        $this->timers[$name]['start'] = array();
        if (!empty($this->timers[$name]['time']['start'])) {
            $this->timers[$name]['time']['end'] = microtime(true);
        }

        return $this;
    }

    /**
     * Output SQl Zend_Db_Profiler
     *
     */
    public function database($profiler = null)
    {
        if (!$this->active) {
            return;
        }
        if (isset($this->queries)) {
            return $this->queries;
        }

        if (null === $profiler) {
            if (\Xoops::registry('db') && method_exists(\Xoops::registry('db'), "getProfiler")) {
                $profiler = \Xoops::registry('db')->getProfiler();
            }
        }
        if (null === $profiler || !$profiler->getEnabled()) {
            return;
        }

        $this->queries['totalTime'] = $profiler->getTotalElapsedSecs();
        $this->queries['queryCount'] = $profiler->getTotalNumQueries();
        $this->queries['longestQuery'] = null;

        $querys = $profiler->getQueryProfiles() ?: array();
        foreach ($querys as $query) {
            $query_string = $query->getQuery();
            if ($params = $query->getQueryParams()) {
                foreach ($params as $param => &$value) {
                    $value = $param . "-[" . $value . "]";
                }
                $query_string .= "<br />params: " . implode(", ", array_values($params));
            }
            $query_data = array(
                'query'     => $query_string,
                'time'      => $query->getElapsedSecs(),
                'start'     => $query->getStartedTime(),
                'exception' => $query->getException()
            );
            // Record longest query
            if ($query_data['query'] != 'connect'
                && (empty($this->queries['longestQuery']) || $query_data['time'] > $this->queries['longestQuery']['time'])) {
                $this->queries['longestQuery'] = $query_data;
            }
            $this->queries['queries'][] = $query_data;
        }
        return $this->queries;
    }

    public function startTime($name = 'XOOPS')
    {
        $this->start($name);
    }

    public function stopTime($name = 'XOOPS')
    {
        $this->stop($name);
    }
}