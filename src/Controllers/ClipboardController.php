<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Doctrine\DBAL\Connection;
use App\Config\ConfigLoader;

class ClipboardController
{
    private Connection $db;
    private string $configPath;
    private array $config;

    public function __construct(Connection $db, string $configPath)
    {
        $this->db = $db;
        $this->configPath = $configPath;
        $this->config = ConfigLoader::load($configPath);
    }

    public function copy(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();
        $uuid = $request->getAttribute('uuid');
        $data = $body['data'] ?? null;
        $description = $body['description'] ?? null;

        if (!$uuid || !$data) {
            return $this->json($response, ['error' => 'Missing UUID or data'], 400);
        }

        $this->db->insert('clipboard_history', [
            'uuid' => $uuid,
            'data' => $data,
            'description' => $description,
            'date' => (new \DateTime())->format(\DateTime::ATOM),
        ]);

        return $this->json($response, ['status' => 'saved'], 200);
    }

    public function paste(Request $request, Response $response): Response
    {
        return $this->fetch($request, $response, []);
    }

    public function list(Request $request, Response $response): Response
    {
        $uuid = $request->getAttribute('uuid');

        try {
            $rows = $this->db->fetchAllAssociative(
                'SELECT id, date, description FROM clipboard_history WHERE uuid = ? ORDER BY id DESC',
                [$uuid]
            );
            return $this->json($response, ['entries' => $rows]);
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => $e->getMessage()], 500);
        }
    }

    public function fetch(Request $request, Response $response, array $args): Response
    {
        $uuid = $request->getAttribute('uuid');
        $id = $args['id'] ?? null;

        try {
            if ($id !== null) {
                $row = $this->db->fetchAssociative(
                    'SELECT * FROM clipboard_history WHERE uuid = ? AND id = ?',
                    [$uuid, $id]
                );
            } else {
                $row = $this->db->fetchAssociative(
                    'SELECT * FROM clipboard_history WHERE uuid = ? ORDER BY id DESC LIMIT 1',
                    [$uuid]
                );
            }

            if (!$row) {
                return $this->json($response, ['error' => 'Not found'], 404);
            }

            return $this->json($response, $row);
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => $e->getMessage()], 500);
        }
    }

    public function reloadConfig(Request $request, Response $response): Response
    {
        try {
            $this->config = ConfigLoader::load($this->configPath);
            return $this->json($response, ['status' => 'reloaded']);
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => $e->getMessage()], 500);
        }
    }

    private function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
