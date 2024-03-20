<?php

namespace Secra\Arch\Router;

use Secra\Arch\Template\TemplateEngine;
use Secra\Components\DI\Attributes\Inject;

class BaseController
{
  #[Inject] protected TemplateEngine $templateEngine;

  public function redirect(string $url): void
  {
    header("Location: $url");
  }

  public function redirectDelay(string $url, int $delay): void
  {
    header("Refresh: $delay; url=$url");
  }

  public function json(
    array $data,
    int   $status = 200,
  ): void
  {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
  }
}
