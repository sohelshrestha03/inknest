document.addEventListener("DOMContentLoaded", function () {
    const followUpForm = document.getElementById("followUpForm");

    if (followUpForm) {
        followUpForm.addEventListener("submit", async function (event) {
            event.preventDefault();
            const submitButton=document.getElementById("followUpSubmit");
            const message=document.getElementById("followUpMessage");
            const comment=document.getElementById("followUpComment");

            if (!comment.value.trim()) {
                message.textContent = "Please enter a comment.";
                message.className = "follow-up-message error";
                comment.focus();
                return;
            }

            submitButton.disabled = true;
            submitButton.textContent = "Adding...";
            message.textContent = "";
            message.className = "follow-up-message";
            try {
                const formData = new FormData(followUpForm);
                const response = await fetch(
                    "add_review_comment.php",
                    {
                        method: "POST",
                        body: formData
                    }
                );
                const responseText = await response.text();
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (error) {
                    console.error(
                        "Invalid JSON:",
                        responseText
                    );
                    throw new Error(
                        "The server returned an invalid response."
                    );
                }

                if (data.success) {
                    message.textContent =data.message || "Additional comment added successfully.";
                    message.className ="follow-up-message success";
                    comment.value = "";
                    setTimeout(function () {
                        window.location.reload();
                    }, 700);

                } else {
                    message.textContent = data.message || "Unable to add comment.";
                    message.className = "follow-up-message error";
                }
            } catch (error) {
                console.error("Comment error:", error);
                message.textContent =error.message || "Something went wrong while adding the comment.";
                message.className = "follow-up-message error";
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = "Add Comment";
            }

        });
    }

    const quantity = document.getElementById("quantity");
    const decreaseButton = document.getElementById("decreaseQty");
    const increaseButton = document.getElementById("increaseQty");

    if (quantity && decreaseButton && increaseButton) {
        decreaseButton.addEventListener(
            "click",
            function () {
                let value=parseInt(quantity.value, 10) || 1;
                const minimum = parseInt(quantity.min, 10) || 1;
                if (value > minimum) {
                    value--;
                }
                quantity.value = value;
            }
        );

        increaseButton.addEventListener(
            "click",
            function () {
                let value = parseInt(quantity.value, 10) || 1;
                const maximum = parseInt(quantity.max, 10) || 999999;
                if (value < maximum) {
                    value++;
                }
                quantity.value = value;
            }
        );

        quantity.addEventListener(
            "change",
            function () {
                let value = parseInt(quantity.value, 10) || 1;
                const minimum = parseInt(quantity.min, 10) || 1;
                const maximum = parseInt(quantity.max, 10) || 999999;

                if (value < minimum) {
                    value = minimum;
                }

                if (value > maximum) {
                    value = maximum;
                }

                quantity.value = value;
            }
        );
    }

    const addToCartButton = document.getElementById("addToCartBtn");
    const cartMessage = document.getElementById("cartMessage");
    const cartCount = document.getElementById("cartCount");

    if (addToCartButton) {
        addToCartButton.addEventListener(
            "click",
            async function () {
                const productId =
                    parseInt(
                        addToCartButton.dataset.productId,
                        10
                    );
                let selectedQuantity = 1;
                if (quantity) {
                    selectedQuantity =
                        parseInt(
                            quantity.value,
                            10
                        ) || 1;
                }

                if (productId <= 0) {
                    if (cartMessage) {
                        cartMessage.textContent = "Invalid product.";
                        cartMessage.className = "cart-message error";
                    }
                    return;
                }

                if (selectedQuantity <= 0) {
                    selectedQuantity = 1;
                }
                addToCartButton.disabled = true;
                addToCartButton.textContent = "Adding...";

                if (cartMessage) {
                    cartMessage.textContent = "";
                    cartMessage.className = "cart-message";
                }

                try {
                    const formData = new FormData();
                    formData.append(
                        "product_id",
                        productId
                    );

                    formData.append(
                        "quantity",
                        selectedQuantity
                    );

                    const response = await fetch(
                        "add_to_cart.php",
                        {
                            method: "POST",
                            body: formData
                        }
                    );

                    const responseText=await response.text();
                    console.log("Add to cart response:",responseText);
                    let data;

                    try {
                        data = JSON.parse(
                            responseText
                        );
                    } catch (error) {
                        console.error(
                            "Invalid JSON:",
                            responseText
                        );
                        throw new Error(
                            "Server returned an invalid response."
                        );
                    }

                    if (!data.success) {
                        if (cartMessage) {
                            cartMessage.textContent=data.message || "Could not add product.";
                            cartMessage.className="cart-message error";
                        }
                        return;
                    }

                    let cart = [];
                    try {
                        const savedCart =
                            localStorage.getItem(
                                "inknestCart"
                            );
                        if (savedCart) {
                            const parsedCart =JSON.parse(savedCart);
                            if (Array.isArray(parsedCart)) {
                                cart = parsedCart;
                            }
                        }
                    } catch (error) {
                        console.error(
                            "Could not read cart:",
                            error
                        );
                        cart = [];
                    }

                    for (let i = 0;i < selectedQuantity;i++) {
                        cart.push(productId);
                    }

                    localStorage.setItem(
                        "inknestCart",
                        JSON.stringify(cart)
                    );

                    if (cartCount) {
                        cartCount.textContent = cart.length;
                    }

                    if (cartMessage) {
                        cartMessage.textContent=data.message || "Product added to cart.";
                        cartMessage.className="cart-message success";
                    }
                    addToCartButton.textContent ="Added to Cart";

                    if (typeof data.stock !== "undefined") {
                        const stockInfo=document.querySelector(
                                ".stock-info"
                            );
                        if (stockInfo) {
                            const newStock =
                                parseInt(
                                    data.stock,
                                    10
                                ) || 0;
                            if (newStock > 0) {
                                stockInfo.innerHTML =
                                    '<span class="in-stock">' +
                                    'In Stock' +
                                    '</span>' +
                                    '<span>' +
                                    newStock +
                                    ' available' +
                                    '</span>';
                            } else {
                                stockInfo.innerHTML =
                                    '<span class="out-stock">' +
                                    'Out of Stock' +
                                    '</span>';
                                addToCartButton.disabled=true;
                                addToCartButton.textContent="Out of Stock";

                                if (quantity) {
                                    quantity.disabled = true;
                                }

                                if (decreaseButton) {
                                    decreaseButton.disabled = true;
                                }

                                if (increaseButton) {
                                    increaseButton.disabled = true;
                                }
                            }
                        }

                        if (quantity) {
                            quantity.max=data.stock;

                            if (parseInt(quantity.value, 10)>parseInt(data.stock, 10)) {
                                quantity.value=data.stock;
                            }
                        }
                    }

                    setTimeout(function () {
                        if (addToCartButton && !addToCartButton.disabled) {
                            addToCartButton.textContent = "Add to Cart";
                        }
                    }, 1200);

                } catch (error) {
                    console.error(
                        "Add to cart error:",
                        error
                    );


                    if (cartMessage) {
                        cartMessage.textContent=error.message || "Something went wrong while adding to cart.";
                        cartMessage.className= "cart-message error";
                    }

                } finally {
                    if (addToCartButton.textContent !=="Out of Stock") {
                        addToCartButton.disabled = false;
                    }
                }

            }
        );
    }

    if (cartCount) {
        let cart = [];
        try {
            const savedCart =
                localStorage.getItem(
                    "inknestCart"
                );
            if (savedCart) {
                const parsedCart =
                    JSON.parse(savedCart);
                if (Array.isArray(parsedCart)) {
                    cart = parsedCart;
                }
            }
        } catch (error) {
            console.error(
                "Could not load cart count:",
                error
            );
        }
        cartCount.textContent = cart.length;
    }

    const ratingInputs =
        document.querySelectorAll(
            '.rating-input input[name="rating"]'
        );

    const ratingLabels =
        document.querySelectorAll(
            ".rating-input label"
        );

    if (ratingInputs.length > 0 && ratingLabels.length > 0) {
        ratingInputs.forEach(function (input) {
            input.addEventListener(
                "change",
                function () {
                    ratingLabels.forEach(
                        function (label) {
                            label.classList.remove(
                                "selected"
                            );
                        }
                    );

                    const selectedLabel =
                        document.querySelector(
                            'label[for="' +
                            input.id +
                            '"]'
                        );

                    if (selectedLabel) {
                        selectedLabel.classList.add(
                            "selected"
                        );
                    }
                }
            );

        });
    }

});