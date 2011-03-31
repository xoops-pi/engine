<?php
/**
 * Sitemap controller for feed
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
 * @package         Default
 * @version         $Id$
 */

class Default_SitemapController extends Xoops_Zend_Controller_Action
{
    /**
     * Namespace for the <urlset> tag
     *
     * @var string
     */
    const SITEMAP_NS = 'http://www.sitemaps.org/schemas/sitemap/0.9';

    protected function loadView()
    {
        return;
    }

    public function indexAction()
    {
        $persistKey = __CLASS__;
        if (Xoops::config("environment") != 'production' || !$content = Xoops::persist()->load($persistKey)) {
            $navigation = XOOPS::service("registry")->navigation->read("front", "default", "guest");
            $container = new Xoops_Zend_Navigation($navigation);
            $content = $this->getDomSitemap($container);
            if (Xoops::config("environment") == 'production') {
                Xoops::persist()->save($content, $persistKey);
            }
        }
        //header("content-type: text/xml");
        echo $content;
    }

    protected function getDomSitemap($container)
    {
        // create validators
        $locValidator        = new Zend_Validate_Sitemap_Loc();
        $lastmodValidator    = new Zend_Validate_Sitemap_Lastmod();
        $changefreqValidator = new Zend_Validate_Sitemap_Changefreq();
        $priorityValidator   = new Zend_Validate_Sitemap_Priority();

        // create document
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = false;

        // ...and urlset (root) element
        $urlSet = $dom->createElementNS(self::SITEMAP_NS, 'urlset');
        $dom->appendChild($urlSet);

        // create iterator
        $iterator = new RecursiveIteratorIterator($container, RecursiveIteratorIterator::SELF_FIRST);

        $minDepth = 0;

        // iterate container
        foreach ($iterator as $page) {
            if ($iterator->getDepth() < $minDepth || !$this->accept($page)) {
                // page should not be included
                continue;
            }

            // get absolute url from page
            if (!$url = $this->url($page)) {
                // skip page if it has no url (rare case)
                continue;
            }

            // create url node for this page
            $urlNode = $dom->createElementNS(self::SITEMAP_NS, 'url');
            $urlSet->appendChild($urlNode);

            if (!$locValidator->isValid($url)) {
                throw new Exception(sprintf(
                        'Encountered an invalid URL for Sitemap XML: "%s"',
                        $url));
            }

            // put url in 'loc' element
            $urlNode->appendChild($dom->createElementNS(self::SITEMAP_NS,
                                                        'loc', $url));

            // add 'lastmod' element if a valid lastmod is set in page
            if (isset($page->lastmod)) {
                $lastmod = strtotime((string) $page->lastmod);

                // prevent 1970-01-01...
                if ($lastmod !== false) {
                    $lastmod = date('c', $lastmod);
                }

                if ($lastmodValidator->isValid($lastmod)) {
                    $urlNode->appendChild(
                        $dom->createElementNS(self::SITEMAP_NS, 'lastmod',
                                              $lastmod)
                    );
                }
            }

            // add 'changefreq' element if a valid changefreq is set in page
            if (isset($page->changefreq)) {
                $changefreq = $page->changefreq;
                if ($changefreqValidator->isValid($changefreq)) {
                    $urlNode->appendChild(
                        $dom->createElementNS(self::SITEMAP_NS, 'changefreq',
                                              $changefreq)
                    );
                }
            }

            // add 'priority' element if a valid priority is set in page
            if (isset($page->priority)) {
                $priority = $page->priority;
                if ($priorityValidator->isValid($priority)) {
                    $urlNode->appendChild(
                        $dom->createElementNS(self::SITEMAP_NS, 'priority',
                                              $priority)
                    );
                }
            }
        }

        $sitemap = $dom->saveXML();

        return rtrim($sitemap, PHP_EOL);
    }

    protected function url(Zend_Navigation_Page $page)
    {
        $href = $page->getHref();

        if (!isset($href{0})) {
            // no href
            $url = '';
        } elseif (preg_match('/^[a-z]+:/im', (string) $href)) {
            // scheme is given in href; assume absolute URL already
            $url = (string) $href;
        } else {
            // href is relative to root; use serverUrl helper
            $url = Xoops::url($href, true);
        }
        $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8', false);

        return $url;
    }

    protected function accept(Zend_Navigation_Page $page, $recursive = true)
    {
        // accept by default
        $accept = true;

        if (!$page->isVisible(false)) {
            // don't accept invisible pages
            $accept = false;
        }

        if ($accept && $recursive) {
            $parent = $page->getParent();
            if ($parent instanceof Zend_Navigation_Page) {
                $accept = $this->accept($parent, true);
            }
        }

        return $accept;
    }
}