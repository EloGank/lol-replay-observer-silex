<?php

/*
 * This file is part of the "EloGank League of Legends Replay Observer Silex" package.
 *
 * https://github.com/EloGank/lol-replay-observer-silex
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require '../vendor/autoload.php';

$app = new \Silex\Application([
    'debug' => true
]);

$app->register(new \Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__ . '/../logs/error.log',
    'monolog.name'    => 'Elogank Replay Observer',
    'monolog.level'   => $app['debug'] ? \Monolog\Logger::INFO : \Monolog\Logger::ERROR
));

$app->register(new \EloGank\Replay\Observer\Provider\ObserverServiceProvider([
    'replay.dir_path' => __DIR__ . '/../../lol-replay-downloader-cli/replays',
    'cache' => new \EloGank\Replay\Observer\Cache\Adapter\RedisCacheAdapter(new \Predis\Client([
        'host' => '127.0.0.1',
        'port' => 6379
    ]))
], $app['logger']));

$app->mount('/', new \EloGank\Replay\Observer\Provider\ObserverControllerProvider());

$app->run();
