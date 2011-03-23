<?php
/**
 * System admin link controller
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
 * @category        Xoops_Module
 * @package         System
 * @version         $Id$
 */

class System_LinkController extends Xoops_Zend_Controller_Action_Admin
{
    public function readmeAction()
    {
        echo XOOPS::_(__METHOD__ . ' called');
    }

    public function updatesAction()
    {
        $this->setTemplate("system/admin/link_updates.html");

        $feed = "http://xoops/api/updates";
        $modules = XOOPS::service("registry")->modulelist->read();
        $cacheKey = md5(XOOPS::config('salt') . $feed);
        if (!$updates = XOOPS::registry('cache')->load($cacheKey)) {
            $updates = array(
                "system"    => array(
                    "version"       => "3.0.0",
                    "date"          => date("Y-m-d H:i:s"),
                    "url"           => "http://sf.net/projects/xoops",
                    "changelog"     => "",
                ),
            );
            XOOPS::registry('cache')->write($updates, $cacheKey, 12 * 3600);
        }
        foreach ($modules as $dirname => &$data) {
            $data["status"] = 0;
            if (!isset($updates[$dirname])) continue;
            $data["status"] = version_compare($data["version"], $updates[$dirname]["version"], "<");
            $data["release"] = $updates[$dirname];
        }
        $this->template->assign('modules', $modules);
    }

    public function planetAction()
    {
        $this->setTemplate("system/admin/link_planet.html");

        $newsFeeds = array(
            "http://www.xoops.org/backend.php",
            "http://xoops.org.cn/backend.php",
            "http://sourceforge.net/export/rss2_keepsake.php?group_id=41586",
        );
        $cacheKey = md5(XOOPS::config('salt') . serialize($newsFeeds));
        if (!$news = XOOPS::registry('cache')->load($cacheKey)) {
            $news = array();
            foreach ($newsFeeds as $feed) {
                try {
                    $newsFeed = Zend_Feed::import($feed);
                } catch (exception $e) {
                    continue;
                }

                // Initialize the channel data array
                $channel = array(
                    'title'       => $newsFeed->title(),
                    'link'        => $newsFeed->link(),
                    'description' => strip_tags($newsFeed->description()),
                    'items'       => array()
                    );

                // Loop over each channel item and store relevant data
                foreach ($newsFeed as $item) {
                    $channel['items'][] = array(
                        'title'       => $item->title(),
                        'link'        => $item->link(),
                        'description' => ""//strip_tags($item->description())
                        );
                }
                $news[] = $channel;
            }
            XOOPS::registry('cache')->write($news, $cacheKey, 12 * 3600);
        }
        $this->template->assign('news', $news);
    }

    public function __call($method, $args)
    {
        Debug::e($method . ' called');
    }
}