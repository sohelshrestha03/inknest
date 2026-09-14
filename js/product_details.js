document.addEventListener("DOMContentLoaded", function () {
    const decreaseQty = document.getElementById("decreaseQty");
    const increaseQty = document.getElementById("increaseQty");
    const quantityInput = document.getElementById("quantity");
    const addToCartBtn = document.getElementById("addToCartBtn");
    const cartMessage = document.getElementById("cartMessage");
    if (quantityInput) {
        const maxQuantity = parseInt(
            quantityInput.getAttribute("max") || "1",
            10
        );
        if (decreaseQty) {
            decreaseQty.addEventListener("click", function () {
                let quantity = parseInt(quantityInput.value, 10) || 1;
                if (quantity > 1) {
                    quantity--;
                    quantityInput.value = quantity;
                }
            });
        }
        if (increaseQty) {
            increaseQty.addEventListener("click", function () {
                let quantity = parseInt(quantityInput.value, 10) || 1;
                if (quantity < maxQuantity) {
                    quantity++;
                    quantityInput.value = quantity;
                }
            });
        }
        quantityInput.addEventListener("input", function () {
            let quantity = parseInt(quantityInput.value, 10) || 1;
            if (quantity < 1) {
                quantity = 1;
            }
            if (quantity > maxQuantity) {
                quantity = maxQuantity;
            }
            quantityInput.value = quantity;
        });
    }
    if (addToCartBtn) {
        addToCartBtn.addEventListener("click", async function () {
            const productId = parseInt(
                addToCartBtn.dataset.productId,
                10
            );
            let quantity = quantityInput
                ? parseInt(quantityInput.value, 10)
                : 1;
            if (!productId || productId <= 0) {
                showCartMessage(
                    "Invalid product.",
                    false
                );
                return;
            }
            if (!quantity || quantity < 1) {
                quantity = 1;
            }
            const originalText = addToCartBtn.textContent;
            addToCartBtn.disabled = true;
            addToCartBtn.textContent = "Adding...";
            try {
                const formData = new FormData();
                formData.append(
                    "product_id",
                    productId
                );
                formData.append(
                    "quantity",
                    quantity
                );
                const response = await fetch(
                    "add_to_cart.php",
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
                const responseText = await response.text();
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (error) {
                    console.error(
                        "Invalid JSON response:",
                        responseText
                    );
                    throw new Error(
                        "Invalid server response."
                    );
                }
                if (!data.success) {
                    showCartMessage(
                        data.message || "Unable to add product to cart.",
                        false
                    );
                    return;
                }
                let cart = [];
                try {
                    cart = JSON.parse(
                        localStorage.getItem("inknestCart")
                    ) || [];
                } catch (error) {
                    cart = [];
                }
                for (let i = 0; i < quantity; i++) {
                    cart.push(productId);
                }
                localStorage.setItem(
                    "inknestCart",
                    JSON.stringify(cart)
                );
                updateCartCount();
                showCartMessage(
                    data.message || "Product added to cart.",
                    true
                );
            } catch (error) {
                console.error(
                    "Add to cart error:",
                    error
                );
                showCartMessage(
                    error.message || "Unable to process cart request.",
                    false
                );
            } finally {
                addToCartBtn.disabled = false;
                addToCartBtn.textContent = originalText;
            }
        });
    }
    function updateCartCount() {
        const cartCount =document.getElementById("cartCount");
        if (!cartCount) {
            return;
        }
        let cart = [];
        try {
            cart = JSON.parse(localStorage.getItem("inknestCart")) || [];
        } catch (error) {
            cart = [];
        }
        cartCount.textContent = cart.length;
    }
    function showCartMessage(message, success) {
        if (!cartMessage) {
            return;
        }
        cartMessage.textContent = message;
        cartMessage.classList.remove(
            "success",
            "error"
        );
        cartMessage.classList.add(
            success ? "success" : "error"
        );
        setTimeout(function () {
            if (cartMessage) {
                cartMessage.textContent = "";
                cartMessage.classList.remove(
                    "success",
                    "error"
                );
            }
        }, 3000);
    }
    updateCartCount();
    const wishlistButtons =document.querySelectorAll(".wishlist-btn");
    wishlistButtons.forEach(function (button) {
        button.addEventListener("click", async function () {
            const productId = button.dataset.productId;
            if (!productId) {
                return;
            }
            const formData = new FormData();
            formData.append(
                "product_id",
                productId
            );
            try {
                const response = await fetch(
                    "wishlist.php",
                    {
                        method: "POST",
                        body: formData,
                        headers: {
                            "Accept": "application/json",
                            "X-Requested-With": "XMLHttpRequest"
                        }
                    }
                );
                const text =await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (error) {
                    console.error("Wishlist response:",text);
                    return;
                }
                if (data.success) {
                    button.classList.toggle(
                        "active"
                    );
                    if (data.message) {
                        alert(data.message);
                    }
                } else {
                    if (data.message) {
                        alert(data.message);
                    }
                }
            } catch (error) {
                console.error("Wishlist error:",error);
                alert("Unable to process wishlist request.");
            }
        });
    });
    const reviewForm =document.getElementById("reviewForm");
    if (reviewForm) {
        reviewForm.addEventListener(
            "submit",
            async function (event) {
                event.preventDefault();
                const formData =new FormData(reviewForm);
                try {
                    const response = await fetch(
                        "submit_review.php",
                        {
                            method: "POST",
                            body: formData,
                            headers: {
                                "Accept": "application/json",
                                "X-Requested-With": "XMLHttpRequest"
                            }
                        }
                    );
                    const text=await response.text();
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (error) {
                        console.error(
                            "Review response:",
                            text
                        );
                        alert("Invalid server response.");
                        return;
                    }
                    if (data.success) {
                        alert(data.message ||"Review submitted successfully.");
                        reviewForm.reset();
                        window.location.reload();
                    } else {
                        alert(data.message || "Unable to submit review.");
                    }
                } catch (error) {
                    console.error(
                        "Review error:",
                        error
                    );
                    alert("Unable to submit review.");
                }
            }
        );
    }
    const followupForms=document.querySelectorAll(".followup-form");
    followupForms.forEach(function (form) {
        form.addEventListener(
            "submit",
            async function (event) {
                event.preventDefault();
                const formData =new FormData(form);
                try {
                    const response = await fetch(
                        "submit_followup_comment.php",
                        {
                            method: "POST",
                            body: formData,
                            headers: {
                                "Accept": "application/json",
                                "X-Requested-With": "XMLHttpRequest"
                            }
                        }
                    );
                    const text =await response.text();
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (error) {
                        console.error("Follow-up response:",text);
                        alert("Invalid server response.");
                        return;
                    }
                    if (data.success) {
                        alert(data.message || "Comment submitted successfully.");
                        form.reset();
                        window.location.reload();
                    } else {
                        alert(data.message || "Unable to submit comment.");
                    }
                } catch (error) {
                    console.error("Follow-up error:",error);
                    alert("Unable to submit comment.");
                }
            }
        );
    });
});