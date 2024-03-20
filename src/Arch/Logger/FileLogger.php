<?php

namespace Secra\Arch\Logger;

use Secra\Components\DI\Attributes\Provide;
use Secra\Components\DI\Attributes\Singleton;


#[Provide(ILogger::class)]
#[Singleton]
class FileLogger implements ILogger
{
  public function __construct(
    private string   $logFile,
    private LogLevel $logLevel = LogLevel::INFO
  )
  {
  }

  public function fatal(string $message)
  {
    $this->log(LogLevel::FATAL, $message);
  }

  private function log(LogLevel $level, string $message)
  {
    if ($level->value <= $this->logLevel->value) {
      $logMessage = '[' . date('Y-m-d H:i:s') . '] ' . $level->name . ': ' . $message . PHP_EOL;
      $this->writeLog($logMessage);
    }
  }

  private function writeLog(string $message)
  {
    if (!file_exists(dirname($this->logFile))) {
      mkdir(dirname($this->logFile), 0777, true);
    }
    if (!file_exists($this->logFile)) {
      file_put_contents($this->logFile, '');
    }
    file_put_contents($this->logFile, $message, FILE_APPEND);
  }

  public function error(string $message)
  {
    $this->log(LogLevel::ERROR, $message);
  }

  public function warn(string $message)
  {
    $this->log(LogLevel::WARN, $message);
  }

  public function info(string $message)
  {
    $this->log(LogLevel::INFO, $message);
  }

  public function debug(string $message)
  {
    $this->log(LogLevel::DEBUG, $message);
  }

  public function trace(string $message)
  {
    $this->log(LogLevel::TRACE, $message);
  }
}
