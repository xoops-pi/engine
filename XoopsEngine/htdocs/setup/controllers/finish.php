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

class Finish extends AbstractController
{
    protected $hasBootstrap = true;

    public function init()
    {
        $this->wizard->destroyPersist();
    }

    public function indexAction()
    {
        $writable_paths = "<ul>";
        $protectionList = array(
            \Xoops::path('www') . '/boot.php',
            \Xoops::path('www') . '/.htaccess',
            \Xoops::path('var') . '/etc',
        );
        foreach ($protectionList as $file) {
            @chmod($file, 0644);
            $writable_paths .= "<li class='files'>" . $file . "</li>";
            if (is_dir($file)) {
                $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($file), \RecursiveIteratorIterator::CHILD_FIRST);
                foreach ($objects as $object) {
                    @chmod($file, 0644);
                }
            }
        }
        $writable_paths .= "</ul>";

        $this->wizard->loadLanguage("finish");
        $this->content = sprintf(_INSTALL_FINISH_MESSAGE, basename(dirname(__DIR__)), $writable_paths);

        $path = \XOOPS::path("var") . "/cache/";
        $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($objects as $object) {
            if ($object->isFile() && 'index.html' != $object->getFilename()) {
                unlink($object->getPathname());
            }
        }

        \XOOPS::persist()->clean();
    }
}