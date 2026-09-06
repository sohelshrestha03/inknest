document.addEventListener("DOMContentLoaded", function () {
    const form =document.getElementById("productForm");
    const productName =document.getElementById("product_name");
    const category =document.getElementById("category");
    const description =document.getElementById("description");
    const price =document.getElementById("price");
    const stock =document.getElementById("stock");
    const productImage =document.getElementById("product_image");
    const preview =document.getElementById("imagePreview");
    const previewImage =document.getElementById("previewImage");
    const productNameError =document.getElementById("productNameError");
    const categoryError =document.getElementById("categoryError");
    const descriptionError =document.getElementById("descriptionError");
    const priceError =document.getElementById("priceError");
    const stockError =document.getElementById("stockError");
    const imageError =document.getElementById("imageError");

    function clearErrors() {
        productNameError.textContent = "";
        categoryError.textContent = "";
        descriptionError.textContent = "";
        priceError.textContent = "";
        stockError.textContent = "";
        imageError.textContent = "";
        productName.classList.remove("input-error");
        category.classList.remove("input-error");
        description.classList.remove("input-error");
        price.classList.remove("input-error");
        stock.classList.remove("input-error");
        productImage.classList.remove("input-error");
    }

    productImage.addEventListener(
        "change",
        function () {
            imageError.textContent = "";
            productImage.classList.remove(
                "input-error"
            );
            if (this.files.length === 0) {
                preview.classList.remove("show");
                previewImage.src = "";
                return;
            }
            const file = this.files[0];
            const allowedTypes = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];
            const maxSize =5 * 1024 * 1024;
            if (!allowedTypes.includes(file.type)) {
                imageError.textContent =
                    "Only JPG, PNG and WEBP images are allowed.";
                productImage.classList.add(
                    "input-error"
                );
                this.value = "";
                preview.classList.remove(
                    "show"
                );
                return;
            }

            if (file.size > maxSize) {
                imageError.textContent =
                    "Image size must be less than 5 MB.";
                productImage.classList.add(
                    "input-error"
                );
                this.value = "";
                preview.classList.remove(
                    "show"
                );
                return;
            }

            const reader =new FileReader();
            reader.onload = function (event) {
                previewImage.src =event.target.result;
                preview.classList.add("show");
            };
            reader.readAsDataURL(file);
        }
    );

    form.addEventListener(
        "submit",
        function (event) {
            clearErrors();
            let valid = true;
            if (productName.value.trim() === "") {
                productNameError.textContent ="Product name is required.";
                productName.classList.add("input-error");
                valid = false;
            } else if (productName.value.trim().length < 2) {
                productNameError.textContent ="Product name must be at least 2 characters.";
                productName.classList.add("input-error");
                valid = false;
            }

            if (category.value.trim() === "") {
                categoryError.textContent ="Category is required.";
                category.classList.add("input-error");
                valid = false;
            }

            if (description.value.trim() === "") {
                descriptionError.textContent ="Description is required.";
                description.classList.add("input-error");
                valid = false;
            } else if (description.value.trim().length < 5) {
                descriptionError.textContent ="Description must be at least 5 characters.";
                description.classList.add("input-error");
                valid = false;
            }

            const priceValue =parseFloat(price.value);
            if (price.value.trim() === "") {
                priceError.textContent ="Price is required.";
                price.classList.add("input-error");
                valid = false;
            } else if (isNaN(priceValue)) {
                priceError.textContent ="Please enter a valid price.";
                price.classList.add("input-error");
                valid = false;
            } else if (priceValue <= 0) {
                priceError.textContent ="Price must be greater than 0.";
                price.classList.add("input-error");
                valid = false;
            }

            const stockValue =Number(stock.value);
            if (stock.value.trim() === "") {
                stockError.textContent ="Stock quantity is required.";
                stock.classList.add("input-error");
                valid = false;
            } else if (!Number.isInteger(stockValue)) {
                stockError.textContent ="Stock must be a whole number.";
                stock.classList.add("input-error");
                valid = false;
            } else if (stockValue < 0) {
                stockError.textContent ="Stock cannot be negative.";
                stock.classList.add("input-error");
                valid = false;
            }

            if (productImage.files.length > 0) {
                const file =productImage.files[0];
                const allowedTypes = [
                    "image/jpeg",
                    "image/png",
                    "image/webp"
                ];

                const maxSize =5 * 1024 * 1024;


                if (!allowedTypes.includes(file.type)) {
                    imageError.textContent ="Only JPG, PNG and WEBP images are allowed.";
                    productImage.classList.add("input-error");
                    valid = false;
                }

                if (file.size > maxSize) {
                    imageError.textContent ="Image size must be less than 5 MB.";
                    productImage.classList.add("input-error");
                    valid = false;
                }
            }

            if (!valid) {
                event.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: "smooth"
                });
            }
        }
    );


    
    productName.addEventListener(
        "input",
        function () {
            productNameError.textContent = "";
            productName.classList.remove(
                "input-error"
            );
        }
    );

    category.addEventListener(
        "input",
        function () {
            categoryError.textContent = "";
            category.classList.remove(
                "input-error"
            );
        }
    );


    description.addEventListener(
        "input",
        function () {
            descriptionError.textContent = "";
            description.classList.remove(
                "input-error"
            );
        }
    );


    price.addEventListener(
        "input",
        function () {
            priceError.textContent = "";
            price.classList.remove(
                "input-error"
            );
        }
    );


    stock.addEventListener(
        "input",
        function () {
            stockError.textContent = "";
            stock.classList.remove(
                "input-error"
            );
        }
    );

});