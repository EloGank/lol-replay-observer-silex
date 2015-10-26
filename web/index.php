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

$app->register(new \EloGank\Replay\Observer\Provider\ObserverServiceProvider([
    'replay.dir_path' => __DIR__ . '/../../lol-replay-downloader-cli/replays'
]));
$app->mount('/', new \EloGank\Replay\Observer\Provider\ObserverControllerProvider());

$app->run();
