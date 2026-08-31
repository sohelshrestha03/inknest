<?php
session_start();
include "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION["username"];
$search = "";

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}

if ($search !== "") {
    $sql = mysqli_prepare(
        $conn,
        "SELECT id, product_name, description, price, image
         FROM products
         WHERE product_name LIKE ?
         OR description LIKE ?
         ORDER BY id DESC"
    );

    $searchTerm = "%" . $search . "%";
    mysqli_stmt_bind_param(
        $sql,
        "ss",
        $searchTerm,
        $searchTerm
    );
    mysqli_stmt_execute($sql);
    $products = mysqli_stmt_get_result($sql);
} else {
    $products = mysqli_query(
        $conn,
        "SELECT id, product_name, description, price, image
         FROM products
         ORDER BY id DESC"
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | Inknest</title>
    <link rel="stylesheet" href="css/home.css?v=<?php echo time(); ?>">
    <script src="js/home.js" defer></script>
</head>

<body>
<nav class="navbar">
    <h1>Inknest</h1>
    <form class="search" method="get" action="home.php">
        <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
    </form>


    <div class="nav-links">
        <span>Hi, <?php echo htmlspecialchars($username); ?></span>
        <a href="cart.php" class="cart">Cart <span id="cartCount">0</span></a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<main class="container">
    <div class="heading">
        <?php if ($search !== ""): ?>
            <h2>Search results for"<?php echo htmlspecialchars($search); ?>"</h2>
        <?php else: ?>
            <h2>Products</h2>
        <?php endif; ?>
    </div>

    <div class="product-grid">
        <?php if (mysqli_num_rows($products) > 0): ?>
            <?php while ($product = mysqli_fetch_assoc($products)): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if (!empty($product["image"])): ?>
                            <img src="images/products/<?php echo htmlspecialchars($product["image"]); ?>" alt="<?php echo htmlspecialchars($product["product_name"]); ?>">
                        <?php else: ?>
                            <div class="no-image">
                                No Image
                            </div>
                        <?php endif; ?>
                    </div>


                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product["product_name"]); ?></h3>
                        <p><?php echo htmlspecialchars($product["description"]); ?></p>

                        <div class="product-bottom">
                            <strong>Rs. <?php echo number_format($product["price"], 2); ?></strong>
                            <button class="add-cart" data-id="<?php echo $product["id"]; ?>">Add to Cart</button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-products">
                <h3>No products found</h3>
                <p>Try searching for another product.</p>
            </div>
        <?php endif; ?>
    </div>
</main>
</body>
</html>