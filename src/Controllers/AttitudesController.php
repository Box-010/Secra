<?php

namespace Secra\Controllers;

use Secra\Arch\Router\Attributes\Controller;
use Secra\Arch\Router\Attributes\FormData;
use Secra\Arch\Router\Attributes\Param;
use Secra\Arch\Router\Attributes\Pipes;
use Secra\Arch\Router\Attributes\Post;
use Secra\Arch\Router\BaseController;
use Secra\Arch\Router\Pipes\ParseIntPipe;
use Secra\Components\DI\Attributes\Inject;
use Secra\Components\DI\Attributes\Provide;
use Secra\Components\DI\Attributes\Singleton;
use Secra\Constants\AttitudeableType;
use Secra\Constants\AttitudeType;
use Secra\Pipes\ParseAttitudeableTypePipe;
use Secra\Pipes\ParseAttitudeTypePipe;
use Secra\Repositories\AttitudesRepository;
use Secra\Services\SessionService;

#[Provide(AttitudesController::class)]
#[Singleton]
#[Controller('/attitudes')]
class AttitudesController extends BaseController
{
  #[Inject] private AttitudesRepository $attitudesRepository;
  #[Inject] private SessionService $sessionService;

  #[Post(':attitudeableType(secrets|comments)/:attitudeableId(\d+)')]
  public function newAttitude(
    #[Param]
    #[Pipes([ParseAttitudeableTypePipe::class])]
    AttitudeableType $attitudeableType,
    #[Param]
    #[Pipes([ParseIntPipe::class])]
    int              $attitudeableId,
    #[FormData("attitude_type")]
    #[Pipes([ParseAttitudeTypePipe::class])]
    AttitudeType     $attitudeType
  )
  {
    if (!$this->sessionService->isUserLoggedIn()) {
      $this->json(
        ["success" => false, "message" => "You must be logged in to do that"],
        401
      );
      return;
    }

    $userId = $this->sessionService->getCurrentUser()->user_id;
    $currentAttitudeType = $this->attitudesRepository->queryUserAttitude($attitudeableType, $attitudeableId, $userId);
    if (!$currentAttitudeType) {
      $this->json(
        ["success" => false, "message" => "Failed to attitude"],
        500
      );
      return;
    }
    if ($currentAttitudeType === $attitudeType) {
      $this->json(
        ["success" => false, "message" => "You already have this attitude"],
        400
      );
      return;
    }
    if ($currentAttitudeType !== AttitudeType::NEUTRAL && !$this->attitudesRepository->delete($attitudeableType, $attitudeableId, $userId)) {
      $this->json(
        ["success" => false, "message" => "Failed to attitude"],
        500
      );
      return;
    }
    if ($attitudeType !== AttitudeType::NEUTRAL && !$this->attitudesRepository->create($attitudeableType, $attitudeableId, $attitudeType, $userId)) {
      $this->json(
        ["success" => false, "message" => "Failed to attitude"],
        500
      );
    }

    $newPositiveAttitudeCount = $this->attitudesRepository->count($attitudeableType, $attitudeableId, AttitudeType::POSITIVE);
    $newNegativeAttitudeCount = $this->attitudesRepository->count($attitudeableType, $attitudeableId, AttitudeType::NEGATIVE);
    $this->json(
      [
        "success" => true,
        "message" => "Successfully attituded",
        "data" => [
          "positive_count" => $newPositiveAttitudeCount,
          "negative_count" => $newNegativeAttitudeCount,
          "attitude_type" => $attitudeType
        ]
      ]
    );
  }
}
