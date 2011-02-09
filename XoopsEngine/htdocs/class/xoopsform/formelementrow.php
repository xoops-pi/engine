<?php
/**
 * XOOPS Framework
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license         BSD liscense
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         xoops
 * @version         $Id$
 */

//global $xoops;
//include_once $xoops->path("www") . "/class/xoopsform/formelement.php";

class XoopsFormElementRow extends XoopsFormElement {

    /**
     * array of form element objects
     * @var array
     * @access  private
     */
    var $_elements = array();

    /**
     * required elements
     * @var array
     */
    var $_required = array();

    /**
     * constructor
     *
     * @param   string  $caption    Caption for the group.
     * @param   string  $delimiter  HTML to separate the elements
     */
    public function __construct($caption, $elements = null)
    {
        $this->setCaption($caption);
        if (!empty($elements)) {
            foreach ((array) $elements as $element) {
                $this->addElement($element);
            }
        }
    }

    /**
     * Is this element a container of other elements?
     *
     * @return  bool true
     */
    public function isContainer()
    {
        return true;
    }

    /**
     * Find out if there are required elements.
     *
     * @return  bool
     */
    public function isRequired()
    {
        return !empty($this->_required);
    }

    /**
     * Add an element to the group
     *
     * @param   object  $element    {@link XoopsFormElement} to add
     */
    public function addElement($formElement, $required = false)
    {
        if (!$formElement instanceof XoopsFormElement) {
            $formElement = new XoopsFormLabel("", $formElement);
        }
        $this->_elements[] = $formElement;
        if (!$formElement->isContainer()) {
            if ($required) {
                $formElement->_required = true;
                $this->_required[] = $formElement;
            }
        } else {
            $required_elements = $formElement->getRequired();
            $count = count($required_elements);
            for ($i = 0 ; $i < $count; $i++) {
                $this->_required[] = $required_elements[$i];
            }
        }
    }

    /**
     * get an array of "required" form elements
     *
     * @return  array   array of {@link XoopsFormElement}s
     */
    public function getRequired()
    {
        return $this->_required;
    }

    /**
     * Get an array of the elements in this group
     *
     * @param   bool    $recurse    get elements recursively?
     * @return  array   Array of {@link XoopsFormElement} objects.
     */
    public function getElements($recurse = false)
    {
        if (!$recurse) {
            return $this->_elements;
        } else {
            $ret = array();
            $count = count($this->_elements);
            for ($i = 0; $i < $count; $i++) {
                if (!$this->_elements[$i]->isContainer()) {
                    $ret[] = $this->_elements[$i];
                } else {
                    $elements = $this->_elements[$i]->getElements(true);
                    $count2 = count($elements);
                    for ($j = 0; $j < $count2; $j++) {
                        $ret[] = $elements[$j];
                    }
                    unset($elements);
                }
            }
            return $ret;
        }
    }

    /**
     * prepare HTML to output this group
     *
     * @return  string  HTML output
     */
    public function render($cols = 1)
    {
        $count = 0;
        $ret = "";
        foreach ($this->getElements() as $ele) {
            $ret .= "<td>" . $ele->render() . "</td>";
            $count++;
            if ($count > $cols) break;
        }
        if ($count < $cols) {
            $ret .= "<td colspan='" . ($cols - $count) . "'>&nbsp;</td>";
        }
        return $ret;
    }
}
?>