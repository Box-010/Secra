<?php

namespace Secra\Repositories;

use PDO;
use Secra\Components\DI\Attributes\Inject;
use Secra\Components\DI\Attributes\Provide;
use Secra\Components\DI\Attributes\Singleton;
use Secra\Constants\AttitudeableType;
use Secra\Constants\SecretsOrderColumn;
use Secra\Models\Secret;
use Secra\Services\SessionService;

#[Provide(SecretsRepository::class)]
#[Singleton]
class CommentsRepository extends BaseRepository
{
  #[Inject] private SessionService $sessionService;

  public function getCommentsByPostId(int $post_id): array
  {
    $stmt = $this->db->query("SELECT
      comments.*,
      users.user_name
    FROM comments
    INNER JOIN users ON comments.user_id = users.user_id
    WHERE comments.post_id = ?;", [$post_id]);
    $stmt->setFetchMode(PDO::FETCH_CLASS, Secret::class);
    return $stmt->fetchAll();
  }

  public function save(Secret $secret): Secret|false
  {
    $stmt = $this->db->query('INSERT INTO secrets (post_id, author_id, content) VALUES (?, ?, ?)', [
      $secret->post_id,
      $secret->author_id,
      $secret->content,
    ]);
    if ($stmt->rowCount() > 0) {
      return $this->getSecretById($this->db->lastInsertId());
    }
    return false;
  }

  public function getSecretById(int $post_id): Secret|bool
  {
    $stmt = $this->db->query("SELECT
      posts.*, u.user_id, u.user_name, u.email AS user_email, u.created_at AS user_created_at,
      (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.post_id) AS comment_count,
      (SELECT COUNT(*) FROM attitudes WHERE attitudes.attitudeable_type = 'secrets' AND attitudes.attitude_type = 'positive' AND attitudes.attitudeable_id = posts.post_id) AS positive_count,
      (SELECT COUNT(*) FROM attitudes WHERE attitudes.attitudeable_type = 'secrets' AND attitudes.attitude_type = 'negative' AND attitudes.attitudeable_id = posts.post_id) AS negative_count
    FROM posts
    INNER JOIN users u on posts.author_id = u.user_id
    WHERE posts.post_id = ?;", [$post_id]);
    $stmt->setFetchMode(PDO::FETCH_CLASS, Secret::class);
    return $stmt->fetch();
  }

  public function update(Secret $secret): void
  {
    $this->db->query('UPDATE comments SET author_id = ?, content = ? WHERE post_id = ?', [
      $secret->author_id,
      $secret->content,
      $secret->post_id,
    ]);
  }

  public function delete(int $post_id): void
  {
    $this->db->query('DELETE FROM comments WHERE post_id = ?', [$post_id]);
  }
}
