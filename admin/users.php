<?php
session_start();
include "../config/database.php";
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}
if (isset($_GET["delete"])) {
    $userId = (int) $_GET["delete"];
    if ($userId > 0) {
        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM users WHERE id = ?"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $userId
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: users.php");
    exit();
}
$sql = "SELECT id, first_name, last_name, user_name, email, phone_no FROM users";
$users = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users | Inknest</title>
    <link rel="stylesheet" href="../css/users.css?v=<?php echo time(); ?>">
    <script src="../js/users.js" defer></script>
    <style>
        .sidebar {
            position: fixed;
            transition: transform 0.3s ease;
            overflow: hidden;
        }
        .sidebar-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1002;
            width: 42px;
            height: 42px;
            border: none;
            border-radius: 6px;
            background: #111;
            color: #fff;
            font-size: 22px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sidebar.closed {
            transform: translateX(calc(-100% + 62px));
        }
        .main {
            transition: margin-left 0.3s ease;
        }
        .main.sidebar-closed {
            margin-left: 62px;
        }
        .sidebar.closed .sidebar-toggle {
            right: 10px;
        }
        .sidebar.closed h1,
        .sidebar.closed .admin-label,
        .sidebar.closed nav,
        .sidebar.closed .sidebar-bottom {
            visibility: hidden;
        }
    </style>
</head>
<body>
<aside class="sidebar" id="sidebar">
    <button type="button" class="sidebar-toggle" id="sidebarToggle">☰</button>
    <h1>Inknest</h1>
    <p class="admin-label">ADMIN PANEL</p>
    <nav>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="products.php">Products</a>
        <a href="add_product.php">Add Product</a>
        <a href="orders.php">Orders</a>
        <a href="users.php" class="active">Users</a>
        <a href="user_profiles.php">User Profiles</a>
        <a href="user_log.php">User Activity</a>
        <a href="product_reviews.php">Product Reviews</a>
        <a href="admin_wishlist.php">Customer Wishlist</a>
        <a href="bill.php">Bills</a>
        <a href="stock_management.php">Stock of Products</a>
        <a href="stock_history.php">Stock History</a>
        <a href="chat.php">
            Chat
            <span
                id="adminChatBadge"
                class="admin-chat-badge">
                0
            </span>
        </a>
    </nav>
    <div class="sidebar-bottom">
        <a href="admin_logout.php">Logout</a>
    </div>
</aside>
<main class="main">
    <header class="header">
        <div>
            <h2>Users</h2>
            <p>Manage registered customers.</p>
        </div>
    </header>
    <section class="user-container">
        <?php if ($users && mysqli_num_rows($users) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($user = mysqli_fetch_assoc($users)): ?>
                    <tr>
                        <td>
                            #<?php echo $user["id"]; ?>
                        </td>
                        <td>
                            <?php
                            echo htmlspecialchars(
                                $user["first_name"] . " " .
                                $user["last_name"]
                            );
                            ?>
                        </td>
                        <td>
                            <?php
                            echo htmlspecialchars($user["user_name"]);
                            ?>
                        </td>
                        <td>
                            <?php
                            echo htmlspecialchars($user["email"]);
                            ?>
                        </td>
                        <td>
                            <?php
                            echo htmlspecialchars($user["phone_no"]);
                            ?>
                        </td>
                        <td class="actions">
                            <a href="users.php?delete=<?php echo $user["id"]; ?>" class="delete-user">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty">
                <h3>No Users Found</h3>
                <p>There are currently no registered users.</p>
            </div>
        <?php endif; ?>
    </section>
</main>
<script>
    const sidebar = document.getElementById("sidebar");
    const sidebarToggle = document.getElementById("sidebarToggle");
    const main = document.querySelector(".main");
    sidebarToggle.addEventListener("click", function () {
        sidebar.classList.toggle("closed");
        main.classList.toggle("sidebar-closed");
        if (sidebar.classList.contains("closed")) {
            sidebarToggle.textContent = "☰";
        } else {
            sidebarToggle.textContent = "☰";
        }
    });
</script>
</body>
</html>