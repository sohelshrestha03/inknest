document.addEventListener("DOMContentLoaded", function () {
    const cartButtons = document.querySelectorAll(".add-cart");
    const wishlistButtons = document.querySelectorAll(".wishlist-btn");
    const cartCount = document.getElementById("cartCount");
    let cart = [];
    try {
        cart = JSON.parse(
            localStorage.getItem("inknestCart")
        ) || [];
    } catch (error) {
        cart = [];
    }
    updateCartCount();
    updateWishlistBadge();
    cartButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const productId = this.dataset.id;
            if (!productId || this.disabled) {
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
                body: formData,
                cache: "no-store"
            })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error(
                        "Server error: " +
                        response.status
                    );
                }
                return response.json();
            })
            .then(function (data) {
                if (!data.success) {
                    throw new Error(
                        data.message ||
                        "Unable to add to cart."
                    );
                }
                fetch("record_activity.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        product_id: productId
                    })
                })
                .then(function (response) {
                    return response.json();
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
                if (!productCard) {
                    return;
                }
                const stockElement=productCard.querySelector(".stock");
                if (stockElement && data.stock !== undefined) {
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
                }
                if (data.stock <= 0) {
                    button.textContent="Out of Stock";
                    button.classList.add(
                        "disabled"
                    );
                    button.disabled = true;
                } else {
                    button.textContent="Added";
                    setTimeout(function () {
                        button.textContent="Add to Cart";
                        button.disabled = false;
                    }, 1000);
                }
            })
            .catch(function (error) {
                console.error(
                    "Cart error:",
                    error
                );
                alert(error.message || "Something went wrong. Please try again.");
                button.disabled = false;
            });
        });
    });
    wishlistButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            toggleWishlist(this);

        });
    });
    async function toggleWishlist(button) {
        const productId = button.dataset.id;
        if (!productId || button.disabled) {
            return;
        }
        const sameProductButtons=document.querySelectorAll(
                '.wishlist-btn[data-id="' +
                productId +
                '"]'
            );
        sameProductButtons.forEach(function (item) {
            item.disabled = true;
        });
        const formData = new FormData();
        formData.append(
            "product_id",
            productId
        );
        try {
            const response = await fetch(
                "wishlist_action.php",
                {
                    method: "POST",
                    body: formData,
                    cache: "no-store"
                }
            );
            if (!response.ok) {
                throw new Error(
                    "Server error: " +
                    response.status
                );
            }
            const data=await response.json();
            if (!data || typeof data !== "object") {
                throw new Error(
                    "Invalid wishlist response."
                );
            }
            if (data.success !== true) {
                throw new Error(
                    data.message ||
                    "Unable to update wishlist."
                );
            }
            const isWishlisted=data.wishlisted === true;
            sameProductButtons.forEach(
                function (item) {
                    if (isWishlisted) {
                        item.textContent="♥ Wishlisted";
                        item.classList.add(
                            "wishlisted"
                        );
                    } else {
                        item.textContent ="♡ Wishlist";
                        item.classList.remove(
                            "wishlisted"
                        );
                    }
                }
            );
            updateWishlistBadge();
            if (!isWishlisted && isWishlistPage()) {
                const productCard=button.closest(
                        ".product-card"
                    );
                if (productCard) {
                    productCard.remove();
                }
                updateWishlistPageCount();
            }
        } catch (error) {
            console.error("Wishlist error:",error);
            alert(error.message || "Unable to update wishlist.");
        } finally {
            sameProductButtons.forEach(
                function (item) {
                    item.disabled = false;
                }
            );
        }
    }
    function isWishlistPage() {
        return window.location.pathname
            .toLowerCase()
            .endsWith("wishlist.php");
    }
    function updateCartCount() {
        if (!cartCount) {
            return;
        }
        cartCount.textContent=cart.length;
    }
    function updateWishlistBadge() {
        fetch("wishlist_count.php", {
            method: "GET",
            cache: "no-store"
        })
        .then(function (response) {
            if (!response.ok) {
                throw new Error(
                    "Wishlist count server error: " +
                    response.status
                );
            }
            return response.json();
        })
        .then(function (data) {
            if (data.success !== true) {
                return;
            }
            const count=parseInt(
                    data.count || 0,
                    10
                );
            updateNavbarWishlistBadge(
                count
            );
        })
        .catch(function (error) {
            console.error("Wishlist count error:",error);
        });
    }
    function updateNavbarWishlistBadge(count) {
        const wishlistWrapper=document.querySelector(
                ".wishlist-wrapper"
            );
        if (!wishlistWrapper) {
            return;
        }
        const wishlistButton=wishlistWrapper.querySelector(".wishlist-view-btn");
        if (!wishlistButton) {
            return;
        }
        let badge=wishlistWrapper.querySelector(
                ".wishlist-badge"
            );
        if (count <= 0) {
            if (badge) {
                badge.remove();
            }
            return;
        }
        if (!badge) {
            badge=document.createElement("span");
            badge.className="wishlist-badge";
            wishlistButton.appendChild(
                badge
            );
        }
        badge.textContent=count > 9
                ? "9+"
                : count;
    }
    function updateWishlistPageCount() {
        const cards=document.querySelectorAll(
                ".product-grid .product-card"
            );
        const count=cards.length;
        const paragraph =document.querySelector(
                ".wishlist-page-header p"
            );
        if (paragraph) {
            paragraph.textContent=count +
                (
                    count === 1
                        ? " item saved"
                        : " items saved"
                );
        }
        if (count > 0) {
            return;
        }
        const productGrid=document.querySelector(
                ".product-grid"
            );
        if (productGrid) {
            productGrid.remove();
        }
        if (document.querySelector(".empty-wishlist")) {
            return;
        }
        const emptyWishlist=document.createElement("div");
        emptyWishlist.className="empty-wishlist";
        emptyWishlist.innerHTML = `
            <div class="empty-wishlist-icon">
                ♡
            </div>
            <h2>
                Your Wishlist is Empty
            </h2>
            <p>
                Save your favorite tattoo products here.
            </p>
            <a href="home.php" class="continue-shopping">
                Browse Products
            </a>
        `;
        const container =document.querySelector(
                ".container"
            );
        if (container) {
            container.appendChild(
                emptyWishlist
            );
        }
    }
});