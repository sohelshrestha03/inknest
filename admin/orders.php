<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_status"])) {
    $orderId = (int) $_POST["order_id"];
    $status = trim($_POST["status"]);
    $allowedStatuses = [
        "Pending",
        "Processing",
        "Shipped",
        "Delivered",
        "Cancelled"
    ];

    if (in_array($status, $allowedStatuses)) {
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

$sql = "
    SELECT orders.id, orders.total_amount, orders.status, orders.order_date, users.user_name, users.first_name, users.last_name
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
    <script src="../js/orders.js" defer></script>
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
            <p>Manage customer orders.</p>
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
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                    <tr>
                        <td>#<?php echo $order["id"]; ?></td>
                        <td><?php echo htmlspecialchars($order["first_name"] . " " . $order["last_name"]);?></td>
                        <td><?php echo htmlspecialchars($order["user_name"]);?></td>
                        <td>Rs. <?php echo number_format($order["total_amount"],2);?></td>
                        <td>
                            <span class="status <?phpecho strtolower($order["status"]);?>">
                                <?php echo htmlspecialchars($order["status"]);?>
                            </span>
                        </td>
                        <td><?php echo date("M d, Y", strtotime($order["order_date"]));?></td>
                        <td class="actions">
                            <form method="POST" action="orders.php">
                                <input type="hidden" name="order_id" value="<?php echo $order["id"]; ?>">
                                <select name="status" class="status-select">
                                    <option value="Pending" <?php echo $order["status"] == "Pending"? "selected": "";?>>
                                        Pending
                                    </option>

                                    <option value="Processing" <?php echo $order["status"] == "Processing" ? "selected": "";?>>
                                        Processing
                                    </option>

                                    <option value="Shipped" <?php echo $order["status"] == "Shipped" ? "selected": "";?>>
                                        Shipped
                                    </option>

                                    <option value="Delivered" <?php echo $order["status"] == "Delivered" ? "selected": "";?>>
                                        Delivered
                                    </option>

                                    <option value="Cancelled" <?php echo $order["status"] == "Cancelled" ? "selected": "";?>>
                                        Cancelled
                                    </option>
                                </select>

                                <button type="submit" name="update_status">Update</button>
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