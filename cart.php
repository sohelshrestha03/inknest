<?php
session_start();
include "config/database.php";
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION["username"] ?? "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart | Inknest</title>
    <link rel="stylesheet" href="css/cart.css?v=<?php echo time(); ?>">
</head>
<body>
<nav class="navbar">
    <h1>Inknest</h1>
    <div class="nav-links">
        <span>Welcome,<?= htmlspecialchars($username) ?></span>
        <a href="home.php">Products</a>
        <a href="cart.php" class="active">Cart
            <span id="cartCount">0</span></a>
        <a href="logout.php">Logout</a>
    </div>
</nav>
<main class="cart-page">
    <h2>Your Cart</h2>
    <div id="cartContainer" class="cart-container"></div>
    <div id="emptyCart" class="empty-cart" style="display:none;">
        <h3>Your cart is empty</h3>
        <p>Add some products to your cart.</p>
        <a href="home.php">Continue Shopping</a>
    </div>
    <div id="cartSummary" class="cart-summary" style="display:none;">
        <div class="summary-row">
            <span>Product Price</span>
            <strong id="cartTotal">Rs. 0.00</strong>
        </div>
        <div class="summary-row">
            <span>Shipping Price</span>
            <strong id="shippingCharge">Rs. 100.00</strong>
        </div>
        <div class="summary-row total">
            <span>Total Price</span>
            <strong id="finalTotal">Rs. 100.00</strong>
        </div>
        <button type="button" id="checkoutButton">Proceed to Checkout</button>
    </div>
</main>
<footer class="footer">
<div class="footer-content">
    <div class="footer-brand">
        <h2>Inknest</h2>
        <p>Your trusted online shopping destination.</p>
    </div>
    <div class="footer-links">
        <div class="footer-contact">
            <h3>Contact Us</h3>
            <p><strong>Phone:</strong>+977-9800000000</p>
            <p><strong>Email:</strong>support@inknest.com</p>
            <p><strong>Address:</strong>Kathmandu, Nepal</p>
        </div>
    </div>
</div>
<div class="footer-bottom">
    <p>&copy; <?php echo date("Y"); ?>
        Inknest.
        All rights reserved.
    </p>
</div>
</footer>
<script src="js/cart.js?v=<?php echo time(); ?>" defer></script>
</body>
</html>

