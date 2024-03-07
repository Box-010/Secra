<?php
#[Provide(SessionService::class)]
#[Singleton]
class SessionService
{
  private User|null $currentUser = null;
  private Session|null $currentSession = null;

  public function __construct(
    #[Inject] private SessionRepository $sessionRepository,
    #[Inject] private UserRepository $userRepository,
    #[Inject] private Logger $logger
  ) {
    session_start();
    $session_id = $_COOKIE['session_id'] ?? $_SESSION['session_id'] ?? null;
    if ($session_id) {
      $this->currentSession = $this->validateSession($session_id);
      if ($this->currentSession) {
        $this->currentUser = $this->userRepository->getUserById($this->currentSession->user_id);
      }
    }
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

    session_start();

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

  public function deleteSession(string $session_id)
  {
    session_start();
    session_destroy();
    setcookie('session_id', '', time() - 3600, '/', '', false, true);
    $this->sessionRepository->delete($session_id);
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
}
