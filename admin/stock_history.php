<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}
$adminUsername = $_SESSION["admin_username"];
$query = mysqli_query(
    $conn,
    "SELECT
        sa.id,
        sa.action,
        sa.quantity,
        sa.old_stock,
        sa.new_stock,
        sa.created_at,
        p.product_name,
        u.user_name AS admin_name
     FROM stock_activity sa
     INNER JOIN products p
        ON sa.product_id = p.id
     LEFT JOIN users u
        ON sa.admin_id = u.id
     ORDER BY sa.created_at DESC"
);
if (!$query) {
    die("Database error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock History | Inknest</title>
    <link rel="stylesheet" href="../css/admin_dashboard.css?v=<?php echo time(); ?>">
    <style>
        .stock-history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .stock-history-table th,
        .stock-history-table td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .stock-history-table th {
            font-weight: 600;
        }

        .stock-history-table tr:hover {
            background: #fafafa;
        }

        .increase {
            color: #16803c;
            font-weight: 600;
        }

        .decrease {
            color: #d93025;
            font-weight: 600;
        }

        .quantity-increase {
            color: #16803c;
            font-weight: 700;
        }

        .quantity-decrease {
            color: #d93025;
            font-weight: 700;
        }

        .stock-number {
            font-weight: 600;
        }

        .empty-history {
            text-align: center;
            padding: 40px;
            color: #777;
        }
    </style>
</head>

<body>
<aside class="sidebar">
    <h1>Inknest</h1>
    <p class="admin-label">ADMIN PANEL</p>
    <nav>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="products.php">Products</a>
        <a href="add_product.php">Add Product</a>
        <a href="orders.php">Orders</a>
        <a href="users.php">Users</a>
        <a href="user_profiles.php">User Profiles</a>
        <a href="user_log.php">User Activity</a>
        <a href="product_reviews.php">Product Reviews</a>
        <a href="bill.php">Bills</a>
        <a href="stock_management.php">Stock of Products</a>
        <a href="stock_history.php" class="active">Stock History</a>
    </nav>

    <div class="sidebar-bottom">
        <a href="admin_logout.php">Logout</a>
    </div>
</aside>

<main class="main">
    <header class="header">
        <div>
            <h2>Stock History</h2>
            <p>
                Track stock increases and decreases
            </p>
        </div>
    </header>

    <section class="section">
        <h3>Stock Increase & Decrease Records</h3>
        <div style="overflow-x:auto;">
            <table class="stock-history-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Action</th>
                        <th>Quantity</th>
                        <th>Old Stock</th>
                        <th>New Stock</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($query) > 0): ?>
                    <?php $count = 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td>
                                <?php echo $count++; ?>
                            </td>
                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $row["product_name"]
                                );
                                ?>
                            </td>
                            <td>
                                <?php if ($row["action"] === "Increase"): ?>
                                    <span class="increase">
                                        ↑ Increase
                                    </span>
                                <?php else: ?>
                                    <span class="decrease">
                                        ↓ Decrease
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row["action"] === "Increase"): ?>
                                    <span class="quantity-increase">
                                        +<?php echo (int)$row["quantity"]; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="quantity-decrease">
                                        -<?php echo (int)$row["quantity"]; ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="stock-number">
                                    <?php echo (int)$row["old_stock"]; ?>
                                </span>
                            </td>

                            <td>
                                <span class="stock-number">
                                    <?php echo (int)$row["new_stock"]; ?>
                                </span>

                            </td>

                            <td>
                                <?php
                                echo date(
                                    "Y-m-d h:i A",
                                    strtotime($row["created_at"])
                                );
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8"
                            class="empty-history">
                            No stock changes have been recorded yet.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>