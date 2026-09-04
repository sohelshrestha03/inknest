document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".add-cart");
    const cartCount = document.getElementById("cartCount");

    let cart = JSON.parse(localStorage.getItem("inknestCart")) || [];
    updateCartCount();

    buttons.forEach(function (button) {
        button.addEventListener("click", function () {
            const productId = this.dataset.id;
            cart.push(productId);
            localStorage.setItem(
                "inknestCart",
                JSON.stringify(cart)
            );
            updateCartCount();
            const originalText = this.textContent;
            this.textContent = "Added";
            this.disabled = true;

            setTimeout(() => {
                this.textContent = originalText;
                this.disabled = false;
            }, 1000);
        });
    });


    function updateCartCount() {
        cartCount.textContent = cart.length;
    }
});