<?php

namespace Secra\Models;

use Secra\Constants\AttitudeableType;
use Secra\Constants\AttitudeType;

class Attitude
{
  public int $attitude_id;
  public int $user_id;
  public AttitudeableType $attitudeable_type;
  public int $attitudeable_id;
  public AttitudeType $attitude_type;
  public string $created_at;

  public function __set($name, $value)
  {
    if ($name === '$attitudeable_id') {
      $this->attitudeable_type = AttitudeableType::from($value);
    } else if ($name === '$attitude_type') {
      $this->attitude_type = AttitudeType::from($value);
    }
  }
}