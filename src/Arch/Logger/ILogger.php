<?php

namespace Secra\Arch\Logger;


interface ILogger
{
  public function fatal(string $message);

  public function error(string $message);

  public function warn(string $message);

  public function info(string $message);

  public function debug(string $message);

  public function trace(string $message);
}
