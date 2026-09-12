<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_GET["order_id"]) && is_numeric($_GET["order_id"])) {
    $orderId = (int) $_GET["order_id"];
    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            orders.*,
            users.user_name,
            users.first_name,
            users.last_name
        FROM orders
        INNER JOIN users
            ON orders.user_id = users.id
        WHERE orders.id = ?
        LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $orderId
    );

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);


    if (!$order) {
        die("Order not found.");
    }

    if (strtolower(trim($order["status"])) !== "delivered" || strtolower(trim($order["payment_status"])) !== "paid"
    ) {
        die(
            "Bill is available only after the order is Delivered and Payment is Paid."
        );
    }

    $customerName = trim( $order["first_name"] . " " . $order["last_name"]
    );

    if (empty($customerName)) {
        $customerName = $order["user_name"];
    }

    $shippingPrice = 100.00;
    $totalPrice = (float) $order["total_amount"];
    $productPrice = $totalPrice - $shippingPrice;

    if ($productPrice < 0) {
        $productPrice = 0;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        Bill #<?php echo (int) $order["id"]; ?> | Inknest
    </title>
    <link rel="stylesheet" href="../css/orders.css?v=<?php echo time(); ?>">

    <style>
        .bill-container {
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 30px;
            max-width: 900px;
        }

        .bill-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #e5e5e5;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }

        .bill-header h3 {
            font-size: 24px;
            margin-bottom: 6px;
        }

        .bill-header p {
            color: #777;
            font-size: 14px;
        }

        .bill-number {
            text-align: right;
        }

        .bill-number strong {
            display: block;
            font-size: 18px;
            margin-bottom: 5px;
        }

        .bill-number span {
            color: #777;
            font-size: 14px;
        }

        .bill-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .bill-info-box {
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            padding: 18px;
        }

        .bill-info-box h4 {
            margin-bottom: 12px;
            font-size: 14px;
        }

        .bill-info-box p {
            font-size: 14px;
            color: #555;
            margin-bottom: 7px;
            line-height: 1.5;
        }

        .bill-table {
            width: 100%;
            border-collapse: collapse;
        }

        .bill-table th {
            background: #f5f5f5;
            padding: 14px;
            text-align: left;
            font-size: 14px;
            border-bottom: 1px solid #ddd;
        }

        .bill-table td {
            padding: 15px 14px;
            font-size: 14px;
            border-bottom: 1px solid #eee;
        }

        .bill-table th:last-child,
        .bill-table td:last-child {
            text-align: right;
        }

        .bill-total {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
        }

        .total-box {
            width: 350px;
            border-top: 2px solid #111;
            padding-top: 15px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 15px;
            margin-bottom: 10px;
        }

        .total-row strong {
            font-size: 15px;
        }

        .final-total {
            border-top: 1px solid #ddd;
            padding-top: 12px;
            margin-top: 10px;
        }


        .final-total span {
            font-size: 18px;
            font-weight: 600;
        }


        .final-total strong {
            font-size: 22px;
        }


        .paid-status {
            display: inline-block;
            margin-top: 12px;
            padding: 6px 12px;
            background: #d9f2df;
            color: #176b2c;
            border-radius: 4px;
            font-size: 13px;
        }


        .bill-footer {
            margin-top: 35px;
            padding-top: 20px;
            border-top: 1px solid #e5e5e5;
            color: #777;
            font-size: 13px;
            text-align: center;
        }


        .bill-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }


        .bill-actions button,
        .bill-actions a {
            padding: 10px 16px;
            border: 1px solid #ddd;
            background: #111;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }


        .bill-actions a {
            background: #fff;
            color: #111;
        }


        .bill-actions button:hover,
        .bill-actions a:hover {
            opacity: 0.8;
        }


        @media (max-width: 700px) {

            .bill-info {
                grid-template-columns: 1fr;
            }


            .bill-header {
                flex-direction: column;
                gap: 15px;
            }


            .bill-number {
                text-align: left;
            }


            .total-box {
                width: 100%;
            }

        }


        @media print {

            .sidebar,
            .header,
            .bill-actions {
                display: none !important;
            }


            .main {
                margin-left: 0;
                width: 100%;
                padding: 0;
            }


            .bill-container {
                border: none;
                max-width: 100%;
                padding: 0;
            }


            body {
                background: #fff;
            }

        }
    </style>
</head>


<body>
<aside class="sidebar">
    <h1>
        Inknest
    </h1>
    <p class="admin-label">
        ADMIN PANEL
    </p>

    <nav>
        <a href="admin_dashboard.php">
            Dashboard
        </a>
        <a href="products.php">
            Products
        </a>
        <a href="add_product.php">
            Add Product
        </a>
        <a href="orders.php">
            Orders
        </a>
        <a href="users.php">
            Users
        </a>
        <a href="user_profiles.php">
            User Profiles
        </a>
        <a href="user_log.php">
            User Activity
        </a>
        <a href="product_reviews.php">
            Product Reviews
        </a>
        <a href="bill.php" class="active">
            Bills
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
                Bill #<?php echo (int) $order["id"]; ?>
            </h2>
            <p>
                Customer order bill and payment details.
            </p>
        </div>
    </header>

    <section class="bill-container">
        <div class="bill-header">
            <div>
                <h3>
                    Inknest
                </h3>
                <p>
                    Order Bill
                </p>
            </div>

            <div class="bill-number">
                <strong>
                    Bill #<?php echo (int) $order["id"]; ?>
                </strong>
                <span>
                    <?php
                    echo date(
                        "M d, Y",
                        strtotime($order["order_date"])
                    );
                    ?>
                </span>
            </div>
        </div>


        <div class="bill-info">
            <div class="bill-info-box">
                <h4>
                    Customer Information
                </h4>
                <p>
                    <strong>
                        Name:
                    </strong>
                    <?php
                    echo htmlspecialchars(
                        $customerName
                    );
                    ?>
                </p>

                <p>
                    <strong>
                        Username:
                    </strong>
                    <?php
                    echo htmlspecialchars(
                        $order["user_name"]
                    );
                    ?>
                </p>

                <p>
                    <strong>
                        Email:
                    </strong>
                    <?php
                    echo htmlspecialchars(
                        $order["email"]
                    );
                    ?>
                </p>

                <p>
                    <strong>
                        Phone:
                    </strong>
                    <?php
                    echo htmlspecialchars(
                        $order["phone"]
                    );
                    ?>
                </p>

                <p>
                    <strong>
                        Address:
                    </strong>
                    <?php
                    echo htmlspecialchars(
                        $order["delivery_address"]
                    );
                    ?>
                </p>
            </div>

            <div class="bill-info-box">
                <h4>
                    Payment Information
                </h4>
                <p>
                    <strong>
                        Payment Method:
                    </strong>
                    <?php
                    echo !empty(
                        $order["payment_method"]
                    )
                        ? htmlspecialchars(
                            ucfirst(
                                $order["payment_method"]
                            )
                        )
                        : "N/A";
                    ?>
                </p>

                <p>
                    <strong>
                        Payment Status:
                    </strong>
                    <?php
                    echo htmlspecialchars(
                        $order["payment_status"]
                    );
                    ?>
                </p>

                <p>
                    <strong>
                        Order Status:
                    </strong>
                    <?php
                    echo htmlspecialchars(
                        $order["status"]
                    );
                    ?>
                </p>

                <p>
                    <strong>
                        Transaction ID:
                    </strong>
                    <?php
                    echo !empty(
                        $order["transaction_id"]
                    )
                        ? htmlspecialchars(
                            $order["transaction_id"]
                        )
                        : "N/A";
                    ?>
                </p>
            </div>
        </div>

        <table class="bill-table">
            <thead>
                <tr>
                    <th>
                        Description
                    </th>
                    <th>
                        Amount
                    </th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>
                        Product Price
                    </td>
                    <td>
                        Rs.
                        <?php
                        echo number_format(
                            $productPrice,
                            2
                        );
                        ?>
                    </td>
                </tr>

                <tr>
                    <td>
                        Shipping Price
                    </td>
                    <td>
                        Rs.
                        <?php
                        echo number_format(
                            $shippingPrice,
                            2
                        );
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="bill-total">
            <div class="total-box">
                <div class="total-row">
                    <span>
                        Product Price
                    </span>
                    <strong>
                        Rs.
                        <?php
                        echo number_format(
                            $productPrice,
                            2
                        );
                        ?>
                    </strong>
                </div>

                <div class="total-row">
                    <span>
                        Shipping Price
                    </span>
                    <strong>
                        Rs.
                        <?php
                        echo number_format(
                            $shippingPrice,
                            2
                        );
                        ?>
                    </strong>
                </div>

                <div class="total-row final-total">
                    <span>
                        Total Price
                    </span>

                    <strong>
                        Rs.
                        <?php
                        echo number_format(
                            $productPrice + $shippingPrice,
                            2
                        );
                        ?>
                    </strong>
                </div>

                <span class="paid-status">
                    Payment Paid
                </span>
            </div>
        </div>

        <div class="bill-footer">
            Thank you for your order from Inknest.
        </div>

        <div class="bill-actions">
            <button type="button" onclick="window.print()">
                Print / Save PDF
            </button>
            <a href="bill.php">Back to Bills</a>
        </div>
    </section>
</main>
</body>
</html>
<?php
exit();
}

$sql = "SELECT
            orders.id,
            orders.total_amount,
            orders.status,
            orders.order_date,
            orders.payment_method,
            orders.payment_status,
            users.user_name,
            users.first_name,
            users.last_name
        FROM orders
        INNER JOIN users
            ON orders.user_id = users.id
        WHERE LOWER(TRIM(orders.status)) = 'delivered'
        AND LOWER(TRIM(orders.payment_status)) = 'paid'
        ORDER BY orders.id DESC";
$bills = mysqli_query(
    $conn,
    $sql
);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        Bills | Inknest
    </title>
    <link rel="stylesheet" href="../css/orders.css?v=<?php echo time(); ?>">
    <style>
        .bill-list {
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            overflow: hidden;
        }


        .bill-list table {
            width: 100%;
            border-collapse: collapse;
        }


        .bill-list th {
            background: #f5f5f5;
            padding: 14px 16px;
            text-align: left;
            font-size: 14px;
            font-weight: 600;
            border-bottom: 1px solid #ddd;
        }


        .bill-list td {
            padding: 14px 16px;
            font-size: 14px;
            border-bottom: 1px solid #eee;
        }


        .bill-list tr:last-child td {
            border-bottom: none;
        }


        .bill-list tr:hover {
            background: #fafafa;
        }


        .paid-status {
            display: inline-block;
            padding: 6px 12px;
            background: #d9f2df;
            color: #176b2c;
            border-radius: 4px;
            font-size: 13px;
        }


        .delivered-status {
            display: inline-block;
            padding: 6px 12px;
            background: #d9f2df;
            color: #176b2c;
            border-radius: 4px;
            font-size: 13px;
        }


        .view-bill-btn {
            display: inline-block;
            padding: 9px 14px;
            background: #111;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 13px;
        }


        .view-bill-btn:hover {
            opacity: 0.8;
        }


        .empty {
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
        }


        .empty h3 {
            margin-bottom: 8px;
        }


        .empty p {
            color: #777;
            font-size: 14px;
        }
    </style>
</head>

<body>
<aside class="sidebar">
    <h1>
        Inknest
    </h1>
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
        <a href="bill.php" class="active">Bills</a>
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
            <h2>
                Bills
            </h2>
            <p>
                Bills are available for delivered and paid orders.
            </p>
        </div>
    </header>

    <section>
        <?php if (
            $bills &&
            mysqli_num_rows($bills) > 0
        ): ?>
            <div class="bill-list">
                <table>
                    <thead>
                        <tr>
                            <th>
                                Bill ID
                            </th>
                            <th>
                                Customer
                            </th>
                            <th>
                                Username
                            </th>
                            <th>
                                Total
                            </th>
                            <th>
                                Order Status
                            </th>
                            <th>
                                Payment Status
                            </th>
                            <th>
                                Date
                            </th>
                            <th>
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php while (
                        $bill = mysqli_fetch_assoc($bills)
                    ): ?>
                        <tr>
                            <td>
                                #
                                <?php
                                echo (int)
                                    $bill["id"];
                                ?>
                            </td>

                            <td>
                                <?php
                                $listCustomerName = trim(
                                    $bill["first_name"]
                                    . " "
                                    . $bill["last_name"]
                                );
                                if (empty($listCustomerName)) {
                                    $listCustomerName =
                                        $bill["user_name"];
                                }
                                echo htmlspecialchars(
                                    $listCustomerName
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $bill["user_name"]
                                );
                                ?>
                            </td>

                            <td>
                                Rs.
                                <?php
                                echo number_format(
                                    (float)
                                    $bill["total_amount"],
                                    2
                                );
                                ?>
                            </td>

                            <td>
                                <span class="delivered-status">
                                    Delivered
                                </span>
                            </td>

                            <td>
                                <span class="paid-status">
                                    Paid
                                </span>
                            </td>

                            <td>
                                <?php
                                echo date(
                                    "M d, Y",
                                    strtotime(
                                        $bill["order_date"]
                                    )
                                );
                                ?>
                            </td>

                            <td>
                                <a href="bill.php?order_id=<?php echo (int) $bill["id"]; ?>" class="view-bill-btn">
                                    View Bill
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <div class="empty">
                <h3>
                    No Bills Available
                </h3>
                <p>
                    Bills will appear here after an order is
                    Delivered and Payment is Paid.
                </p>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>