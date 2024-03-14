<?php

namespace Secra\Arch\Router\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Query
{
  /**
   * Query constructor.
   *
   * @param string|null $name
   * @param bool $required
   */
  public function __construct(
    public string|null $name = null,
    public bool        $required = false
  )
  {
  }
}
