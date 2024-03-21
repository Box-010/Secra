<?php
include_once __DIR__ . '/config/website.php';

if (!defined('IS_INSTALLED') || !IS_INSTALLED) {
  echo '未安装，请先安装。3秒后自动跳转到安装向导。';
  header('Refresh: 3; url=install/');
  exit;
}

require_once __DIR__ . '/src/app.php';
