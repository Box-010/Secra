<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <link rel="stylesheet" href="../public/styles/normalize.css"/>
  <link rel="stylesheet" href="../public/styles/main.css"/>
  <link rel="stylesheet" href="../public/styles/attitudes.css"/>
  <link rel="stylesheet" href="../public/styles/material-symbols/index.css"/>
  <title>配置 | 安装向导 | 隐境 Secra</title>
  <style>
    .success {
      color: #4caf50;
    }

    .error {
      color: #f44336;
    }

    .modes {
      display: flex;
      flex-direction: column;
    }

    .mode-item {
      margin: 1rem;
      border: 1px solid #e0e0e0;
      border-radius: 0.5rem;
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
        <h2>配置</h2>
        <p>我们提供了两种安装模式，您可以选择适合您的模式。</p>
        <div class="modes">

          <a class="card mode-item" href="root-mode.php">
            <div class="card-content">
              <h3>超级用户模式</h3>
              <p>如果您拥有数据库的超级用户权限，您可以选择此模式。此模式将会自动完成数据库的创建和初始化等操作。</p>
            </div>
          </a>

          <a class="card mode-item" href="manual-mode.php">
            <div class="card-content">
              <h3>手动模式</h3>
              <p>
                如果您没有数据库的超级用户权限，您可以选择此模式。此模式将会完成数据库的初始化等操作，但是需要一个空数据库和拥有数据库权限的普通用户。</p>
            </div>
          </a>
        </div>
      </div>
    </div>
  </div>
</main>
</body>

</html>