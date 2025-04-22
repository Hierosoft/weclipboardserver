<?php

namespace App\Tests\Middleware;

use App\Middleware\IPWhitelistMiddleware;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Response;

class IPWhitelistMiddlewareTest extends TestCase
{
    private $middleware;

    protected function setUp(): void
    {
        // Instantiate the middleware with a mocked configuration
        $config = ['allowed_ips' => ['192.168.1.1']];
        $this->middleware = new IPWhitelistMiddleware($config);
    }

    public function testAllowedIP(): void
    {
        // Create a request with an allowed IP
        $request = ServerRequestFactory::createFromGlobals()
            ->withHeader('X-Forwarded-For', '192.168.1.1');
        $response = new Response();

        // Simulate a request and apply the middleware
        $next = function ($request, $response) {
            return $response->withStatus(200);
        };

        $response = $this->middleware->__invoke($request, $response, $next);

        // Assert the response status is 200 (OK)
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testBlockedIP(): void
    {
        // Create a request with a blocked IP
        $request = ServerRequestFactory::createFromGlobals()
            ->withHeader('X-Forwarded-For', '192.168.1.2');
        $response = new Response();

        // Simulate a request and apply the middleware
        $next = function ($request, $response) {
            return $response->withStatus(200);
        };

        $response = $this->middleware->__invoke($request, $response, $next);

        // Assert the response status is 403 (Forbidden)
        $this->assertEquals(403, $response->getStatusCode());
    }
}
