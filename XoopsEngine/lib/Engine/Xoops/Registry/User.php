<?php
/**
 * XOOPS user meta registry
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * Different actions -
 *
 * For edit:
 *  null method - use default form element "Text"
 *  empty method - hide the element
 *  method is string - use system form element
 *  method is array([module], element, [options]) - use module form element
 *
 * For admin:
 *  null method - inherite from edit
 *  otherwise - same mehtods as edit
 *
 * For view:
 *  null method - use raw data
 *  empty method - hide the data
 *  method is array(module, element) - transform raw data via the module_profile::method
 *
 * @copyright       Xoops Engine http://www.xoopsengine.org/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         Xoops_Core
 * @subpackage      Registry
 * @version         $Id$
 */

namespace Engine\Xoops\Registry;

class User extends \Kernel\Registry
{
    //protected $registry_key = "registry_role";

    protected function loadDynamic($options = array())
    {
        $parseView = function ($row)
        {
            $view = array();
            if (!empty($row->view)) {
                $view["method"] = array($row->module . "_profile", $row->view);
            } elseif (!empty($row->options)) {
                $view["options"] = unserialize($row->options);
            }

            return $view;
        };

        $parseEdit = function ($row, $action)
        {
            if ($action == "admin") {
                $input = is_null($row->admin) ? $row->edit : $row->admin;
            } else {
                $input = $row->edit;
            }
            if (!empty($input)) {
                $input = unserialize($input);
                if (is_string($input) && !empty($input)) {
                    $input = array("type" => $input);
                }
                if (empty($input["options"]["multiOptions"])) {
                    if (!empty($row->options)) {
                        $input["options"]["multiOptions"] = unserialize($row->options);
                    }
                }
                if (!empty($input["module"])) {
                    $input["module"] = $row->module;
                }
            }
            if (!empty($input) || is_null($input)) {
                $input["options"]["label"] = $row->title;
                if ($row->required) {
                    $meta["options"]["required"] = 1;
                }
            }

            return $input;
        };

        $parseSearch = function ($row)
        {
            if (!is_null($row->search)) {
                $input = $row->search;
            } elseif (!empty($row->edit)) {
                $input = $row->edit;
            } elseif (!empty($row->admin)) {
                $input = $row->admin;
            }
            if (!empty($input)) {
                $input = unserialize($input);
                if (is_string($input) && !empty($input)) {
                    $input = array("type" => $input);
                }
                if (empty($input["options"]["multiOptions"])) {
                    if (!empty($row->options)) {
                        $input["options"]["multiOptions"] = unserialize($row->options);
                    }
                }
                if (!empty($input["module"])) {
                    $input["module"] = $row->module;
                }
            }
            if (!empty($input) || is_null($input)) {
                $input["options"]["label"] = $row->title;
            }

            return $input;
        };

        $model = \Xoops::getModel("user_meta");
        $select = $model->select()->where("active = ?", 1)->order("id ASC");
        $rowset = $model->fetchAll($select);
        $data = array();
        foreach ($rowset as $row) {
            if ($options["action"] == "edit") {
                if ($meta = $parseEdit($row, "edit")) {
                    $data[$row->key] = $meta;
                }
                continue;
            }
            if ($options["action"] == "admin") {
                if ($meta = $parseEdit($row, "admin")) {
                    $data[$row->key] = $meta;
                }
                continue;
            }
            if ($options["action"] == "search") {
                if ($meta = $parseSearch($row)) {
                    $data[$row->key] = $meta;
                }
                continue;
            }
            if (isset($row->view)) {
                $data[$row->key] = $parseView($row);
            }
            $data[$row->key]["title"] = $row->title;
        }
        return $data;
    }

    public function read($action = "view", $meta = null)
    {
        $options = compact("action");
        $data = $this->loadData($options);
        if (isset($meta)) {
            $result = isset($data[$meta]) ? $data[$meta] : false;
        } else {
            $result = $data;
        }

        return $result;
    }

    public function create($action = "view")
    {
        self::delete($action);
        self::read($action);
        return true;
    }

    public function delete($action = null)
    {
        $options = compact("action");
        return $this->cache->clean('matchingTag', self::createTags($options));
    }

    public function flush()
    {
        return self::delete();
    }
}