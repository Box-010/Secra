<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <link rel="stylesheet" href="../public/styles/normalize.css"/>
  <link rel="stylesheet" href="../public/styles/main.css"/>
  <link rel="stylesheet" href="../public/styles/attitudes.css"/>
  <link rel="stylesheet" href="../public/styles/material-symbols/index.css"/>
  <title>安装结果 | 安装向导 | 隐境 Secra</title>
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
    <div class="card" id="publish">
      <div class="card-content">
        <h2>安装中</h2>
        <?php
        $mode = $_POST['mode'];
        $success = true;
        if ($mode === 'root') {
          echo '<p>正在使用超级用户模式安装...</p>';
        } else {
          echo '<p>正在使用手动模式安装...</p>';
        }

        // 先写入配置文件
        $db_config = "<?php\n";
        $db_config .= "const DB_HOST = '" . $_POST['db_host'] . "';\n";
        $db_config .= "const DB_PORT = '" . $_POST['db_port'] . "';\n";
        $db_config .= "const DB_NAME = '" . $_POST['db_name'] . "';\n";
        $db_config .= "const DB_USER = '" . $_POST['db_user'] . "';\n";
        $db_config .= "const DB_PASS = '" . $_POST['db_pass'] . "';\n";

        if (file_put_contents(dirname(__DIR__) . '/config/database.php', $db_config)) {
          echo '<p class="success">数据库配置写入成功</p>';
        } else {
          echo '<p class="error">数据库配置写入失败</p>';
          $success = false;
        }

        if ($success) {
          $admin_config = "<?php\n";
          $admin_config .= "const ADMIN_USERNAME = '" . $_POST['admin_user'] . "';\n";
          $admin_config .= "const ADMIN_PASSWORD = '" . $_POST['admin_pass'] . "';\n";
          $admin_config .= "const ADMIN_EMAIL = '" . $_POST['admin_email'] . "';\n";

          if (file_put_contents(dirname(__DIR__) . '/config/admin.php', $admin_config)) {
            echo '<p class="success">管理员配置写入成功</p>';
          } else {
            echo '<p class="error">管理员配置写入失败</p>';
            $success = false;
          }
        }

        // 再执行数据库初始化
        if ($success) {
          require_once dirname(__DIR__) . '/config/admin.php';
          require_once dirname(__DIR__) . '/config/database.php';
          if ($mode === 'root') {
            // 超级用户模式，先创建数据库和用户
            $db_root_user = $_POST['db_root_user'];
            $db_root_pass = $_POST['db_root_pass'];
            try {
              $conn = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT, $db_root_user, $db_root_pass);
              $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

              $sql = "CREATE USER IF NOT EXISTS '" . DB_USER . "'@'" . DB_HOST . "' IDENTIFIED BY '" . DB_PASS . "'";
              $conn->exec($sql);
              echo "<p class='success'>用户创建成功</p>";
              $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
              $conn->exec($sql);
              echo "<p class='success'>数据库创建成功</p>";
              $sql = "GRANT ALL PRIVILEGES ON " . DB_NAME . ".* TO '" . DB_USER . "'@'" . DB_HOST . "'";
              $conn->exec($sql);
              echo "<p class='success'>权限授予成功</p>";

              $conn = null;
            } catch (PDOException $e) {
              echo "<p class='error'>数据库创建失败：" . $e->getMessage() . "</p>";
              $success = false;
            }
          }
        }

        if ($success) {
          try {
            $conn = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          } catch (PDOException $e) {
            echo "<p class='error'>数据库连接失败：" . $e->getMessage() . "</p>";
            $success = false;
          }
        }

        if ($success) {
          $sql = file_get_contents(__DIR__ . '/schema.sql');
          try {
            $conn->exec($sql);
            echo "<p class='success'>数据库初始化成功</p>";
          } catch (PDOException $e) {
            echo "<p class='error'>数据库初始化失败：" . $e->getMessage() . "</p>";
            $success = false;
          }
        }

        if ($success) {
          try {
            $randomSalt = bin2hex(random_bytes(32));
            $sql = "INSERT INTO users (user_name, password, salt, email) VALUES ('" . ADMIN_USERNAME . "', '" . password_hash(ADMIN_PASSWORD . $randomSalt, PASSWORD_DEFAULT) . "', '" . $randomSalt . "', '" . ADMIN_EMAIL . "')";
            $conn->exec($sql);
            $sql = "INSERT INTO user_roles (user_id, role_id) VALUES (1, 2)";
            $conn->exec($sql);
            echo "<p class='success'>默认管理员创建成功</p>";
          } catch (PDOException $e) {
            echo "<p class='error'>默认管理员创建失败：" . $e->getMessage() . "</p>";
            $success = false;
          }
        }

        $conn = null;

        if ($success) {
          $website_config = "<?php\n";
          $website_config .= "const PUBLIC_ROOT = '" . $_POST['public_root'] . "';\n";
          $website_config .= "const IS_INSTALLED = true;\n";

          if (file_put_contents(dirname(__DIR__) . '/config/website.php', $website_config)) {
            echo '<p class="success">网站配置写入成功</p>';
          } else {
            echo '<p class="error">网站配置写入失败</p>';
            $success = false;
          }
        }
        ?>

        <?php if ($success) : ?>
          <p>安装成功！</p>
        <?php else : ?>
          <p>安装失败。</p>
        <?php endif; ?>
      </div>
      <div class="card-actions">
        <?php if ($success) : ?>
          <a href="../" class="button button-primary">完成</a>
        <?php else : ?>
          <a href="index.php" class="button">返回</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>
</body>

</html>