<?php
global $container;
$sessionService = $container->get(SessionService::class);
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>我的隐境 | 隐境 Secra</title>
  <link rel="stylesheet" href="/styles/normalize.css" />
  <link rel="stylesheet" href="/styles/main.css" />
  <link rel="stylesheet" href="/styles/material-symbols/index.css" />
</head>

<body>
  <header class="header">
    <a class="button button-icon" href="/">
      <span class="icon material-symbols-outlined"> arrow_back </span>
    </a>
    <span class="header-title">我的隐境</span>
    <div class="spacer"></div>
  </header>
  <main class="main">
    <div class="container">
      <?php if ($sessionService->getCurrentUser()) : ?>
        <div class="top-cards">
          <div class="stats-grid card">
            <div class="stats-item">
              <div class="stats-item-title">帖子</div>
              <div class="stats-item-value">114</div>
            </div>
            <div class="stats-item">
              <div class="stats-item-title">评论</div>
              <div class="stats-item-value">514</div>
            </div>
            <div class="stats-item">
              <div class="stats-item-title">点赞</div>
              <div class="stats-item-value">1919810</div>
            </div>
          </div>
        </div>

        <div class="item-list">
          <div class="card item-card">
            <div class="card-content">
              <div class="item-info-text">#4 2024/03/01 11:45:14</div>
              <p>大家好啊我是说的道理，今天来点大家想看的东西啊</p>
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
              <div class="item-info-text">#2 2024/03/01 11:45:14</div>
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
              <div class="item-info-text">#1 2024/03/01 11:45:14</div>
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
      <?php else : ?>
        <div class="auth-container">
          <div class="auth-card">
            <div class="auth-card-image" id="auth-card-image"></div>
            <div class="auth-card-content">
              <h1>请先登录</h1>
              <a href="/users/login?redirect=/users/me" class="button button-primary">登录</a>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </main>
  <footer class="footer">
    Copyright © 2024 Secra | Made with ♥️ by HuanChengFly
  </footer>
</body>

</html>