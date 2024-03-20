<?php

namespace Secra\Repositories;

use Secra\Components\DI\Attributes\Provide;
use Secra\Components\DI\Attributes\Singleton;
use Secra\Constants\AttitudeableType;
use Secra\Constants\AttitudeType;

#[Provide(AttitudesRepository::class)]
#[Singleton]
class AttitudesRepository extends BaseRepository
{
  public function count(
    AttitudeableType $attitudeableType,
    int              $attitudeableId,
    AttitudeType     $attitudeType
  ): int|false
  {
    $stmt = $this->db->query(
      "SELECT COUNT(*) FROM attitudes
      WHERE attitudeable_type = :attitudeableType
      AND attitudeable_id = :attitudeableId
      AND attitude_type = :attitudeType",
      [
        'attitudeableType' => $attitudeableType->value,
        'attitudeableId' => $attitudeableId,
        'attitudeType' => $attitudeType->value
      ]
    );

    if (!$stmt) {
      return false;
    }

    return $stmt->fetchColumn();
  }

  public function queryUserAttitude(
    AttitudeableType $attitudeableType,
    int              $attitudeableId,
    int              $userId
  ): AttitudeType|false
  {
    $stmt = $this->db->query(
      "SELECT attitude_type FROM attitudes
      WHERE attitudeable_type = :attitudeableType
      AND attitudeable_id = :attitudeableId
      AND user_id = :userId",
      [
        'attitudeableType' => $attitudeableType->value,
        'attitudeableId' => $attitudeableId,
        'userId' => $userId
      ]
    );

    if (!$stmt) {
      return false;
    }

    $attitudeType = $stmt->fetchColumn();

    if ($attitudeType === false) {
      return AttitudeType::NEUTRAL;
    }

    return AttitudeType::from($attitudeType);
  }

  public function create(
    AttitudeableType $attitudeableType,
    int              $attitudeableId,
    AttitudeType     $attitudeType,
    int              $userId
  ): bool
  {
    $stmt = $this->db->query(
      "INSERT INTO attitudes (attitudeable_type, attitudeable_id, attitude_type, user_id)
      VALUES (:attitudeableType, :attitudeableId, :attitudeType, :userId)",
      [
        'attitudeableType' => $attitudeableType->value,
        'attitudeableId' => $attitudeableId,
        'attitudeType' => $attitudeType->value,
        'userId' => $userId
      ]
    );

    return $stmt !== false && $stmt->rowCount() > 0;
  }

  public function delete(
    AttitudeableType $attitudeableType,
    int              $attitudeableId,
    int              $userId
  ): bool
  {
    $stmt = $this->db->query(
      "DELETE FROM attitudes
      WHERE attitudeable_type = :attitudeableType
      AND attitudeable_id = :attitudeableId
      AND user_id = :userId",
      [
        'attitudeableType' => $attitudeableType->value,
        'attitudeableId' => $attitudeableId,
        'userId' => $userId
      ]
    );

    return $stmt !== false && $stmt->rowCount() > 0;
  }
}
