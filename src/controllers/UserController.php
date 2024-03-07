<?php
#[Provide(UserController::class)]
#[Singleton]
#[Controller('/users')]
class UserController
{
  #[Inject] private UserRepository $userRepository;
  #[Inject] private SessionService $sessionService;
  #[Inject] private Logger $logger;

  private function get_pdo_exception_message(PDOException $e)
  {
    $code = $e->getCode();
    if ($code === '23000') {
      return 'User already exists';
    }
    $this->logger->error($e->getMessage());
    return 'Internal error';
  }

  private function location(string $location, string|null $error = null)
  {
    if ($error) {
      $error = urlencode($error);
      header("Location: {$location}?error={$error}");
    } else {
      header("Location: {$location}");
    }
  }

  #[Get('register')]
  public function registerPage()
  {
    require_once(dirname(__DIR__) . '/views/register.php');
  }

  #[Post('register')]
  public function register()
  {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
      $this->location('/users/register', 'Invalid email');
      return;
    }

    $username = htmlspecialchars($_POST['username']);

    if ($this->userRepository->getUserByUsername($username)) {
      $this->location('/users/register', 'Username already taken');
      return;
    }

    if ($this->userRepository->getUserByEmail($email)) {
      $this->location('/users/register', 'Email already used');
      return;
    }

    $user = new User();
    $user->user_name = $username;
    $user->email = $email;

    $randomSalt = bin2hex(random_bytes(32));
    $user->password = password_hash($_POST['password'] . $randomSalt, PASSWORD_DEFAULT);
    $user->salt = $randomSalt;

    $this->userRepository->save($user);
    $this->sessionService->createSession($user);
    $this->location('/');
  }

  #[Get('login')]
  public function loginPage()
  {
    require_once(dirname(__DIR__) . '/views/login.php');
  }

  #[Post('login')]
  public function login()
  {
    $user = $this->userRepository->getUserByUsername($_POST['username']);
    if (!$user || !password_verify($_POST['password'] . $user->salt, $user->password)) {
      $this->location('/users/login', 'Invalid username or password');
      return;
    }

    $this->sessionService->createSession($user);
    $this->location('/');
  }
}
