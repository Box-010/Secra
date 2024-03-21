<?php

namespace Secra\Controllers;

use PDOException;
use Secra\Arch\DI\Attributes\Inject;
use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Attributes\Singleton;
use Secra\Arch\Logger\ILogger;
use Secra\Arch\Router\Attributes\Controller;
use Secra\Arch\Router\Attributes\FormData;
use Secra\Arch\Router\Attributes\Get;
use Secra\Arch\Router\Attributes\Header;
use Secra\Arch\Router\Attributes\Post;
use Secra\Arch\Router\BaseController;
use Secra\Models\User;
use Secra\Repositories\CommentsRepository;
use Secra\Repositories\SecretsRepository;
use Secra\Repositories\UserRepository;
use Secra\Services\CaptchaService;
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
  #[Inject] private CaptchaService $captchaService;
  #[Inject] private ILogger $logger;

  #[Get('register')]
  public function registerPage(): void
  {
    $this->templateEngine->render('Views/Users/Register');
  }

  #[Post('register')]
  public function register(): void
  {
    $redirect = $_POST['redirect'] ?? '';

    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

    $username = htmlspecialchars($_POST['username']);

    if ($this->userRepository->getUserByUsername($username)) {
      $this->location(PUBLIC_ROOT . 'users/register', 'Username already taken');
      return;
    }

    if ($this->userRepository->getUserByEmail($email)) {
      $this->location(PUBLIC_ROOT . 'users/register', 'Email already used');
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
    $this->location(PUBLIC_ROOT . $redirect);
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

  private function loginJson(
    string $username,
    string $password,
    string $captchaType,
    string $lotNumber,
    string $passToken,
    string $genTime,
    string $captchaOutput,
  ): void
  {
    $captchaResult = match ($captchaType) {
      "geetest4" => $this->captchaService->validateGeeTest4("953b873286a0f857dc5b78d114c3eb3b", "be31e986e0cc1c48e4a9141cb604abea", $lotNumber, $passToken, $genTime, $captchaOutput),
      default => false
    };

    if (!$captchaResult) {
      $this->json(["success" => false, "message" => "Invalid captcha"]);
      return;
    }

    $user = $this->userRepository->getUserByUsername($username);
    if (!$user || !password_verify($password . $user->salt, $user->password)) {
      $this->json(["success" => false, "message" => "Invalid username or password"]);
      return;
    }

    $this->sessionService->createSession($user);
    $this->json(['success' => true]);
  }

  private function loginHtml(
    string $username,
    string $password,
    string $redirect,
    string $captchaType,
    string $lotNumber,
    string $passToken,
    string $genTime,
    string $captchaOutput,
  ): void
  {
    $captchaResult = match ($captchaType) {
      "geetest4" => $this->captchaService->validateGeeTest4("953b873286a0f857dc5b78d114c3eb3b", "be31e986e0cc1c48e4a9141cb604abea", $lotNumber, $passToken, $genTime, $captchaOutput),
      default => false
    };

    if (!$captchaResult) {
      $this->json(["success" => false, "message" => "Invalid captcha"]);
      return;
    }

    $user = $this->userRepository->getUserByUsername($username);
    if (!$user || !password_verify($password . $user->salt, $user->password)) {
      $this->location(PUBLIC_ROOT . 'users/login', 'Invalid username or password');
      return;
    }

    $this->sessionService->createSession($user);
    $this->location(PUBLIC_ROOT . $redirect);
  }

  #[Post('login')]
  public function login(
    #[Header('Accept')] string|null      $accept,
    #[FormData('username')] string       $username,
    #[FormData('password')] string       $password,
    #[FormData('captcha_type')] string   $captchaType,
    #[FormData('lot_number')] string     $lotNumber,
    #[FormData('pass_token')] string     $passToken,
    #[FormData('gen_time')] string       $genTime,
    #[FormData('captcha_output')] string $captchaOutput,
    #[FormData('redirect')] string       $redirect = '',
  ): void
  {
    if ($accept === 'application/json') {
      $this->loginJson($username, $password, $captchaType, $lotNumber, $passToken, $genTime, $captchaOutput);
    } else {
      $this->loginHtml($username, $password, $redirect, $captchaType, $lotNumber, $passToken, $genTime, $captchaOutput);
    }
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

  #[Get('changepassword')]
  public function changePasswordPage(): void
  {
    $this->templateEngine->render('Views/Users/ChangePassword');
  }

  #[Post('changepassword')]
  public function changePassword(): void
  {
    $user = $this->userRepository->getUserByUsername($this->sessionService->getCurrentUser()->user_name);
    if (!$user || !password_verify($_POST['oldpassword'] . $user->salt, $user->password)) {
      $this->location(PUBLIC_ROOT . 'users/changepassword', 'Invalid password');
      return;
    }
    $randomSalt = bin2hex(random_bytes(32));
    $user->password = password_hash($_POST['newpassword'] . $randomSalt, PASSWORD_DEFAULT);
    $user->salt = $randomSalt;
    $this->userRepository->update($user);
    $this->sessionService->destroyAllSessions();
    //弹出修改成功
    $this->location(PUBLIC_ROOT . 'users/login', '密码修改成功，请重新登录');
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
}
