document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("passwordForm");
    const currentPassword =document.getElementById("current_password");
    const newPassword =document.getElementById("new_password");
    const confirmPassword =document.getElementById("confirm_password");

    if (!form) {
        return;
    }

    form.addEventListener("submit", function (event) {
        clearErrors();
        if (currentPassword.value.trim() === "") {
            event.preventDefault();
            showError(
                currentPassword,
                "Current password is required."
            );
            return;
        }

        if (newPassword.value.trim() === "") {
            event.preventDefault();
            showError(
                newPassword,
                "New password is required."
            );

            return;
        }

        if (newPassword.value.length < 8) {
            event.preventDefault();
            showError(
                newPassword,
                "Password must be at least 8 characters."
            );
            return;
        }

        if (confirmPassword.value.trim() === "") {
            event.preventDefault();
            showError(
                confirmPassword,
                "Please confirm your new password."
            );
            return;
        }

        if (newPassword.value !== confirmPassword.value) {
            event.preventDefault();
            showError(
                confirmPassword,
                "Passwords do not match."
            );
            return;
        }
    });

    function showError(input, message) {
        input.classList.add("input-error");
        const error = document.createElement("span");
        error.className = "error-text";
        error.textContent = message;
        input.parentElement.appendChild(error);
    }

    function clearErrors() {
        const inputs = [
            currentPassword,
            newPassword,
            confirmPassword
        ];

        inputs.forEach(function (input) {
            input.classList.remove("input-error");
            const error =input.parentElement.querySelector(".error-text");
            if (error) {
                error.remove();
            }
        });
    }

    currentPassword.addEventListener("input", function () {
        removeError(currentPassword);
    });

    newPassword.addEventListener("input", function () {
        removeError(newPassword);
        if (confirmPassword.value !== "" && newPassword.value !== confirmPassword.value) {
            confirmPassword.classList.add("input-error");
        } else {
            removeError(confirmPassword);
        }
    });

    confirmPassword.addEventListener("input", function () {
        removeError(confirmPassword);

        if (confirmPassword.value !== "" && newPassword.value !== confirmPassword.value) {
            confirmPassword.classList.add("input-error");
        }
    });

    function removeError(input) {
        input.classList.remove("input-error");
        const error =input.parentElement.querySelector(".error-text");
        if (error) {
            error.remove();
        }
    }
});