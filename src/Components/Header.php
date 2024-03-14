<header class="header">
  <span class="header-title"><?= $title ?? "隐境 Secra" ?></span>
  <div class="spacer"></div>
  <?php /** @var bool $isLoggedIn */
  if ($isLoggedIn) : ?>
    <a class="button button-icon" href="./users/me" id="logged-in">
      <span class="icon material-symbols-outlined"> account_circle </span>
    </a>
  <?php else : ?>
    <a class="button button-tonal" href="./users/login" id="not-logged-in">
      <span class="material-symbols-outlined"> login </span>
      登录
    </a>
  <?php endif; ?>
</header>