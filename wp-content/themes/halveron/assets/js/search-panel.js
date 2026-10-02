(() => {
    const panel = document.getElementById("search-panel");
    const link = document.querySelector("[data-search-link]");
    const toggle = document.querySelector("[data-search-toggle]");

    if (!panel || !link || !toggle) {
        return;
    }

    const input = panel.querySelector("input[type='search']");
    const close = panel.querySelector("[data-search-close]");

    const setOpen = (open) => {
        panel.hidden = !open;
        toggle.setAttribute("aria-expanded", String(open));

        if (open) {
            input.focus();
            return;
        }

        toggle.focus();
    };

    link.hidden = true;
    toggle.hidden = false;

    toggle.addEventListener("click", () => setOpen(panel.hidden));
    close.addEventListener("click", () => setOpen(false));

    document.addEventListener("keydown", (event) => {
        if (event.key !== "Escape" || panel.hidden) {
            return;
        }

        setOpen(false);
    });
})();
