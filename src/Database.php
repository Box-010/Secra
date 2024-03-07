<?php
require_once(__DIR__ . '/arch/DI.php');
require_once(dirname(__DIR__) . '/config/database.php');

#[Singleton]
class Database
{
  private PDO $conn;

  public function __construct($host = DB_HOST, $port = DB_PORT, $dbname = DB_NAME, $user = DB_USER, $pass = DB_PASS)
  {
    $this->conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }

  public function query($sql, $params = [])
  {
    $stmt = $this->conn->prepare($sql);
    $stmt->execute($params);
    return $stmt;
  }
}
