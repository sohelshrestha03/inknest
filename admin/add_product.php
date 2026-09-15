<?php
session_start();
include "../config/database.php";
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}
$error = "";
$success = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productName = trim($_POST["product_name"]);
    $category = trim($_POST["category"]);
    $description = trim($_POST["description"]);
    $price = trim($_POST["price"]);
    $stock = trim($_POST["stock"]);
    if ($productName === "" ||
        $category === "" ||
        $description === "" ||
        $price === "" ||
        $stock === "") {
        $error = "Please fill in all fields.";
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = "Please enter a valid price.";
    } elseif (
        !filter_var($stock, FILTER_VALIDATE_INT) &&
        $stock !== "0"
    ) {
        $error = "Please enter a valid stock quantity.";
    } elseif ((int)$stock < 0) {
        $error = "Stock cannot be negative.";
    } elseif (
        !isset($_FILES["image"]) ||
        $_FILES["image"]["error"] !== UPLOAD_ERR_OK
    ) {
        $error = "Please select a product image.";
    } else {
        $image = $_FILES["image"];
        $allowedExtensions = [
            "jpg",
            "jpeg",
            "png",
            "webp"
        ];
        $extension = strtolower(
            pathinfo(
                $image["name"],
                PATHINFO_EXTENSION
            )
        );
        if (!in_array($extension, $allowedExtensions, true)) {
            $error = "Only JPG, JPEG, PNG and WEBP images are allowed.";
        } elseif ($image["size"] > 5 * 1024 * 1024) {
            $error = "Image size must be less than 5MB.";
        } else {
            $allowedMimeTypes = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];
            $imageInfo = getimagesize(
                $image["tmp_name"]
            );
            if ($imageInfo === false) {
                $error = "Invalid image file.";
            } else {
                $mimeType = $imageInfo["mime"];
                if (!in_array($mimeType, $allowedMimeTypes, true)) {
                    $error = "Invalid image type.";
                } else {
                    $newFileName =
                        bin2hex(random_bytes(16))
                        . "."
                        . $extension;
                    $uploadDirectory ="../images/products/";

                    if (!is_dir($uploadDirectory)) {
                        mkdir(
                            $uploadDirectory,
                            0755,
                            true
                        );
                    }
                    $uploadPath =$uploadDirectory . $newFileName;
                    if (move_uploaded_file(
                            $image["tmp_name"],
                            $uploadPath
                        )
                    ) {
                        mysqli_begin_transaction($conn);
                        try {
                            $sql = mysqli_prepare(
                                $conn,
                                "INSERT INTO products
                                (
                                    product_name,
                                    category,
                                    description,
                                    price,
                                    stock,
                                    image
                                )
                                VALUES (?, ?, ?, ?, ?, ?)"
                            );
                            if (!$sql) {
                                throw new Exception(
                                    "Failed to prepare product query."
                                );
                            }
                            $priceValue = (float)$price;
                            $stockValue = (int)$stock;
                            mysqli_stmt_bind_param(
                                $sql,
                                "sssdis",
                                $productName,
                                $category,
                                $description,
                                $priceValue,
                                $stockValue,
                                $newFileName
                            );
                            if (!mysqli_stmt_execute($sql)) {
                                throw new Exception(
                                    "Failed to add product."
                                );
                            }
                            $productId=mysqli_insert_id($conn);
                            mysqli_stmt_close($sql);
                            if ($stockValue > 0) {
                                $adminId =(int)$_SESSION["admin_id"];
                                $action = "Increase";
                                $oldStock = 0;
                                $newStock = $stockValue;
                                $historySql = mysqli_prepare(
                                    $conn,
                                    "INSERT INTO stock_activity
                                    (
                                        product_id,
                                        admin_id,
                                        action,
                                        quantity,
                                        old_stock,
                                        new_stock
                                    )
                                    VALUES (?, ?, ?, ?, ?, ?)"
                                );
                                if (!$historySql) {
                                    throw new Exception(
                                        "Failed to prepare stock history query."
                                    );
                                }
                                mysqli_stmt_bind_param(
                                    $historySql,
                                    "iisiii",
                                    $productId,
                                    $adminId,
                                    $action,
                                    $stockValue,
                                    $oldStock,
                                    $newStock
                                );
                                if (!mysqli_stmt_execute($historySql)) {
                                    throw new Exception(
                                        "Failed to save stock history."
                                    );
                                }
                                mysqli_stmt_close(
                                    $historySql
                                );
                            }
                            mysqli_commit($conn);
                            $success ="Product added successfully.";
                        } catch (Exception $e) {
                            mysqli_rollback($conn);
                            if (file_exists($uploadPath)) {
                                unlink($uploadPath);
                            }
                            $error = $e->getMessage();
                        }
                    } else {
                        $error ="Failed to upload image.";
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product | Inknest</title>
    <link rel="stylesheet" href="../css/add_product.css?v=<?php echo time(); ?>">
    <script src="../js/add_product.js" defer></script>
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
        <a href="add_product.php" class="active">Add Product</a>
        <a href="orders.php">Orders</a>
        <a href="users.php">Users</a>
        <a href="user_profiles.php">User Profiles</a>
        <a href="user_log.php">User Activity</a>
        <a href="product_reviews.php">Product Reviews</a>
        <a href="admin_wishlist.php">Customer Wishlist</a>
        <a href="bill.php">Bills</a>
        <a href="stock_management.php">Stock of Products</a>
        <a href="stock_history.php">Stock History</a>
        <a href="chat.php">
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
            <h2>Add Product</h2>
            <p>Add a new product to your store.</p>
        </div>
        <a href="products.php" class="back-button">Back to Products</a>
    </header>
    <section class="form-section">
        <?php if ($error !== ""): ?>
            <div class="message error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <?php if ($success !== ""): ?>
            <div class="message success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        <form id="addProductForm" action="add_product.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="product_name">Product Name</label>
                <input type="text" id="product_name"
                    name="product_name"
                    placeholder="Enter product name"
                    value="<?php
                    echo isset($_POST["product_name"])
                        ? htmlspecialchars($_POST["product_name"])
                        : "";
                    ?>">
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" id="category"
                    name="category"
                    placeholder="Enter product category"
                    value="<?php
                    echo isset($_POST["category"])
                        ? htmlspecialchars($_POST["category"])
                        : "";
                    ?>">
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Enter product description"
                    rows="5"
                ><?php
                echo isset($_POST["description"])
                    ? htmlspecialchars($_POST["description"])
                    : "";
                ?></textarea>
            </div>
            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" id="price" name="price" placeholder="Enter price" min="0.01" step="0.01"
                    value="<?php
                    echo isset($_POST["price"])
                        ? htmlspecialchars($_POST["price"])
                        : "";
                    ?>">
            </div>
            <div class="form-group">
                <label for="stock">Stock</label>
                <input type="number" id="stock" name="stock" placeholder="Enter stock quantity" min="0" step="1"
                    value="<?php
                    echo isset($_POST["stock"])
                        ? htmlspecialchars($_POST["stock"])
                        : "";
                    ?>">
                <small>Enter the available quantity of this product.</small>
            </div>
            <div class="form-group">
                <label for="image">Product Image</label>
                <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">
                <small>
                    JPG, JPEG, PNG or WEBP.
                    Maximum size: 5MB.
                </small>
            </div>
            <div class="image-preview">
                <img id="previewImage" src="" alt="Image Preview">
            </div>
            <div class="form-actions">
                <a href="products.php" class="cancel-button">Cancel</a>
                <button type="submit">Add Product</button>
            </div>
        </form>
    </section>
</main>
<script>
const sidebar = document.getElementById("sidebar");
const sidebarToggle = document.getElementById("sidebarToggle");
const main = document.querySelector(".main");
sidebarToggle.addEventListener("click", function () {
    sidebar.classList.toggle("closed");
    main.classList.toggle("sidebar-closed");
});
</script>
</body>
</html>