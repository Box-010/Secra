<?php

/**
 * @var callable(string, array): string $render
 * @var bool $isLoggedIn
 * @var User $currentUser
 * @var Secret[] $mySecrets
 * @var int $secretCount
 * @var int $commentCount
 */

use Secra\Models\Secret;
use Secra\Models\User;

?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <?= $render('Components/HtmlHead') ?>
  <title>我的隐境 | 隐境 Secra</title>
</head>

<body>
  <header class="header">
    <a class="button button-icon" href="./">
      <span class="icon material-symbols-outlined"> arrow_back </span>
    </a>
    <span class="header-title">我的隐境</span>
    <div class="spacer"></div>
    <a class="button button-icon" href="./users/changepassword">
      <span class="icon material-symbols-outlined"> settings </span>
    </a>
    <a class="button button-icon" href="./users/logout">
      <span class="icon material-symbols-outlined"> exit_to_app </span>
    </a>
  </header>
  <main class="main">
    <div class="container">
      <?php if ($isLoggedIn) : ?>
        <div class="top-cards">
          <div class="stats-grid card">
            <div class="stats-item">
              <div class="stats-item-title">秘语</div>
              <div class="stats-item-value"><?= $secretCount ?></div>
            </div>
            <div class="stats-item">
              <div class="stats-item-title">评论</div>
              <div class="stats-item-value"><?= $commentCount ?></div>
            </div>
          </div>
        </div>

        <div class="item-list-container">
          <div class="item-list-header">
            <div class="item-list-header-title">我的秘语</div>
          </div>
          <div class="item-list" <?= empty($mySecrets) ? ' data-empty="1"' : '' ?>>
            <?php foreach ($mySecrets as $secret) : ?>
              <?= $render('Components/SecretCard', ['secret' => $secret, 'link' => true, 'showCommentBtn' => true]); ?>
            <?php endforeach; ?>
            <?= $render('Components/EmptyTip') ?>
          </div>
        </div>
      <?php else : ?>
        <div class="auth-container">
          <div class="auth-card">
            <div class="auth-card-image" id="auth-card-image"></div>
            <div class="auth-card-content">
              <h1>请先登录</h1>
              <a href="./users/login?redirect=users/me" class="button button-primary">登录</a>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </main>
  <?= $render('Components/Footer') ?>
  <script src="./scripts/attitudes.min.js"></script>
</body>

</html>