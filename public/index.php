<?php
// Entry point for Minecraft WorldEdit Clipboard Sync Relay Server

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use App\Config\ConfigLoader;
use App\Controllers\ClipboardController;
use App\Middleware\AuthMiddleware;
use App\Middleware\IPWhitelistMiddleware;
use Doctrine\DBAL\DriverManager;

$configFilePath = __DIR__ . '/../config/endpoints.json';
$config = ConfigLoader::load($configFilePath);

// Configure SQLite DB connection
$connectionParams = [
    'url' => 'sqlite:///' . __DIR__ . '/../storage/database.sqlite',
];
$connection = DriverManager::getConnection($connectionParams);

// Create Slim app
$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Inject shared resources into controller statically (for simplicity)
ClipboardController::init($connection, $configFilePath, $config);

// Route setup
$app->group('/api', function (RouteCollectorProxy $group) use ($config) {
    $group->group('/clipboard', function (RouteCollectorProxy $clipboard) use ($config) {
        $clipboard->post('/copy', ClipboardController::class . ':copy')
            ->add(new IPWhitelistMiddleware($config['permit']['copy']))
            ->add(new AuthMiddleware());

        $clipboard->post('/paste', ClipboardController::class . ':paste')
            ->add(new IPWhitelistMiddleware($config['permit']['paste']))
            ->add(new AuthMiddleware());

        $clipboard->get('/list', ClipboardController::class . ':list')
            ->add(new IPWhitelistMiddleware($config['permit']['paste']))
            ->add(new AuthMiddleware());

        $clipboard->get('/entry[/{id}]', ClipboardController::class . ':fetch')
            ->add(new IPWhitelistMiddleware($config['permit']['paste']))
            ->add(new AuthMiddleware());
    });

    $group->post('/reload', ClipboardController::class . ':reloadConfig')
        ->add(new IPWhitelistMiddleware($config['permit']['reload']));
});

// Run server
$app->run();
