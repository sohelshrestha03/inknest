document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("emailForm");
    const email = document.getElementById("email");

    if (!form || !email) {
        return;
    }

    form.addEventListener("submit", function (event) {
        email.classList.remove("input-error");
        const oldError = document.querySelector(".error-text");
        if (oldError) {
            oldError.remove();
        }
        const value = email.value.trim();
        if (value === "") {
            event.preventDefault();
            showError("Email is required.");
            return;
        }

        const emailPattern =
            /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(value)) {
            event.preventDefault();
            showError("Please enter a valid email address.");
            return;
        }

        if (value.length > 100) {
            event.preventDefault();
            showError("Email cannot exceed 100 characters.");
            return;
        }
    });


    function showError(message) {
        email.classList.add("input-error");
        const error = document.createElement("span");
        error.className = "error-text";
        error.textContent = message;
        email.parentElement.appendChild(error);
    }

    email.addEventListener("input", function () {
        email.classList.remove("input-error");
        const error =email.parentElement.querySelector(".error-text");
        if (error) {
            error.remove();
        }
    });
});