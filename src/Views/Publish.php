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

    .classic-captcha {
      display: flex;
      flex-direction: row;
      align-items: center;
      margin-top: 1rem;
    }

    .classic-captcha .captcha-image {
      width: 100px;
      height: 50px;
      opacity: 0;
      cursor: pointer;
    }

    .classic-captcha .captcha-image.captcha-image--visible {
      opacity: 1;
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
              <div id="image-preview" class="preview-images-container"></div>
              <div class="classic-captcha">
                <img src="" alt="" id="captcha-image" class="captcha-image"/>
                <div class="textfield-wrapper textfield-wrapper--dense">
                  <div class="textfield">
                    <input type="text" id="captcha-code" name="captcha-code"/>
                    <label for="captcha-code">验证码</label>
                  </div>
                </div>
              </div>
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
      <?php else : ?>
        <div class="auth-container">
          <div class="auth-card">
            <div class="auth-card-image" id="auth-card-image"></div>
            <div class="auth-card-content">
              <h1>请先登录</h1>
              <a href="./users/login?redirect=publish" class="button button-primary">登录</a>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>
<?= $render('Components/Footer') ?>

<script src="./scripts/input.js"></script>
<script src="./scripts/gt4.js"></script>
<script>
  const publishFormEl = document.getElementById("publish-form");
  const imageInputEl = document.getElementById("image-input");
  const imagePreviewEl = document.getElementById("image-preview");
  const captchaImageEl = document.getElementById("captcha-image");

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
        // 'Content-Type': 'multipart/form-data',
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

  function publish(content, nickname, imageIds = [], captchaResult) {
    if (imageIds.length < selectedImages.length) {
      return Promise.all(selectedImages.map(uploadImage))
        .then(data => {
          imageIds.push(...data.map(item => item.imageId));
          return publish(content, nickname, imageIds, captchaResult);
        });
    }
    const formData = new FormData();
    formData.append("content", content);
    formData.append("nickname", nickname);
    formData.append("imageIds", JSON.stringify(imageIds));
    formData.append("captcha_type", "classic");
    formData.append("captcha_code", captchaResult);
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
    const content = publishFormEl.querySelector("#content").value;
    const nickname = publishFormEl.querySelector("#nickname").value;
    const captchaCode = publishFormEl.querySelector("#captcha-code").value;

    if (!captchaCode) {
      alert("验证码不能为空");
      return;
    }
    if (!content) {
      alert("内容不能为空");
      return;
    }

    publish(content, nickname, [], captchaCode)
      .then(data => {
        if (data.success) {
          window.location.href = "./";
        } else {
          alert(data.message);
        }
      })
      .catch(error => {
        alert(error.message);
      });
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

  function fetchCaptcha() {
    let formData = new FormData();
    formData.append("id", "publish");
    fetch("./captcha", {
      method: "POST",
      body: formData,
      credentials: "same-origin",
    })
      .then(response => {
        switch (response.status) {
          case 200:
            return response.json();
          default:
            return response.json().then(data => {
              throw new Error(data.message);
            });
        }
      })
      .then(data => {
        captchaImageEl.src = data.data;
        captchaImageEl.classList.add("captcha-image--visible");
      })
      .catch(error => {
        alert(error.message);
      });
  }

  imageInputEl.addEventListener("change", handleFileSelect);
  document.addEventListener("DOMContentLoaded", fetchCaptcha);
  captchaImageEl.addEventListener("click", fetchCaptcha);
</script>
</body>

</html>