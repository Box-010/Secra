<?php

/**
 * @var callable $render
 * @var Comment $comment
 * @var User $currentUser
 * @var bool $isAdmin
 */

use Secra\Constants\AttitudeableType;
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
    <?= $render('Components/AttitudeButton', [
      'attitudeableType' => AttitudeableType::COMMENTS,
      'attitudeType' => AttitudeType::POSITIVE,
      'attitudeableId' => $comment->comment_id,
      'count' => $comment->positive_count,
      'attituded' => $comment->user_attitude === AttitudeType::POSITIVE,
    ]) ?>
    <?= $render('Components/AttitudeButton', [
      'attitudeableType' => AttitudeableType::COMMENTS,
      'attitudeType' => AttitudeType::NEGATIVE,
      'attitudeableId' => $comment->comment_id,
      'count' => $comment->negative_count,
      'attituded' => $comment->user_attitude === AttitudeType::NEGATIVE,
    ]) ?>
    <!--        <button class="button">-->
    <!--          <span class="icon material-symbols-outlined">-->
    <!--            chat_bubble-->
    <!--          </span>-->
    <!--                --><?php //if ($comment->comment_count) : 
    ?>
    <!--                  --><?php //= $comment->comment_count 
    ?>
    <!--                --><?php //endif; 
    ?>
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