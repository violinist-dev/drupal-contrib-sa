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
        if ($package == 'undefined/undefined') {
            $data['filename'] = $filename;
        }
        file_put_contents(sprintf('%s/%s', $dir, $filename), Yaml::dump($data));
    }
}
