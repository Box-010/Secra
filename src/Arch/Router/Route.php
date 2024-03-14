<?php

namespace Secra\Arch\Router;

use Secra\Arch\Router\Models\MatchResult;


class Route
{
  public function __construct(
    public string $controller,
    public string $method,
    public string $basePath,
    public string $fullPath,
    public array  $pathPatternItems,
  )
  {
  }

  /**
   * @param string $path
   * @return MatchResult
   */
  public function match(string $path): MatchResult
  {
    if ($path === '') {
      if ($this->fullPath === '') {
        return new MatchResult($this, true, []);
      }
      return new MatchResult($this, false, []);
    }
    $regex = $this->generateRegex();
    $isMatch = preg_match($regex, $path, $matches);
    if ($isMatch) {
      $params = [];
      foreach ($this->pathPatternItems as $pathPatternItem) {
        if ($pathPatternItem->isDynamicParam) {
          $params[$pathPatternItem->dynamicParam->name] = $matches[$pathPatternItem->dynamicParam->index + 1];
        }
      }
      return new MatchResult($this, true, $params);
    }
    return new MatchResult($this, false, []);
  }

  private function generateRegex()
  {
    $regex = '/^[\/]{0,1}';
    foreach ($this->pathPatternItems as $index => $pathPatternItem) {
      if ($index > 0) {
        $regex .= '\/';
      }
      if ($pathPatternItem->isDynamicParam) {
        if ($pathPatternItem->dynamicParam->hasPattern) {
          $regex .= '(' . $pathPatternItem->dynamicParam->pattern . ')';
        } else {
          $regex .= '([^\/]+)';
        }
      } else {
        $regex .= $pathPatternItem->name;
      }
    }
    return $regex . '$/i';
  }
}
