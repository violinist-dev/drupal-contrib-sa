<?php

namespace Violinist\DrupalContribSA;

use GuzzleHttp\Client;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DomCrawler\Crawler;

abstract class HtmlDownloaderBase
{
    /**
     * @var Client
     */
    protected $client;

    protected FilesystemAdapter $cache;

    public function __construct(Client $client, FilesystemAdapter $cache)
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
        /** @var \Psr\Cache\CacheItemInterface $data */
        $data = $this->cache->getItem($cid);
        if ($data->isHit()) {
            return $data->get();
        }
        $params['headers'] = [
            'headers' => [
                'User-Agent' => 'drupal-contrib-sa package (https://github.com/violinist-dev/drupal-contrib-sa)',
            ],
        ];
        $response = $this->client->get($url, $params);
        $response_body = (string) $response->getBody();
        $data->set($response_body);
        $this->cache->save($data);
        return $response_body;
    }
}
