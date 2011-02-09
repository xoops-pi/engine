<?php
/**
 * Kernel registry
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

namespace Kernel;

abstract class Registry
{
    /**
     * Cache enginer object
     */
    protected $cache;
    protected $registry_key;
    const TAG = "registry";

    protected function normalizeValue($val)
    {
        $val = str_replace(array(":", "-", ".", "/"), "_", strval($val));

        return $val;
    }

    protected function loadLocale($locale)
    {
        if (is_null($locale) && \Xoops::registry('locale')) {
            $locale = (string) \Xoops::registry('locale');
        }

        return $locale;
    }

    protected function loadRole($role)
    {
        if (is_null($role)) {
            $role = \Xoops::registry('user')->role;
        }

        return $role;
    }

    protected function createTags(&$options = array())
    {
        $tags = array(self::TAG);
        if (!empty($this->registry_key)) {
            $tags[] = "id_" . $this->registry_key;
        }
        $options = (array) $options;
        foreach (array_keys($options) as $var) {
            if (is_callable(array($this, "load" . $var))) {
                $options[$var] = $this->{"load" . $var}($options[$var]);
            }
            if (!is_null($options[$var])) {
                $tags[] = $var . '_' . $this->normalizeValue($options[$var]);
            }
        }
        return $tags;
    }

    protected function createKey(&$options = array())
    {
        $key = "";
        foreach (array_keys($options) as $var) {
            if (is_callable(array($this, "load" . $var))) {
                $options[$var] = $this->{"load" . $var}($options[$var]);
            }
            if (!is_null($options[$var])) {
                $key .= '_' . $this->normalizeValue($options[$var]);
            }
        }
        return self::TAG . "_" . $this->registry_key . $key;
    }

    public function setCache($cache)
    {
        $this->cache = $cache;
        return $this;
    }

    protected function loadData(&$options = array())
    {
        if (false === ($data = $this->loadCache($options))) {
            $data = $this->loadDynamic($options);
            $this->saveCache($data, $options);
        }
        return $data;
    }

    protected function loadCache(&$options= array())
    {
        $data = $this->cache->load($this->createKey($options));
        return $data;
    }

    protected function saveCache($data, &$options= array())
    {
        if ($data === false) {
            return false;
        }
        return $this->cache->save($data, $this->createKey($options));
    }

    public function setKey($key)
    {
        $this->registry_key = $key;
        return $this;
    }

    /*
    protected function loadDynamic($options) {}
    public function read() {}
    public function create() {}
    public function delete() {}
    public function flush() {}
    */
}