document.addEventListener("DOMContentLoaded", function () {
    const deleteAccountForm =document.getElementById("deleteAccountForm");
    if (deleteAccountForm) {
        deleteAccountForm.addEventListener(
            "submit",
            function (event) {
                const confirmed = confirm("Are you sure you want to permanently delete your Inknest account? This action cannot be undone.");
                if (!confirmed) {
                    event.preventDefault();
                }
            }
        );
    }
    const deleteButtons =document.querySelectorAll(".delete-user");
    deleteButtons.forEach(
        function (button) {
            button.addEventListener(
                "click",
                function (event) {
                    const confirmed =confirm("Are you sure you want to delete this user?");
                    if (!confirmed) {
                        event.preventDefault();
                    }
                }
            );
        }
    );
    const logout =document.querySelector(
            'a[href="logout.php"]'
        );
    if (logout) {
        logout.addEventListener(
            "click",
            function (event) {
                const confirmed =
                    confirm("Are you sure you want to logout?");
                if (!confirmed) {
                    event.preventDefault();
                }
            }
        );
    }
});