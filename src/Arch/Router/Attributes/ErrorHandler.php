<?php

namespace Secra\Arch\Router\Attributes;

use Attribute;
use Exception;


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
