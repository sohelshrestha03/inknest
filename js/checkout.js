document.addEventListener("DOMContentLoaded", function () {
    const checkoutForm = document.getElementById("checkoutForm");
    const orderItems = document.getElementById("orderItems");
    const subtotalElement = document.getElementById("subtotal");
    const totalElement = document.getElementById("total");
    const continueButton = document.getElementById("continueButton");
    const formMessage = document.getElementById("formMessage");
    const emailInput = document.getElementById("email");
    const phoneInput = document.getElementById("phone");
    const addressInput = document.getElementById("delivery_address");
    const shippingCharge = 100;
    let cart = [];

    try {
        cart = JSON.parse(
            localStorage.getItem("inknestCart")
        ) || [];
    } catch (error) {
        console.error("Failed to read cart:", error);
        cart = [];
    }

    cart = cart
        .map(function (id) {
            return parseInt(id, 10);
        })
        .filter(function (id) {
            return id > 0;
        });

    if (cart.length === 0) {
        showEmptyCart();
        if (continueButton) {
            continueButton.disabled = true;
        }
        return;
    }

    const cartQuantities = {};
    cart.forEach(function (productId) {

        if (!cartQuantities[productId]) {
            cartQuantities[productId] = 0;
        }

        cartQuantities[productId]++;
    });
    loadProducts();

    function loadProducts() {
        const productIds = Object.keys(cartQuantities);
        if (productIds.length === 0) {
            showEmptyCart();
            return;
        }
        fetch(
            "get_cart_products.php?ids=" +
            encodeURIComponent(productIds.join(","))
        )
        .then(function (response) {
            if (!response.ok) {
                throw new Error(
                    "Server error: " + response.status
                );
            }
            return response.json();
        })
        .then(function (products) {
            if (!Array.isArray(products)) {
                if (products.error) {
                    throw new Error(products.error);
                }
                throw new Error(
                    "Invalid product data."
                );
            }

            if (products.length === 0) {
                showEmptyCart();
                return;
            }
            displayProducts(products);
        })
        .catch(function (error) {
            console.error(
                "Checkout Cart Error:",
                error
            );

            if (orderItems) {
                orderItems.innerHTML = `
                    <p class="error-message">
                        Unable to load your cart.
                        Please return to the cart and try again.
                    </p>
                `;
            }
            if (continueButton) {
                continueButton.disabled = true;
            }
        });
    }

    function displayProducts(products) {
        if (!products || products.length === 0) {
            showEmptyCart();
            return;
        }
        let subtotal = 0;
        let html = "";
        products.forEach(function (product) {
            const productId = parseInt(
                product.id,
                10
            );
            const quantity=cartQuantities[productId] || 0;
            const price=parseFloat(product.price) || 0;
            const itemTotal=price * quantity;
            subtotal += itemTotal;
            let imagePath =
                "img/default-product.png";

            if (product.image && String(product.image).trim() !== "") {
                imagePath ="images/products/" +
                    String(product.image).trim();
            }

            html+= `
                <div class="order-item">
                    <div class="order-item-left">
                        <div class="order-image">
                            <img
                                src="${escapeHtml(imagePath)}"
                                alt="${escapeHtml(product.product_name)}"
                                onerror="this.onerror=null; this.src='img/default-product.png';"
                            >
                        </div>

                        <div class="order-item-info">
                            <strong>
                                ${escapeHtml(
                                    product.product_name
                                )}
                            </strong>
                            <span>
                                Rs. ${price.toFixed(2)}
                                × ${quantity}
                            </span>
                        </div>
                    </div>
                    <span class="order-item-price">
                        Rs. ${itemTotal.toFixed(2)}
                    </span>
                </div>
            `;
        });
        orderItems.innerHTML = html;
        const total=subtotal + shippingCharge;
        subtotalElement.textContent="Rs. " + subtotal.toFixed(2);
        totalElement.textContent="Rs. " + total.toFixed(2);
    }

    if (checkoutForm) {
        checkoutForm.addEventListener(
            "submit",
            function (event) {
                event.preventDefault();
                clearErrors();
                const email=emailInput.value.trim();
                const phone=phoneInput.value.trim();
                const address=addressInput.value.trim();
                let valid = true;
                const emailPattern =/^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (!emailPattern.test(email)) {
                    showError(
                        "emailError",
                        "Please enter a valid email address."
                    );
                    valid = false;
                }

                const phoneDigits =phone.replace(/\D/g, "");
                if (phoneDigits.length < 10) {
                    showError(
                        "phoneError",
                        "Please enter a valid phone number."
                    );
                    valid = false;
                }

                if (address.length < 5) {
                    showError(
                        "addressError",
                        "Please enter your complete delivery address."
                    );
                    valid = false;
                }

                if (cart.length === 0) {
                    showFormMessage(
                        "Your cart is empty.",
                        "error"
                    );
                    valid = false;
                }

                if (!valid) {
                    return;
                }

                const paymentMethod=document.querySelector(
                        'input[name="payment_method"]:checked'
                    );

                if (!paymentMethod) {
                    showFormMessage(
                        "Please select a payment method.",
                        "error"
                    );
                    return;
                }

                let cartInput=checkoutForm.querySelector(
                        'input[name="cart"]'
                    );

                if (!cartInput) {
                    cartInput=document.createElement("input");
                    cartInput.type = "hidden";
                    cartInput.name = "cart";

                    checkoutForm.appendChild(
                        cartInput
                    );
                }

                cartInput.value=JSON.stringify(cart);

                if (continueButton) {
                    continueButton.disabled = true;
                    continueButton.textContent="Processing...";
                }

                showFormMessage(
                    "Processing your order...",
                    "success"
                );
                checkoutForm.submit();
            }
        );
    }

    function showError(
        elementId,
        message
    ) {
        const element=document.getElementById(elementId);
        if (element) {
            element.textContent = message;
        }
    }

    function clearErrors() {
        const errors =document.querySelectorAll(".error");
        errors.forEach(function (error) {
            error.textContent = "";
        });

        if (formMessage) {
            formMessage.textContent = "";
            formMessage.className ="form-message";
        }
    }

    function showFormMessage(
        message,
        type
    ) {
        if (!formMessage) {
            return;
        }
        formMessage.textContent=message;
        formMessage.className="form-message " + type;
    }

    function showEmptyCart() {
        if (orderItems) {
            orderItems.innerHTML = `
                <div class="empty-order">
                    <h3>
                        Your cart is empty
                    </h3>
                    <p>
                        Please add products before checkout.
                    </p>
                    <a href="home.php">
                        Continue Shopping
                    </a>
                </div>
            `;
        }

        if (subtotalElement) {
            subtotalElement.textContent="Rs. 0.00";
        }

        if (totalElement) {
            totalElement.textContent ="Rs. 0.00";
        }

        if (continueButton) {
            continueButton.disabled = true;
        }
    }

    function escapeHtml(value) {
        const div=document.createElement("div");
        div.textContent=value ?? "";
        return div.innerHTML;
    }
});