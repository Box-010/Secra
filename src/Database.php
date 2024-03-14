<?php

namespace Secra;

use PDO;
use PDOStatement;
use Secra\Arch\DI\Attributes\Singleton;
use Throwable;

#[Singleton]
class Database
{
  private PDO $conn;
  private Throwable|null $lastError = null;

  public function __construct($host = DB_HOST, $port = DB_PORT, $dbname = DB_NAME, $user = DB_USER, $pass = DB_PASS)
  {
    $this->conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }

  public function query($sql, $params = []): PDOStatement|false
  {
    try {
      $stmt = $this->conn->prepare($sql);
      $stmt->execute($params);
      return $stmt;
    } catch (Throwable $e) {
      $this->lastError = $e;
      return false;
    }
  }

  public function lastInsertId(): string
  {
    return $this->conn->lastInsertId();
  }

  public function lastError(): Throwable|null
  {
    return $this->lastError;
  }
}
