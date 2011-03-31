<?php
/**
 * XOOPS module ACL installer
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
 * @package         Xoops_Installer
 * @subpackage      Installer
 * @version         $Id$
 */

/**
 * ACL configuration specs
 *
 *  return array(
 *      "roles" => array(
 *          "roleName"  => array(
 *              "title"     => "Title",
 *              "parents"   => array("parent")
 *          ),
 *          ...
 *      ),
 *      "resources" => array(
 *          // module-wide resources
 *          "module"    => array(
 *              array(
 *                  "name"          => "category",
 *                  "title"         => "Category Title",
 *                  "parent"        => "parentCategory"
 *                  "rules"         => array(
 *                      "guest"     => 1,
 *                      "member"    => 1
 *                  ),
 *                  "privileges"    => array(
 *                      "read"      => array(
 *                          "title" => "Read articles",
 *                      ),
 *                      "post"      => array(
 *                          "title" => "Post articles",
 *                          "rules" => array(
 *                              "guest"     => 0,
 *                          ),
 *                      ),
 *                      "delete"    => array(
 *                          "title" => "Post articles",
 *                          "rules" => array(
 *                              "guest"     => 0,
 *                              "member"    => 0,
 *                          ),
 *                      ),
 *                  ),
 *              ),
 *              ...
 *          ),
 *      ),
 *  );
 */

class Xoops_Installer_Module_Acl extends Xoops_Installer_Abstract
{
    public function install(&$message)
    {
        $module = $this->module->dirname;
        XOOPS::service('registry')->resource->flush($module);
        $message = $this->message;
        $status = true;

        $modelRole = XOOPS::getModel('acl_role');
        $modelResource = XOOPS::getModel('acl_resource');
        $modelRule = XOOPS::getModel('acl_rule');
        $modelPrivilege = XOOPS::getModel('acl_privilege');
        if (!empty($this->config['roles'])) {
            $inheritance = array();
            foreach ($this->config['roles'] as $key => $role) {
                $role['name'] = $key;
                $role['module'] = $module;
                if (isset($role['parents'])) {
                    $inheritance[$role['name']] = $role['parents'];
                    unset($role['parents']);
                }
                if ($modelRole->insert($role)) {
                    $message[] = "Role " . $role['title'] . " created";
                } else {
                    $message[] = "Role " . $role['title'] . " failed";
                    $status = false;
                }
            }
            if (!empty($inheritance)) {
                $modelInherit = XOOPS::getModel('acl_inherit');
                foreach ($inheritance as $child => $parents) {
                    foreach ($parents as $parent) {
                        $inherit = compact("child", "parent");
                        if ($modelInherit->insert($inherit)) {
                            $message[] = "Inherit " . $child . "-" . $parent . " created";
                        } else {
                            $message[] = "Inherit " . $child . "-" . $parent . " failed";
                            $status = false;
                        }
                    }
                }
            }
        }

        $resources = isset($this->config['resources']) ? $this->config['resources'] : array();
        foreach ($resources as $section => $resourceList) {
            foreach ($resourceList as $key => $resource) {
                $resource['module'] = isset($resource['module']) ? $resource['module'] : $module;
                $resource['section'] = $section;
                $resource['type'] = "system";
                $status = $this->insertResource($resource, $message) * $status;
            }
        }

        return $status;
    }

    public function update(&$message)
    {
        $module = $this->module->dirname;
        XOOPS::service('registry')->resource->flush($module);
        $message = $this->message;
        $status = true;

        if (version_compare($this->version, $this->module->version, ">=")) {
            return true;
        }

        $resources_new = isset($this->config['resources']) ? $this->config['resources'] : array();

        $model = XOOPS::getModel('acl_resource');
        $select = $model->select()->where('module = ?', $module)
                                    ->where('type = ?', "system");
        $rowset = $model->fetchAll($select);
        $resources_exist = array();
        foreach ($rowset as $row) {
            $key = $row->section . ':' . $row->module . ':' . $row->name;
            $resources_exist[$key] = $row->toArray();
        }

        foreach ($resources_new as $section => $resourceList) {
            foreach ($resourceList as $index => $resource) {
                $resource["module"] = empty($resource["module"]) ? $module : $resource["module"];
                $resource["section"] = $section;
                $resource["type"] = "system";
                $key = $section . ':' . $resource["module"] . ':' . $resource["name"];
                if (isset($resources_exist[$key])) {
                    $resource["id"] = $resources_exist[$key]['id'];
                    $this->updateResource($resource, $message);
                    unset($resources_exist[$key]);
                    continue;
                }

                $status = $this->insertResource($resource, $message) * $status;
            }
        }

        foreach ($resources_exist as $key => $resource) {
            $this->deleteResource($resource['id'], $message);
        }

        return;
    }

    public function uninstall(&$message)
    {
        if (empty($this->module)) {
            return;
        }
        $module = $this->module->dirname;
        XOOPS::service('registry')->resource->flush($module);
        $message = $this->message;

        // role: remove or not?
        $model = XOOPS::getModel('acl_role');
        $where = array('module = ?' => $module);
        $model->delete($where);

        // resource: remove
        $model = XOOPS::getModel('acl_resource');
        //$columnLeft = $model->getAdapter()->quoteIdentifier($model->left);
        $select = $model->select()->where("module = ?", $module)->order($model->left);
        $row = $model->fetchRow($select);
        $rows = $model->delete(array("module = ?" => $module));
        if (!empty($rows)) {
            //$model->trim($row->left);
        }

        // privilege: remove
        $model = XOOPS::getModel('acl_privilege');
        $where = array('module = ?' => $module);
        $model->delete($where);

        // rule: remove
        $model = XOOPS::getModel('acl_rule');
        $where = array('module = ?' => $module);
        $model->delete($where);
    }

    public function activate(&$message)
    {
        $module = $this->module->dirname;
        XOOPS::service('registry')->resource->flush($module);
        $message = $this->message;

        // update role active => 1
        $model = XOOPS::getModel('acl_role');
        $where = array('module = ?' => $module);
        $model->update(array("active" => 1), $where);
    }

    public function deactivate(&$message)
    {
        $module = $this->module->dirname;
        XOOPS::service('registry')->resource->flush($module);
        $message = $this->message;

        // update role active => 0
        $model = XOOPS::getModel('acl_role');
        $where = array('module = ?' => $module);
        $model->update(array("active" => 0), $where);
    }

    private function insertResource($resource, &$message)
    {
        $module = $this->module->dirname;
        $modelResource = XOOPS::getModel("acl_resource");
        $modelRule = XOOPS::getModel("acl_rule");
        $modelPrivilege = XOOPS::getModel('acl_privilege');
        $columnsResource = $modelResource->info("cols");
        $status = true;

        $data = array();
        foreach ($resource as $col => $val) {
            if (in_array($col, $columnsResource)) {
                $data[$col] = $val;
            }
        }
        if (!empty($resource["parent"])) {
            $where = array(
                "section"   => $resource["section"],
                "modul"     => $resource["module"],
                "name"      => $resource["parent"]
            );
            if (is_array($resource["parent"])) {
                $where = array_merge($where, $resource["parent"]);
            }
            $select = $modelResource->select()
                                        ->where("section = ?", $where["section"])
                                        ->where("module = ?", $where["module"])
                                        ->where("name = ?", $where["parent"]);
            $parent = $modelResource->fetchRow($select);
        } else {
            $parent = 0;
        }

        // Add resource
        if ($resourceId = $modelResource->add($data, $parent)) {
            $message[] = "Resource " . $data["name"] . " created";

            if (isset($resource["privileges"])) {
                foreach ($resource["privileges"] as $name => $privilege) {
                    $data = array(
                        "resource"  => $resourceId,
                        "module"    => $resource['module'],
                        "name"      => $name,
                        "title"     => isset($privilege['title']) ? $privilege['title'] : $name,
                    );
                    if ($modelPrivilege->insert($data)) {
                        $message[] = "Privilege " . implode("-", array_values($data)) . " created";

                        if (isset($privilege["access"])) {
                            foreach ($privilege["access"] as $role => $rule) {
                                $data = array();
                                $data["role"] = $role;
                                $data["privilege"] = $name;
                                $data["resource"] = $resourceId;
                                $data["section"] = $resource["section"];
                                $data["module"] = $resource["module"];
                                $data["deny"] = empty($rule) ? 1 : 0;
                                if ($modelRule->insert($data)) {
                                    $message[] = "Rule " . implode("-", array_values($data)) . " created";
                                } else {
                                    $message[] = "Rule " . implode("-", array_values($data)) . " failed";
                                    $status = false;
                                }
                            }
                        }

                    } else {
                        $message[] = "Privilege " . implode("-", array_values($data)) . " failed";
                        $status = false;
                    }
                }
            // Insert access rules
            } elseif (isset($resource["access"])) {
                foreach ($resource["access"] as $role => $rule) {
                    $data = array();
                    $data["role"] = $role;
                    $data["resource"] = $resourceId;
                    $data["section"] = $resource["section"];
                    $data["module"] = $resource["module"];
                    $data["deny"] = empty($rule) ? 1 : 0;
                    if ($modelRule->insert($data)) {
                        $message[] = "Rule " . implode("-", array_values($data)) . " created";
                    } else {
                        $message[] = "Rule " . implode("-", array_values($data)) . " failed";
                        $status = false;
                    }
                }
            }


        } else {
            $message[] = "Resource " . $resource["name"] . " failed";
            $status = false;
        }


        return $status;
    }

    private function updateResource($resource, &$message)
    {
        $module = $this->module->dirname;
        $modelResource = XOOPS::getModel("acl_resource");
        $modelRule = XOOPS::getModel("acl_rule");
        $modelPrivilege = XOOPS::getModel('acl_privilege');
        $status = true;

        if (!$resourceRow = $modelResource->findRow($resource["id"])) {
            $message[] = "Resource " . $resource["id"] . " is not found";
            return false;
        }
        $data = array("title" => $resource["title"]);
        $modelResource->update($data, array("id = ?" => $resource["id"]));

        $select = $modelPrivilege->select()->where("resource = ?", $resource["id"]);
        $privileges_exist = $modelPrivilege->fetchAll($select);
        $privileges_new = isset($resource["privileges"]) ? $resource["privileges"] : array();
        $privileges_remove = array();
        foreach ($privileges_exist as $privilege) {
            if (isset($privileges_new[$privilege["name"]])) {
                $modelPrivilege->update(array("title" => $privilege["title"]), array("id = ?" => $privilege["id"]));
                unset($privileges_new[$privilege["name"]]);
                continue;
            }
            $privileges_remove[$privilege["id"]] = $privilege["name"];
        }
        if (!empty($privileges_remove)) {
            $modelPrivilege->delete(array("id IN (?)" => array_keys($privileges_remove)));
            $modelRule->delete(array("privilege IN (?)" => array_values($privileges_remove), "resource = ?" => $resource["id"]));
        }
        foreach ($privileges_new as $name => $privilege) {
            $data = array(
                "resource"  => $resource["id"],
                "module"    => $resource['module'],
                "name"      => $name,
                "title"     => isset($privilege['title']) ? $privilege['title'] : $name,
            );
            if ($modelPrivilege->insert($data)) {
                $message[] = "Privilege " . implode("-", array_values($data)) . " created";

                if (isset($privilege["access"])) {
                    foreach ($privilege["access"] as $role => $rule) {
                        $data = array();
                        $data["role"] = $role;
                        $data["privilege"] = $name;
                        $data["resource"] = $resource["id"];
                        $data["section"] = $resource["section"];
                        $data["module"] = $resource["module"];
                        $data["deny"] = empty($rule) ? 1 : 0;
                        if ($modelRule->insert($data)) {
                            $message[] = "Rule " . implode("-", array_values($data)) . " created";
                        } else {
                            $message[] = "Rule " . implode("-", array_values($data)) . " failed";
                            $status = false;
                        }
                    }
                }
            } else {
                $message[] = "Privilege " . implode("-", array_values($data)) . " failed";
                $status = false;
            }
        }

        return;
    }

    private function deleteResource($resource, &$message)
    {
        $module = $this->module->dirname;
        $modelResource = XOOPS::getModel("acl_resource");
        $modelRule = XOOPS::getModel("acl_rule");
        $modelPrivilege = XOOPS::getModel('acl_privilege');

        if (!$resourceRow = $modelResource->findRow($resource)) {
            $message[] = "Resource " . $resource . " is not found";
            return false;
        }
        $modelResource->remove($resourceRow);
        $modelRule->delete(array("section" => $resourceRow->section, "resource = ?" => $resourceRow->id));
        $modelPrivilege->delete(array("resource = ?" => $resourceRow->id));
        return;
    }
}