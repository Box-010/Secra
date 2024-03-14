<?php

namespace Secra\Arch\Router\Attributes;

use Attribute;
use Secra\Arch\Router\Pipes\Pipe;

/**
 * 标记一个参数的值需要经过一系列的管道处理，管道的顺序即为数组的顺序
 * 输入的值类型为第一个管道的输入类型，输出的值类型为最后一个管道的输出类型
 */
#[Attribute]
class Pipes
{
  /**
   * @param array<class-string<Pipe> | Pipe> $pipes
   */
  public function __construct(
    public array $pipes
  )
  {
  }
}