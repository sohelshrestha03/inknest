document.addEventListener("DOMContentLoaded", function () {
    const logout = document.querySelector('a[href="admin_logout.php"]');

    if (logout) {
        logout.addEventListener("click", function (event) {
            const confirmLogout = confirm("Are you sure you want to logout?");
            if (!confirmLogout) {
                event.preventDefault();
            }
        });

    }

    const forms = document.querySelectorAll(".actions form");

    forms.forEach(function (form) {
        form.addEventListener("submit", function (event) {
            const select = form.querySelector(
                ".status-select"
            );
            const status = select.value;
            const confirmUpdate = confirm(
                "Change order status to " + status + "?"
            );
            if (!confirmUpdate) {
                event.preventDefault();
            }
        });
    });
});