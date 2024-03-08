<?php

use Secra\Services\SessionService;

global $container;
$sessionService = $container->get(SessionService::class);
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>秘语详情 | 隐境 Secra</title>
    <link rel="stylesheet" href="/styles/normalize.css"/>
    <link rel="stylesheet" href="/styles/main.css"/>
    <link rel="stylesheet" href="/styles/material-symbols/index.css"/>
</head>

<body>
<header class="header">
    <a class="button button-icon" href="/">
        <span class="icon material-symbols-outlined"> arrow_back </span>
    </a>
    <span class="header-title">秘语详情</span>
    <div class="spacer"></div>
  <?php if ($sessionService->getCurrentUser()) : ?>
      <a class="button button-icon" href="/users/me" id="logged-in">
          <span class="icon material-symbols-outlined"> account_circle </span>
      </a>
  <?php else : ?>
      <a class="button button-tonal" href="/users/login" id="not-logged-in">
          <span class="material-symbols-outlined"> login </span>
          登录
      </a>
  <?php endif; ?>
</header>
<main class="main">
    <div class="container">
        <div class="top-cards">
            <div class="card item-card">
                <div class="card-content">
                    <div class="item-info">
                        <div class="item-info-text">#1</div>
                        <div class="spacer"></div>
                        <div class="item-info-text">发表于 2024/03/01 11:45:14</div>
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

          <?php if (!$sessionService->getCurrentUser()) : ?>
            <?php include(dirname(__DIR__) . '/components/LoginCard.php'); ?>
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

        <div class="item-list">
            <div class="item-list-header">
                <div class="item-list-header-title">全部回复 2</div>
            </div>

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
</main>
<footer class="footer">
    Copyright © 2024 Secra | Made with ♥️ by HuanChengFly
</footer>

<script src="/scripts/input.js"></script>
</body>

</html>