<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(Request $request, Handler $handler): Response
    {
        $headers = $request->getHeaders();

        if (!isset($headers['X-Minecraft-UUID'][0]) || !isset($headers['X-Minecraft-Hash'][0])) {
            return $this->unauthorizedResponse('Missing authentication headers');
        }

        $uuid = $headers['X-Minecraft-UUID'][0];
        $hash = $headers['X-Minecraft-Hash'][0];

        if (!$this->isSessionValid($uuid, $hash)) {
            return $this->unauthorizedResponse('Invalid session');
        }

        // Attach UUID to request attributes for downstream use
        $request = $request->withAttribute('uuid', $uuid);

        return $handler->handle($request);
    }

    private function isSessionValid(string $uuid, string $hash): bool
    {
        $url = "https://sessionserver.mojang.com/session/minecraft/hasJoined?username={$uuid}&serverId={$hash}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseBody = curl_exec($ch);
        curl_close($ch);

        if ($responseBody === false) {
            return false;
        }

        $response = json_decode($responseBody, true);

        return isset($response['id']) && strtolower($response['id']) === strtolower($uuid);
    }

    private function unauthorizedResponse(string $message): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode(['error' => $message]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
}
