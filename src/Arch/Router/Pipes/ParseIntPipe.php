<?php

namespace Secra\Arch\Router\Pipes;

class ParseIntPipe implements Pipe
{
  public function transform(mixed $input): int
  {
    if (is_int($input)) {
      return $input;
    }
    return intval($input);
  }
}