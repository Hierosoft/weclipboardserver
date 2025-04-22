<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class IPWhitelistMiddleware implements MiddlewareInterface
{
    private array $allowedIps;

    public function __construct(array $allowedIps)
    {
        $this->allowedIps = $allowedIps;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $clientIp = $this->getClientIp($request);

        if (!$this->isAllowed($clientIp)) {
            $response = new Response();
            $response->getBody()->write(json_encode([
                'error' => 'Access denied from IP ' . $clientIp
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(403);
        }

        return $handler->handle($request);
    }

    private function getClientIp(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();
        return $serverParams['REMOTE_ADDR'] ?? 'unknown';
    }

    private function isAllowed(string $ip): bool
    {
        foreach ($this->allowedIps as $allowed) {
            // Allow localhost short-circuit
            if (in_array($ip, ['127.0.0.1', '::1']) && in_array('localhost', $this->allowedIps)) {
                return true;
            }

            // Exact match
            if ($allowed === $ip) {
                return true;
            }

            // CIDR support
            if ($this->ipInCidr($ip, $allowed)) {
                return true;
            }
        }

        return false;
    }

    private function ipInCidr(string $ip, string $cidr): bool
    {
        if (strpos($cidr, '/') === false) {
            return false;
        }

        list($subnet, $maskLength) = explode('/', $cidr);
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $mask = -1 << (32 - (int)$maskLength);
        return ($ipLong & $mask) === ($subnetLong & $mask);
    }
}
?>
