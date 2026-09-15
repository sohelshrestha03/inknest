<?php
session_start();
include "../config/database.php";
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}
$adminUsername = $_SESSION["admin_username"];
$profileQuery = mysqli_query(
    $conn,
    "SELECT
        up.id,
        up.user_id,
        up.profile_picture,
        up.created_at,
        up.updated_at,
        u.first_name,
        u.last_name,
        u.user_name,
        u.email,
        u.phone_no
     FROM user_profiles up
     LEFT JOIN users u ON up.user_id = u.id
     ORDER BY up.updated_at DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profiles | Inknest</title>
    <link rel="stylesheet" href="../css/admin_dashboard.css?v=<?php echo time(); ?>">
    <script src="../js/admin_dashboard.js" defer></script>
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
        <a href="users.php">Users</a>
        <a href="user_profiles.php" class="active">User Profiles</a>
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
        <a href="admin_logout.php">
            Logout
        </a>
    </div>
</aside>
<main class="main">
    <header class="header">
        <div>
            <h2>User Profiles</h2>
            <p>View registered user profiles</p>
        </div>
    </header>
    <section class="section">
        <h3>User Profiles</h3>
        <?php if ($profileQuery && mysqli_num_rows($profileQuery) > 0): ?>
            <table class="profile-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Profile Picture</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($profile = mysqli_fetch_assoc($profileQuery)): ?>
                        <tr>
                            <td>
                                <?php
                                echo htmlspecialchars($profile["id"]);
                                ?>
                            </td>
                            <td>
                                <?php if (!empty($profile["profile_picture"])): ?>
                                    <img src="../images/profile/<?php echo htmlspecialchars($profile["profile_picture"]); ?>"
                                        alt="Profile Picture"
                                        width="50"
                                        height="50">
                                <?php else: ?>
                                    No Picture
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                echo htmlspecialchars(
                                    trim(
                                        ($profile["first_name"] ?? "") . " " .
                                        ($profile["last_name"] ?? "")
                                    )
                                );
                                ?>
                            </td>
                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $profile["user_name"] ?? "Unknown User"
                                );
                                ?>
                            </td>
                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $profile["email"] ?? "-"
                                );
                                ?>
                            </td>
                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $profile["phone_no"] ?? "-"
                                );
                                ?>
                            </td>
                            <td>
                                <?php
                                echo date(
                                    "M d, Y h:i A",
                                    strtotime($profile["created_at"])
                                );
                                ?>
                            </td>
                            <td>
                                <?php
                                echo date(
                                    "M d, Y h:i A",
                                    strtotime($profile["updated_at"])
                                );
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No user profiles found.</p>
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