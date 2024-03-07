<?php
#[Provide(HomeController::class)]
#[Singleton]
#[Controller('/')]
class HomeController
{
  #[Get('')]
  public function index()
  {
    include_once(dirname(__DIR__) . '/views/home.php');
  }

  #[Get('install')]
  public function install()
  {
    include_once(dirname(__DIR__) . '/install.php');
  }
}
