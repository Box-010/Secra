<?php
/**
 * @var callable $render
 * @var Comment $comment
 * @var User $currentUser
 * @var bool $isAdmin
 */

use Secra\Constants\AttitudeType;
use Secra\Models\Comment;
use Secra\Models\User;

?>
<div class="card item-card" data-item-type="comments" data-item-id="<?= $comment->comment_id ?>">
  <div class="card-content">
    <div class="item-info">
      <div class="item-info-text">#<?= $comment->floor ?></div>
      <?php if ($comment->nickname) : ?>
        <div class="item-info-text"><?= $comment->nickname ?></div>
      <?php endif; ?>
      <div class="spacer"></div>
      <div class="item-info-text">回复于 <?= $comment->created_at ?></div>
    </div>
    <div class="post-content">
      <?= $comment->content ?>
    </div>
  </div>
  <div class="card-actions">
    <button class="button attitude-button" data-attitude-type="positive" data-attitudeable-type="comments"
            data-attitudeable-id="<?= $comment->post_id ?>" data-count="<?= $comment->positive_count ?>"
            data-attituded="<?= $comment->user_attitude === AttitudeType::POSITIVE ? 1 : 0 ?>">
      <span class="icon material-symbols-outlined"> thumb_up </span>
      <span class="attitude-button-count">
    <?php if ($comment->positive_count) : ?>
      <?= $comment->positive_count ?>
    <?php endif; ?>
    </span>
    </button>
    <button class="button attitude-button" data-attitude-type="negative" data-attitudeable-type="comments"
            data-attitudeable-id="<?= $comment->post_id ?>" data-count="<?= $comment->negative_count ?>"
            data-attituded="<?= $comment->user_attitude === AttitudeType::NEGATIVE ? 1 : 0 ?>">
      <span class="icon material-symbols-outlined"> thumb_down </span>
      <span class="attitude-button-count">
    <?php if ($comment->negative_count) : ?>
      <?= $comment->negative_count ?>
    <?php endif; ?>
    </span>
    </button>
    <!--        <button class="button">-->
    <!--          <span class="icon material-symbols-outlined">-->
    <!--            chat_bubble-->
    <!--          </span>-->
    <!--                --><?php //if ($comment->comment_count) : ?>
    <!--                  --><?php //= $comment->comment_count ?>
    <!--                --><?php //endif; ?>
    <!--        </button>-->
    <?php if ($isAdmin || ($currentUser->user_id === $comment->user_id)) : ?>
      <button class="button button-icon" id="secret-popup-btn-<?= $comment->comment_id ?>">
      <span class="icon material-symbols-outlined">
        more_vert
      </span>
      </button>
      <div class="dropdown-menu" data-activator="#secret-popup-btn-<?= $comment->comment_id ?>">
        <?php if ($currentUser->user_id === $comment->user_id) : ?>
          <div class="dropdown-item" data-action-type="edit-comment"
               data-action-data="<?= $comment->post_id ?>/<?= $comment->comment_id ?>">
            编辑
          </div>
        <?php endif; ?>
        <div class="dropdown-item" data-action-type="delete-comment"
             data-action-data="<?= $comment->post_id ?>/<?= $comment->comment_id ?>">
          删除
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>