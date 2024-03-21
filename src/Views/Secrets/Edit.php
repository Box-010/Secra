<?php

/**
 * @var callable(string, array): string $render
 * @var bool $isLoggedIn
 * @var User $currentUser
 * @var Secret $secret
 */

use Secra\Models\Secret;
use Secra\Models\User;

?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <?= $render('Components/HtmlHead') ?>
  <title>编辑秘语 | 隐境 Secra</title>
</head>

<body>
<header class="header">
  <a class="button button-icon" href="./">
    <span class="icon material-symbols-outlined"> arrow_back </span>
  </a>
  <span class="header-title">编辑秘语</span>
  <div class="spacer"></div>
</header>
<main class="main">
  <div class="container">
    <?php if ($isLoggedIn) : ?>
      <?php if ($secret->author_id === $currentUser->user_id) : ?>
        <form action="./secrets/<?= $secret->post_id ?>/edit" method="post" enctype="multipart/form-data"
              id="publish-form">
          <div class="card">
            <div class="card-content">
              <textarea class="post-textarea" placeholder="What's your problem?" id="content"
                        name="content"><?= $secret->content ?></textarea>
            </div>
            <div class="card-actions">
              <div class="textfield-wrapper textfield-wrapper--dense">
                <div class="textfield">
                  <input type="text" id="nickname" name="nickname" placeholder="可选" value="<?= $secret->nickname ?>">
                  <label for="nickname">昵称</label>
                </div>
              </div>

              <div class="spacer"></div>

              <button type="submit" class="button button-primary" id="publish-btn">
                提交
              </button>
            </div>
          </div>
        </form>
      <?php else : ?>
        <div class="auth-container">
          <div class="auth-card">
            <div class="auth-card-content">
              <h1>你没有权限编辑这个秘语</h1>
              <p>只有秘语的作者才能编辑它</p>
            </div>
          </div>
        </div>
      <?php endif; ?>
    <?php else : ?>
      <div class="auth-container">
        <div class="auth-card">
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
<script src="./scripts/polyfill.min.js"></script>
<script src="./scripts/cross-fetch.js"></script>
<script src="./scripts/input.js"></script>
</body>

</html>