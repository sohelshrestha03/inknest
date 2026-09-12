<?php
session_start();
include "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$userId = (int) $_SESSION["user_id"];
$username = $_SESSION["username"] ?? "User";
$sql = "SELECT
            o.id AS order_id,
            o.total_amount,
            o.status,
            o.order_date,
            o.payment_method,
            o.payment_status,
            o.transaction_id,

            GROUP_CONCAT(
                CONCAT(
                    p.product_name,
                    ' (Qty: ',
                    oi.quantity,
                    ')'
                )
                SEPARATOR ', '
            ) AS product_name
        FROM orders o
        INNER JOIN order_items oi
            ON o.id = oi.order_id
        INNER JOIN products p
            ON oi.product_id = p.id
        WHERE o.user_id = ?
        GROUP BY
            o.id,
            o.total_amount,
            o.status,
            o.order_date,
            o.payment_method,
            o.payment_status,
            o.transaction_id
        ORDER BY o.id DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database query error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $userId);

if (!mysqli_stmt_execute($stmt)) {
    die("Failed to load order history.");
}

$result = mysqli_stmt_get_result($stmt);

$orders = [];

while ($order = mysqli_fetch_assoc($result)) {
    $orders[] = $order;
}

mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History | Inknest</title>
    <link rel="stylesheet" href="css/history.css?v=<?php echo time(); ?>">
</head>

<body>
<nav class="navbar">
    <h1>Inknest</h1>
    <div class="nav-links">
        <span>
            Hi,
            <?php echo htmlspecialchars($username); ?>
        </span>
        <a href="home.php">Products</a>
        <a href="cart.php">Cart</a>
        <a href="history.php">History</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>


<main class="container">
    <div class="heading">
        <a href="cart.php" class="back-link">Back to Cart</a>
        <h2>
            Order History
        </h2>
        <p>
            View your previous orders and their current status.
        </p>
    </div>

    <?php if (count($orders) === 0): ?>
        <div class="empty-history">
            <h3>
                No Orders Yet
            </h3>
            <p>
                You have not placed any orders yet.
            </p>
            <a href="home.php" class="shop-button">
                Start Shopping
            </a>
        </div>
    <?php else: ?>

        <div class="orders-container">
            <?php foreach ($orders as $order): ?>
                <?php
                $orderStatus = strtolower(
                    $order["status"] ?? "pending"
                );
                $paymentStatus = strtolower(
                    $order["payment_status"] ?? "pending"
                );
                $orderStatusClass =
                    "status-" .
                    preg_replace(
                        "/[^a-z]/",
                        "",
                        $orderStatus
                    );
                $paymentStatusClass =
                    "status-" .
                    preg_replace(
                        "/[^a-z]/",
                        "",
                        $paymentStatus
                    );
                $paymentMethod = !empty($order["payment_method"])
                    ? ucfirst($order["payment_method"])
                    : "N/A";
                $orderDate = !empty($order["order_date"])
                    ? date(
                        "F d, Y h:i A",
                        strtotime($order["order_date"])
                    )
                    : "N/A";
                ?>

                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <h3>
                                Order #
                                <?php
                                echo (int) $order["order_id"];
                                ?>
                            </h3>
                            <span class="order-date">
                                <?php
                                echo htmlspecialchars($orderDate);
                                ?>
                            </span>
                        </div>

                        <div class="order-amount">
                            Rs.
                            <?php
                            echo number_format(
                                (float) $order["total_amount"],
                                2
                            );
                            ?>
                        </div>
                    </div>

                    <div class="order-details">
                        <div class="detail-box">
                            <span>
                                Product(s)
                            </span>
                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $order["product_name"] ?? "N/A"
                                );
                                ?>
                            </strong>
                        </div>

                        <div class="detail-box">
                            <span>
                                Order Status
                            </span>
                            <strong>
                                <span class="status <?php echo htmlspecialchars($orderStatusClass); ?>">
                                    <?php
                                    echo htmlspecialchars(
                                        ucfirst($orderStatus)
                                    );
                                    ?>
                                </span>
                            </strong>
                        </div>

                        <div class="detail-box">
                            <span>
                                Payment Method
                            </span>
                            <strong>
                                <?php
                                echo htmlspecialchars($paymentMethod);
                                ?>
                            </strong>
                        </div>

                        <div class="detail-box">
                            <span>
                                Payment Status
                            </span>
                            <strong>
                                <span class="status <?php echo htmlspecialchars($paymentStatusClass); ?>">
                                    <?php
                                    echo htmlspecialchars(
                                        ucfirst($paymentStatus)
                                    );
                                    ?>
                                </span>
                            </strong>
                        </div>

                        <div class="detail-box">
                            <span>
                                Transaction ID
                            </span>
                            <strong>
                                <?php if (!empty($order["transaction_id"])): ?>
                                    <?php
                                    echo htmlspecialchars(
                                        $order["transaction_id"]
                                    );
                                    ?>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </strong>
                        </div>

                        <div class="detail-box">
                            <span>
                                Payment Amount
                            </span>
                            <strong>
                                Rs.
                                <?php
                                echo number_format(
                                    (float) $order["total_amount"],
                                    2
                                );
                                ?>

                            </strong>
                        </div>
                    </div>

                    <?php
                    $canCancel = in_array(
                        strtolower($order["status"] ?? ""),
                        [
                            "pending",
                            "processing"
                        ],
                        true
                    );
                    ?>

                    <?php if ($canCancel): ?>
                        <div class="order-actions">
                            <form action="cancel_order.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                <input type="hidden" name="order_id" value="<?php echo (int) $order["order_id"]; ?>">
                                <button type="submit" class="cancel-button">
                                    Cancel Order
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
</body>
</html>