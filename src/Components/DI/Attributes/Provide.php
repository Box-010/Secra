<?php

namespace Secra\Components\DI\Attributes;

use Attribute;


#[Attribute]
class Provide
{
  /**
   * @param class-string $class
   */
  public function __construct(public string $class)
  {
  }
}
