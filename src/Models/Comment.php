<?php

namespace Secra\Models;
use Secra\Constants\AttitudeType;

class Comment
{
  public int $user_id;
  public int $comment_id;
  public string $content;
  public string|null $nickname;
  public int $author_id;
  public int $parent_id;
  public User $author;

  public string $created_at;
  public string $updated_at;
  public int $comment_count;
  public int $positive_count;
  public int $negative_count;
  public ?AttitudeType $user_attitude;

  public function __set($name, $value)
  {
    if (!isset($this->author)) {
      $this->author = new User();
    }
    if ($name === 'user_id') {
      $this->author->user_id = $value;
    } elseif ($name === 'user_name') {
      $this->author->user_name = $value;
    } elseif ($name === 'user_email') {
      $this->author->email = $value;
    } elseif ($name === 'user_created_at') {
      $this->author->created_at = $value;
    }
  }

}
