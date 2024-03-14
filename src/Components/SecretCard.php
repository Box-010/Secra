<?php
/**
 * @var callable $render
 * @var Secret $secret
 * @var bool $link
 * @var bool $showCommentBtn
 */

use Secra\Constants\AttitudeType;
use Secra\Models\Secret;

$cardTag = $link ? 'a' : 'div';
?>
<<?= $cardTag ?> class="card item-card"<?php if ($link) : ?> href="./secrets/<?= $secret->post_id ?>"<?php endif; ?>>
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
</div>
<div class="card-actions">
  <button class="button attitude-button" data-attitude-type="positive" data-attitudeable-type="secrets"
          data-attitudeable-id="<?= $secret->post_id ?>" data-count="<?= $secret->positive_count ?>"
          data-attituded="<?= $secret->user_attitude === AttitudeType::POSITIVE ? 1 : 0 ?>">
    <span class="icon material-symbols-outlined"> thumb_up </span>
    <span class="attitude-button-count">
    <?php if ($secret->positive_count) : ?>
      <?= $secret->positive_count ?>
    <?php endif; ?>
    </span>
  </button>
  <button class="button attitude-button" data-attitude-type="negative" data-attitudeable-type="secrets"
          data-attitudeable-id="<?= $secret->post_id ?>" data-count="<?= $secret->negative_count ?>"
          data-attituded="<?= $secret->user_attitude === AttitudeType::NEGATIVE ? 1 : 0 ?>">
    <span class="icon material-symbols-outlined"> thumb_down </span>
    <span class="attitude-button-count">
    <?php if ($secret->negative_count) : ?>
      <?= $secret->negative_count ?>
    <?php endif; ?>
    </span>
  </button>
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
</div>
</<?= $cardTag ?>>