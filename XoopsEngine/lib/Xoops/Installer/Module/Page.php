<?php
/**
 * XOOPS module page installer
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
 * Page configuration specs
 *
 *  return array(
 *          // font mvc pages
 *          "front" => array(
 *              array(
 *                  "title"         => "Title",
 *                  "controller"    => "controllerName",
 *                  "cache_expire"  => 0,
 *                  "cache_level"   => "",
 *                  "block"         => 0,
 *                  "access"        => array(
 *                      "guest"     => 1,
 *                      "member"    => 0
 *                  ),
 *              ),
 *              array(
 *                  "title"         => "Title",
 *                  "controller"    => "controllerName",
 *                  "action"        => "actionName",
 *                  "cache_expire"  => 60,
 *                  "cache_level"   => "role",
 *                  "block"         => 1,
 *                  "access"        => array(
 *                      "guest"     => 1,
 *                      "member"    => 0
 *                  ),
 *              ),
 *              ...
 *          ),
 *          // admin mvc pages
 *          "admin"  => array(
 *              array(
 *                  "title"         => "Title",
 *                  "controller"    => "controllerName",
 *                  "action"        => "actionName",
 *                  "cache_expire"  => 0,
 *                  "cache_level"   => "",
 *                  "access"        => array(
 *                      "roleA" => 1,
 *                      "roleB" => 0
 *                  ),
 *              ),
 *              ...
 *          ),
 *          // feed mvc pages
 *          "feed"  => array(
 *              array(
 *                  "title"         => "Title",
 *                  "controller"    => "controllerName",
 *                  "action"        => "actionName",
 *                  "cache_expire"  => 0,
 *                  "cache_level"   => "",
 *              ),
 *              ...
 *          ),
 *          ...
 *  );
 */

class Xoops_Installer_Module_Page extends Xoops_Installer_Abstract
{
    public function install(&$message)
    {
        $module = $this->module->dirname;
        XOOPS::service('registry')->page->flush($module);
        $message = $this->message;
        $status = true;

        // Skip if pages disabled
        if (false === ($pages = $this->config)) {
            return;
        }
        if (!isset($pages["front"])) {
            $pages["front"][] = array(
                "title" => $this->module->name,
            );
        }
        if (!isset($pages["admin"])) {
            $pages["admin"][] = array(
                "title" => $this->module->name,
            );
        }

        foreach (array_keys($pages) as $section) {
            // Skip the section if disabled
            if ($pages[$section] === false) continue;
            $pageList = array();
            foreach ($pages[$section] as $key => $page) {
                $page["section"] = $section;
                if (!isset($page["module"])) {
                    $page["module"] = $module;
                }
                $pageName = $page["module"];
                if (!empty($page["controller"])) {
                    $pageName .= "-" . $page["controller"];
                    if (!empty($page["action"])) {
                        $pageName .= "-" . $page["action"];
                    }
                }
                if (empty($page["title"])) {
                    $page["title"] = $pageName;
                }
                $pageList[$pageName] = $page;
            }
            if (!isset($pageList[$module])) {
                $pageList[$module] = array(
                    "section"   => $section,
                    "module"    => $module,
                    "name"      => $module,
                    "title"     => $this->module->name,
                    "block"     => 1
                );
            }
            // Sort page list by module-controller-action
            ksort($pageList);
            $pages[$section] = $pageList;
        };

        if (!empty($pages["front"]) && !isset($pages["front"][$module]["access"])) {
            $pages["front"][$module]["access"] = array(
                "guest"     => 1,
                "member"    => 1
            );
        }
        if (!empty($pages["admin"]) && !isset($pages["admin"][$module]["access"])) {
            $pages["admin"][$module]["access"] = array(
                "guest"     => 0,
                "moderator" => 1,
            );
        }
        foreach ($pages as $section => $pageList) {
            $resources = array();
            foreach ($pageList as $name => $page) {
                $status = $this->insertPage($page, $message) * $status;
            }
        }

        return $status;
    }

    public function update(&$message)
    {
        $module = $this->module->dirname;
        XOOPS::service('registry')->page->flush($module);
        $message = $this->message;

        if (version_compare($this->version, $this->module->version, ">=")) {
            return true;
        }

        if ($this->config === false) {
            $pages = array();
            $diablePage = true;
        } else {
            $pages = $this->config;
            $diablePage = false;
        }

        $model = XOOPS::getModel('page');
        $select = $model->select();
        $select->where('module = ?', $module);
        if ($module == "system") {
            $select->orWhere('module = ?', "default");
        }
        $rowset = $model->fetchAll($select);
        $pages_exist = array();
        foreach ($rowset as $row) {
            $page = $row->toArray();
            $key = $page['section'] . ':' . $page['module'] . ':' . $page['controller'] . ':' . $page['action'];
            $pages_exist[$key] = $page;
        }

        $pagesOtherModule = array();
        foreach ($pages as $section => $pageList) {
            foreach ($pageList as $index => $page) {
                $page["section"] = $section;
                if (!isset($page["module"])) {
                    $page["module"] = $module;
                }
                $controller = empty($page['controller']) ? "" : $page['controller'];
                $action = empty($page['action']) ? "" : $page['action'];
                $key = $section . ':' . $page['module'] . ':' . $controller . ':' . $action;
                if (isset($pages_exist[$key])) {
                    $page_exist = $pages_exist[$key];
                    $data = array();
                    if ($page_exist['custom']) {
                        $data['custom'] = 0;
                    }
                    if ($page['title'] != $page_exist['title']) {
                        $data['title'] = $page['title'];
                    }
                    if (!empty($data)) {
                        $model->update($data, array("id = ?" => $page_exist['id']));
                    }
                    unset($pages_exist[$key]);
                    continue;
                }

                $status = $this->insertPage($page, $message) * $status;
            }
        }
        foreach ($pages_exist as $key => $page) {
            if ($page['custom'] && !$diablePage) continue;
            $this->deletePage($page['id'], $message);
        }
        return;
    }

    public function uninstall(&$message)
    {
        if (!is_object($this->module)) {
            return;
        }
        $dirname = $this->module->dirname;
        XOOPS::service('registry')->page->flush($dirname);
        $message = $this->message;

        $model = XOOPS::getModel('page');
        $rows = $model->delete(array("module = ?" => $dirname));
        if (!empty($rows)) {
            //$model->trim($row->left);
        }
        return;
    }

    private function insertPage($page, &$message)
    {
        $module = $this->module->dirname;
        $modelPage = XOOPS::getModel("page");
        $modelResource = XOOPS::getModel("acl_resource");
        $modelRule = XOOPS::getModel("acl_rule");
        $columnsPage = $modelPage->info("cols");
        $columnsResource = $modelResource->info("cols");

        $data = array();
        foreach ($page as $col => $val) {
            if (in_array($col, $columnsPage)) {
                $data[$col] = $val;
            }
        }
        $status = true;
        // Insert page
        if ($pageId = $modelPage->insert($data)) {
            $message[] = "Page " . $page['title'] . " added";

            // No access restrict is set on default module
            if ($data["module"] != "default" && (!isset($page["resource"]) || $page["resource"] !== false)) {
                $resource = array();
                foreach ($page as $col => $val) {
                    if (in_array($col, $columnsResource)) {
                        $resource[$col] = $val;
                    }
                }
                $resource["id"] = null;
                $resource["name"] = $pageId;
                $resource["type"] = "page";
                /*
                if (empty($resource["title"]) && !empty($page["title"])) {
                    $resource["title"] = $page["title"];
                }
                */
                $parent = 0;
                // set parent by named resource
                if (!empty($page["parent"])) {
                    $where = array(
                        "section"   => $resource["section"],
                        "module"     => $resource["module"],
                        "name"      => $page["parent"]
                    );
                    if (is_array($page["parent"])) {
                        $where = array_merge($where, $page["parent"]);
                    }
                    //Debug::e($page);
                    //Debug::e($where);
                    $select = $modelResource->select()
                                                ->where("section = ?", $where["section"])
                                                ->where("module = ?", $where["module"])
                                                ->where("name = ?", $where["name"]);
                    $parent = $modelResource->fetchRow($select);
                // set parent by m-v-a
                } elseif (!empty($page["controller"])) {
                    $select = $modelPage->select()->where("section = ?", $page["section"]);
                    $select->where("module = ?", $page["module"]);
                    if (!empty($page["action"])) {
                        $select->where("controller = ?", $page["controller"]);
                    }
                    if ($parentPage = $modelPage->fetchRow($select)) {
                        $select = $modelResource->select()
                                                    ->where("section = ?", $page["section"])
                                                    ->where("name = ?", $parentPage->id);
                        $parent = $modelResource->fetchRow($select);
                    }
                }

                //Debug::e($resource);
                // Add resource
                if ($resourceId = $modelResource->add($resource, $parent)) {
                    $message[] = "Resource " . $resource["name"] . " created";

                    // Insert access rules
                    if (isset($page["access"])) {
                        foreach ($page["access"] as $role => $rule) {
                            $data = array();
                            $data["role"] = $role;
                            $data["resource"] = $resourceId;
                            $data["section"] = $resource["section"];
                            $data["module"] = $module;
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
            }

        } else {
            $message[] = "Page " . $page['title'] . " failed";
            $status = false;
        }

        return $status;
    }

    private function deletePage($page, &$message)
    {
        $module = $this->module->dirname;
        $modelPage = XOOPS::getModel("page");
        $modelResource = XOOPS::getModel("acl_resource");
        $modelRule = XOOPS::getModel("acl_rule");
        if (!$pageRow = $modelPage->findRow($page)) {
            $message[] = "Page " . $page . " is not found";
            return false;
        }
        $select = $modelResource->select()
                                    ->where("section = ?", $pageRow->section)
                                    ->where("module = ?", $pageRow->module)
                                    ->where("type = ?", "page")
                                    ->where("name = ?", $pageRow->id);
        if ($resourceRow = $modelResource->fetchRow($select)) {
            return false;
        }
        $resourceRows = $modelResource->getChildren($resourceRow, array("id"));
        $resources = array();
        foreach ($resourceRows as $row) {
            $resources[] = $row->id;
        }
        $modelRule->delete(array("section" => $pageRow->section, "resource IN (?)" => $resources));
        $modelResource->remove($resourceRow, true);
        $modelPage->delete(array("id = ?", $page));
    }
}