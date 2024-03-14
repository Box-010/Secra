<?php

namespace Secra\Services;

use Exception;
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
    if (isset($_SESSION['session_id'])) {
      // There is a active session, just validate it
      if ($this->currentSession = $this->validateSession($_SESSION['session_id'])) {
        $this->currentUser = $this->userRepository->getUserById($this->currentSession->user_id);
      }
    } elseif (isset($_COOKIE['session_id'])) {
      // There is a session id in the cookie, validate and rotate it
      if ($this->currentSession = $this->validateSession($_COOKIE['session_id'])) {
        $this->currentUser = $this->userRepository->getUserById($this->currentSession->user_id);
        $this->rotateCurrentSession();
      }
    }
  }

  /**
   * Rotates the current session and changes the session id to enhance security
   */
  private function rotateCurrentSession(): void
  {
    if (!$this->getCurrentSession()) {
      return;
    }
    $current_session_id = $this->getCurrentSession()->session_id;
    $this->sessionRepository->delete($current_session_id);

    $new_session_id = session_create_id();
    $new_session = new Session();
    $new_session->session_id = $new_session_id;
    $new_session->user_id = $this->getCurrentSession()->user_id;
    $new_session->expires_at = $this->getCurrentSession()->expires_at;

    $this->currentSession = $this->sessionRepository->save($new_session);
    $expires_at = strtotime($this->getCurrentSession()->expires_at);

    setcookie('session_id', $new_session_id, $expires_at, '/', '', false, true);
    $_SESSION['session_id'] = $new_session_id;

    $this->logger->info('Rotated session id from ' . $current_session_id . ' to ' . $new_session_id);
  }

  public function isUserLoggedIn(): bool
  {
    return $this->currentUser !== null;
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
      if ($oldSession && $oldSession->user_id === $user->user_id) {
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
    $session = $this->sessionRepository->save($session);

    if (!$session) {
      throw new Exception('Failed to create session');
    }

    setcookie('session_id', $session->session_id, strtotime('+30 day'), '/', '', false, true);

    return $session;
  }

  public function destroyCurrentSession(): void
  {
    $session_id = $_COOKIE['session_id'] ?? $_SESSION['session_id'] ?? null;
    if ($session_id) {
      $this->sessionRepository->delete($session_id);
    }
    setcookie('session_id', '', time() - 3600, '/', '', false, true);
    session_destroy();
    $this->currentSession = null;
    $this->currentUser = null;
  }
}
