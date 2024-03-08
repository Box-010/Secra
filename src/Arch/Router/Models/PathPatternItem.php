<?php

namespace Secra\Arch\Router\Models;


class PathPatternItem
{
  public function __construct(
    public string                $name,
    public int                   $depth,
    public bool                  $isDynamicParam,
    public PathDynamicParam|null $dynamicParam,
  )
  {
  }
}
