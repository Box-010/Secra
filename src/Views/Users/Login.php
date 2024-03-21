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
              <div id="captcha-box"></div>
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
<script src="./scripts/gt4.js"></script>
<script>
  let captchaObj = null;

  addRandomBackground("#auth-card-image");

  document
    .getElementById("auth-card-image")
    .addEventListener("click", () => {
      addRandomBackground("#auth-card-image");
    });

  const loginFormEl = document.getElementById('login-form');
  loginFormEl.addEventListener('submit', function (event) {
    event.preventDefault();
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    if (!username || !password) {
      alert('请输入用户名和密码');
      return;
    }
    if (!captchaObj) {
      alert('验证码加载中，请稍后');
      return;
    }
    captchaObj.showCaptcha();
  });

  initGeetest4({
    captchaId: '953b873286a0f857dc5b78d114c3eb3b',
    product: 'bind',
    hideSuccess: true
  }, function (captcha) {
    captchaObj = captcha;
    captcha.onSuccess(function () {
      var result = captcha.getValidate();
      if (!result) {
        return alert('请先完成验证');
      }
      console.log(result);
      const username = document.getElementById('username').value;
      const password = document.getElementById('password').value;
      const formData = new FormData();
      formData.append('username', username);
      formData.append('password', password);
      formData.append('captcha_type', "geetest4");
      formData.append('captcha_id', "953b873286a0f857dc5b78d114c3eb3b");
      formData.append('lot_number', result.lot_number);
      formData.append('pass_token', result.pass_token);
      formData.append('gen_time', result.gen_time);
      formData.append('captcha_output', result.captcha_output);
      fetch('./users/login', {
        method: 'POST',
        headers: {
          'Accept': 'application/json'
        },
        body: formData
      }).then(response => {
        if (response.ok) {
          return response.json();
        }
        throw new Error('网络错误');
      }).then(data => {
        if (data.success) {
          const redirect = new URLSearchParams(window.location.search).get('redirect') || '';
          window.location.href = `./${redirect}`
        } else {
          alert(data.message);
          captcha.reset();
        }
      }).catch(error => {
        alert(error.message);
      });
    });
  });
</script>
</body>

</html>