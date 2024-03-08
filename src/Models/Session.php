<?php

namespace Secra\Models;


class Session
{
  public string $session_id;
  public int $user_id;
  public string $issued_at;
  public string $expires_at;
  public string $user_name;
}
