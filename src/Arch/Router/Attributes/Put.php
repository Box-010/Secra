<?php

namespace Secra\Arch\Router\Attributes;

use Attribute;


#[Attribute]
class Put
{
  public function __construct(public string $path = '')
  {
  }
}