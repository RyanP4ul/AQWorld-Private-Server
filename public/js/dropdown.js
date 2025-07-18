function initDropdown(buttonId, menuSelector) {
    const button = document.getElementById(buttonId);
    const menu = document.querySelector(menuSelector);

    let isOpen = false;

    function showMenu() {
        menu.classList.remove("hidden");
        requestAnimationFrame(() => {
            menu.classList.remove("opacity-0", "scale-95");
            menu.classList.add("opacity-100", "scale-100");
        });
    }

    function hideMenu() {
        menu.classList.remove("opacity-100", "scale-100");
        menu.classList.add("opacity-0", "scale-95");

        // Delay hiding to match transition duration
        setTimeout(() => {
            menu.classList.add("hidden");
        }, 75);
    }

    button.addEventListener("click", (e) => {
        e.stopPropagation();
        isOpen = !isOpen;
        if (isOpen) {
            showMenu();
        } else {
            hideMenu();
        }
    });

    document.addEventListener("click", (e) => {
        if (!button.contains(e.target) && !menu.contains(e.target)) {
            if (isOpen) {
                hideMenu();
                isOpen = false;
            }
        }
    });
}

document.addEventListener("DOMContentLoaded", () => {
    initDropdown("menu-button", "#menu-dropdown");
});