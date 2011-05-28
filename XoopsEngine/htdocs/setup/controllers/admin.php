<?php
/**
 * Xoops Engine Setup Controller
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
 * @credits         Skalpa Keo <skalpa@xoops.org>
 * @since           3.0
 * @package         Setup
 * @version         $Id$
 */

namespace Xoops\Setup\Controller;

class Admin extends AbstractController
{
    protected $hasBootstrap = true;

    public function init()
    {
        $locale = $this->wizard->getLocale();
        \Xoops::config('locale', $locale['lang']);
        \Xoops::config('charset', $locale['charset']);
        \Xoops::config('language', $this->wizard->getLanguage());

        $vars = $this->wizard->getPersist('siteconfig');
        if (empty($vars)) {
            $vars['adminname'] = 'root';
            $hostname = preg_replace('/^www\./i', '', $_SERVER['SERVER_NAME']);
            if (false === strpos($hostname, '.')) {
                $hostname .= '.com';
            }
            $vars['adminmail'] = $vars['adminname'] . '@' . $hostname;
            $vars['adminpass'] = $vars['adminpass2'] = '';
            $this->wizard->setPersist('siteconfig', $vars);
        }
        $this->vars = $vars;
    }

    public function cleanAction()
    {
        $this->hasForm = true;
        if ($this->request->getPost('retry')) {
            $ret = \Xoops_Installer::instance()->uninstall("system");
            if (!$ret) {
                $this->content = '<p class="error">' . _INSTALL_SYSTEM_INSTALLED_FAILED . "</p>" .
                            \Xoops_Installer::instance()->getMessage() .
                        "<input type='hidden' name='page' value='admin' />".
                        "<input type='hidden' name='retry' value='1' />".
                        "<input type='hidden' name='action' value='clearn' />";
            } else {
                $this->loadForm();
            }
        }
    }

    public function setAction()
    {
        $var = $this->request->getParam('var');
        $val = $this->request->getParam('val', '');
        $this->vars[$var] = $val;
        $this->wizard->setPersist('siteconfig', $this->vars);
        echo 1;
    }

    public function checkAction()
    {
        $var = $this->request->getParam('var');
        $val = $this->vars[$var];
        $error = '';
        switch ($var) {
            case 'adminname':
                if (empty($val)) {
                    $error = _INSTALL_ERR_REQUIRED;
                }
                break;
            case 'adminmail':
                if (empty($val)) {
                    $error = _INSTALL_ERR_REQUIRED;
                } elseif (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+([\.][a-z0-9-]+)+$/i", $val)) {
                    $error = _INSTALL_ERR_INVALID_EMAIL;
                }
                break;
            case 'adminpass':
            case 'adminpass2':
                $v1 = $this->vars['adminpass'];
                $v2 = $this->vars['adminpass2'];
                if (empty($v1) || empty($v2)) {
                    $error = _INSTALL_ERR_REQUIRED;
                } elseif ($v1 !== $v2) {
                    $error = _INSTALL_ERR_PASSWORD_MATCH;
                }
                break;
            default:
                break;
        }
        echo $error;
    }

    public function submitAction()
    {
        $ret = \Xoops_Installer::instance()->install("system");
        if (!$ret) {
            $this->hasForm = true;
            $this->content = '<p class="error">' . _INSTALL_SYSTEM_INSTALLED_FAILED . "</p>" .
                        \Xoops_Installer::instance()->getMessage() .
                        "<input type='hidden' name='page' value='admin' />".
                        "<input type='hidden' name='retry' value='1' />".
                        "<input type='hidden' name='action' value='clearn' />";
            return;
        }

        $vars = $this->vars;
        $vars['adminname'] = $this->request->getPost('adminname');
        $vars['adminmail'] = $this->request->getPost('adminmail');
        $vars['adminpass'] = $this->request->getPost('adminpass');
        $vars['adminpass2'] = $this->request->getPost('adminpass2');
        $this->wizard->setPersist('siteconfig', $vars);

        $error = array();
        if (empty($vars['adminname'])) {
            $error['name'][] = _INSTALL_ERR_REQUIRED;
        }
        if (empty($vars['adminmail'])) {
            $error['email'][] = _INSTALL_ERR_REQUIRED;
        }
        if (empty($vars['adminpass'])) {
            $error['pass'][] = _INSTALL_ERR_REQUIRED;
        }
        if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+([\.][a-z0-9-]+)+$/i", $vars['adminmail'])) {
            $error['email'][] = _INSTALL_ERR_INVALID_EMAIL;
        }
        if ($vars['adminpass'] != $vars['adminpass2']) {
            $error['pass'][] = _INSTALL_ERR_PASSWORD_MATCH;
        }
        if (!$error) {
            $configModel = \XOOPS::getModel("config");
            $configModel->update(array("value" => $vars['adminmail']), array("name = ?" => "adminmail"));
            $rootRow = \XOOPS::getModel("user_root")->createRow(array(
                "identity"      => $vars['adminname'],
                "credential"    => $vars['adminpass'],
                "email"         => $vars['adminmail'],
            ));
            if ($rootRow->save($data)) {
                $this->status = 1;
            }
        }

        if ($this->status < 1) {
            $this->loadForm();
        }
    }

    public function indexAction()
    {
        $this->hasForm = true;

        $desc = null;
        $db = \Xoops::registry('db');
        $table = $db->prefix('module', \Xoops_Zend_Db::getPrefix('core'));
        try {
            $desc = $db->describeTable($table);
        } catch (\Exception $e) {
        }
        if ($desc) {
            $this->content = '<p class="error">' . _INSTALL_SYSTEM_ALREADY_INSTALLED . "</p>" .
                        "<input type='hidden' name='page' value='admin' />".
                        "<input type='hidden' name='retry' value='1' />".
                        "<input type='hidden' name='action' value='clean' />";
        } else {
            $this->loadForm();
        }
    }

    protected function loadForm($error = array())
    {
        $this->hasForm = true;
        $vars = $this->vars;
        $this->wizard->setPersist('siteconfig', $vars);

        $displayItem = function ($item) use ($vars) {
            $content = '<div class="item">
                <label for="' . $item . '">' . constant('_INSTALL_' . strtoupper($item) . '_LABEL') . '</label>
                <p class="capthion"></p>
                <input type="text" name="' . $item . '" id="' . $item . '" value="' . $vars[$item] . '" />
                <em id="' . $item . '-status" class="">&nbsp;</em>
                <p id="' . $item . '-message" class="admin-message">&nbsp;</p>
                </div>';
            return $content;
        };

        $content = '<div class="install-form">';
        $content .= '<h3 class="section">' . _INSTALL_LEGEND_ADMIN_ACCOUNT . '</h3>';
        $content .= $displayItem('adminmail');
        $content .= $displayItem('adminname');

        $item = 'adminpass';
        $content .= '<div class="item">
            <label for="' . $item . '">' . constant('_INSTALL_' . strtoupper($item) . '_LABEL') . '</label>
            <p class="capthion"></p>
            <input type="password" name="' . $item . '" id="' . $item . '" value="' . $vars[$item] . '" />
            </div>';
        $item = 'adminpass2';
        $content .= '<div class="item">
            <label for="' . $item . '">' . constant('_INSTALL_' . strtoupper($item) . '_LABEL') . '</label>
            <p class="capthion"></p>
            <input type="password" name="' . $item . '" id="' . $item . '" value="' . $vars[$item] . '" />
            <em id="adminpass-status" class="">&nbsp;</em>
            <p id="adminpass-message" class="admin-message">&nbsp;</p>
            </div>';
        $content .= '</div>';

        $this->content = $content;

        $this->headContent .= '
        <style type="text/css" media="screen">
            .admin-message {
                display: none;
                font-size: 80%;
                background-color: yellow;
                border: 1px solid #666;
                margin-top: 5px;
                padding-left: 5px;
            }
            .item {
                margin-top: 20px;
            }
            .install-form input[type="password"] {
                width: 400px;
                font-size: 16px;
                color: #666;
            }
        </style>
        ';

        $this->footContent .= '
            <script type="text/javascript">
            var url="' . $_SERVER['PHP_SELF'] . '";
            $(document).ready(function(){
                $("input[type=text]").each(function(index) {
                    check($(this).attr("id"));
                    $(this).bind("change", function() {
                        update($(this).attr("id"));
                    });
                });
                check("adminpass");
                $("#adminpass, #adminpass2").each(function(index) {
                    $(this).bind("change", function() {
                        $.get(url, {"action": "set", "var": $(this).attr("name"), "val": this.value, "page": "admin"}, function (data) {
                            if (data) {
                                check("adminpass");
                            }
                        });
                    });
                });
            });

            function update(id) {
                $.get(url, {"action": "set", "var": id, "val": $("#" + id).val(), "page": "admin"}, function (data) {
                    if (data) {
                        check(id);
                    }
                });
            }

            function check(id) {
                $.get(url, {"action": "check", "var": id, "page": "admin"}, function (data) {
                    if (data.length == 0) {
                        $("#"+id+"-status").attr("class", "success");
                        $("#"+id+"-message").css("display", "none");
                    } else {
                        $("#"+id+"-status").attr("class", "failure");
                        $("#"+id+"-message").html(data);
                        $("#"+id+"-message").css("display", "block");
                    }
                });
            }

            </script>';
    }
}