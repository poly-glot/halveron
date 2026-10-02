(() => {
    const initialise = (carousel, index) => {
        const track = carousel.querySelector("[data-carousel-track]");
        const slides = [...track.querySelectorAll("[data-carousel-slide]")];
        const dots = [...carousel.querySelectorAll("[data-carousel-dot]")];
        const previous = carousel.querySelector("[data-carousel-previous]");
        const next = carousel.querySelector("[data-carousel-next]");

        track.id ||= `carousel-track-${index + 1}`;

        carousel.querySelectorAll("[hidden]").forEach((control) => {
            control.hidden = false;
        });

        [previous, next, ...dots].forEach((control) => control?.setAttribute("aria-controls", track.id));

        const step = () => (slides.length > 1 ? slides[1].offsetLeft - slides[0].offsetLeft : track.clientWidth);
        const scrollBySlides = (direction) => track.scrollBy({ left: direction * step(), behavior: "smooth" });

        previous?.addEventListener("click", () => scrollBySlides(-1));
        next?.addEventListener("click", () => scrollBySlides(1));

        dots.forEach((dot, dotIndex) => {
            dot.addEventListener("click", () => track.scrollTo({ left: dotIndex * step(), behavior: "smooth" }));
        });

        const updateControls = () => {
            const maxScroll = track.scrollWidth - track.clientWidth - 2;
            const activeIndex = Math.round(track.scrollLeft / (step() || 1));

            dots.forEach((dot, dotIndex) => dot.setAttribute("aria-current", String(dotIndex === activeIndex)));

            previous?.setAttribute("aria-disabled", String(track.scrollLeft <= 2));
            next?.setAttribute("aria-disabled", String(track.scrollLeft >= maxScroll));
        };

        track.addEventListener("scroll", updateControls, { passive: true });
        window.addEventListener("resize", updateControls);
        updateControls();
    };

    document.querySelectorAll("[data-carousel]").forEach(initialise);
})();
