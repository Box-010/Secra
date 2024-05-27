<?php
if (file_exists(dirname(__DIR__) . '/config/.installed')) {
    header('refresh:3;url=../');
    echo '已安装，如需重新安装请删除 config 目录下的 .installed 文件<br>';
    echo '如果无需重新安装，请删除 install 目录以保证安全';
    exit;
}
