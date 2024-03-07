<?php

/**
 * 标记一个类为控制器
 * 
 * @param string $basePath 控制器的基础路径
 */
#[Attribute]
class Controller
{
  public function __construct(public string $basePath)
  {
  }
}

/**
 * 标记一个方法为 GET 请求处理器
 * 
 * @param string $path 请求路径模式
 */
#[Attribute]
class Get
{
  public function __construct(public string $path)
  {
  }
}

/**
 * 标记一个方法为 POST 请求处理器
 * 
 * @param string $path 请求路径模式
 */
#[Attribute]
class Post
{
  public function __construct(public string $path)
  {
  }
}

/**
 * 标记一个参数为错误处理器
 * 
 * @param string $errorClass 错误类名，留空则处理所有未被处理的异常
 */
#[Attribute]
class ErrorHandler
{
  public function __construct(public string $errorClass = Exception::class)
  {
  }
}

/**
 * 标记一个参数为请求路径参数
 * 
 * @param string|null $name 参数名，为空则使用变量名
 */
#[Attribute]
class Param
{
  public function __construct(public string|null $name = null)
  {
  }
}

/**
 * 标记一个参数为请求头参数
 * 
 * @param string|null $name 参数名，为空则使用变量名
 */
#[Attribute]
class Header
{
  public function __construct(public string|null $name = null)
  {
  }
}

/**
 * 标记一个参数为 IP 地址
 */
#[Attribute]
class IP
{
}

/**
 * 标记一个参数为 Cookie
 * 
 * @param string|null $name Cookie 名，为空则使用变量名
 */
#[Attribute]
class Cookie
{
  public function __construct(public string|null $name = null)
  {
  }
}

/**
 * 标记一个参数为本次请求的 HTTP 方法
 */
#[Attribute]
class HttpMethod
{
}
