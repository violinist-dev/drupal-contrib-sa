<?php

namespace Violinist\DrupalContribSA;

class HtmlDownloader extends HtmlDownloaderBase
{
    private $page = 0;

    private $baseUrl = 'https://www.drupal.org/api-d7/node.json';

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
                'type' => 'sa',
                'status' => 1,
                'sort' => 'nid',
                'direction' => 'DESC',
            ],
        ]);
        $data = json_decode($res);
        $data->list = array_filter($data->list, function ($item) {
            return $item->field_project->id != 3060;
        });
        $sa_links = array_map(function ($item) {
            $link = $item->url;
            $link_url_array = parse_url($link);
            $link_url = substr($link_url_array["path"], 1);
            return [
                'link' => $link,
                'title' => $item->title,
                'filename' => $link_url,
                'filename_temp' => md5($link),
                'data' => json_encode($item),
            ];
        }, $data->list);
        $this->page++;
        // Now see if we are on the last page or not.
        if (empty($data->next)) {
            $this->lastPage = true;
        }
        return $sa_links;
    }

    public function hasMore()
    {
        return !$this->lastPage;
    }

}
