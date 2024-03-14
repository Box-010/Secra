<?php

namespace Secra\Arch\Router\Pipes;

/**
 * @template I
 * @template O
 */
interface Pipe
{
  /**
   * @param I $input
   * @return O
   */
  public function transform($input);
}