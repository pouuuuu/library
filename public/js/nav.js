document.addEventListener("DOMContentLoaded", () => {
    document.querySelector("nav button.search-button").addEventListener("click", () => {
        const searchBar = document.querySelector("header .search-bar");
        searchBar.classList.toggle("hidden");

        if (!searchBar.classList.contains("hidden")) {
            setTimeout(() => {
                searchBar.querySelector("input").focus();
            }, 50);
        }
    })
})