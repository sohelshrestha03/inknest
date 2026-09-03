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

$sql = "SELECT id, first_name, last_name, user_name, email, phone_no FROM users ORDER BY id DESC";
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
        <a href="users.php" class="active">Users</a>
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
                        <td>#<?php echo $user["id"]; ?></td>
                        <td><?php echo htmlspecialchars(
                                $user["first_name"] . " " .
                                $user["last_name"]
                            );
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($user["user_name"]);?></td>
                        <td><?php echo htmlspecialchars($user["email"]);?></td>
                        <td><?php echo htmlspecialchars($user["phone_no"]);?></td>
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
</body>
</html>