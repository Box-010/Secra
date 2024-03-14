<?php
/**
 * @var callable(string, array): string $render
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <?= $render('Components/HtmlHead') ?>
  <title>登录 | 隐境 Secra</title>
  <link rel="stylesheet" href="./styles/bg-patterns.css"/>
  <link rel="stylesheet" href="./styles/auth.css"/>
</head>

<body>
<main class="main auth-main">
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-card-image" id="auth-card-image"></div>
      <div class="auth-card-content">
        <form action="./users/login" method="post" id="login-form">
          <?php if (isset($_GET['redirect'])) : ?>
            <input type="hidden" name="redirect" value="<?php echo $_GET['redirect']; ?>"/>
          <?php endif; ?>
          <div class="card">
            <div class="card-content">
              <h1>登录</h1>
              <?php if (isset($_GET['error'])) : ?>
                <div class="alert alert-error">
                  <?php echo $_GET['error']; ?>
                </div>
              <?php endif; ?>
              <div class="textfield-wrapper">
                <div class="textfield">
                    <span class="icon material-symbols-outlined">
                      person
                    </span>
                  <input type="text" id="username" name="username" placeholder="请输入用户名"
                         required/>
                  <label for="username">用户名</label>
                </div>
              </div>
              <div class="textfield-wrapper">
                <div class="textfield">
                  <span class="icon material-symbols-outlined"> lock </span>
                  <input type="password" id="password" name="password" placeholder="请输入密码"
                         required/>
                  <label for="password">密码</label>
                </div>
              </div>
              <a class="link" href="./users/forgot-password">忘记密码？</a>
            </div>
            <div class="card-actions">
              <a href="./users/register<?php if (isset($_GET['redirect'])) echo '?redirect=' . $_GET['redirect'] ?>"
                 class="button"> 注册 </a>
              <button class="button button-primary" type="submit">
                登录
              </button>
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
<script>
    addRandomBackground("#auth-card-image");

    document
        .getElementById("auth-card-image")
        .addEventListener("click", () => {
            addRandomBackground("#auth-card-image");
        });
</script>
</body>

</html>