<?php
require_once(dirname(__DIR__) . '/config/admin.php');
require_once(dirname(__DIR__) . '/config/database.php');
require_once(__DIR__ . '/autoload.php');

use Secra\Database;
use Secra\Arch\DI\Container;
use Secra\Arch\Logger\ILogger;
use Secra\Arch\Logger\FileLogger;
use Secra\Arch\Logger\LogLevel;
use Secra\Arch\Router\Router;
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
  SessionService::class,
  Router::class
);

$router = $container->get(Router::class);
$router->registerStaticRoute('/', dirname(__DIR__) . '/public');
$router->registerGlobalErrorHandler(function (Exception $e) use ($container) {
  $logger = $container->get(ILogger::class);
  $logger->error($e->getMessage());
  http_response_code(500);
  echo $e->getMessage();
  echo '<br>';
  echo $e->getTraceAsString();
});

$sessionService = $container->get(SessionService::class);

$route = substr($_SERVER['REQUEST_URI'], 0, 1) === '/' ? $path = substr($_SERVER['REQUEST_URI'], 1) : $path = $_SERVER['REQUEST_URI'];

$router->route($route, $_SERVER['REQUEST_METHOD']);
