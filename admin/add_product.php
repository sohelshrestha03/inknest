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
    $description = trim($_POST["description"]);
    $price = trim($_POST["price"]);

    if ($productName === "" || $description === "" || $price === "") {
        $error = "Please fill in all fields.";
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = "Please enter a valid price.";
    } elseif (!isset($_FILES["image"]) || $_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
        $error = "Please select a product image.";
    } else {
        $image = $_FILES["image"];
        $allowedExtensions = ["jpg", "jpeg", "png", "webp"];
        $extension = strtolower(
            pathinfo($image["name"], PATHINFO_EXTENSION)
        );

        if (!in_array($extension, $allowedExtensions)) {
            $error = "Only JPG, JPEG, PNG and WEBP images are allowed.";
        } elseif ($image["size"] > 5 * 1024 * 1024) {
            $error = "Image size must be less than 5MB.";
        } else {
            $allowedMimeTypes = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];
            $imageInfo = getimagesize($image["tmp_name"]);
            if ($imageInfo === false) {
                $error = "Invalid image file.";
            } else {
                $mimeType = $imageInfo["mime"];
                if (!in_array($mimeType, $allowedMimeTypes)) {
                    $error = "Invalid image type.";
                } else {
                    $newFileName = bin2hex(random_bytes(16)) . "." . $extension;
                    $uploadDirectory = "../images/products/";
                    if (!is_dir($uploadDirectory)) {
                        mkdir($uploadDirectory, 0755, true);
                    }

                    $uploadPath = $uploadDirectory . $newFileName;
                    if (move_uploaded_file($image["tmp_name"], $uploadPath)) {
                        $sql = mysqli_prepare(
                            $conn,
                            "INSERT INTO products(product_name, description, price, image) VALUES (?, ?, ?, ?)"
                        );

                        mysqli_stmt_bind_param(
                            $sql,
                            "ssds",
                            $productName,
                            $description,
                            $price,
                            $newFileName
                        );

                        if (mysqli_stmt_execute($sql)) {
                            $success = "Product added successfully.";
                        } else {
                            if (file_exists($uploadPath)) {
                                unlink($uploadPath);
                            }
                            $error = "Failed to add product.";
                        }
                        mysqli_stmt_close($sql);
                    } else {
                        $error = "Failed to upload image.";
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
</head>

<body>
<aside class="sidebar">
    <h1>Inknest</h1>
    <p class="admin-label">ADMIN PANEL</p>
    <nav>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="products.php">Products</a>
        <a href="add_product.php" class="active">Add Product</a>
        <a href="orders.php">Orders</a>
        <a href="users.php">Users</a>
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

                <input type="text" id="product_name" name="product_name" placeholder="Enter product name" value="<?php echo isset($_POST["product_name"]) ? htmlspecialchars($_POST["product_name"]) : ""; ?>">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Enter product description" rows="5"><?php echo isset($_POST["description"]) ? htmlspecialchars($_POST["description"]) : ""; ?></textarea>
            </div>

            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" id="price" name="price" placeholder="Enter price" min="0.01" step="0.01" value="<?php echo isset($_POST["price"]) ? htmlspecialchars($_POST["price"]) : ""; ?>">
            </div>

            <div class="form-group">
                <label for="image">Product Image</label>
                <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">
                <small>JPG, JPEG, PNG or WEBP. Maximum size: 5MB.</small>
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
</body>
</html>