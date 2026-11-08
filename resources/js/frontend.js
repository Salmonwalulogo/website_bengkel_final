document.querySelectorAll(".nav-toggle").forEach((toggle) => {
    const navbar = toggle.closest(".navbar");
    const links = navbar?.querySelector(".nav-links");

    if (!links) {
        return;
    }

    const closeDropdown = () => {
        const dropdown = navbar.querySelector(".nav-dropdown");
        const dropdownToggle = dropdown?.querySelector(".nav-dropdown-toggle");

        dropdown?.classList.remove("open");
        dropdownToggle?.setAttribute("aria-expanded", "false");
    };

    const closeMenu = () => {
        closeDropdown();
        links.classList.remove("open");
        toggle.setAttribute("aria-expanded", "false");
        toggle.setAttribute("aria-label", "Buka menu navigasi");
        toggle.querySelector("i")?.classList.replace("bi-x-lg", "bi-list");
    };

    const dropdownToggle = navbar.querySelector(".nav-dropdown-toggle");
    dropdownToggle?.addEventListener("click", () => {
        const dropdown = dropdownToggle.closest(".nav-dropdown");
        const isOpen = dropdown?.classList.toggle("open") ?? false;

        dropdownToggle.setAttribute("aria-expanded", String(isOpen));
    });

    toggle.addEventListener("click", () => {
        const isOpen = links.classList.toggle("open");
        toggle.setAttribute("aria-expanded", String(isOpen));
        toggle.setAttribute(
            "aria-label",
            isOpen ? "Tutup menu navigasi" : "Buka menu navigasi",
        );
        toggle.querySelector("i")?.classList.toggle("bi-list", !isOpen);
        toggle.querySelector("i")?.classList.toggle("bi-x-lg", isOpen);
    });

    links.addEventListener("click", (event) => {
        if (event.target instanceof Element && event.target.closest("a")) {
            closeMenu();
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            closeMenu();
        }
    });

    document.addEventListener("click", (event) => {
        if (event.target instanceof Node && !navbar.contains(event.target)) {
            closeDropdown();
        }
    });
});
