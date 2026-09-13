<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

$adminId=(int) $_SESSION["admin_id"];
$adminUsername=$_SESSION["admin_username"] ?? "Admin";
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
</head>


<body>
<aside class="sidebar">
    <h1>Inknest</h1>
    <div class="admin-label">Admin Panel
    </div>
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
        <a href="chat.php" class="active">Chat
            <span id="adminChatBadge" class="admin-chat-badge">
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
            <h2>
                Customer Chat
            </h2>
            <p>
                Respond to customer messages
            </p>
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
                <h3>
                    Conversations
                </h3>
            </div>

            <div id="adminConversationList" class="admin-conversation-list">
                <div class="admin-chat-loading">
                    Loading conversations...
                </div>
            </div>
        </div>

        <div class="admin-chat-panel">
            <div id="adminChatHeader" class="admin-chat-panel-header">
                <div class="admin-chat-user">
                    <div id="adminChatAvatar" class="admin-chat-avatar">
                        👤
                    </div>

                    <div class="admin-chat-user-info">
                        <strong id="adminChatUserName">
                            Select a conversation
                        </strong>
                        <span id="adminChatUserEmail">
                            Choose a customer to start chatting.
                        </span>
                    </div>=
                </div>
            </div>

            <div id="adminChatMessages" class="admin-chat-panel-messages">
                <div class="admin-chat-empty">
                    Select a conversation.
                </div>
            </div>

            <div class="admin-chat-input-area">
                <input type="text" id="adminChatInput" placeholder="Type your reply..." maxlength="2000" autocomplete="off" disabled>
                <button type="button" id="adminChatSend" disabled>
                    ➤
                </button>
            </div>
        </div>
    </section>
</main>
<script>
const ADMIN_ID =<?php echo $adminId; ?>;
</script>
<script src="../js/admin_chat.js?v=<?php echo time(); ?>"></script>
</body>
</html>