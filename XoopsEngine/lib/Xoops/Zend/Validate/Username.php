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
 * @package         Form
 * @version         $Id$
 */

class Xoops_Zend_Validate_Username extends Zend_Validate_Abstract
{
    const INVALID = 'invalid';

    protected $format = "strict";

    protected $_messageTemplates = array(
        self::INVALID => 'Invalid characters found'
    );

    /**
     * Sets validator options
     *
     * @param  integer|array|Zend_Config $options
     * @return void
     */
    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (!is_array($options)) {
            $options     = func_get_args();
            $temp['min'] = array_shift($options);
            $options = $temp;
        }

        if (array_key_exists('format', $options)) {
            $this->format = $options['format'];
        }
    }

    public function isValid($value, $context = null)
    {
        $value = (string) $value;
        $this->_setValue($value);

        switch ($this->format) {
            case "strict":
            default:
                $restriction = '/[^a-zA-Z0-9\_\-]/';
                break;
            case "medium":
                $restriction = '/[^a-zA-Z0-9\_\-\<\>\,\.\$\%\#\@\!\\\'\"]/';
                break;
            case "loose":
                $restriction = '/[\000-\040]/';
                break;
        }
        if (!preg_match($restriction, $value)) {
            return true;
        }

        $this->_error(self::INVALID);
        return false;
    }
}