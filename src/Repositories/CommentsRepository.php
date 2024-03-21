<?php

namespace Secra\Repositories;

use PDO;
use Secra\Arch\DI\Attributes\Inject;
use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Attributes\Singleton;
use Secra\Constants\AttitudeableType;
use Secra\Models\Comment;
use Secra\Services\SessionService;

#[Provide(CommentsRepository::class)]
#[Singleton]
class CommentsRepository extends BaseRepository
{
  #[Inject] private AttitudesRepository $attitudesRepository;
  #[Inject] private SessionService $sessionService;

  private function resolveCommentsAttitudeStatus(array $comments): array
  {
    return array_map(fn(Comment $comment) => $this->resolveCommentAttitudeStatus($comment), $comments);
  }

  private function resolveCommentAttitudeStatus(Comment $comment): Comment
  {
    if ($this->sessionService->isUserLoggedIn()) {
      $userId = $this->sessionService->getCurrentSession()->user_id;
      $comment->user_attitude = $this->attitudesRepository->queryUserAttitude(AttitudeableType::COMMENTS, $comment->post_id, $userId);
    }
    return $comment;
  }

  public function getCommentsByPostId(
    int $post_id,
    int $limit = 10,
    int $offset = 0
  ): array
  {
    $stmt = $this->db->query("SELECT
        comments.*, u.user_id, u.user_name, u.email AS user_email, u.created_at AS user_created_at,
      (SELECT COUNT(*) FROM attitudes WHERE attitudes.attitudeable_type = 'comments' AND attitudes.attitude_type = 'positive' AND attitudes.attitudeable_id = comments.post_id) AS positive_count,
      (SELECT COUNT(*) FROM attitudes WHERE attitudes.attitudeable_type = 'comments' AND attitudes.attitude_type = 'negative' AND attitudes.attitudeable_id = comments.post_id) AS negative_count
    FROM comments
    INNER JOIN users u ON comments.user_id = u.user_id
    WHERE comments.post_id = ?
    ORDER BY comments.created_at DESC
    LIMIT $limit OFFSET $offset;", [$post_id]);
    $stmt->setFetchMode(PDO::FETCH_CLASS, Comment::class);
    return $this->resolveCommentsAttitudeStatus($stmt->fetchAll());
  }

  public function countCommentsByPostId(int $post_id): int
  {
    $stmt = $this->db->query("SELECT COUNT(*) FROM comments WHERE post_id = ?", [$post_id]);
    return $stmt->fetchColumn();
  }

  public function countCommentsByUserId(int $user_id): int
  {
    $stmt = $this->db->query("SELECT COUNT(*) FROM comments WHERE user_id = ?", [$user_id]);
    return $stmt->fetchColumn();
  }

  public function save(Comment $comment): Comment|false
  {
    $stmt = $this->db->query('INSERT INTO comments (post_id, user_id, content, nickname, parent_comment_id) VALUES (?, ?, ?, ?, ?)', [
      $comment->post_id,
      $comment->user_id,
      $comment->content,
      $comment->nickname,
      $comment->parent_comment_id
    ]);
    if ($stmt->rowCount() > 0) {
      return $this->getCommentById($this->db->lastInsertId());
    }
    return false;
  }

  public function getCommentById(int $comment_id): Comment|bool
  {
    $stmt = $this->db->query("SELECT
      comments.*, u.user_id, u.user_name, u.email AS user_email, u.created_at AS user_created_at,
      (SELECT COUNT(*) FROM attitudes WHERE attitudes.attitudeable_type = 'comments' AND attitudes.attitude_type = 'positive' AND attitudes.attitudeable_id = comments.post_id) AS positive_count,
      (SELECT COUNT(*) FROM attitudes WHERE attitudes.attitudeable_type = 'comments' AND attitudes.attitude_type = 'negative' AND attitudes.attitudeable_id = comments.post_id) AS negative_count
    FROM comments
    INNER JOIN users u on comments.user_id = u.user_id
    WHERE comments.comment_id = ?;", [$comment_id]);
    $stmt->setFetchMode(PDO::FETCH_CLASS, Comment::class);
    return $this->resolveCommentAttitudeStatus($stmt->fetch());
  }

  public function update(Comment $comment): bool
  {
    $stmt = $this->db->query('UPDATE comments SET user_id = ?, content = ?, nickname = ? WHERE comment_id = ?', [
      $comment->user_id,
      $comment->content,
      $comment->nickname,
      $comment->comment_id,
    ]);
    return $stmt->rowCount() > 0;
  }

  public function delete(int $comment_id): bool
  {
    $stmt = $this->db->query('DELETE FROM comments WHERE comment_id = ?', [$comment_id]);
    return $stmt->rowCount() > 0;
  }
}
