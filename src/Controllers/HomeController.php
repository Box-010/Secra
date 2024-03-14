<?php

namespace Secra\Controllers;

use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Attributes\Singleton;
use Secra\Arch\Router\Attributes\Controller;
use Secra\Arch\Router\Attributes\Get;


#[Provide(HomeController::class)]
#[Singleton]
#[Controller('/')]
class HomeController
{
  #[Get('')]
  public function homePage(): void
  {
    include_once(dirname(__DIR__) . '/Views/home.php');
  }

  #[Get('publish')]
  public function publishPage(): void
  {
    include_once(dirname(__DIR__) . '/Views/publish.php');
  }
}
