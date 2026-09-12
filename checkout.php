<?php
session_start();
include "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION["username"] ?? "User";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Inknest</title>
    <link rel="stylesheet" href="css/checkout.css?v=<?php echo time(); ?>">
</head>

<body>
<nav class="navbar">
    <h1>Inknest</h1>
    <div class="nav-links">
        <span>
            Hi,
            <?php
            echo htmlspecialchars($username);
            ?>
        </span>
        <a href="home.php">Products</a>
        <a href="cart.php">Cart</a>
        <a href="history">History</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<main class="checkout-container">
    <div class="heading">
        <h2>Checkout</h2>
        <p>Enter your delivery details before payment.</p>
    </div>

    <form id="checkoutForm" method="POST" action="process_payment.php">
        <div class="checkout-grid">
            <section class="checkout-card">
                <h3>Delivery Information</h3>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required autocomplete="email">
                    <small class="error" id="emailError"></small>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required autocomplete="tel">
                    <small class="error" id="phoneError"></small>
                </div>

                <div class="form-group">
                    <label for="delivery_address">Delivery Address</label>
                    <textarea id="delivery_address" name="delivery_address" rows="5" placeholder="Enter your complete delivery address" required autocomplete="street-address"></textarea>
                    <small class="error" id="addressError"></small>
                </div>

                <div class="form-group">
                   <label>Payment Method</label>
                    <div class="payment-options">
                          <label class="payment-option">
                          <input type="radio" name="payment_method" value="esewa" checked>
                            <div class="payment-logo">
                                <img src="img/esewa-logo.png" alt="eSewa">
                            </div>
                             <div class="payment-info">
                               <strong>eSewa</strong>
                               <span>Pay securely with eSewa</span>
                            </div>
                          </label>

                          <label class="payment-option">
                          <input type="radio" name="payment_method" value="cash">
                            <div class="payment-logo cash-logo">
                                <img src="img/cash.png" alt="Cash on Delivery">
                            </div>
                            <div class="payment-info">
                              <strong>Cash on Delivery</strong>
                              <span>Pay when your order is delivered</span>
                            </div>
                          </label>
                    </div>
                </div>

                <div id="formMessage" class="form-message"></div>
                <button type="submit" id="continueButton" class="continue-button">Continue to Payment</button>

                <a href="cart.php" class="back-cart">Back to Cart</a>
            </section>

            <aside class="checkout-card order-card">
                <h3>Your Order</h3>
                <div id="orderItems">
                    <p class="loading">
                        Loading cart...
                    </p>
                </div>

                <div class="summary">
                    <div class="summary-row">
                        <span>
                            Subtotal
                        </span>
                        <strong id="subtotal">
                            Rs. 0.00
                        </strong>
                    </div>

                    <div class="summary-row">
                        <span>
                            Shipping
                        </span>
                        <strong>
                            Rs. 100.00
                        </strong>
                    </div>

                    <div class="total-row">
                        <span>
                            Total
                        </span>
                        <strong id="total">
                            Rs. 0.00
                        </strong>
                    </div>
                </div>
            </aside>
        </div>
    </form>
</main>
<script src="js/checkout.js?v=<?php echo time(); ?>"></script>
</body>
</html>