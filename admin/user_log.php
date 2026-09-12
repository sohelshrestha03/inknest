<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

$adminUsername = $_SESSION["admin_username"];
$activityQuery = mysqli_query(
    $conn,
    "SELECT
        upa.id,
        upa.user_id,
        upa.product_id,
        upa.activity_type,
        upa.created_at,
        u.user_name,
        p.product_name
     FROM user_product_activity upa
     LEFT JOIN users u ON upa.user_id = u.id
     LEFT JOIN products p ON upa.product_id = p.id
     ORDER BY upa.created_at DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Activity | Inknest</title>
    <link rel="stylesheet" href="../css/admin_dashboard.css?v=<?php echo time(); ?>">
    <script src="../js/admin_dashboard.js" defer></script>
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
        <a href="user_log.php" class="active">User Activity</a>
        <a href="product_reviews.php">Product Reviews</a>
        <a href="bill.php">Bills</a>
        <a href="stock_management.php">Stock of Products</a>
        <a href="stock_history.php">Stock History</a>
    </nav>

    <div class="sidebar-bottom">
        <a href="admin_logout.php">
            Logout
        </a>
    </div>
</aside>


<main class="main">
    <header class="header">
        <div>
            <h2>User Activity</h2>
            <p>
                View user product activity
            </p>
        </div>
    </header>

    <section class="section">
        <h3>User Activity Log</h3>
        <?php if ($activityQuery && mysqli_num_rows($activityQuery) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Product</th>
                        <th>Activity</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($activity = mysqli_fetch_assoc($activityQuery)): ?>
                        <tr>
                            <td>
                                <?php
                                echo htmlspecialchars($activity["id"]);
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $activity["user_name"] ?? "Unknown User"
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $activity["product_name"] ?? "Unknown Product"
                                );
                                ?>
                            </td>

                            <td>
                                <span class="activity-badge">
                                    <?php 
                                        echo htmlspecialchars(
                                        $activity["activity_type"]
                                            ); 
                                    ?>
                                </span>
                            </td>

                            <td>
                                <?php
                                echo date(
                                    "M d, Y h:i A",
                                    strtotime($activity["created_at"])
                                );
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No user activity found.</p>
        <?php endif; ?>
    </section>
</main>
</body>
</html>