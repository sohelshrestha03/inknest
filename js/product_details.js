document.addEventListener("DOMContentLoaded", function () {
    const decreaseQty =document.getElementById("decreaseQty");
    const increaseQty =document.getElementById("increaseQty");
    const quantity =document.getElementById("quantity");
    const addToCartBtn =document.getElementById("addToCartBtn");
    const cartMessage =document.getElementById("cartMessage");
    const wishlistBtn =document.getElementById("wishlistBtn");
    const wishlistMessage =document.getElementById("wishlistMessage");

    if (quantity) {
        quantity.addEventListener(
            "input",
            function () {
                let value =
                    parseInt(
                        this.value,
                        10
                    );
                const min =
                    parseInt(
                        this.min || "1",
                        10
                    );
                const max =
                    parseInt(
                        this.max || "999999",
                        10
                    );

                if (isNaN(value)) {
                    value = min;
                }

                value =
                    Math.max(
                        min,
                        Math.min(
                            value,
                            max
                        )
                    );

                this.value =
                    value;
            }
        );
    }

    if (decreaseQty) {
        decreaseQty.addEventListener(
            "click",
            function () {
                if (!quantity) {
                    return;
                }
                let value =
                    parseInt(
                        quantity.value,
                        10
                    ) || 1;
                const min =
                    parseInt(
                        quantity.min || "1",
                        10
                    );
                quantity.value =
                    Math.max(
                        min,
                        value - 1
                    );
            }
        );
    }

    if (increaseQty) {
        increaseQty.addEventListener(
            "click",
            function () {
                if (!quantity) {
                    return;
                }
                let value =
                    parseInt(
                        quantity.value,
                        10
                    ) || 1;
                const max =
                    parseInt(
                        quantity.max || "999999",
                        10
                    );
                quantity.value =
                    Math.min(
                        max,
                        value + 1
                    );
            }
        );
    }

    if (addToCartBtn) {
        addToCartBtn.addEventListener(
            "click",
            async function () {
                const productId =this.dataset.productId;

                const quantityValue =
                    quantity
                        ? parseInt(
                            quantity.value,
                            10
                        ) || 1
                        : 1;

                if (!productId) {
                    return;
                }

                this.disabled = true;

                if (cartMessage) {
                    cartMessage.textContent =
                        "Adding to cart...";
                }

                const formData =
                    new FormData();

                formData.append(
                    "product_id",
                    productId
                );

                formData.append(
                    "quantity",
                    quantityValue
                );

                try {

                    const response =
                        await fetch(
                            "cart.php",
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
                            "Cart response:",
                            text
                        );

                        throw new Error(
                            "Invalid server response."
                        );
                    }

                    if (!data.success) {

                        throw new Error(
                            data.message ||
                            "Unable to add product to cart."
                        );
                    }

                    if (cartMessage) {

                        cartMessage.textContent =
                            data.message ||
                            "Product added to cart.";
                    }

                    const cartCount =
                        document.getElementById(
                            "cartCount"
                        );

                    if (
                        cartCount &&
                        data.cartCount !== undefined
                    ) {

                        cartCount.textContent =
                            data.cartCount;
                    }

                } catch (error) {

                    console.error(
                        "Add to cart error:",
                        error
                    );

                    if (cartMessage) {

                        cartMessage.textContent =
                            error.message ||
                            "Unable to add product to cart.";
                    }

                } finally {

                    this.disabled = false;
                }
            }
        );
    }

    if (wishlistBtn) {

        wishlistBtn.addEventListener(
            "click",
            async function () {

                const productId =
                    this.dataset.productId;

                if (
                    !productId ||
                    this.disabled
                ) {
                    return;
                }

                this.disabled = true;

                if (wishlistMessage) {
                    wishlistMessage.textContent =
                        "Updating wishlist...";
                }

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

                    console.log(
                        "wishlist.php response:",
                        text
                    );

                    let data;

                    try {

                        data =
                            JSON.parse(text);

                    } catch (error) {

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

                    this.textContent =
                        data.wishlisted
                            ? "♥ Wishlisted"
                            : "♡ Wishlist";

                    if (wishlistMessage) {

                        wishlistMessage.textContent =
                            data.message || "";
                    }

                } catch (error) {

                    console.error(
                        "Wishlist error:",
                        error
                    );

                    if (wishlistMessage) {

                        wishlistMessage.textContent =
                            error.message ||
                            "Unable to update wishlist.";
                    }

                } finally {

                    this.disabled = false;
                }
            }
        );
    }

    const followUpForm =
        document.getElementById(
            "followUpForm"
        );

    const followUpMessage =
        document.getElementById(
            "followUpMessage"
        );

    const followUpSubmit =
        document.getElementById(
            "followUpSubmit"
        );

    if (followUpForm) {

        followUpForm.addEventListener(
            "submit",
            async function (event) {

                event.preventDefault();

                if (followUpSubmit) {
                    followUpSubmit.disabled = true;
                }

                if (followUpMessage) {
                    followUpMessage.textContent =
                        "Adding comment...";
                }

                const formData =
                    new FormData(
                        followUpForm
                    );

                try {

                    const response =
                        await fetch(
                            "submit_followup_comment.php",
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
                            "Follow-up response:",
                            text
                        );

                        throw new Error(
                            "Invalid server response."
                        );
                    }

                    if (!data.success) {

                        throw new Error(
                            data.message ||
                            "Unable to add comment."
                        );
                    }

                    if (followUpMessage) {

                        followUpMessage.textContent =
                            data.message ||
                            "Comment added successfully.";
                    }

                    const textarea =
                        document.getElementById(
                            "followUpComment"
                        );

                    if (textarea) {
                        textarea.value = "";
                    }

                    setTimeout(
                        function () {
                            window.location.reload();
                        },
                        700
                    );

                } catch (error) {

                    console.error(
                        "Follow-up comment error:",
                        error
                    );

                    if (followUpMessage) {

                        followUpMessage.textContent =
                            error.message ||
                            "Unable to add comment.";
                    }

                } finally {

                    if (followUpSubmit) {
                        followUpSubmit.disabled = false;
                    }
                }
            }
        );
    }

});