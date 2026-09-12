document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".add-cart");
    const cartCount = document.getElementById("cartCount");
    let cart = JSON.parse(localStorage.getItem("inknestCart")) || [];
    updateCartCount();
    buttons.forEach(function (button) {
        button.addEventListener("click", function () {
            const productId = this.dataset.id;

            if (this.disabled) {
                return;
            }
            this.disabled = true;
            const formData = new FormData();
            formData.append(
                "product_id",
                productId
            );
            fetch("add_to_cart.php", {
                method: "POST",
                body: formData
            })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data.success) {
                    fetch("record_activity.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            product_id: productId
                        })
                    })
                    .then(function (activityResponse) {
                        return activityResponse.json();
                    })
                    .then(function (activityData) {

                        if (!activityData.success) {
                            console.error(
                                "Activity was not recorded:",
                                activityData.message
                            );
                        }
                    })
                    .catch(function (error) {
                        console.error(
                            "Activity recording error:",
                            error
                        );
                    });

                    cart.push(productId);
                    localStorage.setItem(
                        "inknestCart",
                        JSON.stringify(cart)
                    );
                    updateCartCount();

                    const productCard=button.closest(".product-card");
                    const stockElement=productCard.querySelector(".stock");

                    if (data.stock > 0) {
                        stockElement.innerHTML="Available: <strong>" +
                            data.stock +
                            "</strong>";
                    } else {
                        stockElement.textContent="Out of Stock";
                        stockElement.classList.remove(
                            "available"
                        );
                        stockElement.classList.add(
                            "out-of-stock"
                        );
                    }

                    if (data.stock <= 0) {
                        button.textContent="Out of Stock";
                        button.classList.add("disabled");
                        button.disabled = true;
                    } else {
                        button.textContent ="Added";
                        setTimeout(function () {
                            button.textContent="Add to Cart";
                            button.disabled = false;
                        }, 1000);
                    }
                } else {
                    alert(data.message);
                    button.disabled = false;
                }
            })
            .catch(function (error) {
                console.error(error);
                alert("Something went wrong. Please try again.");
                button.disabled = false;
            });
        });
    });

    function updateCartCount() {
        if (cartCount) {
            cartCount.textContent=cart.length;
        }
    }
});