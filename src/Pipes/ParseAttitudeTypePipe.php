<?php

namespace Secra\Pipes;

use InvalidArgumentException;
use Secra\Arch\Router\Pipes\Pipe;
use Secra\Constants\AttitudeType;

class ParseAttitudeTypePipe implements Pipe
{
  public function transform(mixed $input): AttitudeType
  {
    if (!is_string($input)) {
      return $input;
    }
    $cases = array_filter(AttitudeType::cases(), function (AttitudeType $item) use ($input) {
      return strtolower($item->name) === strtolower($input);
    });
    if (count($cases) === 0) {
      throw new InvalidArgumentException("Invalid column name: $input");
    }
    return array_values($cases)[0];
  }
}