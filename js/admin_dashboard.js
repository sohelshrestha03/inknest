document.addEventListener("DOMContentLoaded", function () {
    const logout = document.querySelector(
        'a[href="admin_logout.php"]'
    );

    if (logout) {
        logout.addEventListener("click", function (event) {
            const confirmLogout = confirm("Are you sure you want to logout?");

            if (!confirmLogout) {
                event.preventDefault();
            }
        });

    }

    const actionLinks = document.querySelectorAll(".actions a");

    actionLinks.forEach(function (link) {
        link.addEventListener("mouseenter", function () {
            this.style.transform = "translateY(-2px)";
        });

        link.addEventListener("mouseleave", function () {
            this.style.transform = "translateY(0)";
        });
    });
});