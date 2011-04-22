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
 * @package         Log
 * @version         $Id$
 */

class Xoops_Zend_Log_Formatter_Debugger implements Zend_Log_Formatter_Interface
{
    /**
     * @var string
     */
    protected $_format;

    //const DEFAULT_FORMAT = '%timestamp% %priorityName% (%priority%): %message%';
    const DEFAULT_FORMAT = "<div class=\"xoops-event\">\n<div class=\"time\">%timestamp%</div>\n<div class=\"message %priorityName%\">%message%</div>\n</div>\n";

    const TIME_FORMAT = "H:i:s";
    /**
     * Class constructor
     *
     * @param  null|string  $format  Format specifier for log messages
     * @throws Zend_Log_Exception
     */
    public function __construct($format = null)
    {
        if ($format === null) {
            $format = self::DEFAULT_FORMAT . PHP_EOL;
        }

        if (! is_string($format)) {
            require_once 'Zend/Log/Exception.php';
            throw new Zend_Log_Exception('Format must be a string');
        }

        $this->_format = $format;
    }

    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array    $event    event data
     * @return string             formatted line to write to the log
     */
    public function format($event)
    {
        $output = $this->_format;
        if ("queries" == $event['category'] && Xoops::registry("db")) {
            $pattern = '/\b' . preg_quote(XOOPS::registry("db")->prefix()) . '\_/i';
            $event['message'] = preg_replace($pattern, '', $event['message']);
        }
        if (!empty($event['timestamp'])) {
            $event['timestamp'] = date(self::TIME_FORMAT, intval($event['timestamp'])) . substr($event['timestamp'], strpos($event['timestamp'], '.'), 5);
        }
        if (!empty($event['priorityName'])) {
            $event['priorityName'] = strtolower($event['priorityName']);
        }
        foreach ($event as $name => $value) {
            $output = str_replace("%{$name}%", $value, $output);
        }
        return array($event['category'], $output);
    }

}
