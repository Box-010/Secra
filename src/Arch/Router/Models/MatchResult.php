<?php

namespace Secra\Arch\Router\Models;


class MatchResult
{
  public function __construct(
    public bool  $isMatch,
    public array $params,
  )
  {
  }
}
