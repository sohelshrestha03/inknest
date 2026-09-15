<?php
session_start();
include "../config/database.php";
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}
$adminId = (int) $_SESSION["admin_id"];
$adminUsername = $_SESSION["admin_username"] ?? "Admin";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat | Inknest Admin</title>
    <link rel="stylesheet" href="../css/admin_dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/admin_chat.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/admin_footer.css?v=<?php echo time(); ?>">
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
    <div class="admin-label">Admin Panel</div>
    <nav>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="products.php">Products</a>
        <a href="add_product.php">Add Product</a>
        <a href="orders.php">Orders</a>
        <a href="users.php">Users</a>
        <a href="user_profiles.php">User Profiles</a>
        <a href="user_log.php">User Activity</a>
        <a href="product_reviews.php">Product Reviews</a>
        <a href="admin_wishlist.php">Customer Wishlist</a>
        <a href="bill.php">Bills</a>
        <a href="stock_management.php">Stock of Products</a>
        <a href="stock_history.php">Stock History</a>
        <a href="chat.php" class="active">
            Chat
            <span id="adminChatBadge" class="admin-chat-badge">
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
            <h2>Customer Chat</h2>
            <p>Respond to customer messages</p>
        </div>
        <div>
            <?php
            echo htmlspecialchars(
                $adminUsername,
                ENT_QUOTES,
                "UTF-8"
            );
            ?>
        </div>
    </header>
    <section class="admin-chat-layout">
        <div class="admin-chat-conversations">
            <div class="admin-chat-list-header">
                <h3>Conversations</h3>
            </div>
            <div id="adminConversationList"
                 class="admin-conversation-list">
                <div class="admin-chat-loading">
                    Loading conversations...
                </div>
            </div>
        </div>
        <div class="admin-chat-panel">
            <div id="adminChatHeader"
                 class="admin-chat-panel-header">
                <div class="admin-chat-user">
                    <div id="adminChatAvatar" class="admin-chat-avatar">
                        👤
                    </div>
                    <div class="admin-chat-user-info">
                        <strong id="adminChatUserName">Select a conversation</strong>
                        <span id="adminChatUserEmail">Choose a customer to start chatting.</span>
                    </div>
                </div>
            </div>
            <div id="adminChatMessages" class="admin-chat-panel-messages">
                <div class="admin-chat-empty">
                    Select a conversation.
                </div>
            </div>
            <div class="admin-chat-input-area">
                <input type="text" id="adminChatInput" placeholder="Type your reply..." maxlength="2000" autocomplete="off" disabled>
                <button type="button" id="adminChatSend" disabled>➤</button>
            </div>
        </div>
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
<script>
    const ADMIN_ID = <?php echo $adminId; ?>;
</script>
<script src="../js/admin_chat.js?v=<?php echo time(); ?>"></script>
</body>
</html>