<?php
/**
 * @var callable $render
 * @var Secret $secret
 * @var bool $link
 * @var bool $showCommentBtn
 */

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
  <button class="button button-icon">
    <span class="icon material-symbols-outlined"> thumb_up </span>
    <?php if ($secret->like_count) : ?>
      <?= $secret->like_count ?>
    <?php endif; ?>
  </button>
  <button class="button button-icon">
    <span class="icon material-symbols-outlined"> thumb_down </span>
    <?php if ($secret->dislike_count) : ?>
      <?= $secret->dislike_count ?>
    <?php endif; ?>
  </button>
  <?php if ($showCommentBtn) : ?>
    <button class="button button-icon">
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