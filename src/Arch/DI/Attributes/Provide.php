<?php

namespace Secra\Arch\DI\Attributes;

use Attribute;


#[Attribute]
class Provide
{
  public string $class;

  public function __construct(string $class)
  {
    $this->class = $class;
  }
}
