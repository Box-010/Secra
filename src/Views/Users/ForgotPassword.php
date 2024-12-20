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
  <title>忘记密码 | 隐境 Secra</title>
  <link rel="stylesheet" href="./styles/bg-patterns.css"/>
  <link rel="stylesheet" href="./styles/auth.css"/>
</head>

<body>
<main class="main auth-main">
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-card-image" id="auth-card-image"></div>
      <div class="auth-card-content">
        <form action="./forgot-password" method="post" id="forgot-password-form">
          <div class="card">
            <div class="card-content">
              <h1>忘记密码</h1>
              <!--              <div class="textfield-wrapper">-->
              <!--                <div class="textfield">-->
              <!--                    <span class="icon material-symbols-outlined">-->
              <!--                      email-->
              <!--                    </span>-->
              <!--                  <input type="email" id="email" name="email" placeholder="请输入注册时填写的邮箱"-->
              <!--                         required/>-->
              <!--                  <label for="username">邮箱</label>-->
              <!--                </div>-->
              <!--              </div>-->
              <p>
                请使用注册时填写的邮箱联系管理员重置密码
              </p>
            </div>
            <div class="card-actions">
              <a href="./users/login" class="button"> 返回登录 </a>
              <!--              <button class="button button-primary" type="submit">-->
              <!--                提交-->
              <!--              </button>-->
            </div>
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

  document
    .getElementById("forgot-password-form")
    .addEventListener("submit", (e) => {
      e.preventDefault();
      const email = document.getElementById("email").value;
      if (!email) {
        alert("请输入邮箱");
        return;
      }
      alert("已发送重置密码邮件，请查收");
    });
</script>
</body>

</html>