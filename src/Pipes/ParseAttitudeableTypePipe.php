<?php

namespace Secra\Pipes;

use InvalidArgumentException;
use Secra\Arch\Router\Pipes\Pipe;
use Secra\Constants\AttitudeableType;

class ParseAttitudeableTypePipe implements Pipe
{
  public function transform(mixed $input): AttitudeableType
  {
    if (!is_string($input)) {
      return $input;
    }
    $cases = array_filter(AttitudeableType::cases(), function (AttitudeableType $item) use ($input) {
      return strtolower($item->name) === strtolower($input);
    });
    if (count($cases) === 0) {
      throw new InvalidArgumentException("Invalid column name: $input");
    }
    return array_values($cases)[0];
  }
}