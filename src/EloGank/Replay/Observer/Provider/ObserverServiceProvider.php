<?php

/*
 * This file is part of the "EloGank League of Legends Replay Observer Silex" package.
 *
 * https://github.com/EloGank/lol-replay-observer-silex
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EloGank\Replay\Observer\Provider;

use EloGank\Replay\Downloader\Client\ReplayClient;
use EloGank\Replay\Observer\Cache\Adapter\CacheAdapterInterface;
use EloGank\Replay\Observer\Client\ReplayObserverClient;
use EloGank\Replay\Observer\ReplayObserver;
use Psr\Log\LoggerInterface;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class ObserverServiceProvider implements ServiceProviderInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var LoggerInterface
     */
    protected $logger;


    /**
     * @param array           $config
     * @param LoggerInterface $logger
     */
    public function __construct(array $config = [], LoggerInterface $logger = null)
    {
        $this->config = array_merge_recursive($this->getDefaultConfigs(), $config);
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function register(Application $app)
    {
        $this->validateConfiguration();

        $app['lol.replay.observer.client'] = $app->share(function () {
            return new ReplayObserverClient($this->config['replay.dir_path']);
        });

        $app['lol.replay.api.client'] = $app->share(function () {
            return new ReplayClient($this->config['api.client']);
        });

        $app['lol.replay.observer'] = $app->share(function (Application $app) {
            $observer = new ReplayObserver(
                $app['lol.replay.observer.client'],
                $this->config['cache'],
                $app['lol.replay.api.client'],
                $this->config['auth.strict']
            );

            if (null != $this->logger) {
                $observer->setLogger($this->logger);
            }

            return $observer;
        });
    }

    /**
     * @inheritdoc
     */
    public function boot(Application $app)
    {

    }

    /**
     * @return array
     */
    protected function getDefaultConfigs()
    {
        return [
            'auth.strict' => false,
            'api.client'  => ReplayClient::getDefaultConfigs()
        ];
    }

    /**
     *
     */
    protected function validateConfiguration()
    {
        if (!isset($this->config['replay.dir_path'])) {
            throw new \RuntimeException('Missing configuration "replay.dir_path" in ObserverServiceProvider');
        }

        if (!isset($this->config['cache']) || !$this->config['cache'] instanceof CacheAdapterInterface) {
            throw new \RuntimeException('The cache configuration class must implement the CacheAdapterInterface');
        }
    }
}
