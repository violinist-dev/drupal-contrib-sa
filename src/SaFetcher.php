<?php

namespace Violinist\DrupalContribSA;

use Symfony\Component\DomCrawler\Crawler;

class SaFetcher extends HtmlDownloaderBase
{
    /**
     * @return SaData
     */
    public function fetchSa($url, $data = null)
    {
        if (!$data) {
            $res = $this->download($url);
            $crawler = $this->getCrawlerFromResponse($res);
            $parser = new ContribSaParser($crawler);
            $parser->setHttpClient($this->client);
            $parser->setCache($this->cache);
            $name = $parser->getProjectName();
            $branches = $parser->getBranches();
            $time = $parser->getTime();
            $versions = $parser->getVersions();
        } else {
            $json = json_decode($data['data']);
            if (empty($json->field_sa_solution->value)) {
                throw new \Exception('Not possible to find project name');
            }
            $crawler = new Crawler('<div class="field-name-field-sa-solution">' . $json->field_sa_solution->value . '</div>');
            $parser = new ContribSaParser($crawler);
            $parser->setCache($this->cache);
            $parser->setHttpClient($this->client);
            $parser->setlinksSelector('a');
            try {
                $name = $parser->getProjectName();
                // This is a redirect.
                if ($name === 'eu-cookie-compliance') {
                    $name = 'eu_cookie_compliance';
                }
                if ($url === 'https://www.drupal.org/sa-contrib-2019-056') {
                    $name = 'imagecache_actions';
                }
                if ($url === 'https://www.drupal.org/sa-contrib-2018-067') {
                    $name = 'workbench_moderation';
                }
                if ($url === 'https://www.drupal.org/sa-contrib-2019-024') {
                    $name = 'tmgmt';
                }
            } catch (\Throwable $e) {
                $res = $this->download($json->field_project->uri . '.json');
                $project_json = json_decode($res);
                if (!empty($project_json->field_project_machine_name)) {
                    $name = $project_json->field_project_machine_name;
                } else {
                    throw $e;
                }
            }
            $date = \DateTime::createFromFormat('U', $json->created);
            $date->setTimezone(new \DateTimeZone('+0000'));
            $date->setTime(12, 00, 00);
            $time = $date->format('U');
            $branches = $parser->getBranches($name);
            $versions = $parser->getVersions();
        }
        $data = new SaData();
        $data->setTime($time);
        $data->setName($name);
        $data->setBranches($branches);
        $data->setVersions($versions);
        return $data;
    }

    protected function getProjectNameFromLink($link)
    {
        $parts = explode('/', $link);
        return $parts[count($parts) - 1];
    }
}
