document.addEventListener("DOMContentLoaded", function () {
    const deleteButtons = document.querySelectorAll(".delete-user");

    deleteButtons.forEach(function (button) {
        button.addEventListener("click", function (event) {
            const confirmDelete = confirm("Are you sure you want to delete this user?");

            if (!confirmDelete) {
                event.preventDefault();
            }
        });
    });

    const logout = document.querySelector('a[href="admin_logout.php"]');

    if (logout) {
        logout.addEventListener("click", function (event) {
            const confirmLogout = confirm("Are you sure you want to logout?");
            if (!confirmLogout) {
                event.preventDefault();
            }
        });
    }
});