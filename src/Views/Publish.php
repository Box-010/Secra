<?php
/**
 * @var callable(string, array): string $render
 * @var bool $isLoggedIn
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <?= $render('Components/HtmlHead') ?>
  <title>发布秘语 | 隐境 Secra</title>
  <style>
      .insert-image input {
          display: none;
      }

      .post-textarea {
          min-height: 12rem;
          resize: vertical;
      }
  </style>
</head>

<body>
<header class="header">
  <a class="button button-icon" href="./">
    <span class="icon material-symbols-outlined"> arrow_back </span>
  </a>
  <span class="header-title">发布秘语</span>
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
      <?php if ($isLoggedIn) : ?>
        <form action="./secrets" method="post" enctype="multipart/form-data" id="publish-form">
          <div class="card" id="publish">
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
  </div>
</main>
<?= $render('Components/Footer') ?>

<script src="./scripts/input.js"></script>
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