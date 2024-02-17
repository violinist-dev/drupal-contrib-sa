<?php

namespace Violinist\DrupalContribSA\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Violinist\DrupalContribSA\HtmlDownloader;
use Violinist\DrupalContribSA\YamlDumper;

class DownloadCommand extends Command
{
    protected static $defaultName = 'drupal-contrib-sa:download';

    protected $downloader;

    protected $dumper;

    public function __construct(HtmlDownloader $downloader, YamlDumper $dumper)
    {
        $this->downloader = $downloader;
        $this->dumper = $dumper;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $output->writeln('Starting download...');
        while ($this->downloader->hasMore()) {
            $output->writeln('Requesting page ' . $this->downloader->getPage());
            $data = $this->downloader->load();
            foreach ($data as $link) {
                $package = 'undefined/undefined';
                $filename = sprintf('%s.yaml', str_replace('/', '', $link['filename']));
                unset($link['filename']);
                $this->dumper->dumpPackageData($package, $link, $filename);
            }
        }
        return 0;
    }
}
