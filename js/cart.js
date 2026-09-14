document.addEventListener("DOMContentLoaded", function () {
    const cartContainer = document.getElementById("cartContainer");
    const emptyCart = document.getElementById("emptyCart");
    const cartSummary = document.getElementById("cartSummary");
    const cartTotal = document.getElementById("cartTotal");
    const shippingCharge = document.getElementById("shippingCharge");
    const finalTotal = document.getElementById("finalTotal");
    const cartCount = document.getElementById("cartCount");
    const checkoutButton = document.getElementById("checkoutButton");
    const SHIPPING_CHARGE = 100;
    function getCart() {
        try {
            const cart = JSON.parse(
                localStorage.getItem("inknestCart") || "[]"
            );
            if (!Array.isArray(cart)) {
                return [];
            }
            return cart.map(Number).filter(id => id > 0);
        } catch (error) {
            return [];
        }
    }
    function saveCart(cart) {
        localStorage.setItem(
            "inknestCart",
            JSON.stringify(cart)
        );
    }
    function updateCartCount() {
        if (cartCount) {
            cartCount.textContent = getCart().length;
        }
    }
    function getQuantity(productId) {
        return getCart().filter(
            id => id === Number(productId)
        ).length;
    }
    function formatPrice(price) {
        return "Rs. " + Number(price).toFixed(2);
    }
    function escapeHtml(value) {
        const div = document.createElement("div");
        div.textContent = value == null ? "" : value;
        return div.innerHTML;
    }
    function showEmptyCart() {
        cartContainer.innerHTML = "";
        emptyCart.style.display = "block";
        cartSummary.style.display = "none";
        updateCartCount();
    }
    async function loadCart() {
        const cart = getCart();
        updateCartCount();
        if (cart.length === 0) {
            showEmptyCart();
            return;
        }
        emptyCart.style.display = "none";
        try {
            const uniqueIds = [...new Set(cart)];
            const response = await fetch(
                "get_cart_products.php?ids=" +
                encodeURIComponent(uniqueIds.join(",")),
                {
                    method: "GET",
                    headers: {
                        "Accept": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    cache: "no-store"
                }
            );
            const text = await response.text();
            console.log("get_cart_products.php:",text);
            let data;
            try {
                data = JSON.parse(text);
            } catch (error) {
                throw new Error(
                    "Invalid server response from cart products."
                );
            }
            if (!data.success) {
                throw new Error(
                    data.message || "Unable to load cart."
                );
            }
            if (!Array.isArray(data.products) || data.products.length === 0) {
                localStorage.removeItem("inknestCart");
                showEmptyCart();
                return;
            }
            renderCart(data.products);
        } catch (error) {
            console.error("Cart loading error:",error);
            cartContainer.innerHTML = `
                <div class="error-message">
                    ${escapeHtml(
                        error.message ||
                        "Unable to load cart."
                    )}
                </div>
            `;
            cartSummary.style.display = "none";
        }
    }
    function renderCart(products) {
        cartContainer.innerHTML = "";
        let productTotal = 0;
        products.forEach(function (product) {
            const productId = Number(product.id);
            const quantity = getQuantity(productId);
            if (quantity <= 0) {
                return;
            }
            const price = Number(product.price);
            const stock = Number(product.stock);
            const itemTotal = price * quantity;
            productTotal += itemTotal;
            const imageName = product.image
                ? String(product.image).split("/").pop()
                : "";
            const imagePath = imageName
                ? "images/products/" + imageName
                : "images/products/default.jpg";
            const item = document.createElement("div");
            item.className = "cart-item";
            item.innerHTML = `
                <div class="cart-image">
                    <img src="${imagePath}" alt="${escapeHtml(product.product_name)}">
                </div>
                <div class="cart-info">
                    <h3>${escapeHtml(product.product_name)}</h3>
                    <p>${escapeHtml(product.description || "")}</p>
                    <strong>${formatPrice(price)}</strong>
                </div>
                <div class="quantity">
                    <button type="button" class="quantity-btn decrease-btn" data-product-id="${productId}">
                        −
                    </button>
                    <span>${quantity}</span>
                    <button type="button" class="quantity-btn increase-btn" data-product-id="${productId}" data-stock="${stock}">
                        +
                    </button>
                </div>

                <div class="item-total">
                    ${formatPrice(itemTotal)}
                </div>
                <button type="button" class="remove-btn" data-product-id="${productId}" data-quantity="${quantity}">
                    Remove
                </button>
            `;
            cartContainer.appendChild(item);
        });
        if (cartContainer.children.length === 0) {
            showEmptyCart();
            return;
        }
        cartTotal.textContent =formatPrice(productTotal);
        shippingCharge.textContent =formatPrice(SHIPPING_CHARGE);
        finalTotal.textContent =formatPrice(productTotal + SHIPPING_CHARGE);
        cartSummary.style.display = "block";
        updateCartCount();
        attachEvents();
    }
    function attachEvents() {
        document.querySelectorAll(".remove-btn").forEach(function (button) {
                button.addEventListener(
                    "click",
                    function () {
                        const productId =Number(button.dataset.productId);
                        const quantity =Number(button.dataset.quantity);
                        removeProduct(
                            productId,
                            quantity,
                            button
                        );
                    }
                );
            });
        document.querySelectorAll(".increase-btn").forEach(function (button) {
                button.addEventListener(
                    "click",
                    function () {
                        increaseProduct(
                            Number(button.dataset.productId),
                            Number(button.dataset.stock)
                        );
                    }
                );
            });
        document.querySelectorAll(".decrease-btn").forEach(function (button) {
                button.addEventListener(
                    "click",
                    function () {
                        decreaseProduct(
                            Number(button.dataset.productId)
                        );
                    }
                );
            });
    }
    async function removeProduct(
        productId,
        quantity,
        button
    ) {
        if (productId <= 0 || quantity <= 0) {
            return;
        }
        button.disabled = true;
        button.textContent = "Removing...";
        try {
            const formData = new FormData();
            formData.append(
                "product_id",
                String(productId)
            );
            formData.append(
                "quantity",
                String(quantity)
            );
            const response = await fetch(
                "remove_from_cart.php",
                {
                    method: "POST",
                    body: formData,
                    headers: {
                        "Accept": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    cache: "no-store"
                }
            );
            const text = await response.text();
            console.log("remove_from_cart.php:",text);
            let data;
            try {
                data = JSON.parse(text);
            } catch (error) {
                throw new Error(
                    "Invalid server response: " +
                    text.substring(0, 300)
                );
            }
            if (!data.success) {
                throw new Error(
                    data.message ||
                    "Unable to remove product."
                );
            }
            let cart = getCart();
            cart = cart.filter(id => id !== productId);
            saveCart(cart);
            updateCartCount();
            await loadCart();
        } catch (error) {
            console.error("Remove error:",error);
            button.disabled = false;
            button.textContent = "Remove";
            alert(error.message || "Unable to remove product.");
        }
    }
    async function increaseProduct(
        productId,
        stock
    ) {
        const cart = getCart();
        const quantity =getQuantity(productId);
        if (quantity >= stock) {
            alert("Only " +stock +" item(s) available.");
            return;
        }
        cart.push(productId);
        saveCart(cart);
        await loadCart();
    }
    async function decreaseProduct(productId) {
        const cart = getCart();
        const quantity =getQuantity(productId);
        if (quantity <= 1) {
            return;
        }
        const index =cart.lastIndexOf(productId);
        if (index === -1) {
            return;
        }
        try {
            const formData = new FormData();
            formData.append(
                "product_id",
                String(productId)
            );
            formData.append(
                "quantity",
                "1"
            );
            const response = await fetch(
                "decrease_cart.php",
                {
                    method: "POST",
                    body: formData,
                    headers: {
                        "Accept": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    cache: "no-store"
                }
            );
            const text = await response.text();
            console.log("decrease_cart.php:",text);
            let data;
            try {
                data = JSON.parse(text);
            } catch (error) {
                throw new Error(
                    "Invalid server response: " +
                    text.substring(0, 300)
                );
            }
            if (!data.success) {
                throw new Error(
                    data.message ||
                    "Unable to decrease quantity."
                );
            }
            cart.splice(index, 1);
            saveCart(cart);
            await loadCart();
        } catch (error) {
            console.error("Decrease error:",error);
            alert(error.message || "Unable to decrease quantity.");
        }
    }
    if (checkoutButton) {
        checkoutButton.addEventListener(
            "click",
            function () {
                if (getCart().length === 0) {
                    alert("Your cart is empty.");
                    return;
                }
                window.location.href ="checkout.php";
            }
        );
    }
    loadCart();
});