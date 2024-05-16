<?php
/**
 * @var string $nonce
 */
?>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<base href="<?= PUBLIC_ROOT ?>">
<link rel="stylesheet" href="./styles/normalize.css">
<link rel="stylesheet" href="./styles/main.css">
<link rel="stylesheet" href="./styles/attitudes.css">
<link rel="stylesheet" href="./styles/material-symbols/index.css">
<meta http-equiv="Content-Security-Policy"
      content="default-src 'self'; script-src 'self' 'nonce-<?= $nonce ?>' *.geetest.com *.geevisit.com; style-src 'self' 'unsafe-inline' static.geetest.com static.geevisit.com; img-src 'self' data: static.geetest.com static.geevisit.com; font-src 'self'; connect-src 'self'; media-src 'self'; object-src 'none'; frame-src 'none';">