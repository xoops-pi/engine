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
 * @copyright       Xoops Engine http://www.xoopsengine.org
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Db
 * @version         $Id$
 */

abstract class Xoops_Zend_Db_Table extends Zend_Db_Table_Abstract
{
    /**
     * Table specific prefix, by module
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * Classname for row
     *
     * @var string
     */
    protected $_rowClass = 'Xoops_Zend_Db_Table_Row';

    /**
     * Classname for rowset
     *
     * @var string
     */
    protected $_rowsetClass = 'Xoops_Zend_Db_Table_Rowset';

    /**
     * __construct() - For concrete implementation of Xoops_Zend_Db_Table
     *
     * @param string|array $config string can reference a Zend_Registry key for a db adapter
     *                             OR it can reference the name of a table
     * @param array|Zend_Db_Table_Definition $definition
     */
    public function __construct($config = array(), $definition = null)
    {
        if ($definition !== null && is_array($definition)) {
            $definition = new Zend_Db_Table_Definition($definition);
        }

        if (is_string($config)) {
            if (Zend_Registry::isRegistered($config)) {
                trigger_error(__CLASS__ . '::' . __METHOD__ . '(\'registryName\') is not valid usage of Zend_Db_Table, '
                    . 'try extending Zend_Db_Table_Abstract in your extending classes.',
                    E_USER_NOTICE
                    );
                $config = array(self::ADAPTER => $config);
            } else {
                // process this as table with or without a definition
                if ($definition instanceof Zend_Db_Table_Definition
                    && $definition->hasTableConfig($config)) {
                    // this will have DEFINITION_CONFIG_NAME & DEFINITION
                    $config = $definition->getTableConfig($config);
                } else {
                    $config = array(self::NAME => $config);
                }
            }
        }

        parent::__construct($config);
    }

    /**
     * setOptions()
     *
     * @param array $options
     * @return Zend_Db_Table_Abstract
     */
    public function setOptions(Array $options)
    {
        if (isset($options["prefix"])) {
            $this->setPrefix($options["prefix"]);
            unset($options["prefix"]);
        }
        parent::setOptions($options);

        return $this;
    }

    /**
     * @param  mixed $db Either an Adapter object, or a string naming a Registry key
     * @return Zend_Db_Adapter_Abstract
     * @throws Zend_Db_Table_Exception
     */
    protected static function _setupAdapter($db)
    {
        if ($db === null) {
            return null;
        }
        if (is_string($db)) {
            $db = XOOPS::registry($db);
        }
        if (!$db instanceof Zend_Db_Adapter_Abstract) {
            require_once 'Zend/Db/Table/Exception.php';
            throw new Zend_Db_Table_Exception('Argument must be of type Zend_Db_Adapter_Abstract, or a Registry key where a Zend_Db_Adapter_Abstract object is stored');
        }
        return $db;
    }

    /**
     * Sets the default metadata cache for information returned by Zend_Db_Adapter_Abstract::describeTable().
     *
     * If $defaultMetadataCache is null, then no metadata cache is used by default.
     *
     * @param  mixed $metadataCache Either a Cache object, or a string naming a Registry key
     * @return void
     */
    public static function setDefaultMetadataCache($metadataCache = null)
    {
        self::$_defaultMetadataCache = self::_setupMetadataCache($metadataCache);
    }

    /**
     * Gets the default metadata cache for information returned by Zend_Db_Adapter_Abstract::describeTable().
     *
     * @return Zend_Cache_Core or null
     */
    public static function getDefaultMetadataCache()
    {
        return self::$_defaultMetadataCache;
    }

    /**
     * Sets the metadata cache for information returned by Zend_Db_Adapter_Abstract::describeTable().
     *
     * If $metadataCache is null, then no metadata cache is used. Since there is no opportunity to reload metadata
     * after instantiation, this method need not be public, particularly because that it would have no effect
     * results in unnecessary API complexity. To configure the metadata cache, use the metadataCache configuration
     * option for the class constructor upon instantiation.
     *
     * @param  mixed $metadataCache Either a Cache object, or a string naming a Registry key
     * @return Zend_Db_Table_Abstract Provides a fluent interface
     */
    protected function _setMetadataCache($metadataCache)
    {
        $this->_metadataCache = self::_setupMetadataCache($metadataCache);
        return $this;
    }

    /**
     * @param mixed $metadataCache Either a Cache object, or a string naming a Registry key
     * @return Zend_Cache_Core
     * @throws Zend_Db_Table_Exception
     */
    protected static function _setupMetadataCache($metadataCache)
    {
        if ($metadataCache === null) {
            return null;
        }
        if (is_string($metadataCache)) {
            $metadataCache = Zend_Registry::get($metadataCache);
        }
        return $metadataCache;
    }

    /**
     * Initializes metadata.
     *
     * If metadata cannot be loaded from cache, adapter's describeTable() method is called to discover metadata
     * information. Returns true if and only if the metadata are loaded from cache.
     *
     * @return boolean
     * @throws Zend_Db_Table_Exception
     */
    protected function _setupMetadata()
    {
        if ($this->metadataCacheInClass() && (count($this->_metadata) > 0)) {
            return true;
        }

        // Assume that metadata will be loaded from cache
        $isMetadataFromCache = true;

        // If $this has no metadata cache but the class has a default metadata cache
        //if (null === $this->_metadataCache && null !== self::$_defaultMetadataCache) {
        if (null === $this->_metadataCache && null !== static::getDefaultMetadataCache()) {
            // Make $this use the default metadata cache of the class
            $this->_setMetadataCache(static::getDefaultMetadataCache());
        }

        // If $this has a metadata cache
        if (null !== $this->_metadataCache) {
            // Define the cache identifier where the metadata are saved

            //get db configuration
            $dbConfig = $this->_db->getConfig();

            // Define the cache identifier where the metadata are saved
            $cacheId = md5( // port:host/dbname:schema.table (based on availabilty)
                (isset($dbConfig['options']['port']) ? ':'.$dbConfig['options']['port'] : null)
                . (isset($dbConfig['options']['host']) ? ':'.$dbConfig['options']['host'] : null)
                . '/'.$dbConfig['dbname'].':'.$this->_schema.'.'.$this->_name
                );
        }

        // If $this has no metadata cache or metadata cache misses
        if (null === $this->_metadataCache || !($metadata = $this->_metadataCache->load($cacheId))) {
            // Metadata are not loaded from cache
            $isMetadataFromCache = false;
            // Fetch metadata from the adapter's describeTable() method
            $metadata = $this->_db->describeTable($this->_name, $this->_schema);
            // If $this has a metadata cache, then cache the metadata
            //if (null !== $this->_metadataCache && !$this->_metadataCache->save($metadata, $cacheId, array('model'))) {
            if (null !== $this->_metadataCache && !$this->_metadataCache->save($metadata, $cacheId)) {
                trigger_error('Failed saving metadata to metadataCache', E_USER_NOTICE);
            }
        }

        // Assign the metadata to $this
        $this->_metadata = $metadata;

        // Return whether the metadata were loaded from cache
        return $isMetadataFromCache;
    }

    /**
     * Set table specific prefix
     *
     * @param string $prefix
     * @return {@Xoops_Zend_Db_Table}
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Prefix a table name
     *
     * if tablename is empty, only prefix will be returned
     *
     * @param string $table tablename
     * @return string prefixed tablename, just prefix if tablename is empty
     */
    public function prefix($table = '')
    {
        /*
        $segs = array();
        if (!empty($this->prefix)) {
            $segs[] = $this->prefix;
        }
        if (!empty($table)) {
            $segs[] = $table;
        }
        $table = implode("_", $segs);
        */
        return $this->getAdapter()->prefix($table, $this->prefix);
    }
}
