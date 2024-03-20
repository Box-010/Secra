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
class SecretsRepository extends BaseRepository
{
  #[Inject] private AttitudesRepository $attitudesRepository;
  #[Inject] private SessionService $sessionService;

  private function resolveSecretsAttitudeStatus(array $secrets): array
  {
    return array_map(fn(Secret $secret) => $this->resolveSecretAttitudeStatus($secret), $secrets);
  }

  private function resolveSecretAttitudeStatus(Secret $secret): Secret
  {
    if ($this->sessionService->isUserLoggedIn()) {
      $userId = $this->sessionService->getCurrentSession()->user_id;
      $secret->user_attitude = $this->attitudesRepository->queryUserAttitude(AttitudeableType::SECRETS, $secret->post_id, $userId);
    }
    return $secret;
  }

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
      (SELECT COUNT(*) FROM attitudes WHERE attitudes.attitudeable_type = 'secrets' AND attitudes.attitude_type = 'positive' AND attitudes.attitudeable_id = posts.post_id) AS positive_count,
      (SELECT COUNT(*) FROM attitudes WHERE attitudes.attitudeable_type = 'secrets' AND attitudes.attitude_type = 'negative' AND attitudes.attitudeable_id = posts.post_id) AS negative_count
    FROM posts
    INNER JOIN users u on posts.author_id = u.user_id
    ORDER BY {$orderBy->value} $order
    LIMIT $limit OFFSET $offset;");
    if (!$stmt) {
      $this->logger->error('Failed to get secrets: ' . $this->db->lastError()->getMessage());
      return [];
    }
    $stmt->setFetchMode(PDO::FETCH_CLASS, Secret::class);
    $secrets = $stmt->fetchAll();

    return $this->resolveSecretsAttitudeStatus($secrets);
  }

  public function countAll(): int
  {
    $stmt = $this->db->query("SELECT COUNT(*) FROM posts");
    if (!$stmt) {
      $this->logger->error('Failed to count secrets: ' . $this->db->lastError()->getMessage());
      return 0;
    }
    return $stmt->fetchColumn();
  }

  public function getByUserId(
    int                $userId,
    SecretsOrderColumn $orderBy = SecretsOrderColumn::CREATED_AT,
    bool               $desc = true,
    int                $limit = 10,
    int                $offset = 0
  )
  {
    $order = $desc ? 'DESC' : 'ASC';
    $stmt = $this->db->query("SELECT
      posts.*, u.user_id, u.user_name, u.email AS user_email, u.created_at AS user_created_at,
      (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.post_id) AS comment_count,
      (SELECT COUNT(*) FROM attitudes WHERE attitudes.attitudeable_type = 'secrets' AND attitudes.attitude_type = 'positive' AND attitudes.attitudeable_id = posts.post_id) AS positive_count,
      (SELECT COUNT(*) FROM attitudes WHERE attitudes.attitudeable_type = 'secrets' AND attitudes.attitude_type = 'negative' AND attitudes.attitudeable_id = posts.post_id) AS negative_count
    FROM posts
    INNER JOIN users u on posts.author_id = u.user_id
    WHERE author_id = :userId
    ORDER BY {$orderBy->value} $order
    LIMIT $limit OFFSET $offset;", [
      'userId' => $userId
    ]);
    if (!$stmt) {
      $this->logger->error('Failed to get secrets by user ID: ' . $this->db->lastError()->getMessage());
      return [];
    }
    $stmt->setFetchMode(PDO::FETCH_CLASS, Secret::class);
    $secrets = $stmt->fetchAll();

    return $this->resolveSecretsAttitudeStatus($secrets);
  }

  public function countByUserId(int $userId): int
  {
    $stmt = $this->db->query("SELECT COUNT(*) FROM posts WHERE author_id = :userId", [
      'userId' => $userId
    ]);
    if (!$stmt) {
      $this->logger->error('Failed to count secrets by user ID: ' . $this->db->lastError()->getMessage());
      return 0;
    }
    return $stmt->fetchColumn();
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
    (SELECT COUNT(*) FROM attitudes WHERE attitudes.attitudeable_type = 'secrets' AND attitudes.attitude_type = 'positive' AND attitudes.attitudeable_id = posts.post_id) AS positive_count,
    (SELECT COUNT(*) FROM attitudes WHERE attitudes.attitudeable_type = 'secrets' AND attitudes.attitude_type = 'negative' AND attitudes.attitudeable_id = posts.post_id) AS negative_count
    FROM posts
    INNER JOIN secra.users u on posts.author_id = u.user_id
    WHERE post_id = :id", [
      'id' => $id
    ]);
    if (!$stmt) {
      $this->logger->error('Failed to get secret by ID: ' . $this->db->lastError()->getMessage());
      return false;
    }
    $stmt->setFetchMode(PDO::FETCH_CLASS, Secret::class);
    return $this->resolveSecretAttitudeStatus($stmt->fetch());
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
   * @param Secret $secret The secret to update
   * @return bool True if the secret was updated successfully
   */
  public function update(Secret $secret): bool
  {
    $stmt = $this->db->query("UPDATE posts SET content = :content, nickname = :nickname WHERE post_id = :id", [
      'id' => $secret->post_id,
      'content' => $secret->content,
      'nickname' => $secret->nickname
    ]);
    return $stmt->rowCount() > 0;
  }
}
