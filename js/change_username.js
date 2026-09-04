document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("usernameForm");
    const username = document.getElementById("username");

    if (!form || !username) {
        return;
    }

    form.addEventListener("submit", function (event) {
        username.classList.remove("input-error");
        const oldError = document.querySelector(".error-text");
        if (oldError) {
            oldError.remove();
        }
        const value = username.value.trim();
        if (value === "") {
            event.preventDefault();
            showError("Username is required.");
            return;
        }

        if (value.length < 4) {
            event.preventDefault();
            showError("Username must be at least 4 characters.");
            return;
        }

        if (value.length > 50) {
            event.preventDefault();
            showError("Username cannot exceed 50 characters.");
            return;
        }

        const usernamePattern = /^[A-Za-z0-9_]+$/;
        if (!usernamePattern.test(value)) {
            event.preventDefault();
            showError(
                "Username can contain letters, numbers and underscore only."
            );
            return;
        }
    });

    function showError(message) {
        username.classList.add("input-error");
        const error = document.createElement("span");
        error.className = "error-text";
        error.textContent = message;
        username.parentElement.appendChild(error);
    }

    username.addEventListener("input", function () {
        username.classList.remove("input-error");
        const error = username.parentElement.querySelector(".error-text");
        if (error) {
            error.remove();
        }
    });
});