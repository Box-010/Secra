<?php

namespace Secra\Repositories;

use PDO;
use Secra\Arch\DI\Attributes\Inject;
use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Attributes\Singleton;
use Secra\Constants\AttitudeableType;
use Secra\Constants\SecretsOrderColumn;
use Secra\Models\Secret;
use Secra\Services\SessionService;

#[Provide(SecretsRepository::class)]
#[Singleton]
class SecretsRepository extends BaseRepository
{
  #[Inject] private AttitudesRepository $attitudesRepository;
  #[Inject] private ImageRepository $imageRepository;
  #[Inject] private SessionService $sessionService;

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
  ): array {
    $order = $desc ? 'DESC' : 'ASC';
    $stmt = $this->db->query("SELECT * FROM v_secret
    ORDER BY {$orderBy->value} $order
    LIMIT $limit OFFSET $offset;");
    if (!$stmt) {
      $this->logger->error('Failed to get secrets: ' . $this->db->lastError()->getMessage());
      return [];
    }
    $stmt->setFetchMode(PDO::FETCH_CLASS, Secret::class);
    $secrets = $stmt->fetchAll();
    $secrets = $this->resolveSecretsAttitudeStatus($secrets);
    return $this->resolveSecretsImageUrls($secrets);
  }

  private function resolveSecretsAttitudeStatus(array $secrets): array
  {
    return array_map(fn (Secret $secret) => $this->resolveSecretAttitudeStatus($secret), $secrets);
  }

  private function resolveSecretAttitudeStatus(Secret $secret): Secret
  {
    if ($this->sessionService->isUserLoggedIn()) {
      $userId = $this->sessionService->getCurrentSession()->user_id;
      $secret->user_attitude = $this->attitudesRepository->queryUserAttitude(AttitudeableType::SECRETS, $secret->post_id, $userId);
    }
    return $secret;
  }

  private function resolveSecretsImageUrls(array $secrets): array
  {
    return array_map(fn (Secret $secret) => $this->resolveSecretImageUrls($secret), $secrets);
  }

  private function resolveSecretImageUrls(Secret $secret): Secret
  {
    $secret->image_urls = array_map(fn ($image) => PUBLIC_ROOT . 'usr/uploads/' . $this->imageRepository->get($image), $secret->images);
    return $secret;
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
  ): array {
    $order = $desc ? 'DESC' : 'ASC';
    $stmt = $this->db->query("SELECT * FROM v_secret
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
    $secrets = $this->resolveSecretsAttitudeStatus($secrets);

    return $this->resolveSecretsImageUrls($secrets);
  }

  /**
   * @param string $query The search query. Use spaces to separate words
   * @param SecretsOrderColumn $orderBy Order by column
   * @param bool $desc Is descending order
   * @param int $limit The maximum number of secrets to get
   * @param int $offset The number of secrets to skip
   * @return Secret[]
   */
  public function search(
    string             $query,
    SecretsOrderColumn $orderBy = SecretsOrderColumn::CREATED_AT,
    bool               $desc = true,
    int                $limit = 10,
    int                $offset = 0
  ): array {
    $query = trim($query);
    $query = preg_replace('/\s+/i', ' ', $query);
    $query = implode('%', explode(' ', $query));
    $order = $desc ? 'DESC' : 'ASC';
    $stmt = $this->db->query("SELECT * FROM v_secret
    WHERE content LIKE :query
    ORDER BY {$orderBy->value} $order
    LIMIT $limit OFFSET $offset;", [
      'query' => "%$query%"
    ]);
    if (!$stmt) {
      $this->logger->error('Failed to search secrets: ' . $this->db->lastError()->getMessage());
      return [];
    }
    $stmt->setFetchMode(PDO::FETCH_CLASS, Secret::class);
    $secrets = $stmt->fetchAll();
    $secrets = $this->resolveSecretsAttitudeStatus($secrets);

    return $this->resolveSecretsImageUrls($secrets);
  }

  public function countBySearchQuery(string $query): int
  {
    $query = trim($query);
    $query = preg_replace('/\s+/i', ' ', $query);
    $query = implode('%', explode(' ', $query));
    $stmt = $this->db->query("SELECT COUNT(*) FROM v_secret WHERE content LIKE :query", [
      'query' => "%$query%"
    ]);
    if (!$stmt) {
      $this->logger->error('Failed to count secrets by search query: ' . $this->db->lastError()->getMessage());
      return 0;
    }
    return $stmt->fetchColumn();
  }

  public function countByUserId(int $userId): int
  {
    $stmt = $this->db->query("SELECT COUNT(*) FROM v_secret WHERE author_id = :userId", [
      'userId' => $userId
    ]);
    if (!$stmt) {
      $this->logger->error('Failed to count secrets by user ID: ' . $this->db->lastError()->getMessage());
      return 0;
    }
    return $stmt->fetchColumn();
  }

  /**
   * @param Secret $secret The secret to save
   * @return Secret|false The saved secret, or false if the save failed
   */
  public function save(Secret $secret): Secret|false
  {
    $stmt = $this->db->query("INSERT INTO posts (author_id, content, nickname, image_ids) VALUES (:authorId, :content, :nickname, :imageIds)", [
      'authorId' => $secret->author_id,
      'content' => $secret->content,
      'nickname' => $secret->nickname,
      'imageIds' => json_encode($secret->images)
    ]);
    if ($stmt->rowCount() > 0) {
      return $this->getById($this->db->lastInsertId());
    }
    return false;
  }

  /**
   * @param int $id The ID of the secret to get
   * @return Secret|false The secret with the given ID, or false if not found
   */
  public function getById(int $id): Secret|false
  {
    $stmt = $this->db->query("SELECT * FROM v_secret
    WHERE post_id = :id", [
      'id' => $id
    ]);
    if (!$stmt) {
      $this->logger->error('Failed to get secret by ID: ' . $this->db->lastError()->getMessage());
      return false;
    }
    $stmt->setFetchMode(PDO::FETCH_CLASS, Secret::class);
    $secret = $stmt->fetch();
    if ($secret) {
      $secret = $this->resolveSecretAttitudeStatus($secret);
      $secret = $this->resolveSecretImageUrls($secret);
    }
    return $secret;
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
