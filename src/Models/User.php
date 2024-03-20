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
  public array $user_roles = [];
  public int $secret_count;

  public function __set($name, $value)
  {
    if ($name === 'roles') {
      $this->user_roles = explode(',', $value);
    }
  }
}
