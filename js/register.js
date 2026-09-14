document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("registerForm");
    const fname = document.getElementById("fname");
    const lname = document.getElementById("lname");
    const uname = document.getElementById("uname");
    const email = document.getElementById("email");
    const contact = document.getElementById("contact");
    const dob = document.getElementById("dob");
    const password = document.getElementById("npassword");
    const confirmPassword = document.getElementById("cpassword");
    const cancelButton = document.getElementById("cancelButton");
    if (form) {
        form.addEventListener("submit", function (event) {
            let valid = true;
            clearErrors();
            if (fname.value.trim() === "") {
                showError(fname, "First name is required.");
                valid = false;
            } else if (!/^[A-Za-z]+$/.test(fname.value.trim())) {

                showError(
                    fname,
                    "First name can contain letters only."
                );
                valid = false;
            }
            if (lname.value.trim() === "") {
                showError(lname, "Last name is required.");
                valid = false;
            } else if (!/^[A-Za-z]+$/.test(lname.value.trim())) {
                showError(
                    lname,
                    "Last name can contain letters only."
                );
                valid = false;
            }
            if (uname.value.trim() === "") {
                showError(uname, "Username is required.");
                valid = false;
            } else if (uname.value.trim().length < 4) {
                showError(
                    uname,
                    "Username must be at least 4 characters."
                );
                valid = false;
            } else if (!/^[A-Za-z0-9_]+$/.test(uname.value.trim())) {
                showError(
                    uname,
                    "Username can contain letters, numbers and underscore only."
                );
                valid = false;
            }
            if (email.value.trim() === "") {
                showError(email, "Email is required.");
                valid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
                showError(
                    email,
                    "Please enter a valid email address."
                );
                valid = false;
            }
            if (contact.value.trim() === "") {
                showError(contact, "Phone number is required.");
                valid = false;
            } else if (!/^\d{10}$/.test(contact.value.trim())) {
                showError(
                    contact,
                    "Phone number must contain exactly 10 digits."
                );
                valid = false;
            }
            if (dob) {
                if (dob.value.trim() === "") {
                    showError(
                        dob,
                        "Date of birth is required."
                    );
                    valid = false;
                } else {
                    const birthDate = new Date(dob.value + "T00:00:00");
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    if (isNaN(birthDate.getTime())) {
                        showError(
                            dob,
                            "Please enter a valid date of birth."
                        );
                        valid = false;
                    } else if (birthDate > today) {
                        showError(
                            dob,
                            "Date of birth cannot be in the future."
                        );
                        valid = false;
                    } else {
                        let age = today.getFullYear() - birthDate.getFullYear();
                        const monthDifference =today.getMonth() - birthDate.getMonth();
                        if (monthDifference < 0 ||(monthDifference === 0 && today.getDate() < birthDate.getDate())) {
                            age--;
                        }
                        if (age < 18) {
                            showError(
                                dob,
                                "You must be at least 18 years old to register."
                            );
                            valid = false;
                        }
                    }
                }
            }
            if (password.value === "") {
                showError(
                    password,
                    "Password is required."
                );
                valid = false;
            } else if (password.value.length < 8) {
                showError(
                    password,
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
            } else if (password.value !== confirmPassword.value) {
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
    }
    if (contact) {
        contact.addEventListener(
            "input",
            function () {
                this.value = this.value.replace(/\D/g, "").slice(0, 10);
            }
        );
    }
    if (cancelButton) {
        cancelButton.addEventListener(
            "click",
            function () {
                if (form) {
                    form.reset();
                }
                clearErrors();
                fetch(
                    "auth/cancel_registration.php",
                    {
                        method: "POST"
                    }
                ).catch(function (error) {
                    console.error(
                        "Unable to clear registration session:",
                        error
                    );
                });
            }
        );
    }
    function showError(
        input,
        message
    ) {
        input.classList.add("error");
        const existingError =
            input.parentElement.querySelector(
                ".error-message"
            );
        if (existingError) {
            existingError.remove();
        }
        const error =document.createElement("small");
        error.className = "error-message";
        error.textContent = message;
        input.parentElement.appendChild(
            error
        );
    }
    function clearErrors() {
        document
            .querySelectorAll(
                ".error-message"
            )
            .forEach(function (error) {
                error.remove();

            });
        document
            .querySelectorAll(
                "#registerForm input"
            )
            .forEach(function (input) {
                input.classList.remove(
                    "error"
                );
            });
    }
});