<?php

namespace Secra\Arch\Router\Attributes;

use Attribute;


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
