<?php

namespace Secra\Arch\Router\Attributes;

use Attribute;


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
