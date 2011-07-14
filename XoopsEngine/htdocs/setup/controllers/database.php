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

class Database extends AbstractController
{
    //protected $hasBootstrap = true;
    protected $vars;
    protected $dbLink;

    public function init()
    {
        defined('XOOPS_BOOTSTRAP') or define('XOOPS_BOOTSTRAP', false);
        include dirname($this->wizard->getRoot()) . '/boot.php';

        $vars = $this->wizard->getPersist('db-settings');
        if (empty($vars)) {
            $vars = array(
                    'DB_TYPE'        => 'mysql',
                    'DB_HOST'        => 'localhost',
                    'DB_USER'        => '',
                    'DB_PASS'        => '',
                    'DB_PCONNECT'    => 0,
                    'DB_NAME'       => 'xoops',
                    'DB_CHARSET'    => 'utf8',
                    'DB_COLLATION'  => '',
                    'DB_PREFIX'     => 'x' . substr(md5(time()), 0, 3),
            );
            $this->wizard->setPersist('db-settings', $vars);
        }

        $this->vars = $vars;
    }

    protected function connection()
    {
        $vars = $this->vars;
        if (!empty($vars['DB_HOST']) && !empty($vars['DB_USER'])) {
            $func_connect = empty($vars['DB_PCONNECT']) ? "mysql_connect" : "mysql_pconnect";
            $pass = $vars['DB_PASS'] ?: null;
            ob_start();
            $this->dbLink = $func_connect($vars['DB_HOST'], $vars['DB_USER'], $pass, true);
            ob_end_clean();
        } else {
            $this->dbLink = false;
        }
    }

    public function connectAction()
    {
        $this->connection();
        echo ($this->dbLink) ? 1 : 0;
        /*
        if (!$this->dbLink) {
            echo ' pass:[' . $this->vars['DB_PASS'] . ']';
        }
        */
    }

    public function charsetAction()
    {
        $this->connection();
        if (empty($this->dbLink)) {
            echo '<option value="">' . _INSTALL_DB_CHARSET_SELECT . '</option>';
            return;
        }

        $charsets = array();
        $charsets["utf8"] = "UTF-8 Unicode";
        $ut8_available = false;
        if ($result = mysql_query("SHOW CHARSET", $this->dbLink)) {
            while ($row = mysql_fetch_assoc($result)) {
                $charsets[$row["Charset"]] = $row["Description"];
                if ($row["Charset"] == "utf8") {
                    $ut8_available = true;
                }
            }
        }
        if (!$ut8_available) {
            unset($charsets["utf8"]);
        }

        $content = '';
        foreach ($charsets as $key => $title) {
            if (empty($this->vars['DB_CHARSET'])) {
                $this->vars['DB_CHARSET'] = $key;
                $this->wizard->setPersist('db-settings', $this->vars);
            }
            if ($key == $this->vars['DB_CHARSET']) {
                $content .= '<option value="'. $key . '" selected>' . $title . '</option>';
            } else {
                $content .= '<option value="'. $key . '">' . $title . '</option>';
            }
        }

        echo $content;
    }

    public function collationAction()
    {
        $this->connection();
        if (empty($this->dbLink) || empty($this->vars['DB_CHARSET'])) {
            echo '<option value="">' . _INSTALL_DB_COLLATION_SELECT . '</option>';
            return;
        }

        $collations = array();
        $default = '';
        if ($result = mysql_query("SHOW COLLATION WHERE CHARSET = '" . mysql_real_escape_string($this->vars['DB_CHARSET']) . "'", $this->dbLink)) {
            while ($row = mysql_fetch_assoc($result)) {
                if ($row['Default']) {
                    $default = $row['Collation'];
                }
                $collations[] = $row['Collation'];
            }
        }
        if (empty($this->vars['DB_COLLATION'])) {
            $this->vars['DB_COLLATION'] = $default;
            $this->wizard->setPersist('db-settings', $this->vars);
        }
        $content = '';
        foreach ($collations as $key) {
            if ($key == $this->vars['DB_COLLATION']) {
                $content .= '<option value="'. $key . '" selected>' . $key . '</option>';
            } else {
                $content .= '<option value="'. $key . '">' . $key . '</option>';
            }
        }

        echo $content;
    }

    public function setAction()
    {
        $var = $this->request->getParam('var');
        $val = $this->request->getParam('val', '');
        $this->vars[$var] = $val;
        $this->wizard->setPersist('db-settings', $this->vars);
        //session_write_close();
        $vars = $this->wizard->getPersist('db-settings');
        //echo $var . '[' . $vars[$var] . ']';
        echo 1;
    }

    public function submitAction()
    {
        $vars =& $this->vars;
        foreach (array_keys($vars) as $name) {
            $vars[$name] = $this->request->getPost($name);
        }
        $this->wizard->setPersist('db-settings', $vars);

        $this->connection();
        $error = '';
        $errorDsn = array();
        $db_exist = true;
        if (!mysql_select_db($vars['DB_NAME'], $this->dbLink)) {
            // Database not here: try to create it
            $result = mysql_query("CREATE DATABASE `" . $vars['DB_NAME'] . '`');
            if (!$result) {
                $error = _INSTALL_ERR_NO_DATABASE;
                $db_exist = false;
            }
        }
        if ($db_exist && $vars['DB_CHARSET']) {
            $sql = "ALTER DATABASE `" . $vars['DB_NAME'] . "` DEFAULT CHARACTER SET " . mysql_real_escape_string($vars['DB_CHARSET']) .
                        ($vars['DB_COLLATION'] ? " COLLATE " . mysql_real_escape_string($vars['DB_COLLATION']) : "");
            if (!mysql_query($sql)) {
                $error = _INSTALL_ERR_CHARSET_NOT_SET . $sql;
            }
        }
        if (empty($error)) {
            $dsn = array();
            $dsn['adapter'] = '"' . "Pdo_Mysql" . '"';
            $dsn['driver'] = '"' . "Pdo_Mysql" . '"';
            $dsn['dbname'] = '"' . $vars["DB_NAME"] . '"';
            $dsn['host'] = '"' . $vars["DB_HOST"] . '"';
            $dsn['username'] = '"' . $vars["DB_USER"] . '"';
            $dsn['password'] = '"' . $vars["DB_PASS"] . '"';
            $dsn['prefix'] = '"' . $vars["DB_PREFIX"] . '"';
            $dsn['persistent'] = intval($vars["DB_PCONNECT"]);
            $dsn['charset'] = '"' . $vars["DB_CHARSET"] . '"';
            $dsn['driver_options.LOCAL_INFILE'] = true;

            $content_dsn = ';<?php __halt_compiler();' . PHP_EOL . PHP_EOL;
            $content_dsn .= ';DSN to database' . PHP_EOL;
            foreach ($dsn as $key => $val) {
                $content_dsn .= $key . " = " . $val . PHP_EOL;
            }

            $error_dsn = false;
            $file_dsn_ini = \XOOPS::path("var") . '/etc/resource.db.ini.php';
            if (!$file = fopen($file_dsn_ini, "w")) {
                $error_dsn = true;
            } else {
                $result = fwrite($file, $content_dsn);
                if ($result == false || $result < 1) {
                    $error_dsn = true;
                }
                fclose($file);
            }
            if (empty($error_dsn)) {
                $this->status = 1;
            } else {
                $errorDsn = array("file" => $file_dsn_ini, "content" => $content_dsn);
            }
        }

        $content = '';
        // Display saving error messages
        if (!empty($errorDsn)) {
            $content .= '
                <h3>' . _INSTALL_ERR_WRITE_CONFIGFILE_LABEL . '</h3>
                    <p class="caption" style="margin-top: 10px;">' . sprintf(_INSTALL_ERR_WRITE_CONFIGFILE_DESC, $errorDsn['file']) . '</p>
                    <textarea cols="80" rows="10">' . $errorDsn['content'] . '</textarea>';
        } elseif (!empty($error)) {
            $content .= '<div class="message error">' . $error . '</div>';
        }
        $this->content .= $content;
    }

    public function indexAction()
    {
        $this->loadForm();
    }

    protected function loadForm()
    {
        $this->hasForm = true;
        $vars = $this->vars;

        $displayInput = function ($item) use ($vars) {
            $content = '<div class="item">
                <label for="' . $item . '" class="">' . constant('_INSTALL_' . strtoupper($item) . '_LABEL') . '</label>
                <p class="caption">' . constant('_INSTALL_' . strtoupper($item) . '_HELP') . '</p>
                <input type="text" name="' . $item . '" id="' . $item . '" value="' . $vars[$item] . '" />
                <em id="' . $item . '-status" class="">&nbsp;</em>
                </div>';
            return $content;
        };

        $content = '';

        $content .= $displayInput('DB_HOST');
        $content .= $displayInput('DB_USER');

        $item = 'DB_PASS';
        $content .= '<div class="item">
            <label for="' . $item . '">' . constant('_INSTALL_' . strtoupper($item) . '_LABEL') . '</label>
            <p class="caption">' . constant('_INSTALL_' . strtoupper($item) . '_HELP') . '</p>
            <input type="password" name="' . $item . '" id="' . $item . '" value="" />
            </div>';

        $content .= $displayInput('DB_NAME');
        $content .= $displayInput('DB_PREFIX');

        $contentSetup = '
            <h2><span id="db-connection-label" class="">' . _INSTALL_DB_SETUP_LABEL . '</span></h2>
            <p class="caption">' . _INSTALL_DB_SETUP_DESC . '</p>' .
            $content;

        $content = '';
        $item = 'DB_TYPE';
        $content .= '<div class="item">
            <label for="' . $item . '">' . constant('_INSTALL_' . strtoupper($item) . '_LABEL') . '</label>
            <p class="caption">' . constant('_INSTALL_' . strtoupper($item) . '_HELP') . '</p>
            <select size="1" name="' . $item . '" id="' . $item . '">';
            foreach ($this->wizard->getConfig('db_types') as $db_type) {
                $selected = ($vars[$item] == $db_type) ? ' selected' : '';
                $content .= '<option value=' . $db_type . $selected . '>' . $db_type . '</option>';
            }
        $content .= '
            </select>
            </div>';

        $item = 'DB_CHARSET';
        $content .= '<div class="item">
            <label for="' . $item . '">' . constant('_INSTALL_' . strtoupper($item) . '_LABEL') . '</label>
            <p class="caption">' . constant('_INSTALL_' . strtoupper($item) . '_HELP') . '</p>
            <select size="1" name="' . $item . '" id="' . $item . '">
                <option value="">' . constant('_INSTALL_' . strtoupper($item) . '_SELECT') . '</option>
            </select>
            </div>';

        $item = 'DB_COLLATION';
        $content .= '<div class="item">
            <label for="' . $item . '">' . constant('_INSTALL_' . strtoupper($item) . '_LABEL') . '</label>
            <p class="caption">' . constant('_INSTALL_' . strtoupper($item) . '_HELP') . '</p>
            <select size="1" name="' . $item . '" id="' . $item . '">
                <option value="">' . constant('_INSTALL_' . strtoupper($item) . '_SELECT') . '</option>
            </select>
            </div>';

        $item = 'DB_PCONNECT';
        $content .= '<div class="item">
            <label for="' . $item . '">' . constant('_INSTALL_' . strtoupper($item) . '_LABEL') . '</label>
            <p class="caption">' . constant('_INSTALL_' . strtoupper($item) . '_HELP') . '</p>
            <input type="checkbox" name="' . $item . '" id="' . $item . '" value="1"' . (empty($vars[$item]) ? '' : ' checked') . ' />
            </div>';

        $contentAdvanced = '
            <h2>' . _INSTALL_DB_ADVANCED_LABEL . ' <a href="javascript:void(0);" id="advanced-toggle"><span>[+]</span><span style="display: none;">[-]</span></a></h2>
            <p class="caption">' . _INSTALL_DB_ADVANCED_DESC . '</p>
            <div class="install-form advanced-form" id="db-advanced">' .
            $content .
            '</div>';

        $content = $contentSetup . $contentAdvanced;
        $this->content = $content;

        $this->headContent .= '
        <style type="text/css" media="screen">
            .item {
                margin-top: 20px;
            }
        </style>
        ';

        $this->footContent .= '
            <script type="text/javascript">
            var url="' . $_SERVER['PHP_SELF'] . '";
            $(document).ready(function(){
                $("input[type=text], input[type=password]").each(function(index) {
                    update($(this).attr("name"));
                });
                $("#DB_HOST, #DB_USER, #DB_NAME, #DB_PREFIX").each(function(index) {
                    checkEmpty($(this).attr("id"));
                });

                checkConnection();
                loadCharset();
                $("#DB_HOST, #DB_USER, #DB_PASS, #DB_TYPE").each(function(index) {
                    $(this).bind("change", function() {
                        updateConnection($(this).attr("name"), this.value);
                    });
                });
                $("[name=DB_PCONNECT]").bind("change", function() {
                    updateConnection($(this).attr("name"), $("[name=DB_PCONNECT]:checked").val());
                });
                $("#DB_NAME, #DB_PREFIX, #DB_COLLATION").each(function(index) {
                    $(this).bind("change", function() {
                        update($(this).attr("name"));
                    });
                });
                $("#DB_CHARSET").bind("change", function() {
                    $.get(url, {"action": "set", "var": $(this).attr("name"), "val": this.value, "page": "database"}, function (data) {
                        if (data) {
                            loadCollation();
                        }
                    });
                });
            });

            function checkEmpty(id) {
                var val = $.trim($("#" + id).val());
                if (val.length == 0) {
                    $("#" + id + "-status").attr("class", "failure");
                } else {
                    $("#" + id + "-status").attr("class", "");
                }
            }

            function updateConnection(v, val) {
                $("#db-connection-label").attr("class", "loading");
                $.get(url, {"action": "set", "var": v, "val": val, "page": "database"}, function (data) {
                    if (data) {
                        checkConnection();
                        checkEmpty(v);
                    }
                });
            }

            function update(id) {
                $.get(url, {"action": "set", "var": id, "val": $("#" + id).val(), "page": "database"}, function (data) {
                    if (data) {
                        checkEmpty(id);
                    }
                });
            }

            function checkConnection() {
                if ($("#DB_HOST").val() && $("#DB_USER").val()) {
                    $.get(url, {"action": "connect", "page": "database"}, function (data) {
                        var statusClass = "failure";
                        if (data == 1) {
                            statusClass = "success";
                        }
                        $("#db-connection-label").attr("class", statusClass);
                        if (data == 1) {
                            loadCharset();
                        }
                    });
                }
            }

            function loadCharset() {
                $.get(url, {"action": "charset", "page": "database"}, function (data) {
                    if (data) {
                        $("#DB_CHARSET").html(data);
                        loadCollation();
                    }
                });
            }

            function loadCollation() {
                $("#DB_COLLATION").load(url, {"action": "collation", "page": "database"});
            }

            $("#advanced-toggle").click(function() {
                $("#db-advanced").slideToggle();
                $("#advanced-toggle span").toggle();
            });
            </script>';
    }
}