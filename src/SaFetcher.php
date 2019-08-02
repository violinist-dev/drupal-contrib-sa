<?php

namespace Violinist\DrupalContribSA;

class SaFetcher extends HtmlDownloaderBase
{
    /**
     * @return SaData
     */
    public function fetchSa($url)
    {
        $res = $this->download($url);
        $crawler = $this->getCrawlerFromResponse($res);
        $parser = new ContribSaParser($crawler);
        $parser->setHttpClient($this->client);
        $parser->setCache($this->cache);
        $name = $parser->getProjectName();
        $branches = $parser->getBranches();
        $time = $parser->getTime();
        $versions = $parser->getVersions();
        $data = new SaData();
        $data->setTime($time);
        $data->setName($name);
        $data->setBranches($branches);
        $data->setVersions($versions);
        return $data;
    }
}
