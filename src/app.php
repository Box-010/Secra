<?php
require_once(dirname(__DIR__) . '/config/admin.php');
require_once(dirname(__DIR__) . '/config/database.php');
require_once(dirname(__DIR__) . '/config/website.php');
require_once(__DIR__ . '/autoload.php');

use Secra\Arch\DI\Container;
use Secra\Arch\Logger\FileLogger;
use Secra\Arch\Logger\ILogger;
use Secra\Arch\Logger\LogLevel;
use Secra\Arch\Router\Router;
use Secra\Arch\Template\TemplateEngine;
use Secra\Database;
use Secra\Repositories\SecretsRepository;
use Secra\Repositories\SessionRepository;
use Secra\Repositories\UserRepository;
use Secra\Services\SessionService;

ini_set('session.cookie_httponly', '1');
ini_set('date.timezone', 'Asia/Shanghai');

global $container;
$container = new Container();
$container->set(Database::class, function () {
  return new Database();
});
$container->set(ILogger::class, function () {
  return new FileLogger(dirname(__DIR__) . '/logs/app.log', LogLevel::INFO);
});
$container->registerAll(
  SessionRepository::class,
  UserRepository::class,
  SecretsRepository::class,
  SessionService::class,
  Router::class
);

$router = $container->get(Router::class);
$router->registerStaticRoute('/', dirname(__DIR__) . '/public');
$router->registerGlobalErrorHandler(function (Exception $e) use ($container) {
  $logger = $container->get(ILogger::class);
  $logger->error($e->getMessage());
  http_response_code(500);
  echo 'Internal Server Error';
});

$sessionService = $container->get(SessionService::class);

$container->set(TemplateEngine::class, function () use ($sessionService) {
  return new TemplateEngine(
    __DIR__,
    [
      "isLoggedIn" => function () use ($sessionService) {
        return $sessionService->isUserLoggedIn();
      },
      "currentUser" => function () use ($sessionService) {
        return $sessionService->getCurrentUser();
      },
    ]
  );
});

$routeStr = $_GET["route"] ?? "";

$router->route($routeStr, $_SERVER['REQUEST_METHOD']);
