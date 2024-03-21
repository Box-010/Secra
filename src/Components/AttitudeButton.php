<?php

/**
 * @var AttitudeableType $attitudeableType
 * @var AttitudeType $attitudeType
 * @var int $attitudeableId
 * @var int $count
 * @var int $attituded
 */

use Secra\Constants\AttitudeableType;
use Secra\Constants\AttitudeType;

?>
<button class="button attitude-button" data-attitude-type="<?= $attitudeType->value ?>"
        data-attitudeable-type="<?= $attitudeableType->value ?>" data-attitudeable-id="<?= $attitudeableId ?>"
        data-count="<?= $count ?>" data-attituded="<?= $attituded ? 1 : 0 ?>">
  <span class="icon material-symbols-outlined">
    <?php if ($attitudeType === AttitudeType::POSITIVE) : ?>
      thumb_up
    <?php elseif ($attitudeType === AttitudeType::NEGATIVE) : ?>
      thumb_down
    <?php endif; ?>
  </span>
  <span class="attitude-button-count">
    <?php if ($count > 0) : ?>
      <?= $count ?>
    <?php endif; ?>
  </span>
</button>