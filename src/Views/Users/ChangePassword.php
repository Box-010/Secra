<?php

/**
 * @var callable(string, array): string $render
 * @var string $nonce
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <?= $render('Components/HtmlHead') ?>
  <title>修改密码 | 隐境 Secra</title>
  <link rel="stylesheet" href="./styles/bg-patterns.css"/>
  <link rel="stylesheet" href="./styles/auth.css"/>
</head>

<body>
<main class="main auth-main">
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-card-image" id="auth-card-image"></div>
      <div class="auth-card-content">
        <h1 class="auth-card-title">修改密码</h1>
        <form action="./users/changepassword" method="post" class="auth-card-form">
          <div class="textfield-wrapper">
            <div class="textfield">
              <!-- <span class="icon material-symbols-outlined"> lock </span> -->
              <input type="password" id="oldpassword" name="oldpassword" placeholder="请输入密码" required/>
              <label for="password">原密码</label>
            </div>
          </div>
          <div class="textfield-wrapper">
            <div class="textfield">
              <!-- <span class="icon material-symbols-outlined"> lock </span> -->
              <input type="password" id="newpassword" name="newpassword" placeholder="请输入新密码" required/>
              <label for="newpassword">新密码</label>
            </div>
            <div class="textfield-wrapper">
              <div class="textfield">
                <!-- <span class="icon material-symbols-outlined"> lock </span> -->
                <input type="password" id="confirmpassword" name="confirmpassword" placeholder="请再次输入密码"
                       required/>
                <label for="confirmpassword">确认密码</label>
              </div>
            </div>

            <div class="card-actions">
              <button class="button button-primary" type="submit">
                <span class="button-text">修改密码</span>
              </button>
            </div>
        </form>
      </div>
    </div>
  </div>
</main>
<?= $render('Components/Footer') ?>

<script src="./scripts/random-bg.js"></script>
<script src="./scripts/input.js"></script>
<script nonce="<?= $nonce ?>">
  addRandomBackground("#auth-card-image");

  document
    .getElementById("auth-card-image")
    .addEventListener("click", () => {
      addRandomBackground("#auth-card-image");
    });

  // 验证两次输入密码是否相等不相等给出提示
  document.getElementById("confirmpassword").addEventListener("input", function () {
    if (document.getElementById("newpassword").value !== document.getElementById("confirmpassword").value) {
      document.getElementById("confirmpassword").setCustomValidity("两次输入密码不一致");
    } else {
      document.getElementById("confirmpassword").setCustomValidity("");
    }
  });
  document.addEventListener('DOMContentLoaded', function () {
    var urlParams = new URLSearchParams(window.location.search);
    var error = urlParams.get('error');

    if (error) {
      alert('原密码错误，请重新输入');
    }
  });
</script>
</body>

</html>