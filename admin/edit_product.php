<?php
session_start();
include "../config/database.php";
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}
$error = "";
$success = "";
$productId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
if ($productId <= 0) {
    header("Location: products.php");
    exit();
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, product_name, category, description, price, stock, image
     FROM products
     WHERE id = ?"
);
mysqli_stmt_bind_param(
    $stmt,
    "i",
    $productId
);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$product) {
    header("Location: products.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $productName = trim($_POST["product_name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $stock = trim($_POST["stock"] ?? "");

    if ($productName === "") {
        $error = "Please enter product name.";
    } elseif ($category === "") {
        $error = "Please enter product category.";
    } elseif ($description === "") {
        $error = "Please enter product description.";
    } elseif ($price === "") {
        $error = "Please enter product price.";
    } elseif (!is_numeric($price)) {
        $error = "Please enter a valid price.";
    } elseif ((float) $price <= 0) {
        $error = "Price must be greater than 0.";
    } elseif ($stock === "") {
        $error = "Please enter stock quantity.";
    } elseif (!ctype_digit($stock)) {
        $error = "Stock must be a whole number.";
    } elseif ((int) $stock < 0) {
        $error = "Stock cannot be negative.";
    } else {
        $newImageName = $product["image"];
        $uploadedNewImage = false;
        $newImagePath = "";

        if (isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES["product_image"]["error"] !== UPLOAD_ERR_OK) {
                $error = "There was an error uploading the image.";
            } else {
                $file = $_FILES["product_image"];
                $maxSize = 5 * 1024 * 1024;

                if ($file["size"] > $maxSize) {
                    $error = "Image size must be less than 5 MB.";
                } else {
                    $allowedTypes = [
                        "image/jpeg" => "jpg",
                        "image/png"  => "png",
                        "image/webp" => "webp"
                    ];

                    $imageInfo = @getimagesize(
                        $file["tmp_name"]
                    );

                    if ($imageInfo === false) {
                        $error = "Uploaded file is not a valid image.";
                    } elseif (!isset($allowedTypes[$imageInfo["mime"]])) {
                        $error = "Only JPG, PNG and WEBP images are allowed.";
                    } else {
                        $extension =$allowedTypes[$imageInfo["mime"]];
                        $newImageName =bin2hex(random_bytes(16))
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

                        $newImagePath=$uploadDirectory . $newImageName;

                        if (!move_uploaded_file($file["tmp_name"],$newImagePath)) {
                            $error ="Failed to upload new image.";
                        } else {
                            $uploadedNewImage = true;
                        }
                    }
                }
            }
        }

        if ($error === "") {
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE products
                 SET product_name = ?,
                     category = ?,
                     description = ?,
                     price = ?,
                     stock = ?,
                     image = ?
                 WHERE id = ?"
            );
            $priceValue = (float) $price;
            $stockValue = (int) $stock;
            mysqli_stmt_bind_param(
                $stmt,
                "sssdisi",
                $productName,
                $category,
                $description,
                $priceValue,
                $stockValue,
                $newImageName,
                $productId
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                if ($uploadedNewImage && !empty($product["image"])) {
                    $oldImagePath ="../images/products/"
                        . basename($product["image"]);
                    if (file_exists($oldImagePath) && is_file($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $success ="Product updated successfully.";
                $product["product_name"]=$productName;
                $product["category"]=$category;
                $product["description"]=$description;
                $product["price"]=$priceValue;
                $product["stock"] =$stockValue;
                $product["image"] =$newImageName;
            } else {
                mysqli_stmt_close($stmt);
                if ( $uploadedNewImage && file_exists($newImagePath)
                ) {
                    unlink($newImagePath);
                }
                $error ="Failed to update product.";
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
    <title>Edit Product | Inknest</title>
    <link rel="stylesheet" href="../css/edit_product.css?v=<?php echo time(); ?>">
    <script src="../js/edit_product.js?v=<?php echo time(); ?>" defer></script>
</head>

<body>
<aside class="sidebar">
    <h1>Inknest</h1>
    <p class="admin-label">ADMIN PANEL</p>
    <nav>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="products.php" class="active">Products</a>
        <a href="add_product.php">Add Product</a>
        <a href="orders.php">Orders</a>
        <a href="users.php">Users</a>
        <a href="user_profiles.php">User Profiles</a>
        <a href="user_log.php">User Activity</a>
        <a href="product_reviews.php">Product Reviews</a>
        <a href="bill.php">Bills</a>
        <a href="stock_management.php">Stock of Products</a>
        <a href="stock_history.php">Stock History</a>
    </nav>

    <div class="sidebar-bottom">
        <a href="admin_logout.php">Logout</a>
    </div>
</aside>

<main class="main">
    <header class="header">
        <div>
            <h2>Edit Product</h2>
            <p>Update product information.</p>
        </div>
    </header>

    <section class="product-container">
        <?php if ($error !== ""): ?>
            <div class="message error">
                <?php echo htmlspecialchars($error);?>
            </div>
        <?php endif; ?>

        <?php if ($success !== ""): ?>
            <div class="message success">
                <?php echo htmlspecialchars($success);?>
            </div>
        <?php endif; ?>


        <div class="form-section">
            <form method="POST" enctype="multipart/form-data" id="productForm">
                <div class="form-group">
                    <label for="product_name">Product Name</label>
                    <input type="text" id="product_name" name="product_name" maxlength="255" value="<?php echo htmlspecialchars($product["product_name"]); ?>" required>
                    <span class="error-text" id="productNameError"></span>
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" maxlength="100" value="<?php echo htmlspecialchars($product["category"]); ?>"required>
                    <span class="error-text" id="categoryError"></span>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5" maxlength="1000" required
                    ><?php echo htmlspecialchars($product["description"]); ?></textarea>
                    <span class="error-text" id="descriptionError"></span>
                </div>

                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" step="0.01" min="0.01" value="<?php echo htmlspecialchars($product["price"]); ?>" required>
                    <span class="error-text" id="priceError"></span>
                </div>

                <div class="form-group">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" min="0" step="1" value="<?php echo (int) $product["stock"]; ?>" required>
                    <span class="error-text" id="stockError"></span>
                </div>

                <div class="form-group">
                    <label>Current Image</label>
                    <?php if (!empty($product["image"])): ?>
                        <div class="current-image">
                            <img
                                src="../images/products/<?php echo htmlspecialchars($product["image"]); ?>"
                                alt="Current Product Image"
                                id="currentImage"
                            >
                        </div>
                    <?php else: ?>
                        <p class="no-image">No image available.</p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="product_image">Change Image</label>
                    <input type="file" id="product_image" name="product_image" accept=".jpg,.jpeg,.png,.webp">
                    <small>Leave empty to keep the current image.
                        Maximum size: 5 MB.
                    </small>

                    <div class="image-preview" id="imagePreview">
                        <img id="previewImage" src="" alt="Image Preview">
                    </div>

                    <span class="error-text" id="imageError"></span>
                </div>

                <div class="form-buttons">
                    <a href="products.php" class="back-button">Back to Products</a>
                    <button type="submit" id="updateButton">Update Product</button>
                </div>
            </form>
        </div>
    </section>
</main>
</body>
</html>