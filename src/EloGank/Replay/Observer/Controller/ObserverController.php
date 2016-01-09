<?php

/*
 * This file is part of the "EloGank League of Legends Replay Observer Silex" package.
 *
 * https://github.com/EloGank/lol-replay-observer-silex
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EloGank\Replay\Observer\Controller;

use EloGank\Replay\Observer\ReplayObserver;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class ObserverController
{
    /**
     * @param ControllerCollection $routing
     */
    public static function createRouting(ControllerCollection $routing)
    {
        $routing->get('/observer-mode/rest/consumer/version', [
            new static, 'getVersionAction'
        ]);

        $routing->get('/observer-mode/rest/consumer/getGameMetaData/{region}/{gameId}/{token}/token', [
            new static, 'getGameMetaDataAction'
        ]);

        $routing->get('/observer-mode/rest/consumer/getLastChunkInfo/{region}/{gameId}/{chunkId}/token', [
            new static, 'getLastChunkInfoAction'
        ]);

        $routing->get('/observer-mode/rest/consumer/getGameDataChunk/{region}/{gameId}/{chunkId}/token', [
            new static, 'getGameDataChunkAction'
        ]);

        $routing->get('/observer-mode/rest/consumer/getKeyFrame/{region}/{gameId}/{keyframeId}/token', [
            new static, 'getKeyFrameAction'
        ]);

        $routing->get('/observer-mode/rest/consumer/endOfGameStats/{region}/{gameId}/null', [
            new static, 'getEndOfGameStatsAction'
        ]);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     *
     * @throws \EloGank\Replay\Downloader\Client\Exception\TimeoutException
     * @throws \EloGank\Replay\Observer\Exception\UnauthorizedAccessException
     */
    public function getVersionAction(Application $app, Request $request)
    {
        return new Response($this->getReplayObserver($app)->getVersion($request->headers->get('Accept')));
    }

    /**
     * @param Application $app
     * @param Request     $request
     * @param string      $region
     * @param string      $gameId
     * @param string      $token
     *
     * @return JsonResponse
     */
    public function getGameMetaDataAction(Application $app, Request $request, $region, $gameId, $token)
    {
        return new JsonResponse(
            $this->getReplayObserver($app)->getGameMetasData($region, $gameId, $token, $request->getClientIp())
        );
    }

    /**
     * @param Application $app
     * @param Request     $request
     * @param string      $region
     * @param string      $gameId
     * @param int         $chunkId
     *
     * @return JsonResponse
     */
    public function getLastChunkInfoAction(Application $app, Request $request, $region, $gameId, $chunkId)
    {
        return new JsonResponse(
            $this->getReplayObserver($app)->getLastChunkInfo($region, $gameId, $chunkId, $request->getClientIp())
        );
    }

    /**
     * @param Application $app
     * @param string      $region
     * @param string      $gameId
     * @param int         $chunkId
     *
     * @return Response
     *
     * @throws \EloGank\Replay\Observer\Client\Exception\ReplayChunkNotFoundException
     * @throws \Exception
     */
    public function getGameDataChunkAction(Application $app, $region, $gameId, $chunkId)
    {
        return $this->createDownloadResponse(
            $this->getReplayObserver($app)->getGameDataChunkPath($region, $gameId, $chunkId)
        );
    }

    /**
     * @param Application $app
     * @param string      $region
     * @param string      $gameId
     * @param int         $keyframeId
     *
     * @return Response
     *
     * @throws \EloGank\Replay\Observer\Client\Exception\ReplayKeyframeNotFoundException
     * @throws \Exception
     */
    public function getKeyFrameAction(Application $app, $region, $gameId, $keyframeId)
    {
        return $this->createDownloadResponse(
            $this->getReplayObserver($app)->getKeyframePath($region, $gameId, $keyframeId)
        );
    }

    /**
     * @param Application $app
     * @param string      $region
     * @param int         $gameId
     *
     * @return Response
     * @throws \EloGank\Replay\Observer\Client\Exception\ReplayEndStatsNotFoundException
     * @throws \Exception
     */
    public function getEndOfGameStatsAction(Application $app, $region, $gameId)
    {
        return $this->createDownloadResponse(
            $this->getReplayObserver($app)->getEndOfGameStatsPath($region, $gameId), 'null', true
        );
    }

    /**
     * @param string $filePath
     * @param string $fileName
     * @param bool   $sendHeaders
     *
     * @return Response
     */
    protected function createDownloadResponse($filePath, $fileName = 'token', $sendHeaders = false)
    {
        $expires = 2592000; // 1 month
        $response = new Response();
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'maxage=' . $expires);
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
        $response->headers->set('Content-length', filesize($filePath));

        if ($sendHeaders) {
            $response->sendHeaders();
        }

        // Read the file content
        readfile($filePath);

        return $response;
    }

    /**
     * @param Application $app
     *
     * @return ReplayObserver
     */
    protected function getReplayObserver(Application $app)
    {
        return $app['lol.replay.observer'];
    }
}
