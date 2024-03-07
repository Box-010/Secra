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

  #[Get('publish')]
  public function publishPage()
  {
    include_once(dirname(__DIR__) . '/views/publish.php');
  }
}
