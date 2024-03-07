<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>注册 | 隐境 Secra</title>
  <link rel="stylesheet" href="/styles/normalize.css" />
  <link rel="stylesheet" href="/styles/main.css" />
  <link rel="stylesheet" href="/styles/material-symbols/index.css" />
  <link rel="stylesheet" href="/styles/bg-patterns.css" />
  <link rel="stylesheet" href="/styles/auth.css" />
</head>

<body>
  <main class="main auth-main">
    <div class="auth-container">
      <div class="auth-card">
        <div class="auth-card-image" id="auth-card-image"></div>
        <div class="auth-card-content">
          <form action="/users/register" method="post" id="register-form">
            <div class="card">
              <div class="card-content">
                <h1>注册</h1>
                <div class="textfield-wrapper">
                  <div class="textfield">
                    <span class="icon material-symbols-outlined">
                      person
                    </span>
                    <input type="text" id="username" name="username" placeholder="请输入用户名" required />
                    <label for="username">用户名</label>
                  </div>
                </div>
                <div class="textfield-wrapper">
                  <div class="textfield">
                    <span class="icon material-symbols-outlined"> lock </span>
                    <input type="password" id="password" name="password" placeholder="请输入密码" required />
                    <label for="password">密码</label>
                  </div>
                </div>
                <div class="textfield-wrapper">
                  <div class="textfield">
                    <span class="icon material-symbols-outlined"> lock </span>
                    <input type="password" id="password-confirm" name="password-confirm" placeholder="请再次输入密码" required />
                    <label for="password-confirm">确认密码</label>
                  </div>
                </div>
                <div class="textfield-wrapper">
                  <div class="textfield">
                    <span class="icon material-symbols-outlined">
                      email
                    </span>
                    <input type="email" id="email" name="email" placeholder="请输入邮箱" />
                    <label for="password-confirm">邮箱</label>
                  </div>
                  <span class="helper-text">可选，用于找回密码</span>
                </div>
              </div>
              <div class="card-actions">
                <a href="/users/login" class="button"> 已有账号？返回登录 </a>
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
  <footer class="footer">
    Copyright © 2024 Secra | Made with ♥️ by HuanChengFly
  </footer>

  <script src="/scripts/random-bg.js"></script>
  <script src="/scripts/input.js"></script>
  <script>
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

        localStorage.setItem("username", username);
        location.href = "/";
      });
  </script>
</body>

</html>