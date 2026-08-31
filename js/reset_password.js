document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("resetForm");
    const newPassword = document.getElementById("new_password");
    const confirmPassword = document.getElementById("confirm_password");


    form.addEventListener("submit", function (event) {
        let valid = true;
        clearErrors();

        if (newPassword.value === "") {
            showError(
                newPassword,
                "Password is required."
            );
            valid = false;
        } else if (newPassword.value.length < 8) {
            showError(
                newPassword,
                "Password must be at least 8 characters."
            );
            valid = false;
        }

        if (confirmPassword.value === "") {
            showError(
                confirmPassword,
                "Please confirm your password."
            );
            valid = false;
        } else if (newPassword.value !== confirmPassword.value) {
            showError(
                confirmPassword,
                "Passwords do not match."
            );
            valid = false;
        }
        if (!valid) {
            event.preventDefault();
        }
    });


    function showError(input, message) {
        input.classList.add("input-error");
        const error = document.createElement("small");
        error.className = "error-message";
        error.textContent = message;
        input.parentElement.appendChild(error);
    }


    function clearErrors() {
        document
            .querySelectorAll(".error-message")
            .forEach(function (error) {
                error.remove();
            });

        document
            .querySelectorAll("input")
            .forEach(function (input) {
                input.classList.remove("input-error");
            });
    }
});