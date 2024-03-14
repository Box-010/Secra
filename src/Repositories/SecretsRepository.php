<?php

namespace Secra\Repositories;

use PDO;
use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Attributes\Singleton;
use Secra\Constants\SecretsOrderColumn;
use Secra\Models\Secret;

#[Provide(SecretsRepository::class)]
#[Singleton]
class SecretsRepository extends BaseRepository
{
  /**
   * @param SecretsOrderColumn $orderBy Order by column
   * @param bool $desc Is descending order
   * @param int $limit The maximum number of secrets to get
   * @param int $offset The number of secrets to skip
   * @return Secret[]
   */
  public function getAll(
    SecretsOrderColumn $orderBy = SecretsOrderColumn::CREATED_AT,
    bool               $desc = true,
    int                $limit = 10,
    int                $offset = 0
  ): array
  {
    $order = $desc ? 'DESC' : 'ASC';
    $stmt = $this->db->query("SELECT
      posts.*, u.user_id, u.user_name, u.email AS user_email, u.created_at AS user_created_at,
    (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.post_id) AS comment_count,
    (SELECT COUNT(*) FROM likes WHERE likes.likeable_type = 'posts' AND likes.like_type = 'like' AND likes.likeable_id = posts.post_id) AS like_count,
    (SELECT COUNT(*) FROM likes WHERE likes.likeable_type = 'posts' AND likes.like_type = 'dislike' AND likes.likeable_id = posts.post_id) AS dislike_count
    FROM posts
    INNER JOIN users u on posts.author_id = u.user_id
    ORDER BY {$orderBy->value} $order
    LIMIT $limit OFFSET $offset;");
    if (!$stmt) {
      $this->logger->error('Failed to get secrets: ' . $this->db->lastError()->getMessage());
      return [];
    }
    $stmt->setFetchMode(PDO::FETCH_CLASS, Secret::class);
    return $stmt->fetchAll();
  }

  /**
   * @param int $id The ID of the secret to get
   * @return Secret|false The secret with the given ID, or false if not found
   */
  public function getById(int $id): Secret|false
  {
    $stmt = $this->db->query("SELECT
      posts.*, u.user_id, u.user_name, u.email AS user_email, u.created_at AS user_created_at,
    (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.post_id) AS comment_count,
    (SELECT COUNT(*) FROM likes WHERE likes.likeable_type = 'posts' AND likes.like_type = 'like' AND likes.likeable_id = posts.post_id) AS like_count,
    (SELECT COUNT(*) FROM likes WHERE likes.likeable_type = 'posts' AND likes.like_type = 'dislike' AND likes.likeable_id = posts.post_id) AS dislike_count
    FROM posts
    INNER JOIN secra.users u on posts.author_id = u.user_id
    WHERE post_id = :id", [
      'id' => $id
    ]);
    $stmt->setFetchMode(PDO::FETCH_CLASS, Secret::class);
    return $stmt->fetch();
  }

  /**
   * @param Secret $secret The secret to save
   * @return Secret|false The saved secret, or false if the save failed
   */
  public function save(Secret $secret): Secret|false
  {
    $stmt = $this->db->query("INSERT INTO posts (author_id, content, nickname) VALUES (:authorId, :content, :nickname)", [
      'authorId' => $secret->author_id,
      'content' => $secret->content,
      'nickname' => $secret->nickname
    ]);
    if ($stmt->rowCount() > 0) {
      return $this->getById($this->db->lastInsertId());
    }
    return false;
  }

  /**
   * @param int $id The ID of the secret to delete
   * @return bool True if the secret was deleted successfully
   */
  public function delete(int $id): bool
  {
    $stmt = $this->db->query("DELETE FROM posts WHERE post_id = :id", [
      'id' => $id
    ]);
    return $stmt->rowCount() > 0;
  }

  /**
   * @param int $id The ID of the secret to update
   * @param string $content The new content of the secret
   * @return bool True if the secret was updated successfully
   */
  public function update(int $id, string $content): bool
  {
    $stmt = $this->db->query("UPDATE posts SET content = :content WHERE post_id = :id", [
      'id' => $id,
      'content' => $content
    ]);
    return $stmt->rowCount() > 0;
  }
}