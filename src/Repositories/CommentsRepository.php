<?php

namespace Secra\Repositories;

use PDO;
use Secra\Components\DI\Attributes\Inject;
use Secra\Components\DI\Attributes\Provide;
use Secra\Components\DI\Attributes\Singleton;
use Secra\Constants\AttitudeableType;
use Secra\Constants\CommentsOrderColumn;
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
      $comment->user_attitude = $this->attitudesRepository->queryUserAttitude(AttitudeableType::COMMENTS, $comment->comment_id, $userId);
    }
    return $comment;
  }

  /**
   * @param CommentsOrderColumn $orderBy Order by column
   * @param bool $desc Is descending order
   */
  public function getAllComments(
    CommentsOrderColumn $orderBy = CommentsOrderColumn::CREATED_AT,
    bool               $desc = true,
  ) : array
  {
    $order = $desc ? 'DESC' : 'ASC';
    $stmt = $this->db->query("SELECT
      comments.*, u.user_id, u.user_name, u.email AS user_email, u.created_at AS user_created_at,
       (SELECT COUNT(*) FROM comments WHERE comments.parent_coment_id = comments.comment_id) AS comment_count,
       (SELECT COUNT(*) FROM attitudes WHERE attitudes.attitudeable_type = 'comments' AND attitudes.attitude_type = 'positive' AND attitudes.attitudeable_id = comments.comment_id) AS positive_count,
        (SELECT COUNT(*) FROM attitudes WHERE attitudes.attitudeable_type = 'comments' AND attitudes.attitude_type = 'negative' AND attitudes.attitudeable_id = comments.comment_id) AS negative_count
    FROM comments
    INNER JOIN users u on comments.user_id = u.user_id
    ORDER BY {$orderBy->value} $order;");
    if (!$stmt) {
      $this->logger->error('Failed to get comments: ' . $this->db->lastError()->getMessage());
      return [];
    }
    $stmt->setFetchMode(PDO::FETCH_CLASS, Comment::class);
    $comments = $stmt->fetchAll();

    return $this->resolveCommentsAttitudeStatus($comments);
  }

  public function countAllComments() : int
  {
    $stmt = $this->db->query("SELECT COUNT(*) FROM comments;");
    if(!$stmt){
      $this->logger->error('Failed to get comments: ' . $this->db->lastError()->getMessage());
      return 0;
    }
    return $stmt->fetchColumn();
  }

  public function getCommentById(int $id) : Comment|false
  {
    $stmt = $this->db->query("SELECT
      comments.*, u.user_id, u.user_name, u.email AS user_email, u.created_at AS user_created_at,
      (SELECT COUNT(*) FROM comments WHERE comments.parent_coment_id = comments.comment_id) AS comment_count,
      (SELECT COUNT(*) FROM attitudes WHERE attitudes.attitudeable_type = 'comments' AND attitudes.attitude_type = 'positive' AND attitudes.attitudeable_id = comments.comment_id) AS positive_count,
      (SELECT COUNT(*) FROM attitudes WHERE attitudes.attitudeable_type = 'comments' AND attitudes.attitude_type = 'negative' AND attitudes.attitudeable_id = comments.comment_id) AS negative_count
    FROM comments
    INNER JOIN users u on comments.user_id = u.user_id
    WHERE comment_id = :id;", ['id' => $id]);
    if(!$stmt){
      $this->logger->error('Failed to get comments: ' . $this->db->lastError()->getMessage());
      return false;
    }
    $stmt->setFetchMode(PDO::FETCH_CLASS, Comment::class);
    return $this->resolveCommentAttitudeStatus($stmt->fetch());
  }

  public function saveComment(Comment $comment) : Comment|false
  {
    $stmt = $this->db->query('INSERT INTO comments (comment_id, user_id, content, parent_id) VALUES (:commentId, :content, :nickname, :parent_id)', [
      'commentId' => $comment->comment_id,
      'content' => $comment->content,
      'nickname' => $comment->nickname,
      'parent_id' => $comment->parent_id,
    ]);
    if ($stmt->rowCount() > 0) {
      return $this->getCommentById($this->db->lastInsertId());
    }
    return false;
  }

  public function deleteComment(int $comment_id) : bool
  {
    $stmt = $this->db->query('DELETE FROM comments WHERE comment_id = :commentId', ['commentId' => $comment_id]);
    return $stmt->rowCount() > 0;
  }


  public function updateComment(Comment $comment) : bool
  {
    $stmt = $this->db->query("UPDATE comments SET content = :content, nickname = :nickname, parent_id = :parentId WHERE comment_id = :commentId", [
      'content' => $comment->content,
      'commentId' => $comment->comment_id,
      'nickname' => $comment->nickname,
      'parentId' => $comment->parent_id
    ]);
    return $stmt->rowCount() > 0;
  }
}
