<?php

/**
 * @var callable(string, array): string $render
 * @var bool $isLoggedIn
 * @var User $currentUser
 * @var string $query
 * @var Secret[] $secrets
 * @var bool $hasMore
 */

use Secra\Models\Secret;
use Secra\Models\User;

?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <?= $render('Components/HtmlHead') ?>
  <title>搜索秘语 | 隐境 Secra</title>
</head>

<body>
<header class="header">
  <a class="button button-icon" href="./">
    <span class="icon material-symbols-outlined"> arrow_back </span>
  </a>
  <span class="header-title">搜索秘语</span>
  <div class="spacer"></div>
</header>
<main class="main">
  <div class="container">

    <div class="top-cards">
      <form class="search-box" action="./search" method="get">
        <input type="text" class="search-box-input" placeholder="搜索秘语" name="q" required value="<?= $query ?>"/>
        <button class="button button-icon" type="submit">
          <span class="icon material-symbols-outlined"> search </span>
        </button>
      </form>
    </div>

    <div class="item-list-container">
      <div class="item-list-header">
        <div class="item-list-header-title">搜索结果</div>
        <div class="spacer"></div>
      </div>

      <div class="item-list" id="secret-list"<?= empty($secrets) ? ' data-empty="1"' : '' ?>>
        <?php foreach ($secrets as $secret) : ?>
          <?= $render('Components/SecretCard', ['secret' => $secret, 'link' => true, 'showCommentBtn' => true]); ?>
        <?php endforeach; ?>
        <?php if ($hasMore) : ?>
          <?= $render('Components/LoadMoreIndicator', ['url' => "./secrets/search?q=" . urlencode($query) . "&page=2"]) ?>
        <?php endif; ?>
        <?= $render('Components/EmptyTip') ?>
      </div>
    </div>
  </div>
</main>
<?= $render('Components/Footer') ?>
<script src="./scripts/polyfill.min.js"></script>
<script src="./scripts/cross-fetch.js"></script>
<script src="./scripts/input.js"></script>
<script src="./scripts/utils.js"></script>
<script src="./scripts/attitudes.min.js"></script>
<script src="./scripts/load-more.js"></script>
<script src="./scripts/dropdown.js"></script>
</body>

</html>