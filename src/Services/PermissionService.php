<?php

namespace Secra\Services;

use Secra\Arch\DI\Attributes\Inject;
use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Attributes\Singleton;

#[Provide(PermissionService::class)]
#[Singleton]
class PermissionService
{
  #[Inject] private SessionService $sessionService;

  public function hasRole(string $role): bool
  {
    if (!$this->sessionService->isUserLoggedIn()) {
      return false;
    }
    return in_array($role, $this->sessionService->getCurrentUser()->user_roles);
  }
}