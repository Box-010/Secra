<?php

namespace Secra\Controllers;

use Secra\Arch\DI\Attributes\Inject;
use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Attributes\Singleton;
use Secra\Arch\Router\Attributes\Controller;
use Secra\Arch\Router\Attributes\Delete;
use Secra\Arch\Router\Attributes\FormData;
use Secra\Arch\Router\Attributes\Get;
use Secra\Arch\Router\Attributes\Header;
use Secra\Arch\Router\Attributes\Param;
use Secra\Arch\Router\Attributes\Pipes;
use Secra\Arch\Router\Attributes\Post;
use Secra\Arch\Router\Attributes\Query;
use Secra\Arch\Router\BaseController;
use Secra\Arch\Router\Pipes\ParseIntPipe;
use Secra\Constants\SecretsOrderColumn;
use Secra\Models\Comment;
use Secra\Models\Secret;
use Secra\Pipes\ParseSecretsOrderColumnPipe;
use Secra\Repositories\CommentsRepository;
use Secra\Repositories\SecretsRepository;
use Secra\Services\PermissionService;
use Secra\Services\SessionService;


#[Provide(SecretsController::class)]
#[Singleton]
#[Controller('/secrets')]
class SecretsController extends BaseController
{
  #[Inject] private SecretsRepository $secretsRepository;
  #[Inject] private CommentsRepository $commentsRepository;
  #[Inject] private SessionService $sessionService;
  #[Inject] private PermissionService $permissionService;

  #[Get(':secretId(\d+)')]
  public function secretDetailPage(#[Param] string $secretId): void
  {
    $secret = $this->secretsRepository->getById($secretId);
    $comments = $this->commentsRepository->getCommentsByPostId($secretId);
    $commentCount = $this->commentsRepository->countCommentsByPostId($secretId);
    $hasMore = $commentCount > count($comments);
    $this->templateEngine->render('Views/Secrets/Detail', ['secret' => $secret, 'comments' => $comments, 'hasMore' => $hasMore, 'commentCount' => $commentCount]);
  }

  #[Delete(':secretId(\d+)')]
  public function deleteSecret(#[Param] string $secretId): void
  {
    if (!$this->sessionService->isUserLoggedIn()) {
      $this->json(
        ["success" => false, "message" => "You must be logged in to delete a secret"],
        401
      );
      return;
    }
    $secret = $this->secretsRepository->getById($secretId);
    if (!$this->permissionService->hasRole("admin") && $secret->author_id !== $this->sessionService->getCurrentUser()->user_id) {
      $this->json(
        ["success" => false, "message" => "You are not authorized to delete this secret"],
        403
      );
      return;
    }
    if ($this->secretsRepository->delete($secretId)) {
      $this->json(
        ["success" => true, "message" => "Secret deleted successfully"]
      );
    } else {
      $this->json(
        ["success" => false, "message" => "Failed to delete secret"],
        500
      );
    }
  }

  #[Get(':secretId(\d+)/edit')]
  public function secretEditPage(#[Param] string $secretId): void
  {
    $secret = $this->secretsRepository->getById($secretId);
    $this->templateEngine->render('Views/Secrets/Edit', ['secret' => $secret]);
  }

  #[Post(':secretId(\d+)/edit')]
  public function editSecret(
    #[Param] string                            $secretId,
    #[FormData("content")] string              $content,
    #[FormData("nickname", false)] string|null $nickname = null,
  ): void
  {
    if (!$this->sessionService->isUserLoggedIn()) {
      $this->redirect(PUBLIC_ROOT . "users/login");
      return;
    }
    $secret = $this->secretsRepository->getById($secretId);
    if ($secret->author_id !== $this->sessionService->getCurrentUser()->user_id) {
      $this->redirect(PUBLIC_ROOT . "secrets/$secret->post_id");
      return;
    }
    $encoded_content = htmlentities($content, ENT_QUOTES);
    $encoded_nickname = empty($nickname) ? null : htmlentities($nickname, ENT_QUOTES);
    $secret->content = $encoded_content;
    $secret->nickname = $encoded_nickname;
    if ($this->secretsRepository->update($secret)) {
      $this->redirect(PUBLIC_ROOT . "secrets/$secret->post_id");
    } else {
      $this->redirectDelay(PUBLIC_ROOT . "secrets/$secret->post_id/edit", 3);
      echo "Failed to update secret";
    }
  }

  #[Get]
  public function secretsList(
    #[Query("order_by")]
    #[Pipes([ParseSecretsOrderColumnPipe::class])]
    SecretsOrderColumn       $orderBy = SecretsOrderColumn::CREATED_AT,
    #[Query("order")] string $order = "desc",
    #[Query("page_size")]
    #[Pipes([ParseIntPipe::class])]
    int                      $pageSize = 10,
    #[Query("page")]
    #[Pipes([ParseIntPipe::class])]
    int                      $page = 1
  ): void
  {
    $order = strtolower($order);
    $offset = ($page - 1) * $pageSize;
    $desc = $order !== "asc";
    $secrets = $this->secretsRepository->getAll($orderBy, $desc, $pageSize, $offset);
    $secretCount = $this->secretsRepository->countAll();
    foreach ($secrets as $secret) {
      $this->templateEngine->render('Components/SecretCard', ['secret' => $secret, 'link' => true, 'showCommentBtn' => true]);
    }
    if ($secretCount > $offset + $pageSize) {
      $this->templateEngine->render('Components/LoadMoreIndicator', ['url' => "./secrets?order_by=" . strtolower($orderBy->name) . "&order={$order}&page_size={$pageSize}&page=" . ($page + 1)]);
    }
  }

  #[Get('search')]
  public function searchSecrets(
    #[Query("q")] string     $query,
    #[Query("order_by")]
    #[Pipes([ParseSecretsOrderColumnPipe::class])]
    SecretsOrderColumn       $orderBy = SecretsOrderColumn::CREATED_AT,
    #[Query("order")] string $order = "desc",
    #[Query("page_size")]
    #[Pipes([ParseIntPipe::class])]
    int                      $pageSize = 10,
    #[Query("page")]
    #[Pipes([ParseIntPipe::class])]
    int                      $page = 1
  ): void
  {
    $order = strtolower($order);
    $offset = ($page - 1) * $pageSize;
    $desc = $order !== "asc";
    $secrets = $this->secretsRepository->search($query, $orderBy, $desc, $pageSize, $offset);
    $secretCount = $this->secretsRepository->countBySearchQuery($query);
    foreach ($secrets as $secret) {
      $this->templateEngine->render('Components/SecretCard', ['secret' => $secret, 'link' => true, 'showCommentBtn' => true]);
    }
    if ($secretCount > $offset + $pageSize) {
      $this->templateEngine->render('Components/LoadMoreIndicator', ['url' => "./secrets/search?q={$query}&order_by=" . strtolower($orderBy->name) . "&order={$order}&page_size={$pageSize}&page=" . ($page + 1)]);
    }
  }

  #[Post]
  public function createSecret(
    #[Header('Accept')] string|null            $accept,
    #[FormData("content")] string              $content,
    #[FormData("nickname", false)] string|null $nickname = null,
  ): void
  {
    if ($accept === "application/json") {
      $this->createSecretJson($content, $nickname);
    } else {
      $this->createSecretHtml($content, $nickname);
    }
  }

  private function createSecretJson(string $content, ?string $nickname): void
  {
    if (!$this->sessionService->isUserLoggedIn()) {
      $this->json(
        ["success" => false, "message" => "You must be logged in to publish a secret"],
        401
      );
      return;
    }
    if (empty($content)) {
      $this->json(
        ["success" => false, "message" => "Content cannot be empty"],
        400
      );
      return;
    }
    $encoded_content = htmlentities($content, ENT_QUOTES);
    $encoded_nickname = empty($nickname) ? null : htmlentities($nickname, ENT_QUOTES);
    $secret = new Secret();
    $secret->author_id = $this->sessionService->getCurrentUser()->user_id;
    $secret->content = $encoded_content;
    $secret->nickname = $encoded_nickname;
    if ($secret = $this->secretsRepository->save($secret)) {
      $this->json(
        [
          "success" => true,
          "message" => "Secret saved successfully",
          "data" => [
            "secret" => $secret
          ]
        ],
        201
      );
    } else {
      $this->json(
        ["success" => false, "message" => "Failed to publish secret"],
        500
      );
    }
  }

  private function createSecretHtml(string $content, ?string $nickname): void
  {
    if (!$this->sessionService->isUserLoggedIn()) {
      $this->redirectDelay(PUBLIC_ROOT . "users/login", 3);
      echo "You must be logged in to publish a secret";
      return;
    }
    $referer = $_SERVER['HTTP_REFERER'];
    if (empty($content)) {
      $this->redirectDelay($referer, 3);
      echo "Content cannot be empty";
      return;
    }
    $encoded_content = htmlentities($content, ENT_QUOTES);
    $encoded_nickname = empty($nickname) ? null : htmlentities($nickname, ENT_QUOTES);
    $secret = new Secret();
    $secret->author_id = $this->sessionService->getCurrentUser()->user_id;
    $secret->content = $encoded_content;
    $secret->nickname = $encoded_nickname;
    if ($secret = $this->secretsRepository->save($secret)) {
      $this->redirect(PUBLIC_ROOT . "secrets/{$secret->post_id}");
    } else {
      $this->redirectDelay($referer, 3);
      echo "Failed to publish secret";
    }
  }

  #[Post(':secretId(\d+)/comments')]
  public function createComment(
    #[Param]
    #[Pipes([ParseIntPipe::class])]
    int                                        $secretId,
    #[Header('Accept')] string|null            $accept,
    #[FormData("content")] string              $content,
    #[FormData("nickname", false)] string|null $nickname = null,
  ): void
  {
    if ($accept === "application/json") {
      $this->createCommentJson($secretId, $content, $nickname);
    } else {
      $this->createCommentHtml($secretId, $content, $nickname);
    }
  }

  private function createCommentJson(
    int     $secretId,
    string  $content,
    ?string $nickname
  ): void
  {
    if (!$this->sessionService->isUserLoggedIn()) {
      $this->json(
        ["success" => false, "message" => "You must be logged in to publish a comment"],
        401
      );
      return;
    }
    if (empty($content)) {
      $this->json(
        ["success" => false, "message" => "Content cannot be empty"],
        400
      );
      return;
    }
    $encoded_content = htmlentities($content, ENT_QUOTES);
    $encoded_nickname = empty($nickname) ? null : htmlentities($nickname, ENT_QUOTES);
    $comment = new Comment();
    $comment->user_id = $this->sessionService->getCurrentUser()->user_id;
    $comment->post_id = $secretId;
    $comment->content = $encoded_content;
    $comment->nickname = $encoded_nickname;
    if ($comment = $this->commentsRepository->save($comment)) {
      $this->json(
        [
          "success" => true,
          "message" => "Comment saved successfully",
          "data" => [
            "comment" => $comment
          ]
        ],
        201
      );
    } else {
      $this->json(
        ["success" => false, "message" => "Failed to publish comment"],
        500
      );
    }
  }

  private function createCommentHtml(
    int     $secretId,
    string  $content,
    ?string $nickname
  ): void
  {
    if (!$this->sessionService->isUserLoggedIn()) {
      $this->redirectDelay(PUBLIC_ROOT . "users/login", 3);
      echo "You must be logged in to publish a comment";
      return;
    }
    $referer = $_SERVER['HTTP_REFERER'];
    if (empty($content)) {
      $this->redirectDelay($referer, 3);
      echo "Content cannot be empty";
      return;
    }
    $encoded_content = htmlentities($content, ENT_QUOTES);
    $encoded_nickname = empty($nickname) ? null : htmlentities($nickname, ENT_QUOTES);
    $comment = new Comment();
    $comment->user_id = $this->sessionService->getCurrentUser()->user_id;
    $comment->post_id = $secretId;
    $comment->content = $encoded_content;
    $comment->nickname = $encoded_nickname;
    if ($comment = $this->commentsRepository->save($comment)) {
      $this->redirect($referer);
    } else {
      $this->redirectDelay($referer, 3);
      echo "Failed to publish comment";
    }
  }

  #[Get(':secretId(\d+)/comments')]
  public function commentsList(
    #[Param]
    #[Pipes([ParseIntPipe::class])]
    int $secretId,
    #[Query("page_size")]
    #[Pipes([ParseIntPipe::class])]
    int $pageSize = 10,
    #[Query("page")]
    #[Pipes([ParseIntPipe::class])]
    int $page = 1
  ): void
  {
    $offset = ($page - 1) * $pageSize;
    $comments = $this->commentsRepository->getCommentsByPostId($secretId, $pageSize, $offset);
    $commentCount = $this->commentsRepository->countCommentsByPostId($secretId);
    $hasMore = $commentCount > $offset + $pageSize;
    foreach ($comments as $comment) {
      $this->templateEngine->render('Components/CommentCard', ['comment' => $comment]);
    }
    if ($hasMore) {
      $this->templateEngine->render('Components/LoadMoreIndicator', ['url' => "./secrets/{$secretId}/comments?page_size={$pageSize}&page=" . ($page + 1)]);
    }
  }

  #[Get(':secretId(\d+)/comments/:commentId(\d+)/edit')]
  public function commentEditPage(
    #[Param]
    #[Pipes([ParseIntPipe::class])]
    int $secretId,
    #[Param]
    #[Pipes([ParseIntPipe::class])]
    int $commentId
  ): void
  {
    $secret = $this->secretsRepository->getById($secretId);
    $comment = $this->commentsRepository->getCommentById($commentId);
    $this->templateEngine->render('Views/Comments/Edit', ['secret' => $secret, 'comment' => $comment]);
  }

  #[Post(':secretId(\d+)/comments/:commentId(\d+)/edit')]
  public function editComment(
    #[Param]
    #[Pipes([ParseIntPipe::class])]
    int                                        $secretId,
    #[Param]
    #[Pipes([ParseIntPipe::class])]
    int                                        $commentId,
    #[FormData("content")] string              $content,
    #[FormData("nickname", false)] string|null $nickname = null,
  ): void
  {
    if (!$this->sessionService->isUserLoggedIn()) {
      $this->redirect(PUBLIC_ROOT . "users/login");
      return;
    }
    $comment = $this->commentsRepository->getCommentById($commentId);
    if ($comment->user_id !== $this->sessionService->getCurrentUser()->user_id) {
      $this->redirect(PUBLIC_ROOT . "secrets/$secretId");
      return;
    }
    $encoded_content = htmlentities($content, ENT_QUOTES);
    $encoded_nickname = empty($nickname) ? null : htmlentities($nickname, ENT_QUOTES);
    $comment->content = $encoded_content;
    $comment->nickname = $encoded_nickname;
    if ($this->commentsRepository->update($comment)) {
      $this->redirect(PUBLIC_ROOT . "secrets/$secretId");
    } else {
      $this->redirectDelay(PUBLIC_ROOT . "secrets/$secretId", 3);
      echo "Failed to update comment";
    }
  }

  #[Delete(':secretId(\d+)/comments/:commentId(\d+)')]
  public function deleteComment(
    #[Param]
    #[Pipes([ParseIntPipe::class])]
    int $secretId,
    #[Param]
    #[Pipes([ParseIntPipe::class])]
    int $commentId
  ): void
  {
    if (!$this->sessionService->isUserLoggedIn()) {
      $this->json(
        ["success" => false, "message" => "You must be logged in to delete a comment"],
        401
      );
      return;
    }
    $comment = $this->commentsRepository->getCommentById($commentId);
    if (!$this->permissionService->hasRole("admin") && $comment->user_id !== $this->sessionService->getCurrentUser()->user_id) {
      $this->json(
        ["success" => false, "message" => "You are not authorized to delete this comment"],
        403
      );
      return;
    }
    if ($this->commentsRepository->delete($commentId)) {
      $this->json(
        ["success" => true, "message" => "Comment deleted successfully"]
      );
    } else {
      $this->json(
        ["success" => false, "message" => "Failed to delete comment"],
        500
      );
    }
  }
}
