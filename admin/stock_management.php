<?php
session_start();
include "../config/database.php";
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}
$adminUsername = $_SESSION["admin_username"] ?? "Admin";
$lowStockLimit = 5;
$search = isset($_GET["search"])
    ? trim($_GET["search"])
    : "";
$totalProducts = 0;
$totalStock = 0;
$lowStockProducts = 0;
$outOfStockProducts = 0;
$statsQuery = mysqli_query(
    $conn,
    "SELECT
        COUNT(*) AS total_products,
        COALESCE(SUM(stock), 0) AS total_stock,
        COALESCE(
            SUM(
                CASE
                    WHEN stock > 0 AND stock <= $lowStockLimit
                    THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS low_stock,
        COALESCE(
            SUM(
                CASE
                    WHEN stock <= 0
                    THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS out_of_stock
     FROM products"
);
if ($statsQuery) {
    $stats=mysqli_fetch_assoc($statsQuery);
    $totalProducts=(int)($stats["total_products"] ?? 0);
    $totalStock=(int)($stats["total_stock"] ?? 0);
    $lowStockProducts=(int)($stats["low_stock"] ?? 0);
    $outOfStockProducts=(int)($stats["out_of_stock"] ?? 0);
}

if ($search !== "") {
    $searchValue = "%" . $search . "%";
    $productQuery = mysqli_prepare(
        $conn,
        "SELECT
            id,
            product_name,
            price,
            image,
            stock
         FROM products
         WHERE product_name LIKE ?
         ORDER BY id DESC"
    );
    mysqli_stmt_bind_param(
        $productQuery,
        "s",
        $searchValue
    );

    mysqli_stmt_execute($productQuery);
    $productsResult=mysqli_stmt_get_result($productQuery);
} else {
    $productsResult = mysqli_query(
        $conn,
        "SELECT
            id,
            product_name,
            price,
            image,
            stock
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
    <title>Stock Management | Inknest</title>
    <link rel="stylesheet" href="../css/admin_dashboard.css?v=<?php echo time(); ?>">
</head>

<body>
<aside class="sidebar">
    <h1>Inknest</h1>
    <p class="admin-label">
        ADMIN PANEL
    </p>
    <nav>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="products.php">Products</a>
        <a href="add_product.php">Add Product</a>
        <a href="orders.php">Orders</a>
        <a href="users.php">Users</a>
        <a href="user_profiles.php">User Profiles</a>
        <a href="user_log.php">User Activity</a>
        <a href="product_reviews.php">Product Reviews</a>
        <a href="bill.php">Bills</a>
        <a href="stock_management.php" class="active">Stock of Products</a>
        <a href="stock_history.php">Stock History</a>
    </nav>

    <div class="sidebar-bottom">
        <a href="admin_logout.php">Logout</a>
    </div>
</aside>

<main class="main">
    <header class="header">
        <div>
            <h2>
                Stock Management
            </h2>
            <p>
                Welcome back,
                <?php
                echo htmlspecialchars($adminUsername);
                ?>
            </p>
        </div>
    </header>

    <section class="stats">
        <div class="stat-card">
            <span>
                Total Products
            </span>
            <strong>
                <?php
                echo $totalProducts;
                ?>
            </strong>
        </div>

        <div class="stat-card">
            <span>
                Total Stock Units
            </span>
            <strong>
                <?php
                echo $totalStock;
                ?>
            </strong>
        </div>
        <div class="stat-card">
            <span>
                Low Stock
            </span>
            <strong>
                <?php
                echo $lowStockProducts;
                ?>
            </strong>
        </div>
        <div class="stat-card">
            <span>
                Out of Stock
            </span>
            <strong>
                <?php
                echo $outOfStockProducts;
                ?>
            </strong>
        </div>

    </section>
    <section class="section">
        <h3>
            Manage Product Stock
        </h3>
        <form method="GET"
            style="display:flex;
                gap:10px;
                margin:20px 0;
                flex-wrap:wrap;">
            <input type="text"
                name="search"
                placeholder="Search product..."
                value="<?php
                    echo htmlspecialchars($search);
                ?>"
                style="
                    flex:1;
                    min-width:220px;
                    padding:11px 14px;
                    border:1px solid #ddd;
                    border-radius:6px;
                    font-size:14px;
                ">
            <button type="submit"
                style="border:none;
                    padding:11px 20px;
                    border-radius:6px;
                    cursor:pointer;
                    font-weight:bold;">
                Search
            </button>
            <?php if ($search !== ""): ?>
                <a href="stock_management.php"
                    style="
                        text-decoration:none;
                        padding:11px 18px;
                        border-radius:6px;
                        background:#eee;
                        color:#333;
                        font-size:14px;
                    ">
                    Clear
                </a>
            <?php endif; ?>
        </form>
        <div id="message" style="
                display:none;
                padding:12px 15px;
                margin-bottom:15px;
                border-radius:6px;
                font-size:14px;
            "></div>
        <div style="
                overflow-x:auto;
                width:100%;
            ">
            <?php if (
                $productsResult &&
                mysqli_num_rows($productsResult) > 0
            ): ?>
                <table
                    style="
                        width:100%;
                        border-collapse:collapse;
                        min-width:850px;
                    "
                >
                    <thead>
                        <tr>
                            <th
                                style="
                                    text-align:left;
                                    padding:14px 10px;
                                    border-bottom:1px solid #ddd;
                                ">
                                Product
                            </th>
                            <th
                                style="
                                    text-align:left;
                                    padding:14px 10px;
                                    border-bottom:1px solid #ddd;
                                ">
                                Price
                            </th>
                            <th
                                style="
                                    text-align:left;
                                    padding:14px 10px;
                                    border-bottom:1px solid #ddd;
                                ">
                                Stock
                            </th>
                            <th
                                style="
                                    text-align:left;
                                    padding:14px 10px;
                                    border-bottom:1px solid #ddd;
                                ">
                                Status
                            </th>
                            <th
                                style="
                                    text-align:left;
                                    padding:14px 10px;
                                    border-bottom:1px solid #ddd;
                                ">
                                Manage
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while (
                        $product =
                        mysqli_fetch_assoc($productsResult)
                    ): ?>
                        <?php
                        $productId=(int)$product["id"];
                        $stock=(int)$product["stock"];
                        if ($stock <= 0) {
                            $status="Out of Stock";
                            $statusColor="#dc2626";
                        } elseif (
                            $stock <= $lowStockLimit
                        ) {
                            $status="Low Stock";
                            $statusColor="#d97706";
                        } else {
                            $status="In Stock";
                            $statusColor="#16a34a";
                        }
                        $image=trim(
                                (string)(
                                    $product["image"] ?? ""
                                )
                            );
                        if ($image !== "") {
                            $imagePath ="../images/products/" .basename($image);
                        } else {
                            $imagePath = "";
                        }
                        ?>
                        <tr
                            data-product-id="<?php
                                echo $productId;
                            ?>"
                            data-stock="<?php
                                echo $stock;
                            ?>">
                            <td
                                style="
                                    padding:14px 10px;
                                    border-bottom:1px solid #eee;
                                ">
                                <div
                                    style="
                                        display:flex;
                                        align-items:center;
                                        gap:12px;
                                    ">
                                    <?php if (
                                        $imagePath !== ""
                                    ): ?>
                                        <img src="<?php
                                                echo htmlspecialchars(
                                                    $imagePath
                                                );
                                            ?>"
                                            alt="<?php
                                                echo htmlspecialchars(
                                                    $product[
                                                        "product_name"
                                                    ]
                                                );
                                            ?>"
                                            style="
                                                width:50px;
                                                height:50px;
                                                object-fit:cover;
                                                border-radius:6px;
                                                border:1px solid #ddd;
                                            ">
                                    <?php else: ?>
                                        <div
                                            style="
                                                width:50px;
                                                height:50px;
                                                border-radius:6px;
                                                background:#eee;
                                            "
                                        ></div>
                                    <?php endif; ?>
                                    <div>
                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $product[
                                                    "product_name"
                                                ]
                                            );
                                            ?>
                                        </strong>
                                        <small
                                            style="
                                                display:block;
                                                color:#888;
                                                margin-top:4px;
                                            ">
                                            ID #<?php
                                            echo $productId;
                                            ?>
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td
                                style="
                                    padding:14px 10px;
                                    border-bottom:1px solid #eee;
                                ">
                                Rs.
                                <?php
                                echo number_format(
                                    (float)$product["price"],
                                    2
                                );
                                ?>
                            </td>
                            <td
                                style="
                                    padding:14px 10px;
                                    border-bottom:1px solid #eee;
                                ">
                                <strong
                                    id="stock-<?php
                                        echo $productId;
                                    ?>"
                                    style="
                                        color:<?php
                                            echo $statusColor;
                                        ?>;
                                        font-size:18px;
                                    ">
                                    <?php
                                    echo $stock;
                                    ?>
                                </strong>
                            </td>
                            <td
                                style="
                                    padding:14px 10px;
                                    border-bottom:1px solid #eee;
                                ">
                                <span
                                    id="status-<?php
                                        echo $productId;
                                    ?>"
                                    style="
                                        color:white;
                                        background:<?php
                                            echo $statusColor;
                                        ?>;
                                        padding:5px 9px;
                                        border-radius:20px;
                                        font-size:12px;
                                        font-weight:bold;
                                    ">
                                    <?php
                                    echo $status;
                                    ?>
                                </span>
                            </td>
                            <td
                                style="
                                    padding:14px 10px;
                                    border-bottom:1px solid #eee;
                                ">
                                <div
                                    style="
                                        display:flex;
                                        gap:6px;
                                        flex-wrap:wrap;
                                    ">
                                    <button type="button"
                                        onclick="openStockModal(
                                            <?php
                                            echo $productId;
                                            ?>,
                                            'add',
                                            <?php
                                            echo $stock;
                                            ?>,
                                            <?php
                                            echo htmlspecialchars(
                                                json_encode(
                                                    $product[
                                                        "product_name"
                                                    ]
                                                ),
                                                ENT_QUOTES,
                                                "UTF-8"
                                            );
                                            ?>
                                        )"
                                        style="
                                            border:none;
                                            padding:7px 10px;
                                            border-radius:5px;
                                            cursor:pointer;
                                            font-size:12px;
                                            font-weight:bold;
                                            background:#dcfce7;
                                            color:#15803d;
                                        ">
                                        + Add
                                    </button>
                                    <button type="button"
                                        onclick="openStockModal(
                                            <?php
                                            echo $productId;
                                            ?>,
                                            'reduce',
                                            <?php
                                            echo $stock;
                                            ?>,
                                            <?php
                                            echo htmlspecialchars(
                                                json_encode(
                                                    $product[
                                                        "product_name"
                                                    ]
                                                ),
                                                ENT_QUOTES,
                                                "UTF-8"
                                            );
                                            ?>
                                        )"
                                        style="
                                            border:none;
                                            padding:7px 10px;
                                            border-radius:5px;
                                            cursor:pointer;
                                            font-size:12px;
                                            font-weight:bold;
                                            background:#fee2e2;
                                            color:#b91c1c;
                                        ">
                                        − Reduce
                                    </button>

                                    <button type="button"
                                        onclick="openStockModal(
                                            <?php
                                            echo $productId;
                                            ?>,
                                            'set',
                                            <?php
                                            echo $stock;
                                            ?>,
                                            <?php
                                            echo htmlspecialchars(
                                                json_encode(
                                                    $product[
                                                        "product_name"
                                                    ]
                                                ),
                                                ENT_QUOTES,
                                                "UTF-8"
                                            );
                                            ?>
                                        )"
                                        style="
                                            border:none;
                                            padding:7px 10px;
                                            border-radius:5px;
                                            cursor:pointer;
                                            font-size:12px;
                                            font-weight:bold;
                                            background:#e5e7eb;
                                            color:#374151;
                                        ">
                                        Set Stock
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>

                <div
                    style="
                        text-align:center;
                        padding:40px 20px;
                        color:#777;
                    ">
                    <?php if ($search !== ""): ?>
                        No products found for
                        <strong>
                            "<?php
                            echo htmlspecialchars($search);
                            ?>"
                        </strong>.
                    <?php else: ?>
                        No products found.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<div
    id="stockModal"
    style="
        display:none;
        position:fixed;
        inset:0;
        background:rgba(0,0,0,.5);
        z-index:999;
        align-items:center;
        justify-content:center;
        padding:20px;
    ">

    <div
        style="
            background:white;
            width:100%;
            max-width:400px;
            padding:25px;
            border-radius:10px;
        ">
        <h3
            id="modalTitle"
            style="margin-bottom:8px;">
            Manage Stock
        </h3>
        <p id="modalProduct"
            style="
                color:#777;
                font-size:14px;
                margin-bottom:20px;
            ">
            Product
        </p>

        <form id="stockForm">
            <input type="hidden"
                id="productId"
                name="product_id">
            <input type="hidden"
                id="stockAction"
                name="action">
            <label for="stockQuantity"
                style="
                    display:block;
                    margin-bottom:7px;
                    font-size:14px;
                    font-weight:bold;
                ">
                Quantity
            </label>
            <input type="number"
                id="stockQuantity"
                name="quantity"
                min="0"
                step="1"
                required
                style="
                    width:100%;
                    padding:11px;
                    border:1px solid #ddd;
                    border-radius:6px;
                    margin-bottom:20px;
                ">
            <div
                style="
                    display:flex;
                    justify-content:flex-end;
                    gap:10px;
                ">
                <button type="button"
                    onclick="closeStockModal()"
                    style="
                        border:none;
                        padding:10px 17px;
                        border-radius:6px;
                        cursor:pointer;
                        background:#eee;
                    ">
                    Cancel
                </button>
                <button type="submit"
                    id="saveStockButton"
                    style="
                        border:none;
                        padding:10px 17px;
                        border-radius:6px;
                        cursor:pointer;
                        background:#111;
                        color:white;
                        font-weight:bold;
                    ">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const lowStockLimit=<?php echo $lowStockLimit; ?>;
function openStockModal(
    productId,
    action,
    currentStock,
    productName
) {
    const modal=document.getElementById("stockModal");
    const title=document.getElementById("modalTitle");
    const product=document.getElementById("modalProduct");
    const quantity=document.getElementById("stockQuantity");
    document.getElementById(
        "productId"
    ).value = productId;
    document.getElementById(
        "stockAction"
    ).value = action;
    product.textContent =
        productName +
        " | Current stock: " +
        currentStock;
    quantity.removeAttribute("max");
    if (action === "add") {
        title.textContent ="Add Stock";
        quantity.value = 1;
        quantity.min = 1;
    } else if (action === "reduce") {
        title.textContent="Reduce Stock";
        quantity.value=1;
        quantity.min= 1;
        quantity.max=currentStock;
    } else {
        title.textContent ="Set Stock";
        quantity.value=currentStock;
        quantity.min = 0;
    }
    modal.style.display ="flex";
    setTimeout(function () {
        quantity.focus();
        quantity.select();
    }, 100);
}

function closeStockModal() {
    document.getElementById(
        "stockModal"
    ).style.display = "none";

}

document
    .getElementById("stockModal")
    .addEventListener(
        "click",
        function(event) {
            if (event.target === this) {
                closeStockModal();
            }
        }
    );

document
    .getElementById("stockForm")
    .addEventListener(
        "submit",
        function(event) {
            event.preventDefault();
            const formData =new FormData(this);
            const productId =
                document.getElementById(
                    "productId"
                ).value;
            const action =
                document.getElementById(
                    "stockAction"
                ).value;
            const quantity =
                parseInt(
                    document.getElementById(
                        "stockQuantity"
                    ).value,
                    10
                );
            if (isNaN(quantity) || quantity < 0) {
                showMessage(
                    "Please enter a valid quantity.",
                    false
                );
                return;
            }
            if ((action === "add" ||
                 action === "reduce") &&
                quantity < 1) {
                showMessage(
                    "Quantity must be at least 1.",
                    false
                );
                return;
            }
            const saveButton =
                document.getElementById(
                    "saveStockButton"
                );
            saveButton.disabled=true;
            saveButton.textContent="Saving...";
            fetch(
                "update_stock.php",
                {
                    method: "POST",
                    body: formData
                }
            )
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (!data.success) {
                    throw new Error(
                        data.message ||
                        "Could not update stock."
                    );
                }
                updateProductStock(
                    productId,
                    parseInt(
                        data.stock,
                        10
                    )
                );
                closeStockModal();
                showMessage(
                    data.message ||
                    "Stock updated successfully.",
                    true
                );
            })
            .catch(function(error) {
                showMessage(
                    error.message ||
                    "Something went wrong.",
                    false
                );
            })
            .finally(function() {
                saveButton.disabled=false;
                saveButton.textContent="Save";
            });
        }
    );

function updateProductStock(
    productId,
    stock
) {
    const stockElement =
        document.getElementById(
            "stock-" + productId
        );

    const statusElement =
        document.getElementById(
            "status-" + productId
        );

    const row =
        document.querySelector(
            'tr[data-product-id="' +
            productId +
            '"]'
        );
    let status;
    let statusColor;
    if (stock <= 0) {
        status="Out of Stock";
        statusColor="#dc2626";
    } else if (
        stock <= lowStockLimit
    ) {
        status="Low Stock";
        statusColor="#d97706";
    } else {
        status="In Stock";
        statusColor="#16a34a";
    }
    if (stockElement) {
        stockElement.textContent=stock;
        stockElement.style.color=statusColor;
    }
    if (statusElement) {
        statusElement.textContent=status;
        statusElement.style.background=statusColor;
    }
    if (row) {
        row.dataset.stock =stock;
    }

}

function showMessage(
    text,
    success
) {
    const message =
        document.getElementById(
            "message"
        );
    message.textContent=text;
    message.style.display="block";
    if (success) {
        message.style.background="#dcfce7";
        message.style.color="#166534";
    } else {
        message.style.background="#fee2e2";
        message.style.color="#991b1b";
    }
    setTimeout(function() {
        message.style.display =
            "none";
    }, 4000);
}
</script>
</body>
</html>