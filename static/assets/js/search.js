(() => {
    const results = document.querySelector(".search-results");

    if (!results) {
        return;
    }

    const summary = results.querySelector(".search-results__summary");
    const list = results.querySelector(".search-results__list");
    const heading = results.querySelector("#results-title");
    const input = document.getElementById("search-page-input");
    const template = list.querySelector(".result").cloneNode(true);
    const query = (new URLSearchParams(window.location.search).get("s") || "").trim();

    if (input) {
        input.value = query;
    }

    if (!query) {
        summary.textContent = "Type a word or phrase above to search the site.";
        list.replaceChildren();
        return;
    }

    const renderResult = (entry) => {
        const item = template.cloneNode(true);
        const link = item.querySelector(".text-link");

        item.querySelector(".result__type").textContent = entry.type;
        item.querySelector(".result__title").textContent = entry.title;
        item.querySelector(".result__excerpt").textContent = entry.excerpt;
        link.href = entry.url;
        link.querySelector(".visually-hidden").textContent = `: ${entry.title}`;

        return item;
    };

    const describe = (count) => {
        const term = document.createElement("strong");
        term.textContent = query;

        if (count === 0) {
            return ["We found no results for '", term, "'. Try a different word, or browse our capabilities and sectors."];
        }

        const noun = count === 1 ? "result" : "results";

        return [`We found ${count} ${noun} for '`, term, "'"];
    };

    const showResults = (entries) => {
        const needle = query.toLocaleLowerCase("en-GB");
        const matches = entries.filter((entry) => `${entry.title} ${entry.excerpt}`.toLocaleLowerCase("en-GB").includes(needle));

        list.replaceChildren(...matches.map(renderResult));
        summary.replaceChildren(...describe(matches.length));

        heading.tabIndex = -1;
        heading.focus();
    };

    fetch("/assets/search-index.json")
        .then((response) => (response.ok ? response.json() : Promise.reject(response.status)))
        .then(showResults)
        .catch(() => {});
})();
