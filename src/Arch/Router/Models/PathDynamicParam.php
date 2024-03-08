<?php

namespace Secra\Arch\Router\Models;


class PathDynamicParam
{
  public function __construct(
    public string      $name,
    public int         $index,
    public bool        $hasPattern,
    public string|null $pattern,
  )
  {
  }
}
