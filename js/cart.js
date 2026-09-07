document.addEventListener("DOMContentLoaded", function () {
    const cartContainer =document.getElementById("cartContainer");
    const emptyCart =document.getElementById("emptyCart");
    const cartSummary =document.getElementById("cartSummary");
    const cartCount =document.getElementById("cartCount");
    const cartTotal =document.getElementById("cartTotal");
    const shippingElement =document.getElementById("shippingCharge");
    const finalTotal =document.getElementById("finalTotal");
    const checkoutButton =document.getElementById("checkoutButton");
    const shippingCharge = 100;

    let cart =JSON.parse(localStorage.getItem("inknestCart")) || [];
    updateCartCount();
    if (cart.length === 0) {
        showEmptyCart();
        return;
    }
    loadCartProducts();

    function loadCartProducts() {
        const productIds =
            [...new Set(cart)];
        fetch(
            "get_cart_products.php?ids=" +
            productIds.join(",")
        )
        .then(response => {
            if (!response.ok) {
                throw new Error( "Server error: " +response.status);
            }
            return response.json();
        })
        .then(products => {
            if (products.error) {
                throw new Error(products.error);
            }

            if (products.length === 0) {
                showEmptyCart();
                return;
            }
            displayCart(products);
        })

        .catch(error => {
            console.error("Cart Error:",error);
            cartContainer.innerHTML = `
                <div class="error-message">
                    ${escapeHtml(error.message)}
                </div>
            `;
        });
    }

    function displayCart(products) {
        cartContainer.innerHTML = "";
        let subtotal = 0;
        products.forEach(function (product) {
            const quantity =
                cart.filter(
                    id => id == product.id
                ).length;
            const price=parseFloat(product.price);
            const itemTotal=price * quantity;
            subtotal += itemTotal;
            const cartItem=document.createElement("div");
            cartItem.className="cart-item";
            cartItem.innerHTML = `
                <div class="cart-image">
                    ${
                        product.image
                        ?
                        `<img
                            src="images/products/${escapeHtml(product.image)}"
                            alt="${escapeHtml(product.product_name)}"
                        >`:
                        `<div class="no-image">
                            No Image
                        </div>`
                    }
                </div>

                <div class="cart-info">
                    <h3>${escapeHtml(product.product_name)}</h3>
                    <p>${escapeHtml(product.description)}</p>
                    <strong>Rs. ${price.toFixed(2)}
                    </strong>
                </div>

                <div class="quantity">
                    <button class="quantity-btn decrease" data-id="${product.id}">−</button>
                    <span>${quantity}</span>
                    <button class="quantity-btn increase"data-id="${product.id}">+</button>
                </div>

                <div class="item-total">
                    Rs. ${itemTotal.toFixed(2)}
                </div>

                <button class="remove-btn" data-id="${product.id}"data-quantity="${quantity}">Remove</button>
            `;
            cartContainer.appendChild(cartItem);
        });

        const finalAmount =subtotal + shippingCharge;
        cartTotal.textContent="Rs. " + subtotal.toFixed(2);
        shippingElement.textContent="Rs. " + shippingCharge.toFixed(2);
        finalTotal.textContent="Rs. " + finalAmount.toFixed(2);
        emptyCart.style.display ="none";
        cartSummary.style.display ="block";
        addCartEvents();
    }

    function addCartEvents() {
        document.querySelectorAll(".increase")
            .forEach(function (button) {
                button.addEventListener(
                    "click",
                    function () {
                        const id =this.dataset.id;
                        button.disabled = true;
                        const formData =new FormData();
                        formData.append(
                            "product_id",
                            id
                        );

                        fetch(
                            "increase_cart.php",
                            {
                                method: "POST",
                                body: formData
                            }
                        )
                        .then(
                            response =>
                                response.json()
                        )
                        .then(data => {
                            if (!data.success) {
                                alert(data.message);
                                button.disabled=false;
                                return;
                            }
                            cart.push(id);
                            saveCart();
                            loadCartProducts();
                        })
                        .catch(error => {console.error(error);
                            alert("Something went wrong.");
                            button.disabled =false;
                        });
                    }
                );

            });

        document.querySelectorAll(".decrease")
            .forEach(function (button) {
                button.addEventListener(
                    "click",
                    function () {
                        const id =this.dataset.id;
                        const index =cart.indexOf(id);
                        if (index === -1) {
                            return;
                        }
                        button.disabled = true;
                        const formData =new FormData();
                        formData.append(
                            "product_id",
                            id
                        );
                        fetch(
                            "decrease_cart.php",
                            {
                                method: "POST",
                                body: formData
                            }
                        )
                        .then(
                            response =>
                                response.json()
                        )
                        .then(data => {
                            if (!data.success) {
                                alert(data.message);
                                button.disabled =false;
                                return;
                            }
                            cart.splice(
                                index,
                                1
                            );
                            saveCart();

                            if (cart.length === 0) {
                                showEmptyCart();
                            } else {
                                loadCartProducts();
                            }
                        })

                        .catch(error => {
                            console.error(error);
                            alert("Something went wrong.");
                            button.disabled =false;
                        });
                    }
                );

            });

        document.querySelectorAll(".remove-btn")
            .forEach(function (button) {
                button.addEventListener(
                    "click",
                    function () {
                        const id =this.dataset.id;
                        const quantity =parseInt(this.dataset.quantity);
                        const formData =new FormData();
                        formData.append(
                            "product_id",
                            id
                        );

                        formData.append(
                            "quantity",
                            quantity
                        );
                        button.disabled = true;
                        fetch(
                            "remove_from_cart.php",
                            {
                                method: "POST",
                                body: formData
                            }
                        )
                        .then(
                            response =>
                                response.json()
                        )
                        .then(data => {
                            if (!data.success) {
                                alert(data.message);
                                button.disabled =false;
                                return;
                            }

                            cart =cart.filter(
                                    cartId =>
                                        cartId != id
                                );
                            saveCart();


                            if (cart.length === 0) {
                                showEmptyCart();

                            } else {
                                loadCartProducts();
                            }
                        })

                        .catch(error => {console.error(error);
                            alert("Something went wrong.");
                            button.disabled =false;
                        });
                    }
                );

            });
    }

    function saveCart() {
        localStorage.setItem(
            "inknestCart",
            JSON.stringify(cart)
        );
        updateCartCount();
    }


    function updateCartCount() {
        if (cartCount) {
            cartCount.textContent =cart.length;
        }
    }

    function showEmptyCart() {
        cartContainer.innerHTML = "";
        emptyCart.style.display ="block";
        cartSummary.style.display ="none";
        cartCount.textContent ="0";
    }

    function escapeHtml(value) {
        const div=document.createElement("div");
        div.textContent =value;
        return div.innerHTML;
    }

    checkoutButton.addEventListener(
        "click",
        function () {
            if (cart.length === 0) {
                alert("Your cart is empty.");
                return;
            }

            window.location.href ="checkout.php";
        }
    );
});