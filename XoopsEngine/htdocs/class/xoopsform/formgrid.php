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

class XoopsFormGrid extends XoopsForm
{
    private $cols;
    private $rows;
    private $widths;
    private $head = array();

    public function setCols($cols)
    {
        $this->cols = $cols;
    }

    public function getCols()
    {
        return isset($this->cols) ? $this->cols : count($this->getHead());
    }

    public function addHead($head)
    {
        $this->head[] = $head;
    }

    public function setHead($head = array())
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
     * Insert an empty row in the table to serve as a seperator.
     *
     * @param    string  $extra  HTML to be displayed in the empty row.
     * @param    string    $class    CSS class name for <td> tag
     */
    public function insertBreak($extra = '', $class= '')
    {
        $class = ($class != '') ? " class='" . htmlspecialchars($class, ENT_QUOTES) . "'" : '';
        $extra = "<tr><td colspan='" . $this->getCols() . "' {$class}>" . (empty($extra) ? " " : $extra) . "</td></tr>";
        $this->addElement($extra);
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

        $widths = $this->getWidths();
        if ($head = $this->getHead()) {
            $ret .= "<thead><tr class='head'>";
            $cols = $this->getCols();
            for ($i = 0; $i < $cols; $i++) {
                $ret .= "<th width='" . $widths[$i]. "%'>" . (isset($head[$i]) ? $head[$i] : " ") . "</th>";
            }
            $ret .= "</tr></thead>";
            $widths = null;
        }

        if ($description = $this->getDescription()) {
            $ret .= "<tfoot><tr class='foot'><td colspan='" . $this->getCols() . "'>{$description}</td></tr></tfoot>";
        }

        $hidden = '';
        $class ='even';
        foreach ($this->getElements() as $ele) {
            if (!is_object($ele)) {
                $ret .= $ele;
            } elseif (!$ele->isHidden()) {
                if (!is_null($widths)) {
                    $left = array_shift($widths);
                } else {
                    $left = null;
                }
                $ret .= "<tr valign='top' align='left'>";
                $ret .= "<td class='head'";
                $ret .= (empty($left) ? "" : " {$left}%");
                $ret .= ">";
                if (($caption = $ele->getCaption()) != '') {
                    $ret .=
                        "<div class='xoops-form-element-caption" . ($ele->isRequired() ? "-required" : "") . "'>".
                        "<span class='caption-text'>{$caption}</span>".
                        "<span class='caption-marker'>*</span>".
                        "</div>";
                }
                if (($desc = $ele->getDescription()) != '') {
                    $ret .= "<div class='xoops-form-element-help'>{$desc}</div>";
                }
                $ret .= "</td>";
                if ($ele instanceof XoopsFormElementRow) {
                    $ret .= $ele->render($this->getCols() - 1, $widths);
                } else {
                    $ret .= "<td class='{$class}' colspan='" . ($this->getCols() - 1). "'>" . $ele->render() . "</td>";
                }
                $ret .= "</tr>\n";
                $widths = null;
            } else {
                $hidden .= $ele->render();
            }
        }
        $ret .= "</table>\n{$hidden}\n</form>\n";
        $ret .= $this->renderValidationJS(true);
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