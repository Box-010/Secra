<?php
/**
 * @var callable(string, array): string $render
 * @var bool $isLoggedIn
 * @var Secret $secret
 */

use Secra\Models\Secret;

?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <?= $render('Components/HtmlHead') ?>
  <title>秘语详情 | 隐境 Secra</title>
</head>

<body>
<header class="header">
  <a class="button button-icon" href="./">
    <span class="icon material-symbols-outlined"> arrow_back </span>
  </a>
  <span class="header-title">秘语详情</span>
  <div class="spacer"></div>
  <?php if ($isLoggedIn) : ?>
    <a class="button button-icon" href="./users/me" id="logged-in">
      <span class="icon material-symbols-outlined"> account_circle </span>
    </a>
  <?php else : ?>
    <a class="button button-tonal" href="./users/login" id="not-logged-in">
      <span class="material-symbols-outlined"> login </span>
      登录
    </a>
  <?php endif; ?>
</header>
<main class="main">
  <div class="container">
    <div class="top-cards">
      <?= $render('Components/SecretCard', ['secret' => $secret, 'link' => false, 'showCommentBtn' => false]) ?>

      <?php if (!$isLoggedIn) : ?>
        <?= $render('Components/LoginCard') ?>
      <?php else : ?>
        <div class="card" id="reply">
          <div class="card-content">
            <textarea class="post-textarea" placeholder="What's your problem?"></textarea>
          </div>
          <div class="card-actions">
            <div class="textfield-wrapper textfield-wrapper--dense">
              <div class="textfield">
                <input type="text" id="nickname" name="nickname" placeholder="可选"/>
                <label for="nickname">昵称</label>
              </div>
            </div>

            <div class="spacer"></div>

            <button class="button button-primary" id="reply-btn">回复</button>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="item-list-container">
      <div class="item-list-header">
        <div class="item-list-header-title">
          全部回复<?= $secret->comment_count > 0 ? " {$secret->comment_count}" : "" ?></div>
      </div>
      <div class="item-list">
        <div class="card item-card">
          <div class="card-content">
            <div class="item-info">
              <div class="item-info-text">#2</div>
              <div class="spacer"></div>
              <div class="item-info-text">回复于 2024/03/01 11:45:14</div>
            </div>
            <p>芝士雪豹</p>
          </div>
          <div class="card-actions">
            <button class="button button-icon">
              <span class="icon material-symbols-outlined"> thumb_up </span>
            </button>
            <button class="button button-icon">
              <span class="icon material-symbols-outlined"> thumb_down </span>
            </button>
            <button class="button button-icon">
              <span class="icon material-symbols-outlined">
                chat_bubble
              </span>
            </button>
          </div>
        </div>

        <div class="card item-card">
          <div class="card-content">
            <div class="item-info">
              <div class="item-info-text">#1</div>
              <div class="spacer"></div>
              <div class="item-info-text">回复于 2024/03/01 11:45:14</div>
            </div>
            <p>芝士雪豹</p>
          </div>
          <div class="card-actions">
            <button class="button button-icon">
              <span class="icon material-symbols-outlined"> thumb_up </span>
            </button>
            <button class="button button-icon">
              <span class="icon material-symbols-outlined"> thumb_down </span>
            </button>
            <button class="button button-icon">
              <span class="icon material-symbols-outlined">
                chat_bubble
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
<?= $render('Components/Footer') ?>

<script src="./scripts/attitudes.min.js"></script>
<script src="./scripts/input.js"></script>
<script src="./scripts/dropdown.js"></script>
</body>

</html>