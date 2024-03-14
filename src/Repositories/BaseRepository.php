<?php

namespace Secra\Repositories;

use Secra\Arch\DI\Attributes\Inject;
use Secra\Arch\Logger\ILogger;
use Secra\Database;

abstract class BaseRepository
{
  #[Inject] protected Database $db;
  #[Inject] protected ILogger $logger;
}