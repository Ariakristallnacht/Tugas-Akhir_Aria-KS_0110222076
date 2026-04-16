(() => {
    "use strict";

    const hideLoaders = () => {
        document.querySelectorAll(".page-loader").forEach((loader) => {
            loader.classList.add("opacity-0");
            loader.style.pointerEvents = "none";

            window.setTimeout(() => {
                loader.classList.add("hidden");
                loader.style.display = "none";
            }, 300);
        });
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () => {
            window.setTimeout(hideLoaders, 150);
        });
    } else {
        window.setTimeout(hideLoaders, 150);
    }

    window.addEventListener("load", hideLoaders);
})();
