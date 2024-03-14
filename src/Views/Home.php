<?php
/**
 * @var callable $render
 * @var bool $isLoggedIn
 * @var string $welcomeMessage
 * @var Secret[] $secrets
 */

use Secra\Models\Secret;

?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <?= $render('Components/HtmlHead') ?>
  <title>隐境 Secra</title>
</head>

<body>
<?= $render('Components/Header') ?>
<main class="main">
  <div class="container">
    <div class="top-cards">
      <div class="header-welcome">
        <div class="welcome-title" id="welcome-text">
          <?= $welcomeMessage ?>
        </div>
        <div class="welcome-subtitle text-secondary">
          今天你想说点什么？
        </div>
      </div>

      <?php if (!$isLoggedIn) : ?>
        <?= $render('Components/LoginCard') ?>
      <?php else : ?>
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
              <button type="submit" class="button button-primary" id="publish-btn">
                发布
              </button>
            </div>
          </div>
        </form>
      <?php endif; ?>

      <div class="search-box">
        <input type="text" class="search-box-input" placeholder="搜索秘语"/>
        <button class="button button-icon">
          <span class="icon material-symbols-outlined"> search </span>
        </button>
      </div>
    </div>

    <div class="item-list-container">
      <div class="item-list-header">
        <div class="item-list-header-title">秘语</div>
        <div class="spacer"></div>
        <div class="button-group sort-type-select">
          <button class="button button-active">新发</button>
          <button class="button">新回</button>
        </div>
      </div>

      <div class="item-list" id="secret-list">
        <?php
        foreach ($secrets as $secret) {
          echo $render('Components/SecretCard', ['secret' => $secret, 'link' => true, 'showCommentBtn' => true]);
        }
        ?>
      </div>
      <?php if (count($secrets) === 0) : ?>
        <?= $render('Components/EmptyTip') ?>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($isLoggedIn) : ?>
    <a class="button button-fab" id="publish-fab" href="./publish">
      <span class="icon material-symbols-outlined"> edit </span>
      <span class="button-fab-text">发布</span>
    </a>
  <?php endif; ?>
</main>
<?= $render('Components/Footer') ?>

<script src="./scripts/polyfill.min.js"></script>
<script src="./scripts/cross-fetch.js"></script>
<script src="./scripts/input.js"></script>
<script src="./scripts/utils.js"></script>
<script src="./scripts/attitudes.min.js"></script>
<script>
    function publishSecret(event) {
        event.preventDefault();
        const formEl = document.getElementById("publish-form");
        const content = formEl.querySelector("#content").value;
        const nickname = formEl.querySelector("#nickname").value;
        // const imageInputEl = formEl.querySelector("#image-input");
        // const images = imageInputEl.files;
        const formData = new FormData();
        formData.append("content", content);
        formData.append("nickname", nickname);
        // for (let i = 0; i < images.length; i++) {
        //     formData.append("images[]", images[i], images[i].name);
        // }
        fetch("./secrets", {
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
                    imageInputEl.value = "";
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                alert(error.message);
            });
    }

    const publishFormEl = document.getElementById("publish-form");
    publishFormEl.addEventListener("submit", publishSecret);

    function refresh() {
        fetch("./secrets")
            .then(response => response.text())
            .then(secretsHtml => {
                const itemListEl = document.getElementById("secret-list");
                itemListEl.innerHTML = secretsHtml;
                window.initAttitudes();
            });
    }

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