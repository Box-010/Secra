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
  <title>注册 | 隐境 Secra</title>
  <link rel="stylesheet" href="./styles/bg-patterns.css"/>
  <link rel="stylesheet" href="./styles/auth.css"/>
</head>

<body>
<main class="main auth-main">
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-card-image" id="auth-card-image"></div>
      <div class="auth-card-content">
        <form action="./users/register" method="post" id="register-form">
          <div class="card">
            <div class="card-content">
              <h1>注册</h1>
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
              <div class="textfield-wrapper">
                <div class="textfield">
                  <span class="icon material-symbols-outlined"> lock </span>
                  <input type="password" id="password-confirm" name="password-confirm"
                         placeholder="请再次输入密码" required/>
                  <label for="password-confirm">确认密码</label>
                </div>
              </div>
              <div class="textfield-wrapper">
                <div class="textfield">
                    <span class="icon material-symbols-outlined">
                      email
                    </span>
                  <input type="email" id="email" name="email" placeholder="请输入邮箱"/>
                  <label for="password-confirm">邮箱</label>
                </div>
                <span class="helper-text">可选，用于找回密码</span>
              </div>
            </div>
            <div class="card-actions">
              <a href="./users/login<?php if (isset($_GET['redirect'])) echo '?redirect=' . $_GET['redirect'] ?>"
                 class="button"> 已有账号？返回登录 </a>
              <button class="button button-primary" type="submit">
                注册
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
<script nonce="<?= $nonce ?>">
  addRandomBackground("#auth-card-image");

  document
    .getElementById("auth-card-image")
    .addEventListener("click", () => {
      addRandomBackground("#auth-card-image");
    });

  document
    .getElementById("register-form")
    .addEventListener("submit", (e) => {
      e.preventDefault();

      const username = document.getElementById("username").value;
      const password = document.getElementById("password").value;
      const passwordConfirm =
        document.getElementById("password-confirm").value;

      if (!username || !password || !passwordConfirm) {
        alert("请填写完整信息");
        return;
      }
      if (password !== passwordConfirm) {
        alert("两次输入的密码不一致");
        return;
      }
      if (!captchaObj) {
        alert('验证码加载中，请稍后');
        return;
      }
      captchaObj.showCaptcha();
    });

  function submitRegister(captchaResult) {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const email = document.getElementById('email').value;
    const formData = new FormData();
    formData.append('username', username);
    formData.append('password', password);
    formData.append('email', email);
    formData.append('captcha_type', "geetest4");
    formData.append('captcha_id', "953b873286a0f857dc5b78d114c3eb3b");
    formData.append('lot_number', captchaResult.lot_number);
    formData.append('pass_token', captchaResult.pass_token);
    formData.append('gen_time', captchaResult.gen_time);
    formData.append('captcha_output', captchaResult.captcha_output);
    fetch('./users/register', {
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
        captchaObj.reset();
      }
    }).catch(error => {
      alert(error.message);
    });
  }

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
      submitRegister(result);
    });
  });
</script>
</body>

</html>