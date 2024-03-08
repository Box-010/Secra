<?php

namespace Secra\Arch\Router;

require_once(dirname(dirname(__DIR__)) . '/Utils/Reflection.php');

use Closure;
use Exception;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use Secra\Arch\DI\Attributes\Inject;
use Secra\Arch\DI\Attributes\Singleton;
use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Container;
use Secra\Arch\Logger\ILogger;
use Secra\Arch\Router\Route;
use Secra\Arch\Router\Attributes\Controller;
use Secra\Arch\Router\Attributes\ErrorHandler;
use Secra\Arch\Router\Attributes\Get;
use Secra\Arch\Router\Attributes\Header;
use Secra\Arch\Router\Attributes\IP;
use Secra\Arch\Router\Attributes\Post;
use Secra\Arch\Router\Attributes\Param;
use Secra\Arch\Router\Attributes\Cookie;
use Secra\Arch\Router\Attributes\HttpMethod;
use Secra\Arch\Router\Models\PathDynamicParam;
use Secra\Arch\Router\Models\PathPatternItem;
use Secra\Arch\Router\Models\MatchResult;


#[Provide(Router::class)]
#[Singleton]
class Router
{
  private array $controllers = [];
  private array $basePathMap = [];

  // 路由表，ControllerName => [Route]
  private array $getRoutes = [];
  private array $postRoutes = [];

  // 静态路由表，basePath => filePath
  // 静态路由用于指向静态资源，例如图片、CSS、JS 等
  private array $staticRoutes = [];

  // 错误处理器，ControllerName => [errorClass => method]
  private array $errorHandlers = [];
  // 全局错误处理器（闭包）
  private Closure|null $globalErrorHandler = null;

  public function __construct(
    protected Container $container,
    #[Inject] private ILogger $logger
  ) {
    $this->controllers = $this->getControllers();
    foreach ($this->controllers as $controller) {
      $this->registerController($controller);
    }
  }

  public function registerStaticRoute($basePath, $filePath)
  {
    $this->staticRoutes[$basePath] = $filePath;
  }

  public function registerGlobalErrorHandler(Closure $handler)
  {
    $this->globalErrorHandler = $handler;
  }

  private function handleStaticRoute($path)
  {
    foreach ($this->staticRoutes as $basePath => $filePath) {
      if (strpos($path, $basePath) === 0) {
        $filePath = $filePath . substr($path, strlen($basePath));
        if (file_exists($filePath)) {
          header('Content-Type: ' . mime_content_type($filePath));
          readfile($filePath);
          return true;
        }
      }
    }
    return false;
  }

  private function getControllers()
  {
    $controllerDir = dirname(dirname(__DIR__)) . '/Controllers';
    $controllerNamespacePrefix = 'Secra\Controllers' . '\\';
    $controllers = [];
    $files = scandir($controllerDir);
    foreach ($files as $file) {
      if (strpos($file, '.php') !== false) {
        $controllerName = str_replace('.php', '', $file);
        require_once($controllerDir . '/' . $file);
        $controllers[] = $controllerNamespacePrefix . $controllerName;
      }
    }
    return $controllers;
  }

  /*
    * 解析路径模式
    * 路径模式中，可能包含动态参数，例如 /user/:userId
    * 动态参数也可以指定一个正则表达式，例如 /user/:userId(\d+)
   */
  private function parsePathPattern($path)
  {
    $pathPatternItems = [];
    $pathItems = explode('/', $path);
    $dynamicParamIndex = 0;
    foreach ($pathItems as $pathItem) {
      if (strpos($pathItem, ':') === 0) {
        $name = substr($pathItem, 1);
        $hasPattern = false;
        $pattern = null;
        if (strpos($name, '(') !== false) {
          $hasPattern = true;
          $pattern = substr($name, strpos($name, '(') + 1, -1);
          $name = substr($name, 0, strpos($name, '('));
        }
        $pathPatternItems[] = new PathPatternItem(
          $name,
          count($pathPatternItems),
          true,
          new PathDynamicParam(
            $name,
            $dynamicParamIndex,
            $hasPattern,
            $pattern
          )
        );
        $dynamicParamIndex++;
      } else {
        $pathPatternItems[] = new PathPatternItem(
          $pathItem,
          count($pathPatternItems),
          false,
          null
        );
      }
    }
    return $pathPatternItems;
  }

  private function registerErrorHandler($controller, $method, $errorClass)
  {
    if (!isset($this->errorHandlers[$controller])) {
      $this->errorHandlers[$controller] = [];
    }
    $this->errorHandlers[$controller][$errorClass] = $method;
  }

  private function registerController($controller)
  {
    $reflection = new ReflectionClass($controller);
    $attributes = $reflection->getAttributes(Controller::class);
    if (isset($attributes[0])) {
      $basePath = $attributes[0]->newInstance()->basePath;
      if (strpos($basePath, '/') !== 0) {
        throw new Exception('Controller basePath must start with /');
      }
      $basePath = substr($basePath, 1);
      if (substr($basePath, -1) === '/') {
        $basePath = substr($basePath, 0, -1);
      }
      $this->basePathMap[$controller] = $basePath;
      $methods = $reflection->getMethods();
      foreach ($methods as $method) {
        if (!$method->isPublic()) {
          continue;
        }
        if ($errorHandler = getAttribute($method, ErrorHandler::class)) {
          $this->registerErrorHandler($controller, $method->getName(), $errorHandler->errorClass);
        } elseif ($getAttribute = getAttribute($method, Get::class)) {
          $path = $getAttribute->path;
          $route = new Route(
            $controller,
            $method->getName(),
            $basePath,
            $path,
            $this->parsePathPattern($path)
          );
          if (!isset($this->getRoutes[$controller])) {
            $this->getRoutes[$controller] = [];
          }
          $this->getRoutes[$controller][] = $route;
          $this->logger->debug("Route registered: GET $path, controller: $controller, pathPattern: " . json_encode($route->pathPatternItems));
        } else if ($postAttribute = getAttribute($method, Post::class)) {
          $path = $postAttribute->path;
          $route = new Route(
            $controller,
            $method->getName(),
            $basePath,
            $path,
            $this->parsePathPattern($path)
          );
          if (!isset($this->postRoutes[$controller])) {
            $this->postRoutes[$controller] = [];
          }
          $this->postRoutes[$controller][] = $route;
          $this->logger->debug("Route registered: POST $path, controller: $controller, pathPattern: " . json_encode($route->pathPatternItems));
        }
      }
      $this->logger->debug("Controller registered: $controller");
      $this->container->set($controller, $controller);
    }
  }

  public function getIp()
  {
    $client_ip = '';
    if (isset($_SERVER)) {
      if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        $ipStr   = $_SERVER["HTTP_X_FORWARDED_FOR"];
        $ipArr = explode(',', $ipStr);
        $client_ip = isset($ipArr[0]) ? $ipArr[0] : '';
      } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
        $client_ip = $_SERVER["HTTP_CLIENT_IP"];
      } else {
        $client_ip = $_SERVER["REMOTE_ADDR"];
      }
    }
    //过滤无效IP
    if (filter_var($client_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false || filter_var($client_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
      return $client_ip;
    } else {
      return $_SERVER["REMOTE_ADDR"];
    }
  }

  private function resolveParameter(ReflectionParameter $parameter, MatchResult $matchResult)
  {
    if ($paramAttribute = getAttribute($parameter, Param::class)) {
      $name = $paramAttribute->name ?? $parameter->getName();
      if (!isset($matchResult->params[$name])) {
        throw new Exception('Parameter not resolved');
      }
      return $matchResult->params[$name];
    } else if ($headerAttribute = getAttribute($parameter, Header::class)) {
      $name = $headerAttribute->name ?? $parameter->getName();
      $headers = getallheaders();
      if (!isset($headers[$name])) {
        throw new Exception('Parameter not resolved');
      }
      return $headers[$name];
    } else if (hasAttribute($parameter, IP::class)) {
      return $this->getIp();
    } else if ($cookieAttribute = getAttribute($parameter, Cookie::class)) {
      $name = $cookieAttribute->name ?? $parameter->getName();
      return $_COOKIE[$name];
    } else if (hasAttribute($parameter, HttpMethod::class)) {
      return $_SERVER['REQUEST_METHOD'];
    }
    throw new Exception('Parameter not resolved');
  }

  private function resolveErrorHandler($error, $controller, $matchResult)
  {
    if (!isset($this->errorHandlers[$controller])) {
      if ($this->globalErrorHandler) {
        $this->globalErrorHandler->call($this, $error);
        return;
      }
      throw $error;
    }
    foreach ($this->errorHandlers[$controller] as $handlerErrorClass => $method) {
      if ($error instanceof $handlerErrorClass) {
        $controllerInstance = $this->container->get($controller);
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod($method);
        $parameters = array_map(function (ReflectionParameter $parameter) use ($error, $matchResult, $handlerErrorClass) {
          $type = $parameter->getType();
          if ($type instanceof ReflectionNamedType) {
            if ($type->getName() === $handlerErrorClass) {
              return $error;
            }
          }
          return $this->resolveParameter($parameter, $matchResult);
        }, $method->getParameters());
        $method->invokeArgs($controllerInstance, $parameters);
        return;
      }
    }
    throw $error;
  }

  public function route($path, $method)
  {
    if ($method === 'GET' && $this->handleStaticRoute($path)) {
      return;
    }
    $matched = false;
    $routes = $method === 'GET' ? $this->getRoutes : $this->postRoutes;
    foreach ($this->basePathMap as $controller => $basePath) {
      if (strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
        if (!isset($routes[$controller])) {
          continue;
        }
        foreach ($routes[$controller] as $route) {
          $matchResult = $route->match($path);
          $this->logger->debug("Matching route: $route->fullPath, result: " . json_encode($matchResult));
          if ($matchResult->isMatch) {
            $matched = true;
            $controllerInstance = $this->container->get($controller);
            $reflection = new ReflectionClass($controller);
            $method = $reflection->getMethod($route->method);
            $parameters = array_map(function (ReflectionParameter $parameter) use ($matchResult) {
              return $this->resolveParameter($parameter, $matchResult);
            }, $method->getParameters());

            try {
              $method->invokeArgs($controllerInstance, $parameters);
            } catch (Exception $e) {
              $this->resolveErrorHandler($e, $controller, $matchResult);
            }
            break;
          }
        }
      }
    }

    if (!$matched) {
      echo '404';
    }
  }
}
