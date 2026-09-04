document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("addProductForm");
    const productName = document.getElementById("product_name");
    const description = document.getElementById("description");
    const price = document.getElementById("price");
    const image = document.getElementById("image");
    const previewImage = document.getElementById("previewImage");

    image.addEventListener("change", function () {
        const file = this.files[0];
        if (!file) {
            previewImage.style.display = "none";
            previewImage.src = "";
            return;
        }

        const allowedTypes = [
            "image/jpeg",
            "image/png",
            "image/webp"
        ];

        if (!allowedTypes.includes(file.type)) {
            alert("Please select a JPG, JPEG, PNG or WEBP image.");
            this.value = "";
            previewImage.style.display = "none";
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            alert("Image size must be less than 5MB.");
            this.value = "";
            previewImage.style.display = "none";
            return;
        }

        const reader = new FileReader();
        reader.onload = function (event) {
            previewImage.src = event.target.result;
            previewImage.style.display = "block";
        };

        reader.readAsDataURL(file);
    });

    form.addEventListener("submit", function (event) {
        let valid = true;
        clearErrors();

        if (productName.value.trim() === "") {
            showError(productName, "Product name is required.");
            valid = false;
        }

        if (description.value.trim() === "") {
            showError(description, "Product description is required.");
            valid = false;
        }

        if (price.value.trim() === "") {
            showError(price, "Product price is required.");
            valid = false;
        } else if (parseFloat(price.value) <= 0) {
            showError(price, "Price must be greater than 0.");
            valid = false;
        }

        if (image.files.length === 0) {
            showError(image, "Product image is required.");
            valid = false;
        }

        if (!valid) {
            event.preventDefault();
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
        document
            .querySelectorAll(".error-text")
            .forEach(function (error) {
                error.remove();
            });

        document
            .querySelectorAll(".input-error")
            .forEach(function (input) {
                input.classList.remove("input-error");
            });
    }

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