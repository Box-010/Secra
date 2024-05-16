<?php

/**
 * @var callable $render
 * @var bool $isLoggedIn
 * @var string $welcomeMessage
 * @var Secret[] $secrets
 * @var bool $hasMore
 * @var string $nonce
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
              <div id="image-preview" class="preview-images-container"></div>
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

      <form class="search-box" action="./search" method="get">
        <input type="text" class="search-box-input" placeholder="搜索秘语" name="q" required/>
        <button class="button button-icon" type="submit">
          <span class="icon material-symbols-outlined"> search </span>
        </button>
      </form>
    </div>

    <div class="item-list-container">
      <div class="item-list-header">
        <div class="item-list-header-title">秘语</div>
        <div class="spacer"></div>
        <!--        <div class="button-group sort-type-select">-->
        <!--          <button class="button button-active">新发</button>-->
        <!--          <button class="button">新回</button>-->
        <!--        </div>-->
      </div>

      <div class="item-list" id="secret-list" <?= empty($secrets) ? ' data-empty="1"' : '' ?>>
        <?php foreach ($secrets as $secret) : ?>
          <?= $render('Components/SecretCard', ['secret' => $secret, 'link' => true, 'showCommentBtn' => true]); ?>
        <?php endforeach; ?>
        <?php if ($hasMore) : ?>
          <?= $render('Components/LoadMoreIndicator', ['url' => "./secrets?page=2"]); ?>
        <?php endif; ?>
        <?= $render('Components/EmptyTip') ?>
      </div>
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
<script src="./scripts/load-more.js"></script>
<script src="./scripts/dropdown.js"></script>
<script src="./scripts/gt4.js"></script>
<script nonce="<?= $nonce ?>">
  let captchaObj;
  const publishFormEl = document.getElementById("publish-form");
  const imageInputEl = document.getElementById("image-input");
  const imagePreviewEl = document.getElementById("image-preview");

  publishFormEl.addEventListener("submit", submitSecret);
  const selectedImages = [];
  const previewImages = [];

  function uploadImage(image) {
    const formData = new FormData();
    formData.append("image", image);
    return fetch("./images", {
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
          default:
            return response.json().then(data => {
              throw new Error(data.message);
            });
        }
      });
  }

  function clearImages() {
    selectedImages.splice(0, selectedImages.length);
    previewImages.splice(0, previewImages.length);
    imagePreviewEl.innerHTML = "";
  }

  function publish(content, nickname, captchaResult, imageIds = []) {
    if (imageIds.length < selectedImages.length) {
      return Promise.all(selectedImages.map(uploadImage))
        .then(data => {
          imageIds.push(...data.map(item => item.imageId));
          return publish(content, nickname, captchaResult, imageIds);
        });
    }
    const formData = new FormData();
    formData.append("content", content);
    formData.append("nickname", nickname);
    formData.append("imageIds", JSON.stringify(imageIds));
    formData.append('captcha_type', "geetest4");
    formData.append('captcha_id', "9edebb865c50012b456e014da606a77c");
    formData.append('lot_number', captchaResult.lot_number);
    formData.append('pass_token', captchaResult.pass_token);
    formData.append('gen_time', captchaResult.gen_time);
    formData.append('captcha_output', captchaResult.captcha_output);
    return fetch("./secrets", {
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
            window.location.href = "./users/login";
            throw new Error("请先登录");
          default:
            return response.json().then(data => {
              throw new Error(data.message);
            });
        }
      })
  }

  function submitSecret(event) {
    event.preventDefault();
    const formEl = document.getElementById("publish-form");
    const content = formEl.querySelector("#content").value;

    if (!content) {
      alert("内容不能为空");
      return;
    }
    if (!captchaObj) {
      alert('验证码加载中，请稍后');
      return;
    }
    captchaObj.showCaptcha();
  }

  function addImage(image) {
    selectedImages.push(image);
    const reader = new FileReader();
    reader.onload = (function (theFile) {
      return function (e) {
        const img = document.createElement("img");
        img.src = e.target.result;
        img.title = theFile.name;
        img.className = "preview-image";
        previewImages.push(img);
        const previewEl = document.createElement("div");
        previewEl.className = "preview";
        previewEl.appendChild(img);
        const removeBtn = document.createElement("button");
        removeBtn.className = "button button-icon";
        removeBtn.innerHTML = '<span class="icon material-symbols-outlined"> close </span>';
        removeBtn.onclick = function () {
          selectedImages.splice(selectedImages.indexOf(theFile), 1);
          previewImages.splice(previewImages.indexOf(img), 1);
          previewEl.remove();
        };
        previewEl.appendChild(removeBtn);
        imagePreviewEl.appendChild(previewEl);
      };
    })(image);

    reader.readAsDataURL(image);
  }

  function handleFileSelect(evt) {
    const files = evt.target.files;
    for (let i = 0, f;
         (f = files[i]); i++) {
      if (!f.type.match("image.*")) {
        continue;
      }
      if (selectedImages.length >= 9) {
        alert("最多只能上传9张图片");
        return;
      }
      selectedImages.push(f);

      const reader = new FileReader();
      reader.onload = (function (theFile) {
        return function (e) {
          const img = document.createElement("img");
          img.src = e.target.result;
          img.title = theFile.name;
          img.className = "preview-image";
          previewImages.push(img);
          const previewEl = document.createElement("div");
          previewEl.className = "preview";
          previewEl.appendChild(img);
          const removeBtn = document.createElement("button");
          removeBtn.className = "button button-icon";
          removeBtn.innerHTML = '<span class="icon material-symbols-outlined"> close </span>';
          removeBtn.onclick = function () {
            selectedImages.splice(selectedImages.indexOf(theFile), 1);
            previewImages.splice(previewImages.indexOf(img), 1);
            previewEl.remove();
          };
          previewEl.appendChild(removeBtn);
          imagePreviewEl.appendChild(previewEl);
        };
      })(f);

      reader.readAsDataURL(f);
    }
  }

  imageInputEl.addEventListener("change", handleFileSelect);

  function refresh() {
    fetch("./secrets")
      .then(response => response.text())
      .then(secretsHtml => {
        const itemListEl = document.getElementById("secret-list");
        itemListEl.innerHTML = secretsHtml;
        window.initAttitudes();
        window.initLoadMore();
        window.initDropdown();
      });
  }

  initGeetest4({
    captchaId: '9edebb865c50012b456e014da606a77c',
    product: 'bind',
    hideSuccess: true
  }, function (captcha) {
    captchaObj = captcha;
    captcha.onSuccess(function () {
      const result = captcha.getValidate();
      if (!result) {
        return alert('请先完成验证');
      }
      const content = publishFormEl.querySelector("#content").value;
      const nickname = publishFormEl.querySelector("#nickname").value;

      publish(content, nickname, result)
        .then(data => {
          if (data.success) {
            refresh();
            publishFormEl.querySelector("#content").value = "";
            publishFormEl.querySelector("#nickname").value = "";
            clearImages();
          } else {
            alert(data.message);
          }
        })
        .catch(error => {
          alert(error.message);
        });
    });
  });
</script>
</body>

</html>