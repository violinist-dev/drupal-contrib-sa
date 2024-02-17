<?php

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use GuzzleHttp\Client;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Violinist\DrupalContribSA\Command\CompleterCommand;
use Violinist\DrupalContribSA\Command\DownloadCommand;
use Violinist\DrupalContribSA\HtmlDownloader;

require_once __DIR__ . '/vendor/autoload.php';

$application = new \Symfony\Component\Console\Application();

$container_builder = new ContainerBuilder();
$container_builder->register('http_client', Client::class);
$container_builder->register('html_downloader', HtmlDownloader::class)
    ->addArgument(new Reference('http_client'))
    ->addArgument(new Reference('cache'));
$container_builder->register('cache', FilesystemAdapter::class);
$container_builder->register('dumper', \Violinist\DrupalContribSA\YamlDumper::class);
$container_builder->register('completer', \Violinist\DrupalContribSA\PackageCompleter::class)
    ->addArgument('%sa_yaml_dir%')
    ->addArgument(new Reference('fetcher'))
    ->addArgument(new Reference('dumper'));
$container_builder->register('fetcher', \Violinist\DrupalContribSA\SaFetcher::class)
    ->addArgument(new Reference('http_client'))
    ->addArgument(new Reference('cache'));
$container_builder->setParameter('sa_yaml_dir', __DIR__ . '/sa_yaml/undefined/undefined');
$application->add(new DownloadCommand($container_builder->get('html_downloader'), $container_builder->get('dumper')));
$application->add(new CompleterCommand($container_builder->get('completer')));
$application->run();
