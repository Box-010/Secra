<?php

namespace Secra\Models;

class Secret
{
  public int $post_id;
  public string $content;
  public string|null $nickname;
  public int $author_id;
  public User $author;

  public string $created_at;
  public string $updated_at;
  public int $comment_count;
  public int $like_count;
  public int $dislike_count;

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