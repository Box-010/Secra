<?php

namespace Secra\Repositories;

use PDO;
use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Attributes\Singleton;
use Secra\Models\Session;

#[Provide(SessionRepository::class)]
#[Singleton]
class SessionRepository extends BaseRepository
{
  public function getSessionById(string $session_id): Session|bool
  {
    $stmt = $this->db->query("SELECT sessions.*, users.user_name
    FROM sessions
    INNER JOIN users ON sessions.user_id = users.user_id WHERE session_id = ?;", [$session_id]);
    $stmt->setFetchMode(PDO::FETCH_CLASS, Session::class);
    return $stmt->fetch();
  }

  /**
   * @param Session $session The session to save
   * @return Session|false Returns the saved session if it was saved successfully, otherwise false
   */
  public function save(Session $session): Session|false
  {
    $stmt = $this->db->query('INSERT INTO sessions (session_id, user_id, expires_at) VALUES (?, ?, ?)', [
      $session->session_id,
      $session->user_id,
      // $session->issued_at,
      $session->expires_at,
    ]);
    if ($stmt->rowCount() > 0) {
      return $this->getSessionById($session->session_id);
    }
    return false;
  }

  public function update(Session $session): void
  {
    $this->db->query('UPDATE sessions SET user_id = ?, issued_at = ?, expires_at = ? WHERE session_id = ?', [
      $session->user_id,
      $session->issued_at,
      $session->expires_at,
      $session->session_id,
    ]);
  }

  public function delete(string $session_id): void
  {
    $this->db->query('DELETE FROM sessions WHERE session_id = ?', [$session_id]);
  }

  public function deleteAllByUserId(int $user_id): int
  {
    $stmt = $this->db->query('DELETE FROM sessions WHERE user_id = ?', [$user_id]);
    return $stmt->rowCount();
  }
}
