<?php

namespace Violinist\DrupalContribSA;

use Symfony\Component\DomCrawler\Crawler;

class ContribSaParser
{
    private $crawler;

    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
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

    public function getVersion()
    {
        $link = $this->getVersionLink();
        $parts = explode('/', $link);
        $version_tag_parts = explode('-', $parts[count($parts) - 1]);
        return sprintf('%s.0', $version_tag_parts[1]);
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
            return $date->format('U');
        }
        throw new \Exception('No time found');
    }

    public function getBranch()
    {
        $link = $this->getVersionLink();
        return $this->getBranchFromLink($link);
    }

    protected function getVersionLink()
    {
        $solution_link = $this->crawler->filter('.field-name-field-sa-solution a');
        if ($solution_link->count() === 1) {
            $href = $solution_link->getNode(0)->getAttribute('href');
            return $href;
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
            $potential_project_links = $potential_project_links->reduce(function (Crawler $node) {
                $href = $node->getNode(0)->getAttribute('href');
                // Ignore Drupal 7 for now, id we have more than one link to work with.
                if (strpos($href, '7.x') !== false) {
                    return false;
                }
                return true;
            });
        }
        if ($potential_project_links->count() === 1) {
            $href = $potential_project_links->getNode(0)->getAttribute('href');
            return $href;
        }
        throw new \Exception('No applicable link found');
    }

    protected function getBranchFromLink($link)
    {
        $link_parts = explode('/', $link);
        $branch_tag = $link_parts[count($link_parts) - 1];
        $branch_tag_parts = explode('-', $branch_tag);
        if (empty($branch_tag_parts[1])) {
            throw new \Exception('Not possible to get branch from link ' . $link);
        }
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
