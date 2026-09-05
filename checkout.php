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
    <title>Checkout | Inknest</title>
    <link rel="stylesheet"href="css/checkout.css?v=<?php echo time(); ?>">
    <script src="js/checkout.js?v=<?php echo time(); ?>"defer></script>
</head>

<body>
<nav class="navbar">
    <h1>Inknest</h1>
    <div class="nav-links">
        <span>Hi,<?php echo htmlspecialchars($username); ?></span>
        <a href="home.php">Products</a>
        <a href="cart.php">Cart</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<main class="checkout-container">
    <div class="checkout-heading">
        <h2>Checkout</h2>
        <p>Review your order and choose your payment method.</p>
    </div>

    <div class="checkout-grid">
        <div class="checkout-card">
            <h3>Order Summary</h3>
            <div id="orderItems">
            </div>

            <div class="summary">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <strong id="subtotal">Rs. 0.00</strong>
                </div>

                <div class="summary-row">
                    <span>Shipping</span>
                    <strong id="shipping">Rs. 100.00</strong>
                </div>

                <div class="summary-row total">
                    <span>Total</span>
                    <strong id="total">Rs. 0.00</strong>
                </div>
            </div>
        </div>

        <div class="checkout-card">
            <h3>Payment Method</h3>
            <form method="POST" action="process_payment.php" id="paymentForm">
                <input type="hidden" name="cart" id="cartData">
                <div class="payment-options">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="esewa" required>
                        <div class="payment-info">
                            <img src="img/esewa-logo.png" alt="eSewa" class="payment-logo">
                            <div class="payment-text">
                                <strong>eSewa</strong>
                            </div>
                        </div>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="khalti">
                        <div class="payment-info">
                            <img src="img/khalti.jpg" alt="Khalti" class="payment-logo">
                            <div class="payment-text">
                                <strong>Khalti</strong>
                            </div>
                        </div>
                    </label>
                </div>
                <button type="submit" id="payButton">Continue to Payment</button>
            </form>
            <a href="cart.php" class="back-cart">Back to Cart</a>
        </div>
    </div>
</main>
</body>
</html>