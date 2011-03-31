<?php
/**
 * Xoops Engine theme configuration
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
 * @category        Xoops_Theme
 * @package         default
 * @version         $Id$
 */

/**
 * A complete theme set should include following files:
 *
 * Folder and file skeleton:
 * REQUIRED for front:
 *  layout.html - complete layout template: header, footer, body, blocks, navigation
 *  simple.html - simplified layout: header, footer, body
 *  empty.html - empty layout with body only
 *  paginator.html - Paginator template
 *  comment.html - Comment template
 *  notification.html - Notification form template
 * REQUIRED for admin:
 *  admin.html - backoffice layout
 * OPTIONAL:
 *  navigation.html - generic navigation template, referenced by layout.html
 *
 * Stylesheet files:
 * REQUIRED:
 *  style.css - main css file
 *  form.css - generic form css file
 * OPTIONAL:
 *  default/scripts/redirect.css - css file for redirecting page
 *  default/scripts/exception.css - css file for error pages
 *  default/images/loading_indicator.jpg - Indicator image for redirecting page
 *
 * Best practices:
 *  1 It is hightly recommended to use 'xoops-' as prefix for all id's used in theme to avoid conflicts.
 */

return array(
    // Version
    "version"       => "1.0.0",
    // Title of the theme
    "name"          => "Xoops Engine Theme",
    // Parent theme to inherit
    "parent"        => "",
    // Author information: name, email, website
    "author"        => "Theme architecture: Taiwen Jiang <taiwenjiang@tsinghua.org.cn>; Resources: Xoops Engine Development Team",
    // Screenshot image. If no screenshot is available, the default theme screenshot will be used
    "screenshot"    => "screenshot.png",
    // License or theme images and scripts
    "license"       => "Creative Common License http://creativecommons.org/licenses/by/3.0/",
    // Disable the theme
    "disable"       => false,
    // Type of layouts available in the theme
    //"type"          => 'both', // Potential value: 'both', 'admin', 'front', default as 'both'
);