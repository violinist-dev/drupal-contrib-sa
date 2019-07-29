<?php

namespace Violinist\DrupalContribSA\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Violinist\DrupalContribSA\PackageCompleter;

class CompleterCommand extends Command
{
    protected static $defaultName = 'drupal-contrib-sa:complete';

    protected $completer;

    public function __construct(PackageCompleter $completer)
    {
        $this->completer = $completer;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        while ($this->completer->hasMore()) {
            $filename = null;
            try {
                $filename = $this->completer->getFile();
                $output->writeln('Completing file ' . $filename);
                $data = $this->completer->completeFiles($filename);
                $this->completer->saveFiles($filename, $data);
            } catch (\Exception $e) {
                if ($filename) {
                    $output->writeln('Caught exception when processing file ' . $filename);
                    $output->writeln([
                        $e->getMessage(),
                        $e->getTraceAsString(),
                    ]);
                }
            }
        }
    }
}
