<?php
/**
 * ACL for Xoops Engine
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
 * @package         Xoops_Core
 * @since           3.0
 * @version         $Id$
 */

class Xoops_Acl
{
    const ADMIN     = "admin";
    const MEMBER    = "member";
    const GUEST     = "guest";
    const MODERATOR = "moderator";
    const BANNED    = "banned";
    const INACTIVE  = "inactive";

    protected $section = null;
    protected $module = null;
    protected $role;
    protected $roles;
    protected $models = array();

    public function __construct($section = null)
    {
        if (!is_null($section)) {
            $this->section = $section;
        }
    }

    public function getModel($modelName)
    {
        if (!isset($this->models[$modelName])) {
            $model = XOOPS::getModel("acl_" . $modelName);
            $this->models[$modelName] = $model;
        }
        if (method_exists($this->models[$modelName], "setSection")) {
            $this->models[$modelName]->setSection($this->getSection());
        }
        if ($this->getSection() == "module" && method_exists($this->models[$modelName], "setModule")) {
            $this->models[$modelName]->setModule($this->getModule());
        }

        return $this->models[$modelName];
    }

    /**
     * Set section for resources
     *
     * @param string $section  section name, potential values: front - "front"; admin - "admin"; block - "block"
     */
    public function setSection($section)
    {
        if (!is_null($section)) {
            $this->section = $section;
        }
        return $this;
    }

    public function getSection()
    {
        return $this->section;
    }

    public function setModule($module)
    {
        if (!is_null($module)) {
            $this->module = $module;
        }
        return $this;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function setRole($role)
    {
        if (!is_null($role)) {
            if ($role != $this->role) {
                $this->roles = null;
            }
            $this->role = $role;
        }
        return $this;
    }

    public function getRole()
    {
        if (is_null($this->role)) {
            $this->role = XOOPS::registry("user")->role;
        }
        return $this->role;
    }

    /**
     * Check access to a resource privilege for a given role
     *
     * @param string $role
     * @param string|array|object  $resource  resource name or array(resource, item), or {@link Zend_Controller_Request_Abstract}
     * @param string $privilege privilege name
     * @return boolean
     */
    public function isAllowed($role, $resource, $privilege = null)
    {
        if ($role == self::ADMIN) return true;

        $clause = new Xoops_Zend_Db_Clause();
        $roles = $this->loadRoles($role);
        if (count($roles) == 1) {
            $clause->add("role = ?", $roles[0]);
        } else {
            $clause->add("role IN (?)", $roles);
        }
        $resources = $this->loadResources($resource);
        if (empty($resources)) {
            return false;
        } elseif (count($resources) == 1) {
            $clause->add("resource = ?", $resources[0]);
        } else {
            $clause->add("resource IN (?)", $resources);
        }
        if (!is_null($privilege)) {
            $clause->add("privilege = ?", $privilege);
        }

        $moduleRule = $this->getModel("rule");
        if (!$allowed = $moduleRule->isAllowed($clause)) {
            return false;
        }
        return $allowed;
    }

    /**
     * Check access to a resource privilege for a given role
     *
     * @param string|array|object  $resource  resource name or array(resource, item), or {@link Zend_Controller_Request_Abstract}
     * @param string    $privilege privilege name
     * @return boolean
     */
    public function hasAccess($resource, $privilege = null)
    {
        return $this->isAllowed($this->getRole(), $resource, $privilege);
    }

    /**
     * Get resources to which a group of roles is allowed/denied to access a given resource privilege
     *
     * @param object    $clause {@link Xoops_Zend_Db_Clause}
     * @param boolean   $allowed allowed or denied
     * @return array of resources
     */
    public function getResources($clause = null, $allowed = true)
    {
        if ($this->getRole() == self::ADMIN) return null;
        $roles = $this->loadRoles();
        return $this->getModel("rule")->getResources($roles, $clause, $allowed);
    }

    /**
     * Get items of a specific resource to which a group of roles is allowed/denied to access a given resource privilege
     *
     * @param string    $resource
     * @param object    $clause {@link Xoops_Zend_Db_Clause}
     * @param boolean   $allowed allowed or denied
     * @return array of items
     */
    public function getItems($resource, $clause = null, $allowed = true)
    {
        if ($this->getRole() == self::ADMIN) return null;
        $roles = $this->loadRoles();
        if (!$clause instanceof Xoops_Zend_Db_Clause) {
            $clause = new Xoops_Zend_Db_Clause("resource = ?", $resource);
        } else {
            $clause->add("resource = ?", $resource);
        }
        return $this->getModel("rule")->getResources($roles, $clause, $allowed);
    }

    /**
     * Load ancestors of a role from database
     *
     * @param string $role
     * @return array of roles
     */
    public function loadRoles($role = null)
    {
        if (!is_null($role) && $role != $this->getRole()) {
            $roles = XOOPS::service('registry')->role->read($role);
            array_push($roles, $role);
            return $roles;
        }
        if (is_null($this->roles)) {
            $this->roles = XOOPS::service('registry')->role->read($this->getRole());
            array_push($this->roles, $this->getRole());
        }
        return $this->roles;
    }

    /**
     * Load ancestors of a resource from database
     *
     * @param string|array|object  $resource  resource name or array(resource, item), or {@link Zend_Controller_Request_Abstract} or {@link Xoops_Zend_Db_Table_Row_Node}
     * @return array of resources
     */
    public function loadResources($resource)
    {
        if ($resource instanceof Zend_Controller_Request_Abstract) {
            $module = $resource->getModuleName();
            $controller = $resource->getControllerName();
            $action = $resource->getActionName();
            //Debug::e($this->getSection());
            //Debug::e($module);
            $resourceList = XOOPS::service("registry")->resource->read($this->getSection(), $module);
            //Debug::e($resourceList);
            $pageList = array_flip(XOOPS::service("registry")->page->read($this->getSection(), $module));
            $resources = array();
            foreach ($resourceList as $page => $list) {
                // Generated from page or named
                $key = isset($pageList[$page]) ? $pageList[$page] : $page;
                $resources[$key] = $list;
            }
            if (isset($resources["{$module}-{$controller}-{$action}"])) {
                return $resources["{$module}-{$controller}-{$action}"];
            } elseif (isset($resources["{$module}-{$controller}"])) {
                return $resources["{$module}-{$controller}"];
            } elseif (isset($resources[$module])) {
                return $resources[$module];
            } else {
                return array();
            }
        }
        $resources = array();
        $modelResource = $this->getModel("resource");
        if (!($resource instanceof Xoops_Zend_Db_Table_Row_Node)) {
            //Debug::e($resource);
            $select = $modelResource->select()->where('section = ?', $this->getSection());
            if ($this->getModule()) {
                $select->where('module = ?', $this->getModule());
            }
            if (is_array($resource)) {
                $select->where("name = ?", $resource[0])
                        ->where("item = ?", $resource[1]);
            } else {
                $select->where("name = ?", $resource);
            }
            if (!$resource = $modelResource->fetchRow($select)) {
                return $resources;
            }
        }
        $resources = $modelResource->getAncestors($resource, "id");
        return $resources;
    }
}