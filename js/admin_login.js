document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("adminLoginForm");
    const username = document.getElementById("username");
    const password = document.getElementById("password");


    form.addEventListener("submit", function (event) {
        let valid = true;
        clearErrors();

        if (username.value.trim() === "") {
            showError(
                username,
                "Username is required."
            );
            valid = false;
        }

        if (password.value === "") {
            showError(
                password,
                "Password is required."
            );

            valid = false;
        }

        if (!valid) {
            event.preventDefault();
        }

    });


    function showError(input, message) {
        input.style.borderColor = "#d00000";
        const error = document.createElement("small");
        error.className = "error-message";
        error.textContent = message;
        error.style.color = "#d00000";
        error.style.fontSize = "12px";
        error.style.display = "block";
        error.style.marginTop = "5px";
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
                input.style.borderColor = "";
            });
    }
});