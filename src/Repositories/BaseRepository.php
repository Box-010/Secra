<?php

namespace Secra\Repositories;

use Secra\Arch\DI\Attributes\Inject;
use Secra\Arch\Logger\ILogger;
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