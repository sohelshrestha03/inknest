<?php
session_start();
include "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION["username"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart | Inknest</title>
    <link rel="stylesheet" href="css/cart.css?v=<?php echo time(); ?>">
    <script src="js/cart.js" defer></script>
</head>

<body>
<nav class="navbar">
    <h1>Inknest</h1>
    <div class="nav-links">
        <span>
            Hi, <?php echo htmlspecialchars($username); ?>
        </span>

        <a href="home.php">Products</a>
        <a href="cart.php" class="active">Cart <span id="cartCount">0</span></a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<main class="container">
    <div class="heading">
        <h2>Your Cart</h2>
        <p>Review your products before checkout.</p>
    </div>

    <div id="cartContainer">
    </div>


    <div id="emptyCart" class="empty-cart">
        <h3>Your cart is empty</h3>
        <p>Add some products to your cart.</p>
        <a href="home.php">Continue Shopping</a>
    </div>

    <div id="cartSummary" class="cart-summary">
        <div class="summary-row">
            <span>Subtotal</span>
            <strong id="cartTotal">Rs. 0.00</strong>
        </div>

        <div class="summary-row">
            <span>Shipping</span>
            <strong>Free</strong>
        </div>

        <div class="summary-row total">
            <span>Total</span>
            <strong id="finalTotal">Rs. 0.00</strong>
        </div>

        <button id="checkoutButton">Proceed to Checkout</button>
    </div>
</main>
</body>
</html>