<?php
$startYear = 2024;
$currentYear = date('Y');

if ($startYear == $currentYear) {
  $year = $startYear;
} else {
  $year = "{$startYear}-{$currentYear}";
}
?>
<footer class="footer">
  <div class="footer-copyright">Copyright © <?= $year ?> Secra | Made with ♥️ by Box-010</div>
  <div class="footer-links">
    <a href="https://github.com/Box-010" target="_blank" class="footer-link">
      <img class="svg-icon" src="./images/github.svg" alt="GitHub" title="GitHub"/>
    </a>
  </div>
</footer>