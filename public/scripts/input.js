(() => {
  function checkValidity(input) {
    if (input.validity.valid === false) {
      input.parentElement.classList.add("textfield--error");
    } else {
      input.parentElement.classList.remove("textfield--error");
    }
  }

  function checkNotEmpty(input) {
    if (input.value.length > 0) {
      input.parentElement.classList.add("textfield--not-empty");
    } else {
      input.parentElement.classList.remove("textfield--not-empty");
    }
  }

  function focusEventHandler(ev) {
    ev.target.parentElement.classList.add("textfield--focused");
    checkNotEmpty(ev.target);
  }

  function blurEventHandler(ev) {
    ev.target.parentElement.classList.remove("textfield--focused");
    checkValidity(ev.target);
    checkNotEmpty(ev.target);
  }

  function inputEventHandler(ev) {
    checkNotEmpty(ev.target);
  }

  function changeEventHandler(ev) {
    checkValidity(ev.target);
  }

  function init() {
    document.querySelectorAll(".textfield input").forEach((input) => {
      input.removeEventListener("focus", focusEventHandler);
      input.removeEventListener("blur", blurEventHandler);
      input.addEventListener("focus", focusEventHandler);
      input.addEventListener("blur", blurEventHandler);
      input.removeEventListener("input", inputEventHandler);
      input.addEventListener("input", inputEventHandler);
      input.removeEventListener("change", changeEventHandler);
      input.addEventListener("change", changeEventHandler);

      if (input.disabled) {
        input.parentElement.classList.add("textfield--disabled");
      } else {
        input.parentElement.classList.remove("textfield--disabled");
      }
      checkNotEmpty(input);
    });
  }

  window.initInput = init;

  document.addEventListener("DOMContentLoaded", init);
})();