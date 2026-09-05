document.addEventListener("DOMContentLoaded", function () {
    const orderItems = document.getElementById("orderItems");
    const subtotalElement =document.getElementById("subtotal");
    const shippingElement =document.getElementById("shipping");
    const totalElement =document.getElementById("total");
    const cartData =document.getElementById("cartData");
    const shippingCharge = 100;
    const cart =
        JSON.parse(
            localStorage.getItem("inknestCart")
        ) || [];
    if (cart.length === 0) {
        orderItems.innerHTML = `
            <p>Your cart is empty.</p>
        `;
        return;
    }
    cartData.value = JSON.stringify(cart);
    const productIds =[...new Set(cart)];
    fetch(
        "get_cart_products.php?ids="
        + productIds.join(",")
    )

    .then(response => {
        if (!response.ok) {
            throw new Error(
                "Unable to load products."
            );
        }
        return response.json();
    })

    .then(products => {
        let subtotal = 0;
        products.forEach(product => {
            const quantity =
                cart.filter(
                    id => id == product.id
                ).length;
            const price =parseFloat(product.price);
            const itemTotal =price * quantity;
            subtotal += itemTotal;
            const item =document.createElement("div");
            item.className ="checkout-item";
            item.innerHTML = `
                <div>
                    <strong>
                        ${escapeHtml(
                            product.product_name
                        )}
                    </strong>
                    <p>Rs. ${price.toFixed(2)}× ${quantity}</p>
                </div>
                <strong>Rs. ${itemTotal.toFixed(2)}</strong>
            `;
            orderItems.appendChild(item);
        });
        const total=subtotal + shippingCharge;
        subtotalElement.textContent="Rs. " + subtotal.toFixed(2);
        shippingElement.textContent="Rs. " + shippingCharge.toFixed(2);
        totalElement.textContent="Rs. " + total.toFixed(2);
    })

    .catch(error => {
        console.error(error);
        orderItems.innerHTML = `
            <p class="error">Unable to load cart.</p>
        `;
    });


    function escapeHtml(value) {
        const div=document.createElement("div");
        div.textContent = value;
        return div.innerHTML;
    }
});