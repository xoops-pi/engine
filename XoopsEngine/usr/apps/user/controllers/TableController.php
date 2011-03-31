<?php
/**
 * User search controller
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
 * @category        Xoops_Module
 * @package         User
 * @version         $Id$
 */

class User_TableController extends Xoops_Zend_Controller_Action
{
    public function indexAction()
    {
        $this->setTemplate("table_test.html");
        $table = $this->getTable("xoops-table");
        $table->assign($this->view);
    }

    public function getTable($id)
    {
        $options = array(
            "id"        => $id,
            "border"    => "2",
            "frame"     => "hsides",
            "rules"     => "groups",
            "summary"   => "Code page support in different versions of MS Windows.",
        );
        $table = new Xoops_Table($options);

        $options = array(
            "content"   => "CODE-PAGE SUPPORT IN MICROSOFT WINDOWS"
        );
        $table->addCaption($options);

        $options = array(
            "align" => "center"
        );
        $table->addColgroup($options);

        $options = array(
            "align" => "left"
        );
        $table->addColgroup($options);

        $options = array(
            "align" => "center",
            "span"  => "2"
        );
        $table->addColgroup($options);

        $options = array(
            "align" => "center",
            "span"  => "3"
        );
        $table->addColgroup($options);

        $options = array(
            // TDs
            "elements"  => array(
                // TD
                array(
                    "options"   => array(
                        "content"   => "Community Courses -- Bath Autumn 1997",
                        "colspan"   => "7",
                        "scope"     => "colgroup",
                        "tag"       => "th",
                        "align"     => "center",
                    ),
                ),
            ),
        );
        $table->addRow($options);

        $options = array(
            "valign"    => "top",
            // TRs
            "elements"  => array(
                // TR
                array(
                    // TDs
                    "options"   => array(
                        "elements"  => array(
                            // TD
                            "Code-Page<BR>ID",
                            "Name",
                            "ACP",
                            "OEMCP",
                            "Windows<BR>NT 3.1",
                            "Windows<BR>NT 3.51",
                            "Windows<BR>95"
                        ),
                    ),
                ),
            ),
        );
        $table->addThead($options);

        $options = array(
            // TRs
            "elements"  => array(
                // TR
                array(
                    // TDs
                    "elements"  => array(
                        "1200",
                        "Unicode (BMP of ISO/IEC-10646)",
                        "",
                        "",
                        "X",
                        "X",
                        "*",
                    ),
                ),
                // TR
                array(
                    // TDs
                    "elements"  => array(
                        "1250",
                        "Windows 3.1 Eastern European",
                        "X",
                        "",
                        "X",
                        "X",
                        "X",
                    ),
                ),
                // TR
                array(
                    // TDs
                    "elements"  => array(
                        "1361",
                        "Korean (Johab)",
                        "X",
                        "",
                        "",
                        "**",
                        "X",
                    ),
                ),
            ),
        );
        $table->addTbody($options);

        $options = array(
            // TRs
            "elements"  => array(
                // TR
                array(
                    // TDs
                    "elements"  => array(
                        "437",
                        "MS-DOS United States",
                        "",
                        "X",
                        "X",
                        "X",
                        "X",
                    ),
                ),
                // TR
                array(
                    // TDs
                    "elements"  => array(
                        "720",
                        "Arabic (Transparent ASMO)",
                        "",
                        "X",
                        "",
                        "",
                        "X",
                    ),
                ),
            ),
        );
        $table->addTbody($options);

        return $table;
    }
}
