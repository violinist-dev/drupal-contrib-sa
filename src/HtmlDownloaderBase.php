<?php

namespace Violinist\DrupalContribSA;

use GuzzleHttp\Client;
use Psr\Cache\CacheItemInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\DomCrawler\Crawler;

abstract class HtmlDownloaderBase
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct(Client $client, FilesystemCache $cache)
    {
        $this->client = $client;
        $this->cache = $cache;
    }

    public function getCrawlerFromResponse($response)
    {
        $html = $response;
        return new Crawler($html);
    }

    public function download($url, $params = [])
    {
        $cid = md5(json_encode([$url, $params]));
        if ($data = $this->cache->get($cid)) {
            return $data;
        }
        $response = $this->client->get($url, $params);
        $response_body = (string) $response->getBody();
        $this->cache->set($cid, $response_body);
        return $response_body;
    }
}
