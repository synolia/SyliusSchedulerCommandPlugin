(function() {
  let lastSize = 0;
  let grep = "";
  let invert = 0;
  let documentHeight = 0;
  let scrollPosition = 0;
  let scroll = false;

  const grepInput = document.getElementById("grep");
  const results = document.getElementById("results");
  const floatElements = document.querySelectorAll(".float");

  // Handle "Enter" keyup in #grep
  grepInput.addEventListener("keyup", function (e) {
    if (e.key === "Enter") {
      lastSize = 0;
      grep = this.value;
      results.innerHTML = "";
    }
  });

  // Focus on #grep input
  grepInput.focus();
  // Periodic log update
  const updateLog = setInterval(function () {
    fetch(`${sy_route}?refresh=1&lastsize=${lastSize}&grep-keywords=${grep}&invert=${invert}`)
      .then(response => response.json())
      .then(data => {
        lastSize = data.size;
        data.data.forEach(value => {
          const entry = document.createElement("div");
          entry.innerHTML = value + "<br/>";
          results.prepend(entry);
        });

        if (scroll) {
          scrollToBottom();
        }

        if (data.data.length < 1) {
          const loader = results.querySelector(".loader");
          if (loader) loader.remove();
        }
      })
      .catch(error => {
        results.innerHTML += error.message;
        clearInterval(updateLog);
      });
  }, sy_updateTime);

  // Fix float element on scroll
  window.addEventListener("scroll", function () {
    const scrollTop = window.scrollY;
    floatElements.forEach(el => {
      if (scrollTop > 0) {
        el.style.position = "fixed";
        el.style.top = "0";
        el.style.left = "auto";
      } else {
        el.style.position = "static";
      }
    });

    documentHeight = document.documentElement.scrollHeight;
    scrollPosition = window.innerHeight + window.scrollY;
    scroll = documentHeight <= scrollPosition;
  });

  // Scroll to bottom on resize if needed
  window.addEventListener("resize", function () {
    if (scroll) {
      scrollToBottom();
    }
  });

  scrollToBottom();

  // Scroll to bottom function
  function scrollToBottom() {
    window.scrollTo({
      top: document.body.scrollHeight,
      behavior: "smooth"
    });
  }
})();