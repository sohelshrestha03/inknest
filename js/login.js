document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("loginForm");
    const login = document.getElementById("login");
    const password = document.getElementById("password");

    form.addEventListener("submit", function (event) {
        let valid = true;
        clearErrors();

        if (login.value.trim() === "") {
            showError(login,"Username or phone number is required.");
            valid = false;
        }

        if (password.value === "") {
            showError(password,"Password is required.");
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