<?php
/**
 * Kernel persist
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Xoops Engine http://www.xoopsengine.org/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @package         Kernel
 * @since           3.0
 * @version         $Id$
 */

namespace Kernel\Persist;

class File implements PersistInterface
{
    public function __construct()
    {
    }

    protected function fileName($id, $hash = false)
    {
        return \Xoops::path("var") . "/cache/system/" . \Xoops::config("identifier") . ".persist." . ($hash ? md5($id) : $id) . ".php";
    }

    /**
     * Test if an item is available for the given id and (if yes) return it (false else)
     *
     * @param  string  $id                     Item id
     * @return mixed|false Cached datas
     */
    public function load($id)
    {
        if (!\Xoops::bound()) {
            return null;
        }
        $cacheFile = $this->fileName($id);
        if (file_exists($cacheFile)) {
            return include $cacheFile;
        }
        return false;
    }

    /**
     * Save some data in a key
     *
     * @param  mixed $data      Data to put in cache
     * @param  string $id       Store id
     * @return boolean True if no problem
     */
    public function save($data, $id, $ttl = 0)
    {
        if (!\Xoops::bound()) {
            return null;
        }
        $cacheFile = $this->fileName($id);
        if (!$file = fopen($cacheFile, "w")) {
            throw new \Exception("Cache file '{$cacheFile}' can not be created.");
        }
        $content = "<?php return " . var_export($data, true) . ";?>";
        fwrite($file, $content);
        fclose($file);
        return true;
    }

    /**
     * Remove an item
     *
     * @param  string $id Data id to remove
     * @return boolean True if ok
     */
    public function remove($id)
    {
        if (!\Xoops::bound()) {
            return null;
        }
        $cacheFile = $this->fileName($id);
        return unlink($cacheFile);
    }

    /**
     * Clean cached entries
     *
     * @return boolean True if ok
     */
    public function clean($type = null)
    {
        if (!\Xoops::bound()) {
            return null;
        }
        $cacheFiles = $this->fileName("*", false);
        foreach (glob($cacheFiles) as $file) {
            unlink($file);
        }
        return true;
    }

    /**
     * Do commit
     *
     * @return boolean True if ok
     */
    public function commit()
    {
        return true;
    }
}