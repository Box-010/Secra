<?php
require_once(__DIR__ . '/arch/DI.php');
require_once(__DIR__ . '/arch/router/Router.php');
require_once(__DIR__ . '/utils/Logger.php');

require_once('Database.php');
require_once('models/repositories/UserRepository.php');
require_once('models/repositories/SessionRepository.php');
require_once('services/SessionService.php');

ini_set('session.cookie_httponly', '1');

global $container;
$container = new Container();
$container->set(Database::class, function () {
  return new Database();
});
$container->set(Logger::class, function () {
  return new FileLogger(LogLevel::INFO);
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
  $logger = $container->get(Logger::class);
  $logger->error($e->getMessage());
  http_response_code(500);
  echo 'Internal error';
});

$sessionService = $container->get(SessionService::class);

$router->route($_GET["route"], $_SERVER['REQUEST_METHOD']);
