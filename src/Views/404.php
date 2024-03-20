<?php
/**
 * @var callable(string, array): string $render
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <?= $render('Components/HtmlHead') ?>
  <title>404 Not Found | 隐境 Secra</title>
  <style>
      html,
      body {
          height: 100%;
          --theme-background-color: 18, 18, 18 !important;
          --theme-on-primary-color: rgb(0, 0, 0) !important;
          --theme-surface-color: rgb(31, 31, 31) !important;
          --theme-text-color: rgba(255, 255, 255, 1) !important;
          --theme-text-color-secondary: rgba(255, 255, 255, 0.7) !important;
          --theme-text-color-disabled: rgba(255, 255, 255, 0.5) !important;
          --theme-highlight-color: 255, 255, 255 !important;
          --theme-divider-color: #2e2e2e !important;
      }

      body {
          display: flex;
          flex-direction: column;
          align-items: stretch;
          justify-content: center;
      }

      .footer .footer-link {
          filter: invert(1);
      }

      .main {
          flex: 1;
          text-align: center;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
      }

      .bg-404 {
          position: absolute;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          z-index: -5;
          pointer-events: none;
          background-position: center;
          background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iNjIwIiBoZWlnaHQ9Ijc0MSIgdmVyc2lvbj0iMS4xIiB2aWV3Qm94PSIwIDAgNjIwIDc0MSI+PHRpdGxlPjQwNDwvdGl0bGU+PGcgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSI+PGcgZmlsbD0iI0ZGRiIgb3BhY2l0eT0iLjEiPjxnPjxwYXRoIGQ9Ik0xNTAuNzk0LDMxMyBMMTUwLjc5NCwyNDguNDE2IEwxLjUwMiwyNDguNDE2IEwxLjUwMiwxOTYgTDEyNS41MjIsMC44NDQgTDIxNy4yNSwwLjg0NCBMMjE3LjI1LDE4OS45MTYgTDI1Ny40OTgsMTg5LjkxNiBMMjU3LjQ5OCwyNDguNDE2IEwyMTcuMjUsMjQ4LjQxNiBMMjE3LjI1LDMxMyBMMTUwLjc5NCwzMTMgWiBNMTUwLjc5NCw1OS44MTIgTDY2LjU1NCwxODkuOTE2IEwxNTAuNzk0LDE4OS45MTYgTDE1MC43OTQsNTkuODEyIFoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC01NjcuMDAwMDAwLCAwLjAwMDAwMCkgdHJhbnNsYXRlKDU2Ny4wMDAwMDAsIDApIi8+PHBhdGggZD0iTTAuMzMyLDUyOS4xNTYgQzAuMzMyLDQ0OC42NiA0MC4xMTIsMzY4LjE2NCAxMjkuNSwzNjguMTY0IEMyMTguNDIsMzY4LjE2NCAyNTguNjY4LDQ0OC42NiAyNTguNjY4LDUyOS4xNTYgQzI1OC42NjgsNjA5LjY1MiAyMTguNDIsNjkwLjYxNiAxMjkuNSw2OTAuNjE2IEM0MC4xMTIsNjkwLjYxNiAwLjMzMiw2MDkuNjUyIDAuMzMyLDUyOS4xNTYgWiBNMTkwLjgwOCw1MjkuMTU2IEMxOTAuODA4LDQ3MS41OTIgMTczLjk2LDQyNy4xMzIgMTI5LjUsNDI3LjEzMiBDODQuNTcyLDQyNy4xMzIgNjcuNzI0LDQ3MS41OTIgNjcuNzI0LDUyOS4xNTYgQzY3LjcyNCw1ODYuNzIgODQuNTcyLDYzMS42NDggMTI5LjUsNjMxLjY0OCBDMTczLjk2LDYzMS42NDggMTkwLjgwOCw1ODYuNzIgMTkwLjgwOCw1MjkuMTU2IFoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC01NjcuMDAwMDAwLCAwLjAwMDAwMCkgdHJhbnNsYXRlKDU2Ny4wMDAwMDAsIDApIi8+PHBhdGggZD0iTTMxMS4zMzIsMTYyLjE1NiBDMzExLjMzMiw4MS42NiAzNTEuMTEyLDEuMTY0IDQ0MC41LDEuMTY0IEM1MjkuNDIsMS4xNjQgNTY5LjY2OCw4MS42NiA1NjkuNjY4LDE2Mi4xNTYgQzU2OS42NjgsMjQyLjY1MiA1MjkuNDIsMzIzLjYxNiA0NDAuNSwzMjMuNjE2IEMzNTEuMTEyLDMyMy42MTYgMzExLjMzMiwyNDIuNjUyIDMxMS4zMzIsMTYyLjE1NiBaIE01MDEuODA4LDE2Mi4xNTYgQzUwMS44MDgsMTA0LjU5MiA0ODQuOTYsNjAuMTMyIDQ0MC41LDYwLjEzMiBDMzk1LjU3Miw2MC4xMzIgMzc4LjcyNCwxMDQuNTkyIDM3OC43MjQsMTYyLjE1NiBDMzc4LjcyNCwyMTkuNzIgMzk1LjU3MiwyNjQuNjQ4IDQ0MC41LDI2NC42NDggQzQ4NC45NiwyNjQuNjQ4IDUwMS44MDgsMjE5LjcyIDUwMS44MDgsMTYyLjE1NiBaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgtNTY3LjAwMDAwMCwgMC4wMDAwMDApIHRyYW5zbGF0ZSg1NjcuMDAwMDAwLCAwKSIvPjxwYXRoIGQ9Ik00NjEuNzk0LDY5MCBMNDYxLjc5NCw2MjUuNDE2IEwzMTIuNTAyLDYyNS40MTYgTDMxMi41MDIsNTczIEw0MzYuNTIyLDM3Ny44NDQgTDUyOC4yNSwzNzcuODQ0IEw1MjguMjUsNTY2LjkxNiBMNTY4LjQ5OCw1NjYuOTE2IEw1NjguNDk4LDYyNS40MTYgTDUyOC4yNSw2MjUuNDE2IEw1MjguMjUsNjkwIEw0NjEuNzk0LDY5MCBaIE00NjEuNzk0LDQzNi44MTIgTDM3Ny41NTQsNTY2LjkxNiBMNDYxLjc5NCw1NjYuOTE2IEw0NjEuNzk0LDQzNi44MTIgWiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTU2Ny4wMDAwMDAsIDAuMDAwMDAwKSB0cmFuc2xhdGUoNTY3LjAwMDAwMCwgMCkiLz48L2c+PC9nPjwvZz48L3N2Zz4=);
          background-position-x: calc(50% - 129px);
          background-position-y: calc(50vh - 163px);
      }

      .footer {
          backdrop-filter: blur(16px);
          background-color: rgba(var(--theme-background-color), 0.5);
      }
  </style>
</head>

<body>
<div class="bg-404"></div>
<main class="main">
  <div>
    <h1>这里什么都没有</h1>
    <a class="button button-primary" href="./">返回首页</a>
  </div>
</main>
<?= $render('Components/Footer') ?>
</body>

</html>