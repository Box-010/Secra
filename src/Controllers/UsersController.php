<?php

namespace Secra\Controllers;

use PDOException;
use Secra\Arch\DI\Attributes\Inject;
use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Attributes\Singleton;
use Secra\Arch\Logger\ILogger;
use Secra\Arch\Router\Attributes\Controller;
use Secra\Arch\Router\Attributes\Get;
use Secra\Arch\Router\Attributes\Post;
use Secra\Arch\Router\BaseController;
use Secra\Models\User;
use Secra\Repositories\CommentsRepository;
use Secra\Repositories\SecretsRepository;
use Secra\Repositories\UserRepository;
use Secra\Services\SessionService;


#[Provide(UsersController::class)]
#[Singleton]
#[Controller('/users')]
class UsersController extends BaseController
{
  #[Inject] private UserRepository $userRepository;
  #[Inject] private SecretsRepository $secretsRepository;
  #[Inject] private CommentsRepository $commentsRepository;
  #[Inject] private SessionService $sessionService;
  #[Inject] private ILogger $logger;

  #[Get('register')]
  public function registerPage(): void
  {
    $this->templateEngine->render('Views/Users/Register');
  }

  #[Post('register')]
  public function register(): void
  {
    $redirect = $_POST['redirect'] ?? '/';

    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

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

  private function location(string $location, string|null $error = null): void
  {
    if ($error) {
      $error = urlencode($error);
      $this->redirect("$location?error=$error");
    } else {
      $this->redirect($location);
    }
  }

  #[Get('login')]
  public function loginPage(): void
  {
    $this->templateEngine->render('Views/Users/Login');
  }

  #[Post('login')]
  public function login(): void
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
  public function logout(): void
  {
    $this->sessionService->destroyCurrentSession();
    $this->location('/');
  }

  #[Get('forgot-password')]
  public function forgotPasswordPage(): void
  {
    $this->templateEngine->render('Views/Users/ForgotPassword');
  }

  #[Get('me')]
  public function me(): void
  {
    if ($this->sessionService->isUserLoggedIn()) {
      $mySecrets = $this->secretsRepository->getByUserId($this->sessionService->getCurrentUserId());
      $secretCount = $this->secretsRepository->countByUserId($this->sessionService->getCurrentUserId());
      $commentCount = $this->commentsRepository->countCommentsByUserId($this->sessionService->getCurrentUserId());
    }
    $this->templateEngine->render('Views/Users/Me', [
      'mySecrets' => $mySecrets ?? [],
      'secretCount' => $secretCount ?? 0,
      'commentCount' => $commentCount ?? 0
    ]);
  }

  private function get_pdo_exception_message(PDOException $e): string
  {
    $code = $e->getCode();
    if ($code === '23000') {
      return 'User already exists';
    }
    $this->logger->error($e->getMessage());
    return 'Internal error';
  }

  #[Get('changepassword')]
  public function changepassword(): void
  {
    $this->templateEngine->render('Views/Users/ChangePassword');
  }

  #[Post('changepassword')]
  public function changepasswordPost(): void
  {
    $user = $this->userRepository->getUserByUsername($this->sessionService->getCurrentUser()->user_name);
    if (!$user || !password_verify($_POST['oldpassword'] . $user->salt, $user->password)) {
      $this->location('./users/changepassword', 'Invalid password');
      return;
    }
    $randomSalt = bin2hex(random_bytes(32));
    $user->password = password_hash($_POST['newpassword'] . $randomSalt, PASSWORD_DEFAULT);
    $user->salt = $randomSalt;
    $this->userRepository->update($user);
    $this->sessionService->destroyAllSessions();
    //弹出修改成功
    $this->location('./users/login', '密码修改成功，请重新登录');
  }
}