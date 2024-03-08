<?php

namespace Secra\Models;


class User
{
  public int $user_id;
  public string $user_name;
  public string $password = "";
  public string $salt = "";
  public string $email;
  public string $created_at;

  public function __set($name, $value)
  {
    if ($name === 'roles') {
      $this->roles = explode(',', $value);
    }
  }
}
