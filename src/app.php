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
use Secra\Repositories\AttitudesRepository;
use Secra\Repositories\CommentsRepository;
use Secra\Repositories\ImageRepository;
use Secra\Repositories\SecretsRepository;
use Secra\Repositories\SessionRepository;
use Secra\Repositories\UserRepository;
use Secra\Services\CaptchaService;
use Secra\Services\ImageService;
use Secra\Services\PermissionService;
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
  AttitudesRepository::class,
  CommentsRepository::class,
  ImageRepository::class,
  SessionService::class,
  PermissionService::class,
  CaptchaService::class,
  ImageService::class,
  Router::class
);

$sessionService = $container->get(SessionService::class);
$permissionService = $container->get(PermissionService::class);

$nonce = bin2hex(openssl_random_pseudo_bytes(32));

$container->set(TemplateEngine::class, function () use ($sessionService, $permissionService, $nonce) {
  return new TemplateEngine(
    __DIR__,
    [
      "isLoggedIn" => function () use ($sessionService) {
        return $sessionService->isUserLoggedIn();
      },
      "currentUser" => function () use ($sessionService) {
        return $sessionService->getCurrentUser();
      },
      "isAdmin" => function () use ($permissionService) {
        return $permissionService->hasRole('admin');
      },
      "nonce" => function () use ($nonce) {
        return $nonce;
      }
    ]
  );
});

$router = $container->get(Router::class);
$router->registerStaticRoute('', dirname(__DIR__) . '/public');
$router->registerGlobalErrorHandler(function (Exception $e) use ($container) {
  $logger = $container->get(ILogger::class);
  $logger->error($e->getMessage());
  http_response_code(500);
  echo 'Internal Server Error';
});

$routeStr = $_GET["route"] ?? "";
$routeStr = '/' . trim($routeStr, '/');
$publicRoot = rtrim(PUBLIC_ROOT, '/');
if (str_starts_with($routeStr, $publicRoot)) {
  $routeStr = substr($routeStr, strlen($publicRoot));
}
$routeStr = trim($routeStr, '/');

$logger = $container->get(ILogger::class);
$logger->info("Route: $routeStr");

$router->route($routeStr, $_SERVER['REQUEST_METHOD']);
