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
        <a href="user_profiles.php" class="active">User Profiles</a>
         <a href="user_log.php">User Activity</a>
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
            <h2>User Profiles</h2>
            <p>
                View registered user profiles
            </p>
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
                                        alt="Profile Picture" width="50" height="50">
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
</body>
</html>