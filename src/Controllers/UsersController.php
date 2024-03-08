<?php

namespace Secra\Controllers;

use PDOException;
use Secra\Arch\DI\Attributes\Inject;
use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Attributes\Singleton;
use Secra\Arch\Router\Attributes\Controller;
use Secra\Arch\Router\Attributes\Get;
use Secra\Arch\Router\Attributes\Post;
use Secra\Arch\Logger\ILogger;
use Secra\Models\User;
use Secra\Repositories\UserRepository;
use Secra\Services\SessionService;


#[Provide(UsersController::class)]
#[Singleton]
#[Controller('/users')]
class UsersController
{
  #[Inject] private UserRepository $userRepository;
  #[Inject] private SessionService $sessionService;
  #[Inject] private ILogger $logger;

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
    include(dirname(__DIR__) . '/Views/users/register.php');
  }

  #[Post('register')]
  public function register()
  {
    $redirect = $_POST['redirect'] ?? '/';
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
    $this->sessionService->createSession($username);
    $this->location($redirect);
  }

  #[Get('login')]
  public function loginPage()
  {
    include(dirname(__DIR__) . '/Views/users/login.php');
  }

  #[Post('login')]
  public function login()
  {
    $redirect = $_POST['redirect'] ?? '/';
    $user = $this->userRepository->getUserByUsername($_POST['username']);
    if (!$user || !password_verify($_POST['password'] . $user->salt, $user->password)) {
      $this->location('/users/login', 'Invalid username or password');
      return;
    }

    $this->sessionService->createSession($user);
    $this->location($redirect);
  }

  #[Get('logout')]
  public function logout()
  {
    $this->sessionService->destoryCurrentSession();
    $this->location('/');
  }

  #[Get('forgot-password')]
  public function forgotPasswordPage()
  {
    include(dirname(__DIR__) . '/Views/users/forgot-password.php');
  }

  #[Get('me')]
  public function me()
  {
    include(dirname(__DIR__) . '/Views/users/me.php');
  }
}
