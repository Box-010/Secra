<?php

namespace Secra\Arch\Router\Attributes;

use Attribute;


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
