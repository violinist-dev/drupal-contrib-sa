<?php

namespace Violinist\DrupalContribSA;

use Symfony\Component\DomCrawler\Crawler;

class HtmlDownloader extends HtmlDownloaderBase
{
    private $page = 0;

    private $baseUrl = 'https://www.drupal.org/security/contrib';

    private $lastPage = false;

    public function getPage()
    {
        return $this->page;
    }

    public function load()
    {
        $res = $this->download($this->baseUrl, [
            'query' => [
                'page' => $this->getPage(),
            ],
        ]);
        $crawler = $this->getCrawlerFromResponse($res);
        $rows = $crawler->filter('.view-drupalorg-security-announcements-contrib .views-row');
        $sa_links = $rows->each(function (Crawler $node) {
            $link = $node->filter('h2 a');
            if ($link->count() != 1) {
                return;
            }
            $link_url = $link->extract(['href'])[0];
            if (strpos($link_url, 'forum/newsletter') !== false) {
                // @todo.
                return;
            }
            return [
                'link' => sprintf('https://www.drupal.org%s', $link_url),
                'title' => $link->text(),
                'filename' => $link_url,
            ];
        });
        $sa_links = array_filter($sa_links, function ($item) {
            return !empty($item);
        });
        $this->page++;
        // Now see if we are on the last page or not.
        if (!$crawler->filter('.pager-next')->count()) {
            $this->lastPage = true;
        }
        return $sa_links;
    }

    public function hasMore()
    {
        return !$this->lastPage;

    }

}
