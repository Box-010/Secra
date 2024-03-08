<?php

namespace Secra\Controllers;

use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Attributes\Singleton;
use Secra\Arch\Router\Attributes\Controller;
use Secra\Arch\Router\Attributes\Get;
use Secra\Arch\Router\Attributes\Param;


#[Provide(SecretsController::class)]
#[Singleton]
#[Controller('/secrets')]
class SecretsController
{
  #[Get(':secretId(\d+)')]
  public function secretDetailPage(#[Param] string $secretId)
  {
    include(dirname(__DIR__) . '/Views/detail.php');
  }
}
