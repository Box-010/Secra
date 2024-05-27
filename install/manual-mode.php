<?php
require __DIR__ . '/check-installed.php';
?>
<?php
try {
  include_once dirname(__DIR__) . '/config/admin.php';
  include_once dirname(__DIR__) . '/config/database.php';
  include_once dirname(__DIR__) . '/config/website.php';
} catch (Exception $e) {
}
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="../public/styles/normalize.css" />
  <link rel="stylesheet" href="../public/styles/main.css" />
  <link rel="stylesheet" href="../public/styles/attitudes.css" />
  <link rel="stylesheet" href="../public/styles/material-symbols/index.css" />
  <title>配置 | 安装向导 | 隐境 Secra</title>
  <style>
    .success {
      color: #4caf50;
    }

    .error {
      color: #f44336;
    }
  </style>
</head>

<body>
  <header class="header">
    <span class="header-title">安装向导</span>
    <div class="spacer"></div>
  </header>

  <main class="main">
    <div class="container">
      <form action="install.php" method="post">
        <div class="card" id="publish">
          <div class="card-content">
            <h2>填写配置</h2>
            <input type="hidden" name="mode" value="manual" />
            <div class="form-header">数据库配置</div>
            <div class="textfield-wrapper">
              <div class="textfield">
                <input type="text" id="db_host" name="db_host" required <?php echo defined('DB_HOST') ? 'value="' . DB_HOST . '"' : 'value="localhost"'; ?> />
                <label for="db_host">数据库主机</label>
              </div>
            </div>
            <div class="textfield-wrapper">
              <div class="textfield">
                <input type="text" id="db_port" name="db_port" required <?php echo defined('DB_PORT') ? 'value="' . DB_PORT . '"' : 'value="3306"'; ?> />
                <label for="db_port">数据库端口</label>
              </div>
            </div>
            <div class="textfield-wrapper">
              <div class="textfield">
                <input type="text" id="db_name" name="db_name" required <?php echo defined('DB_NAME') ? 'value="' . DB_NAME . '"' : ''; ?> />
                <label for="db_name">数据库名</label>
              </div>
              <div class="helper-text">请填写创建好的<strong>空数据库</strong>的名称。</div>
            </div>
            <div class="textfield-wrapper">
              <div class="textfield">
                <input type="text" id="db_user" name="db_user" required <?php echo defined('DB_USER') ? 'value="' . DB_USER . '"' : ''; ?> />
                <label for="db_user">数据库用户名</label>
              </div>
              <span class="helper-text">请填写已经创建的拥有数据库权限的用户名。</span>
            </div>
            <div class="textfield-wrapper">
              <div class="textfield">
                <input type="password" id="db_pass" name="db_pass" required <?php echo defined('DB_PASS') ? 'value="' . DB_PASS . '"' : ''; ?> />
                <label for="db_pass">数据库用户密码</label>
              </div>
            </div>

            <div class="form-header">网站配置</div>
            <div class="textfield-wrapper">
              <div class="textfield">
                <input type="text" id="admin_user" name="admin_user" required <?php echo defined('ADMIN_USERNAME') ? 'value="' . ADMIN_USERNAME . '"' : ''; ?> />
                <label for="admin_user">默认管理员用户名</label>
              </div>
            </div>
            <div class="textfield-wrapper">
              <div class="textfield">
                <input type="password" id="admin_pass" name="admin_pass" required <?php echo defined('ADMIN_PASSWORD') ? 'value="' . ADMIN_PASSWORD . '"' : ''; ?> />
                <label for="admin_pass">默认管理员密码</label>
              </div>
            </div>
            <div class="textfield-wrapper">
              <div class="textfield">
                <input type="email" id="admin_email" name="admin_email" required <?php echo defined('ADMIN_EMAIL') ? 'value="' . ADMIN_EMAIL . '"' : ''; ?> />
                <label for="admin_email">默认管理员邮箱</label>
              </div>
            </div>

            <div class="form-header">其他配置</div>
            <div class="textfield-wrapper">
              <div class="textfield">
                <input type="text" id="public_root" name="public_root" required <?php echo defined('PUBLIC_ROOT') ? 'value="' . PUBLIC_ROOT . '"' : '/'; ?> />
                <label for="public_root">网站根目录</label>
              </div>
              <span class="helper-text">请填写网站部署位置相对于域名的路径，例如 / 或 /team5/。</span>
            </div>
          </div>
          <div class="card-actions">
            <button class="button button-primary" type="submit">下一步：安装</button>
          </div>
        </div>
      </form>
    </div>
  </main>
  <script src="../public/scripts/input.js"></script>
</body>

</html>