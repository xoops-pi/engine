<?php
/**
 * Zend Framework for Xoops Engine
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
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Paginator
 * @version         $Id$
 */

class Xoops_Zend_Paginator extends Zend_Paginator
{
    protected $params;
    protected $pageParam = "page";

    public function setPageParam($param)
    {
        $this->pageParam = $param;
        return $this;
    }

    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    public function getParams()
    {
        if (!isset($this->params)) {
            $this->params = XOOPS::registry("frontController")->getRequest()->getParams();
        }
        return $this->params;
    }

    /**
     * Factory.
     *
     * @param  mixed $data
     * @param  string $adapter
     * @param  array $prefixPaths
     * @return Zend_Paginator
     */
    public static function factory($data, $adapter = self::INTERNAL_ADAPTER,
                                   array $prefixPaths = null)
    {
        if ($data instanceof Zend_Paginator_AdapterAggregate) {
            return new self($data->getPaginatorAdapter());
        } else {
            if ($adapter == static::INTERNAL_ADAPTER) {
                if (is_array($data)) {
                    $adapter = 'Array';
                } else if ($data instanceof Zend_Db_Table_Select) {
                    $adapter = 'DbTableSelect';
                } else if ($data instanceof Zend_Db_Select) {
                    $adapter = 'DbSelect';
                } else if ($data instanceof Iterator) {
                    $adapter = 'Iterator';
                } else if (is_integer($data)) {
                    $adapter = 'Null';
                } else {
                    $type = (is_object($data)) ? get_class($data) : gettype($data);

                    /**
                     * @see Zend_Paginator_Exception
                     */
                    require_once 'Zend/Paginator/Exception.php';

                    throw new Zend_Paginator_Exception('No adapter for type ' . $type);
                }
            }

            $pluginLoader = static::getAdapterLoader();

            if (null !== $prefixPaths) {
                foreach ($prefixPaths as $prefix => $path) {
                    $pluginLoader->addPrefixPath($prefix, $path);
                }
            }

            $adapterClassName = $pluginLoader->load($adapter);

            return new self(new $adapterClassName($data));
        }
    }

    /**
     * Returns the adapter loader.  If it doesn't exist it's created.
     *
     * @return Zend_Loader_PluginLoader
     */
    public static function getAdapterLoader()
    {
        if (self::$_adapterLoader === null) {
            self::$_adapterLoader = new Xoops_Zend_Loader_PluginLoader(
                array(
                    'Zend_Paginator_Adapter'        => XOOPS::path('lib') . '/Zend/Paginator/Adapter',
                    'Xoops_Zend_Paginator_Adapter'  => XOOPS::path('lib') . '/Xoops/Zend/Paginator/Adapter'
                )
            );
        }

        return self::$_adapterLoader;
    }

    /**
     * Returns the scrolling style loader.  If it doesn't exist it's
     * created.
     *
     * @return Zend_Loader_PluginLoader
     */
    public static function getScrollingStyleLoader()
    {
        if (self::$_scrollingStyleLoader === null) {
            self::$_scrollingStyleLoader = new Zend_Loader_PluginLoader(
                array(
                    'Zend_Paginator_ScrollingStyle'         => XOOPS::path('lib') . '/Zend/Paginator/ScrollingStyle',
                    'Xoops_Zend_Paginator_ScrollingStyle'   => XOOPS::path('lib') . '/Xoops/Zend/Paginator/ScrollingStyle'
                )
            );
        }

        return self::$_scrollingStyleLoader;
    }

    /**
     * Creates the page collection.
     *
     * @param  string $scrollingStyle Scrolling style
     * @return stdClass
     */
    protected function _createPages($scrollingStyle = null)
    {
        $pages = parent::_createPages($scrollingStyle);
        if (!empty($pages->previous)) {
            $pages->firstUrl = $this->createUrl($pages->first);
            $pages->previousUrl = $this->createUrl($pages->previous);
        }
        if (!empty($pages->next)) {
            $pages->lastUrl = $this->createUrl($pages->last);
            $pages->nextUrl = $this->createUrl($pages->next);
        }

        return $pages;
    }


    /**
     * Returns a subset of pages within a given range.
     *
     * @param  integer $lowerBound Lower bound of the range
     * @param  integer $upperBound Upper bound of the range
     * @return array
     */
    public function getPagesInRange($lowerBound, $upperBound)
    {
        $pages = parent::getPagesInRange($lowerBound, $upperBound);
        foreach ($pages as $number => &$page) {
            $page = array(
                "number"    => $number,
                "url"       => $this->createUrl($number),
            );
        }
        return $pages;
    }

    protected function createUrl($page)
    {
        $params = $this->getParams();
        if (isset($params[$this->pageParam])) {
            unset($params[$this->pageParam]);
        }
        if ($page) {
            $params[$this->pageParam] = $page;
        }
        $url = XOOPS::registry("frontController")->getRouter()->assemble($params);
        return $url;
    }
}