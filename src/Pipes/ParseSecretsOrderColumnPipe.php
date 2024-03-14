<?php

namespace Secra\Pipes;

use InvalidArgumentException;
use Secra\Arch\Router\Pipes\Pipe;
use Secra\Constants\SecretsOrderColumn;

class ParseSecretsOrderColumnPipe implements Pipe
{
  public function transform(mixed $input): SecretsOrderColumn
  {
    if (!is_string($input)) {
      return $input;
    }
    $cases = array_filter(SecretsOrderColumn::cases(), function (SecretsOrderColumn $column) use ($input) {
      return strtolower($column->name) === strtolower($input);
    });
    if (count($cases) === 0) {
      throw new InvalidArgumentException("Invalid column name: $input");
    }
    return array_values($cases)[0];
  }
}