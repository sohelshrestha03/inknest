document.addEventListener("DOMContentLoaded", function () {
    const buttons =document.querySelectorAll(".add-cart");
    const wishlistButtons =document.querySelectorAll(".wishlist-btn");
    const cartCount =document.getElementById("cartCount");
    let cart = [];
    try {
        cart =
            JSON.parse(
                localStorage.getItem("inknestCart")
            ) || [];
    } catch (error) {
        cart = [];
    }

    updateCartCount();
    buttons.forEach(function (button) {
        button.addEventListener(
            "click",
            function () {
                const productId =
                    this.dataset.id;

                if (!productId || this.disabled) {
                    return;
                }
                this.disabled = true;
                const formData =new FormData();
                formData.append(
                    "product_id",
                    productId
                );

                fetch(
                    "add_to_cart.php",
                    {
                        method: "POST",
                        body: formData
                    }
                )
                .then(function (response) {

                    return response.json();
                })
                .then(function (data) {

                    if (!data.success) {

                        throw new Error(
                            data.message ||
                            "Unable to add to cart."
                        );
                    }

                    fetch(
                        "record_activity.php",
                        {
                            method: "POST",
                            headers: {
                                "Content-Type":
                                    "application/json"
                            },
                            body: JSON.stringify({
                                product_id:
                                    productId
                            })
                        }
                    )
                    .then(function (response) {

                        return response.json();
                    })
                    .then(function (activityData) {

                        if (
                            !activityData.success
                        ) {

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

                    const productCard =
                        button.closest(
                            ".product-card"
                        );

                    if (!productCard) {
                        return;
                    }

                    const stockElement =
                        productCard.querySelector(
                            ".stock"
                        );

                    if (
                        stockElement &&
                        data.stock !== undefined
                    ) {

                        if (data.stock > 0) {

                            stockElement.innerHTML =
                                "Available: <strong>" +
                                data.stock +
                                "</strong>";

                        } else {

                            stockElement.textContent =
                                "Out of Stock";

                            stockElement.classList.remove(
                                "available"
                            );

                            stockElement.classList.add(
                                "out-of-stock"
                            );
                        }
                    }

                    if (
                        data.stock <= 0
                    ) {

                        button.textContent =
                            "Out of Stock";

                        button.classList.add(
                            "disabled"
                        );

                        button.disabled = true;

                    } else {

                        button.textContent =
                            "Added";

                        setTimeout(
                            function () {

                                button.textContent =
                                    "Add to Cart";

                                button.disabled =
                                    false;

                            },
                            1000
                        );
                    }
                })
                .catch(function (error) {

                    console.error(
                        "Cart error:",
                        error
                    );

                    alert(
                        error.message ||
                        "Something went wrong. Please try again."
                    );

                    button.disabled =
                        false;
                });
            }
        );
    });

    wishlistButtons.forEach(function (button) {

        button.addEventListener(
            "click",
            async function () {

                const productId =
                    this.dataset.id;

                if (
                    !productId ||
                    this.disabled
                ) {
                    return;
                }

                const sameProductButtons =
                    document.querySelectorAll(
                        '.wishlist-btn[data-id="' +
                        productId +
                        '"]'
                    );

                sameProductButtons.forEach(
                    function (item) {
                        item.disabled = true;
                    }
                );

                const formData =
                    new FormData();

                formData.append(
                    "product_id",
                    productId
                );

                try {

                    const response =
                        await fetch(
                            "wishlist.php",
                            {
                                method: "POST",
                                body: formData
                            }
                        );

                    const text =
                        await response.text();

                    let data;

                    try {

                        data =
                            JSON.parse(text);

                    } catch (error) {

                        console.error(
                            "Wishlist response:",
                            text
                        );

                        throw new Error(
                            "Invalid server response."
                        );
                    }

                    if (!data.success) {

                        throw new Error(
                            data.message ||
                            "Unable to update wishlist."
                        );
                    }

                    sameProductButtons.forEach(
                        function (item) {

                            item.textContent =
                                data.wishlisted
                                    ? "♥ Wishlisted"
                                    : "♡ Wishlist";

                        }
                    );

                    console.log(
                        "Wishlist activity:",
                        data.activity_type
                    );

                } catch (error) {

                    console.error(
                        "Wishlist error:",
                        error
                    );

                    alert(
                        error.message ||
                        "Unable to update wishlist."
                    );

                } finally {

                    sameProductButtons.forEach(
                        function (item) {
                            item.disabled = false;
                        }
                    );
                }
            }
        );
    });

    function updateCartCount() {
        if (cartCount) {
            cartCount.textContent=cart.length;
        }
    }

});