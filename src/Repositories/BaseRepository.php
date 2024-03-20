<?php

namespace Secra\Repositories;

use Secra\Arch\Logger\ILogger;
use Secra\Components\DI\Attributes\Inject;
use Secra\Database;
use Throwable;

abstract class BaseRepository
{
  #[Inject] protected Database $db;
  #[Inject] protected ILogger $logger;

  public function lastError(): ?Throwable
  {
    return $this->db->lastError();
  }
}
