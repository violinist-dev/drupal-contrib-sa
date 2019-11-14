<?php

namespace Violinist\DrupalContribSA;

use GuzzleHttp\Client;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\DomCrawler\Crawler;
use Violinist\DrupalContribSA\Exception\UnsupportedVersionException;

class ContribSaParser
{
    private $crawler;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var FilesystemCache
     */
    private $cache;

    private $versions = [];

    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    public function setCache(FilesystemCache $cache)
    {
        $this->cache = $cache;
    }

    public function setHttpClient(Client $client)
    {
        $this->httpClient = $client;
    }

    public function getProjectName()
    {
        // First try the entity reference field.
        $ref = $this->crawler->filter('.field-name-field-project a');
        if ($ref->count()) {
            $href = $ref->getNode(0)->getAttribute('href');
            return $this->getProjectNameFromLink($href);
        }
        $links_on_page = $this->getLinksOnPage();
        $indexed_links = [];
        $potential_project_links = $links_on_page->reduce(function (Crawler $node) use (&$indexed_links) {
            if (!$node->count()) {
                return false;
            }
            $href = $node->getNode(0)->getAttribute('href');
            if (strpos($href, '/releases/') !== false) {
                return false;
            }
            if (strpos($href, '/project/') === false) {
                return false;
            }
            if (in_array($href, $indexed_links)) {
                return false;
            }
            $indexed_links[] = $href;
            return true;
        });
        if ($potential_project_links->count() === 1) {
            $href = $potential_project_links->getNode(0)->getAttribute('href');
            return $this->getProjectNameFromLink($href);
        }
        throw new \Exception('No project name found');
    }

    public function getVersions()
    {
        // @todo: Not even sure I need the rest of this function now?
        $versions = [];
        if (!empty($this->versions)) {
            foreach ($this->versions as $version_array) {
                $versions[] = sprintf('%s.0', $version_array[1]);
            }
        }
        if (!empty($versions)) {
            return $versions;
        }
        $links = $this->getVersionLinks();
        foreach ($links as $link) {
            $parts = explode('/', $link);
            $version_tag_parts = explode('-', $parts[count($parts) - 1]);
            if (count($version_tag_parts) > 1) {
                $versions[] = sprintf('%s.0', $version_tag_parts[1]);
            }
        }
        return $versions;
    }

    public function getTime()
    {
        $time_el = $this->crawler->filter('time');
        if ($time_el->count() === 1) {
            return $time_el->getNode(0)->getAttribute('datetime');
        }
        // Another alternative is this field, that seems to be present some places.
        $sa_time_el =  $this->crawler->filter('.field-name-drupalorg-sa-date .field-item');
        if ($sa_time_el->count() === 1) {
            $crappy_date = $sa_time_el->text();
            $date = \DateTime::createFromFormat('Y-F-d', $crappy_date);
            $date->setTime(12, 00, 00);
            return $date->format('U');
        }
        throw new \Exception('No time found');
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getBranches()
    {
        $links = $this->getVersionLinks();
        if (empty($links)) {
            throw new \Exception('No version links found in page');
        }
        return $this->getBranchesFromLinks($links);
    }

    protected function getLinkHrefs(Crawler $nodes)
    {
        return $nodes->each(function (Crawler $node) {
            return $node->attr('href');
        });
    }

    protected function getVersionLinks()
    {
        $solution_links = $this->crawler->filter('.field-name-field-sa-solution a');
        if ($solution_links->count() > 0) {
            return $this->getLinkHrefs($solution_links);
        }
        // If the version is unsupported, then that is just a fact of life.
        $unsupported_variations = [
            'Module Unsupported',
            'Unsupported Module',
            'Unsupported',
        ];
        if (in_array($this->crawler->filter('.field-name-field-sa-type .field-item.even')->text(), $unsupported_variations)) {
            throw new UnsupportedVersionException('Unsupported version');
        }
        $links_on_page = $this->getLinksOnPage();
        $indexed_links = [];
        $potential_project_links = $links_on_page->reduce(function (Crawler $node) use (&$indexed_links) {
            if (!$node->count()) {
                return false;
            }
            $href = $node->getNode(0)->getAttribute('href');
            if (strpos($href, '/releases/') === false) {
                return false;
            }
            if (in_array($href, $indexed_links)) {
                return false;
            }
            $indexed_links[] = $href;
            return true;
        });
        if ($potential_project_links->count() > 1) {
            return $this->getLinkHrefs($potential_project_links);
        }
        throw new \Exception('No applicable link found');
    }

    protected function getBranchesFromLinks($links)
    {
        $branches = [];
        if (count($links) === 1 && strpos($links[0], 'node/251466')) {
            // That means the project is unsupported.
            return $branches;
        }
        foreach ($links as $link) {
            $link_parts = explode('/', $link);
            $branch_tag = $link_parts[count($link_parts) - 1];
            if (strpos($branch_tag, '6.x') === 0) {
                continue;
            }
            if (strpos($link, '/project/')) {
                if (!strpos($link, $this->getProjectName())) {
                    continue;
                }
            }
            $branch_tag_parts = explode('-', $branch_tag);
            if (empty($branch_tag_parts[1])) {
                continue;
            }
            if (!empty($branch_tag_parts[2])) {
                continue;
            }
            $this->versions[] = $branch_tag_parts;
            $branches[] = $this->getBranchNameFromDrupalVersion($branch_tag_parts);
        }
        if (count($branches) === 0) {
            // How about trying to go to said link, and then just getting it from there?
            foreach ($links as $link) {
                // Some times the link is relative, in different forms.
                if (strpos($link, '/node') === 0) {
                    $link = "https://drupal.org$link";
                }
                if (strpos($link, 'node') === 0) {
                    $link = "https://drupal.org/$link";
                }
                $html = $this->getData($link);
                $link_crawler = new Crawler($html);
                $heading = $link_crawler->filter('h1')->text();
                $heading_parts = explode(' ', $heading);
                $version = $heading_parts[count($heading_parts) - 1];
                $branch_tag_parts = explode('-', $version);
                if (strpos($version, '6.x') === 0) {
                    continue;
                }
                if (empty($branch_tag_parts[1])) {
                    continue;
                }
                $this->versions[] = $branch_tag_parts;
                $branches[] = $this->getBranchNameFromDrupalVersion($branch_tag_parts);
            }
        }
        if (count($branches) === 0) {
            throw new \Exception('No branches could be extracted from SA');
        }
        return $branches;
    }

    protected function getData($url)
    {
        $cid = md5(json_encode([$url]));
        if ($data = $this->cache->get($cid)) {
            return $data;
        }
        $response = $this->httpClient->get($url);
        $response_body = (string) $response->getBody();
        $this->cache->set($cid, $response_body);
        return $response_body;
    }

    protected function getBranchNameFromDrupalVersion($branch_tag_parts)
    {
        $tag_parts = explode('.', $branch_tag_parts[1]);
        return sprintf('%s-%d.x', $branch_tag_parts[0], $tag_parts[0]);
    }

    protected function getLinksOnPage()
    {
        return $this->crawler->filter('.node a');
    }

    protected function getProjectNameFromLink($link)
    {
        $parts = explode('/', $link);
        return $parts[count($parts) - 1];
    }
}
