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
        $name = $parser->getProjectName();
        $branch = $parser->getBranch();
        $time = $parser->getTime();
        $version = $parser->getVersion();
        $data = new SaData();
        $data->setTime($time);
        $data->setName($name);
        $data->setBranch($branch);
        $data->setVersion($version);
        return $data;
    }
}
