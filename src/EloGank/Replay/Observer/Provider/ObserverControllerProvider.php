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

use EloGank\Replay\Observer\Controller\ObserverController;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class ObserverControllerProvider implements ControllerProviderInterface
{
    /**
     * @inheritdoc
     */
    public function connect(Application $app)
    {
        /** @var ControllerCollection $routing */
        $routing = $app['controllers_factory'];

        ObserverController::createRouting($routing);

        return $routing;
    }
}
