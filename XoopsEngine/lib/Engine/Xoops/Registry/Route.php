<?php
/**
 * XOOPS route registry
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
 * @package         Xoops_Core
 * @subpackage      Registry
 * @version         $Id$
 */

namespace Engine\Xoops\Registry;

class Route extends \Kernel\Registry
{
    //protected $registry_key = "registry_route";

    protected function loadDynamic($options = array())
    {
        $model = \Xoops::getModel('route');
        $select = $model->select()
                        ->distinct()
                        ->from($model, array("name", "data"))
                        //->where('section = ?', $options['section'])
                        //->where('active = ?', 1)
                        ->order('priority DESC');
        if (empty($options["exclude"])) {
            $clause = new \Xoops_Zend_Db_Clause("active = ?", 1);
            $clauseSection = new \Xoops_Zend_Db_Clause("section = ?", $options['section']);
            $clauseSection->add("section = ?", "", "OR");
            $clause->add($clauseSection);
            $select->where($clause);
        } else {
            $clause = new \Xoops_Zend_Db_Clause("active = ?", 1);
            $clauseSection = new \Xoops_Zend_Db_Clause("section <> ?", $options['section']);
            $clauseSection->add("section <> ?", "");
            $clause->add($clauseSection);
            $select->where($clause);
        }
        $rowset = $model->fetchAll($select);

        $configs = array();
        foreach ($rowset as $row) {
            $configs[$row->name] = unserialize($row->data);
        }

        return $configs;
    }

    public function read($section, $exclude = false)
    {
        $options = compact('section', 'exclude');
        return $this->loadData($options);
    }

    public function create($section, $exclude = false)
    {
        self::delete($section);
        self::read($section);
        return true;
    }

    public function delete($section, $exclude = false)
    {
        $options = compact('section', 'exclude');
        return $this->cache->clean('matchingTag', self::createTags($options));
    }

    public function flush()
    {
        return self::delete(null);
    }
}