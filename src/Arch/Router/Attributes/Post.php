<?php

namespace Secra\Arch\Router\Attributes;

use Attribute;


/**
 * 标记一个方法为 POST 请求处理器
 *
 * @param string $path 请求路径模式
 */
#[Attribute]
class Post
{
  public function __construct(public string $path = '')
  {
  }
}
