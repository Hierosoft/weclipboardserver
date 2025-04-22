<?php

namespace App\Tests\Controllers;

use App\Controllers\ClipboardController;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Response;

class ClipboardControllerTest extends TestCase
{
    private ClipboardController $controller;
    private $mockDb;

    protected function setUp(): void
    {
        // Create a mock for the Doctrine DBAL connection
        $this->mockDb = $this->createMock(Connection::class);

        // Instantiate the controller with the mocked DB connection
        $this->controller = new ClipboardController($this->mockDb);
    }

    public function testCopySuccess(): void
    {
        // Simulate valid clipboard data
        $data = [
            'uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'timestamp' => time(),
            'description' => 'Test clipboard',
            'boundingBox' => [
                'minX' => 0, 'minY' => 0, 'minZ' => 0,
                'maxX' => 10, 'maxY' => 10, 'maxZ' => 10
            ],
            'payload' => 'clipboard data here'
        ];

        $request = ServerRequestFactory::createFromGlobals();
        $response = new Response();

        // Set up the mock DB to simulate an insert
        $this->mockDb->expects($this->once())
            ->method('insert')
            ->with('clipboard', $this->arrayHasKey('uuid'))
            ->willReturn(true);

        // Call the copy method
        $response = $this->controller->copy($request, $response);

        // Assert the response code and structure
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['uuid' => '123e4567-e89b-12d3-a456-426614174000']),
            (string) $response->getBody()
        );
    }

    public function testCopyMissingFields(): void
    {
        $data = []; // Missing required fields
        $request = ServerRequestFactory::createFromGlobals();
        $response = new Response();

        // Call the copy method with missing data
        $response = $this->controller->copy($request, $response);

        // Assert error response
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => 'Missing required fields']),
            (string) $response->getBody()
        );
    }

    public function testPasteSuccess(): void
    {
        // Valid UUID
        $data = ['uuid' => '123e4567-e89b-12d3-a456-426614174000'];
        $request = ServerRequestFactory::createFromGlobals();
        $response = new Response();

        // Mock the DB to return a clipboard entry
        $this->mockDb->expects($this->once())
            ->method('fetchAssociative')
            ->with('SELECT * FROM clipboard WHERE uuid = ?', ['123e4567-e89b-12d3-a456-426614174000'])
            ->willReturn([
                'uuid' => '123e4567-e89b-12d3-a456-426614174000',
                'description' => 'Test clipboard',
                'boundingBox' => json_encode(['minX' => 0, 'minY' => 0, 'minZ' => 0, 'maxX' => 10, 'maxY' => 10, 'maxZ' => 10]),
                'payload' => 'clipboard data here'
            ]);

        // Call the paste method
        $response = $this->controller->paste($request, $response);

        // Assert the response
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'uuid' => '123e4567-e89b-12d3-a456-426614174000',
                'description' => 'Test clipboard',
                'boundingBox' => ['minX' => 0, 'minY' => 0, 'minZ' => 0, 'maxX' => 10, 'maxY' => 10, 'maxZ' => 10]
            ]),
            (string) $response->getBody()
        );
    }

    public function testPasteNotFound(): void
    {
        $data = ['uuid' => 'non-existent-uuid'];
        $request = ServerRequestFactory::createFromGlobals();
        $response = new Response();

        // Mock the DB to return null (clipboard not found)
        $this->mockDb->expects($this->once())
            ->method('fetchAssociative')
            ->with('SELECT * FROM clipboard WHERE uuid = ?', ['non-existent-uuid'])
            ->willReturn(null);

        // Call the paste method
        $response = $this->controller->paste($request, $response);

        // Assert error response
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => 'Clipboard not found']),
            (string) $response->getBody()
        );
    }

    public function testListSuccess(): void
    {
        $request = ServerRequestFactory::createFromGlobals();
        $response = new Response();

        // Mock DB to return clipboard entries
        $this->mockDb->expects($this->once())
            ->method('fetchAllAssociative')
            ->with('SELECT uuid, description, timestamp FROM clipboard ORDER BY timestamp DESC')
            ->willReturn([
                ['uuid' => '123e4567-e89b-12d3-a456-426614174000', 'description' => 'Test clipboard', 'timestamp' => time()],
                ['uuid' => '987e6543-e89b-12d3-a456-426614174000', 'description' => 'Another clipboard', 'timestamp' => time()]
            ]);

        // Call the list method
        $response = $this->controller->list($request, $response);

        // Assert the response
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'clipboards' => [
                    ['uuid' => '123e4567-e89b-12d3-a456-426614174000', 'description' => 'Test clipboard', 'timestamp' => time()],
                    ['uuid' => '987e6543-e89b-12d3-a456-426614174000', 'description' => 'Another clipboard', 'timestamp' => time()]
                ]
            ]),
            (string) $response->getBody()
        );
    }
}
