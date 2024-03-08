<?php

namespace Secra\Repositories;

use PDO;
use Secra\Arch\DI\Attributes\Inject;
use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Attributes\Singleton;
use Secra\Database;
use Secra\Models\Session;

#[Provide(SessionRepository::class)]
#[Singleton]
class SessionRepository
{
  #[Inject] private Database $db;

  public function getSessionById(string $session_id): Session|bool
  {
    $stmt = $this->db->query("SELECT sessions.*, users.user_name
    FROM sessions
    INNER JOIN users ON sessions.user_id = users.user_id WHERE session_id = ?;", [$session_id]);
    $stmt->setFetchMode(PDO::FETCH_CLASS, Session::class);
    return $stmt->fetch();
  }

  public function save(Session $session)
  {
    $this->db->query('INSERT INTO sessions (session_id, user_id, expires_at) VALUES (?, ?, ?)', [
      $session->session_id,
      $session->user_id,
      // $session->issued_at,
      $session->expires_at,
    ]);
  }

  public function update(Session $session)
  {
    $this->db->query('UPDATE sessions SET user_id = ?, issued_at = ?, expires_at = ? WHERE session_id = ?', [
      $session->user_id,
      $session->issued_at,
      $session->expires_at,
      $session->session_id,
    ]);
  }

  public function delete(string $session_id)
  {
    $this->db->query('DELETE FROM sessions WHERE session_id = ?', [$session_id]);
  }
}
