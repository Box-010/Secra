<?php

namespace Secra\Models;

use Secra\Constants\AttitudeType;

class Comment
{
  public int $comment_id;
  public int $post_id;

  public string $content;
  public string|null $nickname = null;
  public int|null $parent_comment_id = null;
  public int $user_id;
  public User $user;

  public int $floor;

  public string $created_at;
  public string $updated_at;
  public int $positive_count;
  public int $negative_count;
  public ?AttitudeType $user_attitude;

  public function __set($name, $value)
  {
    if (!isset($this->user)) {
      $this->user = new User();
    }
    if ($name === 'user_id') {
      $this->user->user_id = $value;
    } elseif ($name === 'user_name') {
      $this->user->user_name = $value;
    } elseif ($name === 'user_email') {
      $this->user->email = $value;
    } elseif ($name === 'user_created_at') {
      $this->user->created_at = $value;
    }
  }
}
