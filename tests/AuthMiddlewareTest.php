<?php

namespace App\Tests\Middleware;

use App\Middleware\AuthMiddleware;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Response;

class AuthMiddlewareTest extends TestCase
{
    private $middleware;

    protected function setUp(): void
    {
        // Instantiate the middleware
        $this->middleware = new AuthMiddleware();
    }

    public function testValidAuth(): void
    {
        // Create a request with a valid token
        $request = ServerRequestFactory::createFromGlobals()
            ->withHeader('Authorization', 'Bearer valid-token');

        $response = new Response();

        // Simulate a request and apply the middleware
        $next = function ($request, $response) {
            return $response->withStatus(200);
        };

        $response = $this->middleware->__invoke($request, $response, $next);

        // Assert the response status is 200 (OK)
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testInvalidAuth(): void
    {
        // Create a request without an authorization token
        $request = ServerRequestFactory::createFromGlobals();
        $response = new Response();

        // Simulate a request and apply the middleware
        $next = function ($request, $response) {
            return $response->withStatus(200);
        };

        // Apply the middleware and assert the response code is 401 (Unauthorized)
        $response = $this->middleware->__invoke($request, $response, $next);
        $this->assertEquals(401, $response->getStatusCode());
    }
}
