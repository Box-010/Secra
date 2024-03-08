<?php

namespace Secra\Arch\Router\Models;

use Secra\Arch\Router\Models\PathDynamicParam;


class PathPatternItem
{
  public function __construct(
    public string $name,
    public int $depth,
    public bool $isDynamicParam,
    public PathDynamicParam|null $dynamicParam,
  ) {
  }
}
