<?php

namespace Secra\Arch\Router\Attributes;

use Attribute;


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
