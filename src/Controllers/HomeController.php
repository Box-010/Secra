<?php

namespace Secra\Controllers;

use Secra\Arch\DI\Attributes\Inject;
use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Attributes\Singleton;
use Secra\Arch\Router\Attributes\Controller;
use Secra\Arch\Router\Attributes\Get;
use Secra\Arch\Router\Attributes\Query;
use Secra\Arch\Router\BaseController;
use Secra\Repositories\SecretsRepository;
use Secra\Services\SessionService;


#[Provide(HomeController::class)]
#[Singleton]
#[Controller('/')]
class HomeController extends BaseController
{
  #[Inject] private SecretsRepository $secretsRepository;
  #[Inject] private SessionService $sessionService;

  #[Get('')]
  public function homePage(): void
  {
    $currentUser = $this->sessionService->getCurrentUser();
    $welcomeMessage = $this->getWelcomeMessage($currentUser?->user_name);
    $secrets = $this->secretsRepository->getAll();
    $secretCount = $this->secretsRepository->countAll();
    $hasMore = $secretCount > count($secrets);

    $this->templateEngine->render('Views/Home', [
      'welcomeMessage' => $welcomeMessage,
      'secrets' => $secrets,
      'hasMore' => $hasMore
    ]);
  }

  private function getWelcomeMessage(string|null $username = null): string
  {
    $hour = date('H');
    if ($hour > 19 || $hour < 6) {
      $text = "晚上好";
    } else if ($hour > 14) {
      $text = "下午好";
    } else if ($hour > 11) {
      $text = "中午好";
    } else {
      $text = "早上好";
    }
    if ($username) {
      $text .= "，$username";
    }
    return $text;
  }

  #[Get('publish')]
  public function publishPage(): void
  {
    $this->templateEngine->render('Views/Publish');
  }

  #[Get('search')]
  public function searchPage(
    #[Query('q')] string $query
  ): void
  {
    $secrets = $this->secretsRepository->search($query);
    $secretCount = $this->secretsRepository->countBySearchQuery($query);
    $hasMore = $secretCount > count($secrets);
    $this->templateEngine->render('Views/Search', [
      'query' => $query,
      'secrets' => $secrets,
      'hasMore' => $hasMore
    ]);
  }
}
