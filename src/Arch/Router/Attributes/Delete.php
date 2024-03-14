<?php

namespace Secra\Arch\Router\Attributes;

use Attribute;


#[Attribute]
class Delete
{
  public function __construct(public string $path = '')
  {
  }
}