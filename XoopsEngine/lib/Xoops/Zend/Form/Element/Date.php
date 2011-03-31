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
 * Based on Matthew Weier O'Phinney's tutorial {@link http://weierophinney.net/matthew/archives/217-Creating-composite-elements.html}
 *
 * @copyright       Xoops Engine http://www.xoopsengine.org
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Form
 * @version         $Id$
 */

class Xoops_Zend_Form_Element_Date extends Zend_Form_Element_Xhtml
{
    protected $_dateFormat = '%year%-%month%-%day%';
    protected $_day;
    protected $_month;
    protected $_year;
    /**
     * Default view helper to use
     * @var string
     */
    public $helper = false;

    public function  loadDefaultDecorators()
    {
        /*
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }
        */

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('Date')
                 ->addDecorator('Errors')
                 ->addDecorator('Description', array(
                    'tag'   => 'p',
                    'class' => 'description')
                 )
                 ->addDecorator('HtmlTag', array(
                    'tag' => 'dd',
                    'id'  => $this->getName() . '-element')
                 )
                 ->addDecorator('Label', array('tag' => 'dt'));
        }
    }

    public function setDay($value)
    {
        $this->_day = (int) $value;
        return $this;
    }

    public function getDay()
    {
        return $this->_day;
    }

    public function setMonth($value)
    {
        $this->_month = (int) $value;
        return $this;
    }

    public function getMonth()
    {
        return $this->_month;
    }

    public function setYear($value)
    {
        $this->_year = (int) $value;
        return $this;
    }

    public function getYear()
    {
        return $this->_year;
    }

    public function setValue($value)
    {
        if (is_int($value)) {
            $this->setDay(date('d', $value))
                 ->setMonth(date('m', $value))
                 ->setYear(date('Y', $value));
        } elseif (is_string($value)) {
            $date = strtotime($value);
            //Debug::e("$value:$date");
            $this->setDay(date('d', $date))
                 ->setMonth(date('m', $date))
                 ->setYear(date('Y', $date));
        } elseif (is_array($value)
            && (isset($value['day'])
                && isset($value['month'])
                && isset($value['year'])
            )
        ) {
            $this->setDay($value['day'])
                 ->setMonth($value['month'])
                 ->setYear($value['year']);
        } else {
            //throw new Exception('Invalid date value provided');
        }

        return $this;
    }

    public function getValue()
    {
        return str_replace(
            array('%year%', '%month%', '%day%'),
            array($this->getYear(), $this->getMonth(), $this->getDay()),
            $this->_dateFormat
        );
    }
}