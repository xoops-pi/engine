<?php
/**
 * XOOPS event registry
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

class Event extends \Kernel\Registry
{
    //protected $registry_key = "registry_event";

    /**
     * load event data from module config
     *
     * A module event configuration file (events in apps/press/configs/event.ini.php):
     * event[] = article_post
     * event[] = article_delete
     * event[] = article_rate
     *
     * Trigger in apps/press/controllers/ArticleController.php
     * \Xoops::service('event')->trigger('press_article_post', $articleObject);
     *
     * Callback configurations in apps/user/configs/event.ini.php
     * observer.press.article_post[] = stats::article
     *
     * Callback calss in apps/user/class/stats.php
     * class User_Stats
     * {
     *      public static function article($articleObject) { ... }
     * }
     */
    protected function loadDynamic($options)
    {
        $observers = array();
        if (!$modelEvent = \Xoops::getModel("event")) {
            return $observers;
        }
        $select = $modelEvent->select()->where("module = ?", $options['module'])
                                        ->where("name = ?", $options['event'])
                                        ->where("active = ?", 1);
        if (!$row = $modelEvent->fetchRow($select)) {
            return $observers;
        }

        $modelObserver = \Xoops::getModel("event_observer");
        $select = $modelObserver->select()->where("event_module = ?", $options['module'])
                                            ->where("event = ?", $options['event'])
                                            ->where("active = ?", 1);
        $observerList = $modelObserver->fetchAll($select);
        foreach ($observerList as $row) {
            //$module = \XOOPS::service("module")->getDirectory($row->module);
            //$prefix = "app" == \XOOPS::service("module")->getType($row->module) ? "app" : "module";
            //$callback = array($prefix . '_' . $module . '_' . $row->class, $row->method);
            $observers[$row->module] = array($row->class, $row->method); //$callback;
        }

        return $observers;
    }

    public function read($module, $event)
    {
        if (empty($event)) return false;
        $options = compact('module', 'event');
        return $this->loadData($options);
    }

    /**
     * Add a module event
     */
    public function create($module, $event = null)
    {
        self::delete($module, $event);
        self::read($module, $event);
        return true;
    }

    /**
     * Remove a module event
     */
    public function delete($module, $event = null)
    {
        $options = compact('module', 'event');
        return $this->cache->clean('matchingTag', self::createTags($options));
    }

    /**
     * Remove module events and module observers
     */
    public function flush($module = null)
    {
        return self::delete($module);
    }
}