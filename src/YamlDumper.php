<?php

namespace Violinist\DrupalContribSA;

use Symfony\Component\Yaml\Yaml;

class YamlDumper
{
    public function dumpPackageData($package, $data, $filename)
    {
        $dir = __DIR__ . '/../sa_yaml/' . $package;
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents(sprintf('%s/%s', $dir, $filename), Yaml::dump($data));
    }
}
