<?php

namespace Secra\Arch\Template;

use RuntimeException;
use Secra\Components\DI\Attributes\Singleton;

#[Singleton]
class TemplateEngine
{
  /**
   * @param string $templateDir Path to the directory containing the templates
   * @param array<string, callable|mixed> $globalData Global data that will be available in all templates
   */
  public function __construct(
    public string $templateDir,
    public array  $globalData = []
  )
  {
  }

  private function resolveGlobalData(array $data): array
  {
    $resolved = [];
    foreach ($data as $key => $value) {
      if (is_callable($value)) {
        $resolved[$key] = $value();
      } else {
        $resolved[$key] = $value;
      }
    }
    return $resolved;
  }

  public function parse(string $template, array $data = []): string
  {
    $template = $this->templateDir . '/' . $template;
    if (str_ends_with($template, '.php') === false) {
      $template .= '.php';
    }
    if (file_exists($template) === false) {
      throw new RuntimeException("Template file not found: $template");
    }

    $resolvedData = [...$this->resolveGlobalData($this->globalData), ...$data];

    extract($resolvedData);

    // Provide render function to the template and pass all the data
    $render = fn(string $template, array $d = []) => $this->parse($template, [...$resolvedData, ...$d]);

    ob_start();
    include $template;
    return ob_get_clean();
  }

  public function render(string $template, array $data = []): void
  {
    echo $this->parse($template, $data);
  }
}
