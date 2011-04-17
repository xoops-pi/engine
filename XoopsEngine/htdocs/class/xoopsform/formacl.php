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
 * @copyright       Xoops Engine http://www.xoopsengine.org
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         XoopsForm
 * @version         $Id$
 */

trigger_error('To be upgraded to Xoops_Form');

class XoopsFormAcl extends XoopsForm
{
    private $cols;
    //private $rows;
    private $widths;
    private $data = array(
        "roles"         => array(),
        "resources"     => array(),
        "rules"         => array(),
        "access"        => array(),
        "privileges"    => array()
    );

    public function __set($var, $val)
    {
        if (isset($this->data[$var]) && !is_null($val)) {
            $this->data[$var] = $val;
        }

        return $this;
    }

    public function __get($var)
    {
        return isset($this->data[$var]) ? $this->data[$var] : null;
    }

    public function setCols($cols)
    {
        $this->cols = $cols;
    }

    public function getCols()
    {
        return isset($this->cols) ? $this->cols : (count($this->roles) * 2 + ($this->privileges ? 2 : 1));
    }

    public function addHead($head)
    {
        $this->head[] = $head;
    }

    public function setRoles($roles = array())
    {
        $this->head = $head;
    }

    public function getHead()
    {
        return $this->head;
    }

    public function setWidths($widths = array())
    {
        $this->widths = $widths;
    }

    public function getWidths()
    {
        if (isset($this->widths)) {
            return $this->widths;
        }

        $cols = $this->getCols();
        return array_fill(0, $cols, 100 / $cols);
    }

    /**
     * create HTML to output the form as a theme-enabled table with validation.
     *
     * @return    string
     */
    public function render()
    {
        $ele_name = $this->getName();
        $ret = "<form name='{$ele_name}' id='{$ele_name}' action='" . $this->getAction() . "' method='" . $this->getMethod() . "' onsubmit='return xoopsFormValidate_{$ele_name}();'" . $this->getExtra() . ">";
        $ret .= "<table width='100%' class='outer' cellspacing='1'>";
        $ret .= "<caption>" . $this->getTitle() . "</caption>";

        $ret .= "<thead>";
        $ret .= "<tr class='head'>";
        $colspan = $this->privileges ? " colspan='2'" : "";
        $ret .= "<th{$colspan}>" . XOOPS::_("Role") . "</th>";
        $cols = $this->getCols();
        foreach ($this->roles as $role => $title) {
            $ret .= "<th colspan='2'>" . $title . "</th>";
        }
        $ret .= "</tr>";
        $ret .= "<tr class='head'>";
        $ret .= "<td>" . XOOPS::_("Resource") . "</td>";
        if ($this->privileges) {
            $ret .= "<td width='5%'>" . XOOPS::_("Privilege") . "</td>";
        }
        $cols = count($this->roles);
        $width = ceil((100 - 10) / ($cols * 2));
        for ($i = 0; $i < $cols; $i ++) {
            $ret .= "<td width='{$width}%'>" . XOOPS::_("Setting") . "</td>";
            $ret .= "<td width='{$width}%'>" . XOOPS::_("Access") . "</td>";
        }
        $ret .= "</tr>";

        $ret .= "</thead>";


        if ($description = $this->getDescription()) {
            $ret .= "<tfoot><tr class='foot'><td colspan='" . $this->getCols() . "'>{$description}</td></tr></tfoot>";
        }

        $hidden = '';
        $class ='even';
        foreach ($this->resources as $id => $resource) {
            if (empty($this->privileges[$id])) {
                $privileges = array("" => array());
                $access[""] = isset($this->access[$id][""]) ? $this->access[$id][""] : $this->access[$id];
                $rules[""] = isset($this->rules[$id][""]) ? $this->rules[$id][""] : $this->rules[$id];
            } else {
                $privileges = $this->privileges[$id];
                $access = $this->access[$id];
                $rules = $this->rules[$id];
            }

            $ret .= "<tr valign='top' align='left'>";
            $rowspan = " rowspan='" . count($privileges) . "' ";
            $ret .= "<td class='head'{$rowspan}>";
            $ret .= "<div class='xoops-form-element-caption'>";
            $ret .= "<span class='caption-text'>" . $resource["title"] . "</span>";
            $ret .= "</div>";
            $ret .= "<div class='xoops-form-element-help'>" . $resource["name"] . "</div>";
            $ret .= "</td>";

            $newNewline = false;
            foreach ($privileges as $privilege => $data) {
                if ($isNewline) {
                    $ret .= "<tr valign='top' align='left'>";
                }
                if ($this->privileges) {
                    $ret .= "<td class='odd'>";
                    if (empty($data)) {
                        $ret = "&nbsp;";
                    } else {
                        $ret .= "<div class='xoops-form-element-caption'>";
                        $ret .= "<span class='caption-text'>" . $data["title"] . "</span>";
                        $ret .= "</div>";
                        $ret .= "<div class='xoops-form-element-help'>" . $data["name"] . "</div>";
                    }
                    $ret .= "</td>";
                }

                foreach (array_keys($this->roles) as $role) {

                    $namespace = empty($this->privileges[$id]) ? "{$id}-{$role}-" : "{$id}-{$role}-{$privilege}";
                    $ret .= "<td class='even'>";
                    // Allowed: deny = 0
                    $ret .= "<input type='radio' id='rules-{$namespace}-1' name='rules[{$namespace}]'";
                    if (isset($rules[$privilege][$role]) && empty($rules[$privilege][$role])) {
                        $ret .= " checked='checked'";
                    }
                    $ret .= " value='0'> Y<br />";
                    // Denied: deny = 1;
                    $ret .= "<input type='radio' id='rules-{$namespace}-0' name='rules[{$namespace}]'";
                    if (!empty($rules[$privilege][$role])) {
                        $ret .= " checked='checked'";
                    }
                    $ret .= " value='1'> N<br />";
                    // Not set: deny = -1
                    $ret .= "<input type='radio' id='rules-{$namespace}--1' name='rules[{$namespace}]'";
                    if (!isset($rules[$privilege][$role])) {
                        $ret .= " checked='checked'";
                    }
                    $ret .= " value='-1'> I";
                    $ret .= "</td>";
                    $ret .= "<td class='odd'>" . (empty($access[$privilege][$role]) ? "N" : "Y") . "</td>";
                }
                $ret .= "</tr>";
                $newNewline = true;
            }
            $ret .= "</tr>";
            if ($this->privileges) {
                $ret .= "<tr><td colspan='" . $this->getCols() . "'><hr noshade></td></tr>";
            }
        }
        $ret .= "<tr class='foot'><td colspan='" . $this->getCols() . "'>";
        $ret .= "<input type='submit' name='submit' value=" . XOOPS::_("Submit") . ">";
        $ret .= "</td></tr>";
        $ret .= "</table>";

        foreach ($this->getElements() as $ele) {
            if (!is_object($ele) || !$ele->isHidden()) {
                continue;
            } else {
                $ret .= $ele->render();
            }
        }

        $ret .= "</form>";
        //$ret .= $this->renderValidationJS(true);
        return $ret;
    }

    /**
     * assign to smarty form template instead of displaying directly
     *
     * @param    object  &$tpl    reference to a {@link Smarty} object
     * @see     Smarty
     */
    public function assign(&$tpl)
    {
        $tpl->assign($this->getName(), $this->render());
    }
}