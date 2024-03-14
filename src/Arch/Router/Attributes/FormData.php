<?php

namespace Secra\Arch\Router\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class FormData
{
  public function __construct(
    public string|null $name = null,
    public bool        $required = true,
  )
  {
  }
}
