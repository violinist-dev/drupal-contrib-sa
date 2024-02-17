<?php

namespace Violinist\DrupalContribSA\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Violinist\DrupalContribSA\Exception\IgnoredProjectException;
use Violinist\DrupalContribSA\Exception\NoLinksException;
use Violinist\DrupalContribSA\Exception\UnsupportedVersionException;
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

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        while ($this->completer->hasMore()) {
            $filename = null;
            try {
                $filename = $this->completer->getFile();
                $output->writeln('Completing file ' . $filename);
                $data = $this->completer->completeFiles($filename);
                $this->completer->saveFiles($filename, $data);
            } catch (UnsupportedVersionException|IgnoredProjectException $e) {
                // No worries.
                unlink($filename);
                continue;
            } catch (NoLinksException $e) {
                // That happens.
            } catch (\Exception $e) {
                if ($filename) {
                    $output->writeln("Caught exception when processing file $filename: {$e->getMessage()}");
                    $output->writeln([
                        $e->getMessage(),
                        $e->getTraceAsString(),
                    ]);
                }
            }
        }
        return 0;
    }
}
