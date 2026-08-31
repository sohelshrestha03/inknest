document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("forgotForm");
    const email = document.getElementById("email");

    form.addEventListener("submit", function (event) {
        let valid = true;
        clearErrors();
        const emailValue = email.value.trim();

        if (emailValue === "") {
            showError(email, "Email is required.");
            valid = false;
        }
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue)) {
            showError(email, "Please enter a valid email address.");
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