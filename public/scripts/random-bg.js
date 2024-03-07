(() => {
  const bgImages = [
    "bg-jigsaw",
    "bg-architect",
    "bg-hideout",
    "bg-dominos",
    "bg-pie-factory",
    "bg-i-like-food",
    "bg-kiwi",
    "bg-random-shapes",
    "bg-x-equals",
    "bg-glamorous",
    "bg-circuit-board",
  ];

  window.addRandomBackground = (element) => {
    if (!element) return;
    if (typeof element === "string") {
      element = document.querySelector(element);
    }

    bgImages.forEach((c) => {
      element.classList.remove(c);
    });
    element.classList.add(
      bgImages[Math.floor(Math.random() * bgImages.length)]
    );
  }
})();