<?php
/**
 * Installer template file
 *
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Xoops Engine
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @author          Skalpa Keo <skalpa@xoops.org>
 * @since           3.0
 * @package         Xoops_Setup
 * @version         $Id$
 */

if (!defined('XOOPS_INSTALL')) { die('XOOPS Installation wizard die'); }

//include_once '../language/' . $wizard->language . '/global.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $wizard->locale['lang']; ?>" lang="<?php echo $wizard->locale['lang']; ?>">

<head>
    <title>
        <?php echo XOOPS_INSTALL_WIZARD; ?>
        (<?php echo ($wizard->pageIndex + 1) . '/' . count($wizard->pages); ?>)
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $wizard->locale['charset']; ?>" />
    <link rel="shortcut icon" type="image/ico" href="../favicon.ico" />
    <link rel="stylesheet" type="text/css" media="all" href="./scripts/style.css" />
    <?php
        if (file_exists('language/' . $wizard->language . '/style.css')) {
            echo '<link rel="stylesheet" type="text/css" media="all" href="language/' . $wizard->language . '/style.css" />';
        }
    ?>
    <script type="text/javascript" src="./scripts/prototype-1.6.0.3.js"></script>
    <script type="text/javascript" src="./scripts/xo-installer.js"></script>
</head>

<body>
    <div id="xo-header">
        <div id="xo-banner">
            <!--
            <img id="xo-main-logo" src="images/logo.png" alt="Xoops Engine Setup" />
            -->
            <div id="xo-main-logo">
                <div class='project'>Xoops Engine</div>
                <div class='description'>Setup</div>
            </div>

            <div id="xo-support">Support @<a href='http://www.xoopsengine.org' rel='external' title="Xoops Engine">Xoops Engine</a>
            </div>
        </div>
    </div>

    <div id="xo-globalnav" class="x2-nl x2-navigation"></div>

    <div id="xo-content">
        <div class="tagsoup1">
            <div class="tagsoup2">
                <div id="wizard">
                    <form id='<?php echo $wizard->pages[$wizard->currentPage]['name']; ?>' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='post'>

                        <div id="header">
                            <div id="title-before"></div>
                            <div id="title-after"></div>
                            <div id="title" class="title">
                                <span id="step">
                                    <?php echo ($wizard->pageIndex + 1) . '/' . count($wizard->pages); ?>
                                </span>
                                <span>
                                    <?php echo XOOPS_INSTALL_WIZARD; ?>
                                </span>
                            </div>
                        </div>


                        <ul id="pageslist" class="x2-navigation">
                        <?php
                        foreach (array_keys($wizard->pages) as $k => $page) {
                            $class = '';
                            if ($k == $wizard->pageIndex) {
                                $class = ' class="current"';
                            } elseif ($k > $wizard->pageIndex) {
                                $class = ' class="disabled"';
                            }
                            if (empty($class)) {
                                $li = '<a href="' . $wizard->pageURI($page) . '">' . $wizard->pages[$page]['name'] . '</a>';
                            } else {
                                $li = $wizard->pages[$page]['name'];
                            }
                            echo "<li$class>$li</li>\n";
                        }
                        ?>
                        </ul>

                        <div class="page" id="<?php echo $wizard->pages[$wizard->currentPage]['name']; ?>">
                            <?php if (!empty($pageHasHelp)) { ?>
                                <img id="help_button" src="images/help.png" alt="help" title="<?php echo SHOW_HIDE_HELP; ?>" class="off" onclick="showHideHelp(this)" />
                            <?php } ?>

                            <h2><?php echo $wizard->pages[$wizard->currentPage]['title'] ; ?></h2>
                            <?php echo $content; ?>
                        </div>

                        <div id="buttons">
                            <?php if ($wizard->pageIndex != 0) { ?>
                                <button type="button" onclick="history.back()">
                                <?php echo BUTTON_PREVIOUS; ?>
                                </button>
                            <?php } ?>

                            <?php if (!empty($pageHasForm)) { ?>
                                <button type="submit">
                            <?php } else { ?>
                                <button type="button" accesskey="n" onclick="location.href='<?php echo $wizard->pageURI('+1'); ?>'">
                            <?php } ?>
                            <?php echo BUTTON_NEXT; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>