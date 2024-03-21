<?php
/**
 * @var callable(string, array): string $render
 * @var bool $isLoggedIn
 * @var Secret $secret
 * @var Comment[] $comments
 * @var bool $hasMore
 * @var int $commentCount
 */

use Secra\Models\Comment;
use Secra\Models\Secret;

?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <?= $render('Components/HtmlHead') ?>
  <title>秘语详情 | 隐境 Secra</title>
</head>

<body>
<div style="display: none;" id="secretId" data-secret-id="<?= $secret->post_id ?>"></div>
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
      <?= $render('Components/SecretCard', ['secret' => $secret, 'link' => false, 'showCommentBtn' => false]); ?>
      <?php if ($isLoggedIn) : ?>
        <form action="./secrets/<?= $secret->post_id ?>/comments" method="post" enctype="multipart/form-data"
              id="reply-form">
          <div class="card">
            <div class="card-content">
              <textarea class="post-textarea" placeholder="What's your problem?" id="content" name="content"></textarea>
            </div>
            <div class="card-actions">
              <div class="textfield-wrapper textfield-wrapper--dense">
                <div class="textfield">
                  <input type="text" id="nickname" name="nickname" placeholder="可选"/>
                  <label for="nickname">昵称</label>
                </div>
              </div>

              <div class="spacer"></div>

              <!--              <div class="badge">-->
              <!--                <div class="insert-image">-->
              <!--                  <input type="file" id="image-input" name="image" accept="image/*" multiple/>-->
              <!--                  <label class="button button-icon" for="image-input">-->
              <!--                    <span class="icon material-symbols-outlined">-->
              <!--                      add_photo_alternate-->
              <!--                    </span>-->
              <!--                  </label>-->
              <!--                </div>-->
              <!--              </div>-->
              <button class="button button-primary" id="reply-btn">
                回复
              </button>
            </div>
          </div>
        </form>
      <?php else : ?>
        <div class="auth-container">
          <div class="auth-card">
            <div class="auth-card-image" id="auth-card-image"></div>
            <div class="auth-card-content">
              <h1>请先登录</h1>
              <a href="./users/login?redirect=/publish" class="button button-primary">登录</a>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="item-list-container">
      <div class="item-list-header">
        <div class="item-list-header-title">全部回复 <?= $commentCount ?></div>
        <div class="spacer"></div>
        <!--        <div class="button-group sort-type-select">-->
        <!--          <button class="button button-active">新发</button>-->
        <!--          <button class="button">新回</button>-->
        <!--        </div>-->
      </div>

      <div class="item-list" id="comments-list"<?= empty($comments) ? ' data-empty="1"' : '' ?>>
        <?php foreach ($comments as $comment) : ?>
          <?= $render('Components/CommentCard', ['comment' => $comment]); ?>
        <?php endforeach; ?>
        <?php if ($hasMore) : ?>
          <?= $render('Components/LoadMoreIndicator', ['url' => "./secrets/$secret->post_id/comments?page=2"]); ?>
        <?php endif; ?>
        <?= $render('Components/EmptyTip') ?>
      </div>
    </div>
  </div>
</main>
<?= $render('Components/Footer') ?>

<script src="./scripts/attitudes.min.js"></script>
<script src="./scripts/input.js"></script>
<script src="./scripts/dropdown.js"></script>
<script src="./scripts/load-more.js"></script>
<script>
    const secretId = parseInt(document.getElementById("secretId").dataset.secretId);

    const replyFormEl = document.getElementById("reply-form");

    function submitReply(event) {
        event.preventDefault();
        const formEl = document.getElementById("reply-form");
        const content = formEl.querySelector("#content").value;
        const nickname = formEl.querySelector("#nickname").value;
        const formData = new FormData();
        formData.append("content", content);
        formData.append("nickname", nickname);
        fetch(`./secrets/${secretId}/comments`, {
            method: "POST",
            body: formData,
            headers: {
                'Accept': 'application/json',
            }
        })
            .then(response => {
                switch (response.status) {
                    case 201:
                        return response.json();
                    case 401:
                        alert("请先登录");
                        window.location.href = "./users/login";
                        break;
                    default:
                        return response.json().then(data => {
                            throw new Error(data.message);
                        });
                }
            })
            .then(data => {
                if (data.success) {
                    refresh();
                    formEl.querySelector("#content").value = "";
                    formEl.querySelector("#nickname").value = "";
                    // imageInputEl.value = "";
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                alert(error.message);
            });
    }

    replyFormEl.addEventListener("submit", submitReply);

    function refresh() {
        fetch(`./secrets/${secretId}/comments`)
            .then(response => response.text())
            .then(secretsHtml => {
                const itemListEl = document.getElementById("comments-list");
                itemListEl.innerHTML = secretsHtml;
                window.initAttitudes();
                window.initLoadMore();
                window.initDropdown();
            });
    }
</script>
</body>

</html>
