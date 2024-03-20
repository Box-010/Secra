<?php

namespace Secra\Controllers;

use Secra\Arch\Router\Attributes\Controller;
use Secra\Arch\Router\Attributes\Get;
use Secra\Arch\Router\BaseController;
use Secra\Components\DI\Attributes\Inject;
use Secra\Components\DI\Attributes\Provide;
use Secra\Components\DI\Attributes\Singleton;
use Secra\Repositories\SecretsRepository;
use Secra\Services\SessionService;


#[Provide(HomeController::class)]
#[Singleton]
#[Controller('/')]
class HomeController extends BaseController
{
  #[Inject] private SecretsRepository $secretsRepository;
  #[Inject] private SessionService $sessionService;

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

  #[Get('')]
  public function homePage(): void
  {
    $currentUser = $this->sessionService->getCurrentUser();
    $welcomeMessage = $this->getWelcomeMessage($currentUser?->user_name);
    $secrets = $this->secretsRepository->getAll();

    $this->templateEngine->render('Views/Home', [
      'welcomeMessage' => $welcomeMessage,
      'secrets' => $secrets,
    ]);
  }

  #[Get('publish')]
  public function publishPage(): void
  {
    $this->templateEngine->render('Views/Publish');
  }
}
