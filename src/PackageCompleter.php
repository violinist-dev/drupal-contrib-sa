<?php

namespace Violinist\DrupalContribSA;

use Symfony\Component\Yaml\Yaml;

class PackageCompleter
{
    private $files;

    private $index = 0;

    private $directory;

    protected $fetcher;

    protected $dumper;

    public function __construct($directory, SaFetcher $fetcher, YamlDumper $dumper)
    {
        $this->directory = $directory;
        $this->fetcher = $fetcher;
        $this->dumper = $dumper;
        $this->files = glob($this->directory . '/*.yaml');
    }

    public function hasMore()
    {
        return $this->index < count($this->files);
    }

    public function getFile()
    {
        if (empty($this->files[$this->index])) {
            throw new \InvalidArgumentException('No more files to complete');
        }
        $file = $this->files[$this->index];
        $this->index++;
        return $file;
    }

    public function completeFile($file)
    {
        // Read the file, and find the link.
        $contents = @file_get_contents($file);
        if (empty($contents)) {
            throw new \Exception(sprintf('The file %s was empty', $file));
        }
        $data = Yaml::parse($contents);
        if (empty($data['link'])) {
            throw new \Exception(sprintf('The file %s had no link', $file));
        }
        $link = $data['link'];
        $details = $this->fetcher->fetchSa($link);
        $composer_name = sprintf('drupal/%s', $details->getName());
        $data['branches'] = [
            $details->getBranch() => [
                'time' => date('Y-m-d H:i:s', $details->getTime()),
                'versions' => [
                    sprintf('>=%s', $details->getLowestVulnerable()),
                    sprintf('<%s', $details->getVersion()),
                ],
            ]
        ];
        $repo = 'https://packages.drupal.org/8';
        if (strpos($details->getBranch(), '8.x-') === false) {
            $repo = 'https://packages.drupal.org/7';
        }
        $data['composer-repository'] = $repo;
        $data['reference'] = sprintf('composer://%s', $composer_name);
        return $data;
    }

    public function saveFile($file, $data, $remove_original = true)
    {
        if ($remove_original) {
            unlink($file);
        }
        $composer_name = str_replace('composer://', '', $data['reference']);
        $dir = __DIR__ . '/../sa_yaml/' . $composer_name;
        if (!file_exists($dir)) {
            mkdir($dir, 0700, true);
        }
        file_put_contents(str_replace('undefined/undefined', $composer_name, $file), Yaml::dump($data));
    }
}
