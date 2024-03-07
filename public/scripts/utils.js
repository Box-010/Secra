(() => {
  function isUserLoggedIn() {
    return !!localStorage.getItem("username");
  }

  function getUser() {
    return localStorage.getItem("username");
  }

  function escapeHTML(unsafeText) {
    return unsafeText
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;")
      .replace(/\n/g, "<br>")
      .replace(/ /g, "&nbsp;");
  }

  function currentDateTime() {
    const formatter = new Intl.DateTimeFormat("zh-CN", {
      timeZone: "Asia/Shanghai",
      year: "numeric",
      month: "2-digit",
      day: "2-digit",
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
    });

    return formatter.format(new Date());
  }

  window.Utils = {
    isUserLoggedIn,
    escapeHTML,
    currentDateTime,
    getUser,
  };
})();