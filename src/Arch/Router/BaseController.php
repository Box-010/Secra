<?php

namespace Secra\Arch\Router;

use Secra\Arch\DI\Attributes\Inject;
use Secra\Arch\Template\TemplateEngine;

class BaseController
{
  #[Inject] protected TemplateEngine $templateEngine;

  public function redirect(string $url): void
  {
    header("Location: $url");
    exit;
  }

  public function redirectDelay(string $url, int $delay): void
  {
    header("Refresh: $delay; url=$url");
    exit;
  }
}