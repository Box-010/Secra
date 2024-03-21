<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <link rel="stylesheet" href="../public/styles/normalize.css"/>
  <link rel="stylesheet" href="../public/styles/main.css"/>
  <link rel="stylesheet" href="../public/styles/attitudes.css"/>
  <link rel="stylesheet" href="../public/styles/material-symbols/index.css"/>
  <title>环境检查 | 安装向导 | 隐境 Secra</title>
  <style>
    .cond-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .success {
      color: #4caf50;
    }

    .error {
      color: #f44336;
    }

    .warning {
      color: #ff9800;
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
        <h2>检查环境</h2>
        <p>检查服务器环境是否满足隐境 Secra 的运行要求。</p>
        <?php
        $envOK = true;
        $requiredExtensions = array('PDO', 'pdo_mysql', 'json');
        $recommendedExtensions = array('gd');
        $requiredApacheModules = array('mod_rewrite');
        $checkPermissions = array(
          'config' => is_writable(dirname(__DIR__) . '/config'),
        );
        ?>
        <div class="cond-item">
          PHP 版本：<?php echo PHP_VERSION; ?>
          <?php if (version_compare(PHP_VERSION, '8.2.0', '>=')) : ?>
            <span class="material-symbols-outlined success">
                check
              </span>
          <?php else : ?>
            <?php $envOK = false; ?>
            <span class="material-symbols-outlined error">
                cancel
              </span>
          <?php endif; ?>
        </div>
        <?php foreach ($requiredExtensions as $extension) : ?>
          <div class="cond-item">
            PHP 扩展：<?php echo $extension; ?>
            <?php if (extension_loaded($extension)) : ?>
              <span class="material-symbols-outlined success">
                  check
                </span>
            <?php else : ?>
              <?php $envOK = false; ?>
              <span class="material-symbols-outlined error">
                  cancel
                </span>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
        <?php foreach ($recommendedExtensions as $extension) : ?>
          <div class="cond-item">
            PHP 扩展（推荐）：<?php echo $extension; ?>
            <?php if (extension_loaded($extension)) : ?>
              <span class="material-symbols-outlined success">
                  check
                </span>
            <?php else : ?>
              <span class="material-symbols-outlined warning">
                  warning
                </span>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
        <?php foreach ($requiredApacheModules as $module) : ?>
          <div class="cond-item">
            Apache 模块：<?php echo $module; ?>
            <?php if (in_array($module, apache_get_modules())) : ?>
              <span class="material-symbols-outlined success">
                  check
                </span>
            <?php else : ?>
              <?php $envOK = false; ?>
              <span class="material-symbols-outlined error">
                  cancel
                </span>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
        <?php foreach ($checkPermissions as $key => $value) : ?>
          <div class="cond-item">
              <span>
                目录 <?php echo $key; ?> 是否可写
              </span>
            <?php if ($value) : ?>
              <span class="material-symbols-outlined success">
                  check
                </span>
            <?php else : ?>
              <?php $envOK = false; ?>
              <span class="material-symbols-outlined error">
                  cancel
                </span>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="card-actions">
        <?php if ($envOK) : ?>
          <a class="button button-primary" href="select-mode.php">下一步：配置</a>
        <?php else : ?>
          <a class="button button-primary" href="check-env.php">重新检查</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>
</body>

</html>