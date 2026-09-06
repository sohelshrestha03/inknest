<?php
session_start();
include "../config/database.php";
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_order"])) {
    $orderId = (int) $_POST["order_id"];
    if ($orderId > 0) {
        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM orders WHERE id = ?"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $orderId
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: orders.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_order_status"])) {
    $orderId = (int) $_POST["order_id"];
    $status = trim($_POST["status"]);
    $allowedStatuses = [
        "Pending",
        "Processing",
        "Shipped",
        "Delivered",
        "Cancelled"
    ];
    if ($orderId > 0 && in_array($status, $allowedStatuses, true)) {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE orders SET status = ? WHERE id = ?"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $status,
            $orderId
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: orders.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_payment_status"])) {
    $orderId = (int) $_POST["order_id"];
    $paymentStatus = trim($_POST["payment_status"]);
    $allowedPaymentStatuses = [
        "Pending",
        "Paid",
        "Failed",
        "Refunded"
    ];

    if ($orderId > 0 && in_array($paymentStatus, $allowedPaymentStatuses, true)) {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE orders SET payment_status = ? WHERE id = ?"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $paymentStatus,
            $orderId
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: orders.php");
    exit();
}

$sql = "SELECT
        orders.id,
        orders.total_amount,
        orders.status,
        orders.order_date,
        orders.payment_method,
        orders.payment_status,
        orders.transaction_id,
        users.user_name,
        users.first_name,
        users.last_name
    FROM orders INNER JOIN users ON orders.user_id = users.id ORDER BY orders.id DESC";
$orders = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders | Inknest</title>
    <link rel="stylesheet" href="../css/orders.css?v=<?php echo time(); ?>">
    <script src="../js/orders.js?v=<?php echo time(); ?>" defer></script>
</head>

<body>
<aside class="sidebar">
    <h1>Inknest</h1>
    <p class="admin-label">ADMIN PANEL</p>
    <nav>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="products.php">Products</a>
        <a href="add_product.php">Add Product</a>
        <a href="orders.php" class="active">Orders</a>
        <a href="users.php">Users</a>
    </nav>

    <div class="sidebar-bottom">
        <a href="admin_logout.php">Logout</a>
    </div>
</aside>

<main class="main">
    <header class="header">
        <div>
            <h2>Orders</h2>
            <p>Manage customer orders and payments.</p>
        </div>
    </header>

    <section class="order-container">
        <?php if ($orders && mysqli_num_rows($orders) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Username</th>
                        <th>Total</th>
                        <th>Order Status</th>
                        <th>Date</th>
                        <th>Order Action</th>
                        <th>Payment Method</th>
                        <th>Payment Status</th>
                        <th>Payment Action</th>
                        <th>Transaction ID</th>
                        <th>Delete</th>
                    </tr>
                </thead>

                <tbody>
                <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                    <tr>
                        <td>#<?php echo (int) $order["id"]; ?></td>
                        <td><?php echo htmlspecialchars($order["first_name"]. " ". $order["last_name"]);?></td>
                        <td><?php echo htmlspecialchars($order["user_name"]);?></td>
                        <td>Rs.<?php echo number_format((float) $order["total_amount"],2);?></td>
                        <td><span class="status <?php echo strtolower($order["status"]); ?>">
                                <?php echo htmlspecialchars($order["status"]);?>
                            </span></td>
                        <td><?php echo date("M d, Y",strtotime($order["order_date"]));?></td>
                        <td class="actions">
                            <form method="POST" action="orders.php">
                                <input type="hidden" name="order_id" value="<?php echo (int) $order["id"]; ?>">
                                <select name="status" class="status-select">
                                    <option value="Pending" <?php echo $order["status"] === "Pending" ? "selected": "";?>>
                                        Pending
                                    </option>

                                    <option value="Processing" <?php echo $order["status"] === "Processing" ? "selected": "";?>>
                                        Processing
                                    </option>

                                    <option value="Shipped" <?php echo $order["status"] === "Shipped" ? "selected": "";?>>
                                        Shipped
                                    </option>

                                    <option value="Delivered" <?php echo $order["status"] === "Delivered" ? "selected": "";?>>
                                        Delivered
                                    </option>

                                    <option value="Cancelled" <?php echo $order["status"] === "Cancelled" ? "selected": "";?>>
                                        Cancelled
                                    </option>
                                </select>
                                <button type="submit" name="update_order_status">Update</button>
                            </form>
                        </td>

                        <td>
                            <?php if (!empty($order["payment_method"])): ?>
                                <?php echo htmlspecialchars(ucfirst($order["payment_method"]));?>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>

                        <td>
                            <span class="payment-status <?php echo strtolower($order["payment_status"]); ?>">
                                <?php echo htmlspecialchars($order["payment_status"]);?>
                            </span>
                        </td>

                        <td>
                            <form method="POST" action="orders.php" class="payment-form">
                                <input type="hidden" name="order_id" value="<?php echo (int) $order["id"]; ?>">
                                <select name="payment_status" class="payment-select">
                                    <option value="Pending" <?php echo $order["payment_status"] === "Pending"? "selected": "";?>>
                                        Pending
                                    </option>

                                    <option value="Paid" <?php echo $order["payment_status"] === "Paid" ? "selected": "";?>>
                                        Paid
                                    </option>

                                    <option value="Failed" <?php echo $order["payment_status"] === "Failed" ? "selected": "";?>>
                                        Failed
                                    </option>

                                    <option value="Refunded" <?php echo $order["payment_status"] === "Refunded" ? "selected": "";?>>
                                        Refunded
                                    </option>
                                </select>
                                <button type="submit" name="update_payment_status">Update</button>
                            </form>
                        </td>

                        <td>
                            <?php if (!empty($order["transaction_id"])): ?>
                                <span class="transaction-id">
                                    <?php echo htmlspecialchars($order["transaction_id"]);?>
                                </span>
                            <?php else: ?>
                                <span class="no-transaction">N/A</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <form method="POST" action="orders.php" class="delete-form">
                                <input type="hidden" name="order_id" value="<?php echo (int) $order["id"]; ?>">

                                <button type="submit" name="delete_order" class="delete-btn" onclick="return confirm('Are you sure you want to delete this order? This action cannot be undone.');">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty">
                <h3>No Orders Found</h3>
                <p>There are currently no customer orders.</p>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>