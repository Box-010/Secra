<?php

namespace Secra\Arch\Router;

require_once(dirname(dirname(__DIR__)) . '/Utils/Reflection.php');

use Closure;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use Secra\Arch\DI\Attributes\Inject;
use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Attributes\Singleton;
use Secra\Arch\DI\Container;
use Secra\Arch\Logger\ILogger;
use Secra\Arch\Router\Attributes\Controller;
use Secra\Arch\Router\Attributes\Cookie;
use Secra\Arch\Router\Attributes\Delete;
use Secra\Arch\Router\Attributes\ErrorHandler;
use Secra\Arch\Router\Attributes\FormData;
use Secra\Arch\Router\Attributes\Get;
use Secra\Arch\Router\Attributes\Header;
use Secra\Arch\Router\Attributes\HttpMethod;
use Secra\Arch\Router\Attributes\IP;
use Secra\Arch\Router\Attributes\Param;
use Secra\Arch\Router\Attributes\Pipes;
use Secra\Arch\Router\Attributes\Post;
use Secra\Arch\Router\Attributes\Put;
use Secra\Arch\Router\Attributes\Query;
use Secra\Arch\Router\Models\MatchResult;
use Secra\Arch\Router\Models\PathDynamicParam;
use Secra\Arch\Router\Models\PathPatternItem;
use Secra\Arch\Router\Pipes\Pipe;


#[Provide(Router::class)]
#[Singleton]
class Router
{
  private array $controllers;
  private array $basePathMap = [];

  /**
   * @var Route[][]
   */
  private array $getRoutes = [];

  /**
   * @var Route[][]
   */
  private array $postRoutes = [];


  /**
   * @var Route[][]
   */
  private array $putRoutes = [];

  /**
   * @var Route[][]
   */
  private array $deleteRoutes = [];

  // 静态路由表，basePath => filePath
  // 静态路由用于指向静态资源，例如图片、CSS、JS 等
  private array $staticRoutes = [];

  // 错误处理器，ControllerName => [errorClass => method]
  private array $errorHandlers = [];
  // 全局错误处理器（闭包）
  private Closure|null $globalErrorHandler = null;

  public function __construct(
    protected Container       $container,
    #[Inject] private ILogger $logger
  )
  {
    $this->controllers = $this->getControllers();
    foreach ($this->controllers as $controller) {
      $this->registerController($controller);
    }
  }

  private function getControllers(): array
  {
    $controllerDir = dirname(__DIR__, 2) . '/Controllers';
    $controllerNamespacePrefix = 'Secra\Controllers' . '\\';
    $controllers = [];
    $files = scandir($controllerDir);
    foreach ($files as $file) {
      if (str_contains($file, '.php')) {
        $controllerName = str_replace('.php', '', $file);
        require_once($controllerDir . '/' . $file);
        $controllers[] = $controllerNamespacePrefix . $controllerName;
      }
    }
    return $controllers;
  }

  private function registerRoute(
    string $httpMethod,
    string $controller,
    string $methodName,
    string $path,
  ): void
  {
    $routes = match ($httpMethod) {
      'GET' => 'getRoutes',
      'POST' => 'postRoutes',
      'PUT' => 'putRoutes',
      'DELETE' => 'deleteRoutes',
      default => throw new Exception('Unsupported method'),
    };
    if (!isset(($this->$routes)[$controller])) {
      ($this->$routes)[$controller] = [];
    }
    $basePath = $this->basePathMap[$controller];
    ($this->$routes)[$controller][] = new Route(
      $controller,
      $methodName,
      $basePath,
      $path,
      $this->parsePathPattern($path)
    );
    $this->logger->debug("Route registered: $httpMethod $path, controller: $controller, pathPattern: " . json_encode($this->parsePathPattern($path)));
  }

  private function registerController($controller): void
  {
    $reflection = new ReflectionClass($controller);
    $attributes = $reflection->getAttributes(Controller::class);
    if (isset($attributes[0])) {
      $basePath = $attributes[0]->newInstance()->basePath;
      if (!str_starts_with($basePath, '/')) {
        throw new Exception('Controller basePath must start with /');
      }
      $basePath = substr($basePath, 1);
      if (str_ends_with($basePath, '/')) {
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
        } else if ($getAttribute = getAttribute($method, Get::class)) {
          $this->registerRoute('GET', $controller, $method->getName(), $getAttribute->path);
        } else if ($postAttribute = getAttribute($method, Post::class)) {
          $this->registerRoute('POST', $controller, $method->getName(), $postAttribute->path);
        } else if ($putAttribute = getAttribute($method, Put::class)) {
          $this->registerRoute('PUT', $controller, $method->getName(), $putAttribute->path);
        } else if ($deleteAttribute = getAttribute($method, Delete::class)) {
          $this->registerRoute('DELETE', $controller, $method->getName(), $deleteAttribute->path);
        }
      }
      $this->logger->debug("Controller registered: $controller");
      $this->container->set($controller, $controller);
    }
  }

  private function registerErrorHandler($controller, $method, $errorClass): void
  {
    if (!isset($this->errorHandlers[$controller])) {
      $this->errorHandlers[$controller] = [];
    }
    $this->errorHandlers[$controller][$errorClass] = $method;
  }

  private function parsePathPattern($path): array
  {
    $pathPatternItems = [];
    $pathItems = explode('/', $path);
    $dynamicParamIndex = 0;
    foreach ($pathItems as $pathItem) {
      if (str_starts_with($pathItem, ':')) {
        $name = substr($pathItem, 1);
        $hasPattern = false;
        $pattern = null;
        if (str_contains($name, '(')) {
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

  /*
    * 解析路径模式
    * 路径模式中，可能包含动态参数，例如 /user/:userId
    * 动态参数也可以指定一个正则表达式，例如 /user/:userId(\d+)
   */
  public function registerStaticRoute($basePath, $filePath): void
  {
    $this->staticRoutes[$basePath] = $filePath;
  }

  public function registerGlobalErrorHandler(Closure $handler): void
  {
    $this->globalErrorHandler = $handler;
  }

  private function getRoutes($method): array
  {
    if ($method === 'GET') {
      return $this->getRoutes;
    } else if ($method === 'POST') {
      return $this->postRoutes;
    } else if ($method === 'PUT') {
      return $this->putRoutes;
    } else if ($method === 'DELETE') {
      return $this->deleteRoutes;
    }
    throw new Exception('Unsupported method');
  }

  public function route($path, $method): void
  {
    if ($method === 'GET' && $this->handleStaticRoute($path)) {
      return;
    }
    if ($method === 'POST') {
      if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
        $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
        $this->logger->debug("Method override: $method");
      }
    }
    $matched = false;
    $routes = $this->getRoutes($method);
    foreach ($this->basePathMap as $controller => $basePath) {
      if (!isset($routes[$controller])) {
        continue;
      }
      if (str_starts_with($path, $basePath)) {
        $path = substr($path, strlen($basePath));
        foreach ($routes[$controller] as $route) {
          $matchResult = $route->match($path);
          $this->logger->debug("Matching route: $route->fullPath, result: " . json_encode($matchResult));
          if ($matchResult->isMatch) {
            $matched = true;
            $controllerInstance = $this->container->get($controller);
            $reflection = new ReflectionClass($controller);
            $method = $reflection->getMethod($route->method);
            $parameters = array_map(function (ReflectionParameter $parameter) use ($matchResult) {
              $val = $this->resolveParameter($parameter, $matchResult);
              if ($pipesAttribute = getAttribute($parameter, Pipes::class)) {
                $pipes = $pipesAttribute->pipes;
                $val = $this->transformParameter($val, $pipes, $parameter->name);
              }
              return $val;
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
      http_response_code(404);
      echo '404';
    }
  }

  private function getStaticFileMimeType($filePath): string
  {
    if (str_ends_with($filePath, '.css')) {
      return 'text/css';
    } else if (str_ends_with($filePath, '.js')) {
      return 'application/javascript';
    } else if (str_ends_with($filePath, '.png')) {
      return 'image/png';
    } else if (str_ends_with($filePath, '.jpg') || str_ends_with($filePath, '.jpeg')) {
      return 'image/jpeg';
    } else if (str_ends_with($filePath, '.gif')) {
      return 'image/gif';
    } else if (str_ends_with($filePath, '.svg')) {
      return 'image/svg+xml';
    } else if (str_ends_with($filePath, '.ico')) {
      return 'image/x-icon';
    } else if (str_ends_with($filePath, '.webp')) {
      return 'image/webp';
    } else if (str_ends_with($filePath, '.mp3')) {
      return 'audio/mpeg';
    } else if (str_ends_with($filePath, '.mp4')) {
      return 'video/mp4';
    } else if (str_ends_with($filePath, '.webm')) {
      return 'video/webm';
    } else if (str_ends_with($filePath, '.ogg')) {
      return 'audio/ogg';
    } else if (str_ends_with($filePath, '.wav')) {
      return 'audio/wav';
    } else if (str_ends_with($filePath, '.flac')) {
      return 'audio/flac';
    }
    return mime_content_type($filePath);
  }

  private function handleStaticRoute($path): bool
  {
    foreach ($this->staticRoutes as $basePath => $filePath) {
      if (str_starts_with($path, $basePath)) {
        $filePath = $filePath . '/' . substr($path, strlen($basePath));
        if (file_exists($filePath) && is_file($filePath)) {
          $this->logger->info("Static route resolved: $filePath");
          header('Content-Type: ' . $this->getStaticFileMimeType($filePath));
          header('X-Resolved-By: Secra');
          readfile($filePath);
          return true;
        }
      }
    }
    return false;
  }

  private function resolveParameter(
    ReflectionParameter $parameter,
    MatchResult         $matchResult
  )
  {
    $paramName = $parameter->getName();

    if ($paramAttribute = getAttribute($parameter, Param::class)) {
      $name = $paramAttribute->name ?? $paramName;
      if (!isset($matchResult->params[$name])) {
        throw new Exception('Parameter not resolved');
      }
      return $matchResult->params[$name];
    } else if ($headerAttribute = getAttribute($parameter, Header::class)) {
      $name = $headerAttribute->name ?? $paramName;
      $headers = getallheaders();
      if (!isset($headers[$name])) {
        throw new Exception('Parameter not resolved');
      }
      return $headers[$name];
    } else if (hasAttribute($parameter, IP::class)) {
      return $this->getIp();
    } else if ($cookieAttribute = getAttribute($parameter, Cookie::class)) {
      $name = $cookieAttribute->name ?? $paramName;
      return $_COOKIE[$name];
    } else if (hasAttribute($parameter, HttpMethod::class)) {
      return $_SERVER['REQUEST_METHOD'];
    } else if ($queryAttribute = getAttribute($parameter, Query::class)) {
      $name = $queryAttribute->name ?? $paramName;
      if (isset($_GET[$name])) {
        return $_GET[$name];
      }
      try {
        return $parameter->getDefaultValue();
      } catch (ReflectionException $e) {
        if (!$queryAttribute->required) {
          return null;
        }
      }
    } else if ($formDataAttribute = getAttribute($parameter, FormData::class)) {
      $name = $formDataAttribute->name ?? $paramName;
      if (isset($_POST[$name])) {
        return $_POST[$name];
      }
      try {
        return $parameter->getDefaultValue();
      } catch (ReflectionException $e) {
        if (!$formDataAttribute->required) {
          return null;
        }
      }
    }

    $routeName = $matchResult->route->controller . '::' . $matchResult->route->method;

    throw new Exception("Failed to resolve parameter $paramName when invoking $routeName");
  }

  /**
   * Transform parameter value through pipes
   *
   * @param mixed $originVal
   * @param array<class-string<Pipe> | Pipe> $pipes
   * @param string $paramName
   */
  private function transformParameter(
    mixed  $originVal,
    array  $pipes,
    string $paramName
  )
  {
    $val = $originVal;
    foreach ($pipes as $pipe) {
      if (is_string($pipe)) {
        $pipe = new $pipe;
      }
      try {
        $val = $pipe->transform($val);
      } catch (Exception $e) {
        throw new Exception("Failed to transform parameter $paramName through pipe: " . $e->getMessage());
      }
    }
    return $val;
  }

  public function getIp()
  {
    $client_ip = '';
    if (isset($_SERVER)) {
      if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        $ipStr = $_SERVER["HTTP_X_FORWARDED_FOR"];
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

  private function resolveErrorHandler($error, $controller, $matchResult): void
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
}
