<?php

use Secra\Services\SessionService;

global $container;
$sessionService = $container->get(SessionService::class);

function getWelcomeText(): string
{
  $currentHour = (int)date('H');
  if ($currentHour > 19 || $currentHour < 6) {
    return "晚上好";
  } else if ($currentHour > 14) {
    return "下午好";
  } else if ($currentHour > 11) {
    return "中午好";
  } else {
    return "早上好";
  }
}

?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>隐境 Secra</title>
    <link rel="stylesheet" href="./styles/normalize.css"/>
    <link rel="stylesheet" href="./styles/main.css"/>
    <link rel="stylesheet" href="./styles/material-symbols/index.css"/>
    <style>
        .header-welcome {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .welcome-title {
            font-size: 1.8em;
            font-weight: bold;
        }

        .welcome-subtitle {
            font-size: 1.2em;
        }

        .insert-image input {
            display: none;
        }
    </style>
</head>

<body>
<header class="header">
    <span class="header-title">隐境<span class="header-title-full">&nbsp;Secra</span></span>
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
            <div class="header-welcome">
                <div class="welcome-title" id="welcome-text">
                  <?php
                  echo getWelcomeText();
                  if ($sessionService->getCurrentUser()) {
                    echo "，{$sessionService->getCurrentUser()->user_name}";
                  }
                  ?>
                </div>
                <div class="welcome-subtitle text-secondary">
                    今天你想说点什么？
                </div>
            </div>

          <?php if (!$sessionService->getCurrentUser()) : ?>
              <div class="card login-tip" id="login-tip">
                  <img src="./images/undraw_things_to_say.svg" alt="Things to say" class="login-tip-image"/>
                  <h3>加入隐境，畅享心灵自由之地</h3>
                  <p class="text-secondary">注册或登录后，即可发布秘语</p>
                  <a href="/users/register" class="button button-tonal"> 立即注册 </a>
                  <p class="caption">
                      已有账号？<a href="/users/login" class="link">立即登录</a>
                  </p>
              </div>
          <?php else : ?>
              <div class="card" id="publish">
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

                      <div class="badge">
                          <div class="insert-image">
                              <input type="file" id="image-input" name="image" accept="image/*" multiple/>
                              <label class="button button-icon" for="image-input">
                    <span class="icon material-symbols-outlined">
                      add_photo_alternate
                    </span>
                              </label>
                          </div>
                      </div>
                      <button class="button button-primary" id="publish-btn">
                          发布
                      </button>
                  </div>
              </div>
          <?php endif; ?>

            <div class="search-box">
                <input type="text" class="search-box-input" placeholder="搜索秘语"/>
                <button class="button button-icon">
                    <span class="icon material-symbols-outlined"> search </span>
                </button>
            </div>
        </div>

        <div class="item-list">
            <div class="item-list-header">
                <div class="item-list-header-title">秘语</div>
                <div class="spacer"></div>
                <div class="button-group sort-type-select">
                    <button class="button button-active">新发</button>
                    <button class="button">新回</button>
                </div>
            </div>

            <a class="card item-card" href="/secrets/2">
                <div class="card-content">
                    <div class="item-info">
                        <div class="item-info-text">#2</div>
                        <div class="spacer"></div>
                        <div class="item-info-text">发表于 2024/03/01 11:45:14</div>
                    </div>
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
            </a>

            <a class="card item-card" href="/secrets/1">
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
            </a>
        </div>
    </div>

  <?php if ($sessionService->getCurrentUser()) : ?>
      <a class="button button-fab" id="publish-fab" href="/publish">
          <span class="icon material-symbols-outlined"> edit </span>
          <span class="button-fab-text">发布</span>
      </a>
  <?php endif; ?>
</main>
<footer class="footer">
    Copyright © 2024 Secra | Made with ♥️ by HuanChengFly
</footer>

<script src="./scripts/input.js"></script>
<script src="./scripts/utils.js"></script>
<script>
    const imageInputEl = document.getElementById("image-input");
    const selectedImages = [];

    function handleFileSelect(evt) {
        const files = evt.target.files;
        for (let i = 0, f;
             (f = files[i]); i++) {
            if (!f.type.match("image.*")) {
                continue;
            }
            console.log(f);
            selectedImages.push(f);
        }
    }

    imageInputEl.addEventListener("change", handleFileSelect);
</script>
</body>

</html>