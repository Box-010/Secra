(() => {
    function loadMore(url) {
        return fetch(url).then(response => response.text())
    }

    function initLoadMore() {
        const indicators = document.querySelectorAll(".load-more-indicator");

        indicators.forEach(indicator => {
            indicator.addEventListener("click", ev => {
                ev.preventDefault();
                const {nextUrl} = ev.target.dataset;
                if (!nextUrl) return;
                ev.target.querySelector(".load-more-indicator-text").innerText = "正在加载…";
                loadMore(nextUrl).then(html => {
                    ev.target.outerHTML = html;
                }).catch(error => {
                    ev.target.querySelector(".load-more-indicator-text").innerText = "加载失败，请重试";
                });
            });
        });
    }

    window.initLoadMore = initLoadMore;
    window.addEventListener("DOMContentLoaded", initLoadMore);
})();