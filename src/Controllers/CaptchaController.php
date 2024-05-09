<?php

namespace Secra\Controllers;

use Exception;
use Secra\Arch\DI\Attributes\Inject;
use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Attributes\Singleton;
use Secra\Arch\Router\Attributes\Controller;
use Secra\Arch\Router\Attributes\FormData;
use Secra\Arch\Router\Attributes\Post;
use Secra\Arch\Router\BaseController;
use Secra\Services\CaptchaService;

#[Provide(CaptchaController::class)]
#[Singleton]
#[Controller('/captcha')]
class CaptchaController extends BaseController
{
  #[Inject] private CaptchaService $captchaService;

  #[Post]
  public function generateCaptchaBase64(
    #[FormData('id')] $id = 'captcha',
  )
  {
    try {
      $imageBase64 = $this->captchaService->generateCaptchaBase64($id);
      $this->json([
        "success" => true,
        "data" => $imageBase64
      ]);
    } catch (Exception $e) {
      $this->json([
        "success" => false,
        "message" => $e->getMessage()
      ]);
    }
  }
}