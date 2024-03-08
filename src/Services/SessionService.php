<?php

namespace Secra\Services;

use Secra\Arch\DI\Attributes\Inject;
use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Attributes\Singleton;
use Secra\Arch\Logger\ILogger;
use Secra\Models\Session;
use Secra\Models\User;
use Secra\Repositories\SessionRepository;
use Secra\Repositories\UserRepository;

#[Provide(SessionService::class)]
#[Singleton]
class SessionService
{
  private User|null $currentUser = null;
  private Session|null $currentSession = null;

  public function __construct(
    #[Inject] private SessionRepository $sessionRepository,
    #[Inject] private UserRepository    $userRepository,
    #[Inject] private ILogger           $logger
  )
  {
    session_start();
    $session_id = $_COOKIE['session_id'] ?? $_SESSION['session_id'] ?? null;
    if ($session_id) {
      $this->currentSession = $this->validateSession($session_id);
      if ($this->currentSession) {
        $this->currentUser = $this->userRepository->getUserById($this->currentSession->user_id);
      }
    }
  }

  public function validateSession(string $session_id): Session|null
  {
    $session = $this->sessionRepository->getSessionById($session_id);
    if ($session) {
      if (strtotime($session->expires_at) > time()) {
        return $session;
      }
      $this->sessionRepository->delete($session_id);
    }
    return null;
  }

  public function getCurrentUser(): User|null
  {
    return $this->currentUser;
  }

  public function getCurrentSession(): Session|null
  {
    return $this->currentSession;
  }

  public function createSession(string|User $user): Session
  {
    if (is_string($user)) {
      $user = $this->userRepository->getUserByUsername($user);
    }

    $current_session_id = $_COOKIE['session_id'] ?? $_SESSION['session_id'] ?? null;
    if ($current_session_id) {
      $oldSession = $this->sessionRepository->getSessionById($current_session_id);
      if ($oldSession) {
        $this->sessionRepository->delete($current_session_id);
      }
    }

    $session_id = session_create_id();
    $_SESSION['session_id'] = $session_id;
    $_SESSION['user'] = $user;

    $session = new Session();
    $session->session_id = $session_id;
    $session->user_id = $user->user_id;
    $session->expires_at = date('Y-m-d H:i:s', strtotime('+30 day'));
    $this->sessionRepository->save($session);

    setcookie('session_id', $session->session_id, strtotime('+30 day'), '/', '', false, true);

    return $this->sessionRepository->getSessionById($session->session_id);
  }

  public function destoryCurrentSession()
  {
    $session_id = $_COOKIE['session_id'] ?? $_SESSION['session_id'] ?? null;
    if ($session_id) {
      $this->sessionRepository->delete($session_id);
    }
    setcookie('session_id', '', time() - 3600, '/', '', false, true);
    session_destroy();
  }
}
