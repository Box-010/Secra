<?php

/**
 * @var callable $render
 * @var Secret $secret
 * @var bool $link
 * @var bool $showCommentBtn
 * @var User $currentUser
 * @var bool $isAdmin
 */

use Secra\Constants\AttitudeableType;
use Secra\Constants\AttitudeType;
use Secra\Models\Secret;
use Secra\Models\User;

$cardTag = $link ? 'a' : 'div';
?>
<<?= $cardTag ?> class="card item-card" <?php if ($link) : ?> href="./secrets/<?= $secret->post_id ?>" <?php endif; ?> data-item-id="<?= $secret->post_id ?>" data-item-type="secrets">
<div class="card-content">
  <div class="item-info">
    <div class="item-info-text">#<?= $secret->post_id ?></div>
    <?php if ($secret->nickname) : ?>
      <div class="item-info-text"><?= $secret->nickname ?></div>
    <?php endif; ?>
    <div class="spacer"></div>
    <div class="item-info-text">发表于 <?= $secret->created_at ?></div>
  </div>
  <div class="post-content">
    <?= $secret->content ?>
  </div>
  <?php if (!empty($secret->image_urls)) : ?>
    <div class="preview-images-container">
      <?php foreach ($secret->image_urls as $image) : ?>
        <div class="preview">
          <img src="<?= $image ?>" alt="图片" class="preview-image">
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<div class="card-actions">
  <?= $render('Components/AttitudeButton', [
    'attitudeableType' => AttitudeableType::SECRETS,
    'attitudeType' => AttitudeType::POSITIVE,
    'attitudeableId' => $secret->post_id,
    'count' => $secret->positive_count,
    'attituded' => $secret->user_attitude === AttitudeType::POSITIVE,
  ]) ?>
  <?= $render('Components/AttitudeButton', [
    'attitudeableType' => AttitudeableType::SECRETS,
    'attitudeType' => AttitudeType::NEGATIVE,
    'attitudeableId' => $secret->post_id,
    'count' => $secret->negative_count,
    'attituded' => $secret->user_attitude === AttitudeType::NEGATIVE,
  ]) ?>
  <?php if ($showCommentBtn) : ?>
    <button class="button">
        <span class="icon material-symbols-outlined">
          chat_bubble
        </span>
      <?php if ($secret->comment_count) : ?>
        <?= $secret->comment_count ?>
      <?php endif; ?>
    </button>
  <?php endif; ?>
  <?php if ($isAdmin || ($currentUser->user_id === $secret->author_id)) : ?>
    <button class="button button-icon" id="secret-popup-btn-<?= $secret->post_id ?>">
        <span class="icon material-symbols-outlined">
          more_vert
        </span>
    </button>
    <div class="dropdown-menu" data-activator="#secret-popup-btn-<?= $secret->post_id ?>">
      <?php if ($currentUser->user_id === $secret->author_id) : ?>
        <div class="dropdown-item" data-action-type="edit-secret" data-action-data="<?= $secret->post_id ?>">
          编辑
        </div>
      <?php endif; ?>
      <div class="dropdown-item" data-action-type="delete-secret" data-action-data="<?= $secret->post_id ?>">
        删除
      </div>
    </div>
  <?php endif; ?>
</div>
</<?= $cardTag ?>>