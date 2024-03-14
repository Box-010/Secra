<?php

namespace Secra\Controllers;

use Secra\Arch\DI\Attributes\Inject;
use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Attributes\Singleton;
use Secra\Arch\Router\Attributes\Controller;
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
use Secra\Models\Secret;
use Secra\Pipes\ParseSecretsOrderColumnPipe;
use Secra\Repositories\SecretsRepository;
use Secra\Services\SessionService;


#[Provide(SecretsController::class)]
#[Singleton]
#[Controller('/secrets')]
class SecretsController extends BaseController
{
  #[Inject] private SecretsRepository $secretsRepository;
  #[Inject] private SessionService $sessionService;

  #[Get(':secretId(\d+)')]
  public function secretDetailPage(#[Param] string $secretId): void
  {
    $secret = $this->secretsRepository->getById($secretId);
    $this->templateEngine->render('Views/Detail', ['secret' => $secret]);
  }

  #[Get]
  public function secretsListPage(
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
    foreach ($secrets as $secret) {
      $this->templateEngine->render('Components/SecretCard', ['secret' => $secret, 'link' => true, 'showCommentBtn' => true]);
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
}
