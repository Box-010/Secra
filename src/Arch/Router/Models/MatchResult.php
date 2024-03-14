<?php

namespace Secra\Arch\Router\Models;


use Secra\Arch\Router\Route;

class MatchResult
{
  public function __construct(
    public Route $route,
    public bool  $isMatch,
    public array $params,
  )
  {
  }
}
