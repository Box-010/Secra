<?php
enum LogLevel: int
{
  case FATAL = 0;
  case ERROR = 1;
  case WARN = 2;
  case INFO = 3;
  case DEBUG = 4;
  case TRACE = 5;
}

interface Logger
{
  public function fatal(string $message);

  public function error(string $message);

  public function warn(string $message);

  public function info(string $message);

  public function debug(string $message);

  public function trace(string $message);
}

#[Provide(Logger::class)]
#[Singleton]
class FileLogger implements Logger
{
  private string $logFile;
  private LogLevel $logLevel;

  public function __construct(LogLevel $logLevel = LogLevel::INFO)
  {
    $this->logFile = dirname(dirname(__DIR__)) . '/logs/app.log';
    $this->logLevel = $logLevel;
  }

  public function fatal(string $message)
  {
    $this->log(LogLevel::FATAL, $message);
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

  private function log(LogLevel $level, string $message)
  {
    if ($level->value <= $this->logLevel->value) {
      $logMessage = '[' . date('Y-m-d H:i:s') . '] ' . $level->name . ': ' . $message . PHP_EOL;
      $this->writeLog($logMessage);
    }
  }
}
