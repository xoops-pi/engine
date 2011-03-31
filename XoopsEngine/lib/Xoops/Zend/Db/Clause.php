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
 * @package         Db
 * @version         $Id$
 */

/**
 * XOOPS database query clause parser
 */

class Xoops_Zend_Db_Clause
{
    public  $adapter;
    private $container;
    private $elements = array();
    private $properties = array();

    /**
     * Constructor
     *
     * @param   mixed   $element
     * @param   mixed   $term
     */
    public function __construct($element = null, $term = null)
    {
        if (is_null($element)) {
        } elseif ($element instanceof Xoops_Zend_Db_Clause) {
            $this->add($element);
        } elseif ($element instanceof Zend_Db_Adapter_Abstract) {
            $this->setAdapter($element);
        } else {
            $this->container = array($element, $term);
        }
    }

    public function add($element, $term = null, $condition = 'AND')
    {
        if (!$element instanceof Xoops_Zend_Db_Clause) {
            $element = new Xoops_Zend_Db_Clause($element, $term);
        }
        $this->elements[] = array($element, $condition);
        return $this;
    }

    public function addAnd($element, $term = null)
    {
        $this->add($element, $term, "AND");
        return $this;
    }

    public function addOr($element, $term = null)
    {
        $this->add($element, $term, "OR");
        return $this;
    }

    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    public function __toString()
    {
        if (empty($this->adapter)) {
            return "";
        }
        return $this->render($this->adapter);
    }

    public function render($adapter)
    {
        $render_string = '';
        if (!empty($this->container)) {
            $where = is_array($this->container) ? $this->container : array($this->container);
            if (count($where) > 1) {
                $render_string .= call_user_func_array(array($adapter, "quoteInto"), $where);
            } else {
                $render_string .= array_shift($where);
            }
        }
        $render_elements = "";
        $operator = "";
        foreach ($this->elements as $element) {
                if (!$render = $element[0]->render($adapter)) continue;
                if (empty($render_elements)) {
                     $render_elements = $render;
                     $operator = $element[1];
                } else {
                    $render_elements .= " " . $element[1] . " (" . $render . ")";
                }
        }
        if (!empty($render_elements)) {
            if (!empty($render_string)) {
                $render_string .= " {$operator} ({$render_elements})";
            } else {
                $render_string = $render_elements;
            }
        }
        return $render_string;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function __get($property)
    {
        return array_key_exists($property, $this->properties)
                ? $this->properties[$property]
                : null;
    }

    public function __call($property, $args)
    {
        $this->properties[$property] = $args;
        return true;
    }
}