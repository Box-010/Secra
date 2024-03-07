<?php
#[Provide(SecretsController::class)]
#[Singleton]
#[Controller('/secrets')]
class SecretsController
{
  #[Get(':secretId(\d+)')]
  public function secretDetailPage(#[Param] string $secretId)
  {
    require_once(dirname(__DIR__) . '/views/detail.php');
  }
}
