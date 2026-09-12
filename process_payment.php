<?php
session_start();
include "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$userId = (int) $_SESSION["user_id"];
$shippingCharge = 100;
$esewaProductCode = "EPAYTEST";
$esewaSecretKey = "8gBm/:&EnhH.1/q";
$esewaUrl = "https://rc-epay.esewa.com.np/api/epay/main/v2/form";
$baseUrl = "http://localhost/inknest";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: checkout.php");
    exit();
}

$paymentMethod = $_POST["payment_method"] ?? "";
$allowedMethods = ["esewa","cash"];

if (!in_array($paymentMethod, $allowedMethods, true)) {
    die("Invalid payment method.");
}

$email=trim($_POST["email"] ?? "");
$phone=trim($_POST["phone"] ?? "");
$deliveryAddress = trim($_POST["delivery_address"] ?? "");

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email address.");
}

$phoneDigits = preg_replace("/\D/", "", $phone);

if (strlen($phoneDigits) < 10) {
    die("Invalid phone number.");
}

if (strlen($deliveryAddress) < 5) {
    die("Invalid delivery address.");
}

$cartJson = $_POST["cart"] ?? "";

if (empty($cartJson)) {
    die("Cart is empty.");
}

$cart = json_decode($cartJson, true);

if (!is_array($cart) || empty($cart)) {
    die("Invalid cart data.");
}

$cleanCart = [];
foreach ($cart as $id) {
    $id = (int) $id;
    if ($id > 0) {
        $cleanCart[] = $id;
    }
}

if (empty($cleanCart)) {
    die("Invalid products in cart.");
}

$productIds = array_values(
    array_unique($cleanCart)
);

if (empty($productIds)) {
    die("No products found in cart.");
}

$placeholders = implode(
    ",",
    array_fill(
        0,
        count($productIds),
        "?"
    )
);

$sql = "
    SELECT
        id,
        product_name,
        price,
        stock
    FROM products
    WHERE id IN ($placeholders)
";

$stmt = mysqli_prepare(
    $conn,
    $sql
);

if (!$stmt) {
    die("Product query failed: " .mysqli_error($conn));
}

$types = str_repeat(
    "i",
    count($productIds)
);

mysqli_stmt_bind_param(
    $stmt,
    $types,
    ...$productIds
);

if (!mysqli_stmt_execute($stmt)) {
    $error = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    die(
        "Product query failed: " .
        $error
    );
}

mysqli_stmt_bind_result(
    $stmt,
    $productId,
    $productName,
    $productPrice,
    $productStock
);

$products = [];
while (mysqli_stmt_fetch($stmt)) {
    $products[$productId] = [
        "id" => $productId,
        "product_name" => $productName,
        "price" => $productPrice,
        "stock" => $productStock
    ];
}

mysqli_stmt_close($stmt);

if (empty($products)) {
    die("No valid products found.");
}

$cartQuantities = [];

foreach ($cleanCart as $cartProductId) {
    $cartProductId = (int) $cartProductId;
    if (!isset($cartQuantities[$cartProductId])) {
        $cartQuantities[$cartProductId] = 0;
    }
    $cartQuantities[$cartProductId]++;
}

$subtotal = 0;
foreach ($cartQuantities as $cartProductId => $quantity) {
    if (!isset($products[$cartProductId])) {
        die(
            "Product ID " .
            $cartProductId .
            " was not found."
        );
    }
    $product = $products[$cartProductId];
    $availableStock = (int) $product["stock"];
    if ($quantity > $availableStock) {
        die(
            "Not enough stock for " .
            htmlspecialchars(
                $product["product_name"]
            ) .
            ". Available stock: " .
            $availableStock .
            ", requested: " .
            $quantity .
            "."
        );
    }
    $price = (float) $product["price"];
    $subtotal += $price * $quantity;
}

if ($subtotal <= 0) {
    die("Invalid order amount.");
}
$totalAmount = $subtotal + $shippingCharge;
mysqli_begin_transaction($conn);

try {
    $orderSql = "
        INSERT INTO orders
        (
            user_id,
            email,
            phone,
            delivery_address,
            total_amount,
            status,
            payment_method,
            payment_status,
            transaction_id
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            'Pending',
            ?,
            'Pending',
            NULL
        )
    ";
    $stmt = mysqli_prepare(
        $conn,
        $orderSql
    );
    if (!$stmt) {
        throw new Exception(
            "Order query failed: " .
            mysqli_error($conn)
        );
    }
    mysqli_stmt_bind_param(
        $stmt,
        "isssds",
        $userId,
        $email,
        $phone,
        $deliveryAddress,
        $totalAmount,
        $paymentMethod
    );

    if (!mysqli_stmt_execute($stmt)) {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        throw new Exception(
            "Order could not be created: " .
            $error
        );
    }
    $orderId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    if ($orderId <= 0) {
        throw new Exception(
            "Order could not be created."
        );
    }

    $itemSql = "
        INSERT INTO order_items
        (
            order_id,
            product_id,
            quantity,
            price
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?
        )
    ";

    $itemStmt = mysqli_prepare(
        $conn,
        $itemSql
    );

    if (!$itemStmt) {
        throw new Exception(
            "Order item query failed: " .
            mysqli_error($conn)
        );
    }

    foreach ($cartQuantities as $productId => $quantity) {
        if ($quantity <= 0) {
            continue;
        }
        $productId = (int) $productId;
        $price = (float) $products[$productId]["price"];
        mysqli_stmt_bind_param(
            $itemStmt,
            "iiid",
            $orderId,
            $productId,
            $quantity,
            $price
        );

        if (!mysqli_stmt_execute($itemStmt)) {
            $error = mysqli_stmt_error($itemStmt);
            mysqli_stmt_close($itemStmt);
            throw new Exception(
                "Failed to create order item: " .
                $error
            );
        }
    }
    mysqli_stmt_close($itemStmt);
    $activityType = "Checkout";
    $activitySql = "
        INSERT INTO user_product_activity
        (
            user_id,
            product_id,
            activity_type
        )
        VALUES
        (
            ?,
            ?,
            ?
        )
    ";

    $activityStmt = mysqli_prepare(
        $conn,
        $activitySql
    );

    if (!$activityStmt) {
        throw new Exception(
            "Activity query failed: " .
            mysqli_error($conn)
        );
    }

    foreach ($cartQuantities as $cartProductId => $quantity) {
        $cartProductId = (int) $cartProductId;
        if ($cartProductId <= 0) {
            continue;
        }

        mysqli_stmt_bind_param(
            $activityStmt,
            "iis",
            $userId,
            $cartProductId,
            $activityType
        );

        if (!mysqli_stmt_execute($activityStmt)) {
            $activityError=mysqli_stmt_error($activityStmt);
            mysqli_stmt_close($activityStmt);
            throw new Exception(
                "Checkout activity failed: " .
                $activityError
            );
        }
    }
    mysqli_stmt_close($activityStmt);
    mysqli_commit($conn);
} catch (Exception $e) {
    mysqli_rollback($conn);
    die(
        "Checkout failed: " .
        htmlspecialchars(
            $e->getMessage()
        )
    );
}

if ($paymentMethod === "cash") {
    header(
        "Location: payment_success.php" .
        "?method=cash" .
        "&order_id=" .
        $orderId
    );
    exit();
}

if ($paymentMethod === "esewa") {
    $transactionUuid =
        "INK-" .
        $orderId .
        "-" .
        time();

    $sql = "
        UPDATE orders
        SET transaction_id = ?
        WHERE id = ?
        AND user_id = ?
    ";
    $stmt = mysqli_prepare(
        $conn,
        $sql
    );

    if (!$stmt) {
        die(
            "Transaction update failed: " .
            mysqli_error($conn)
        );
    }

    mysqli_stmt_bind_param(
        $stmt,
        "sii",
        $transactionUuid,
        $orderId,
        $userId
    );

    if (!mysqli_stmt_execute($stmt)) {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        die(
            "Transaction update failed: " .
            $error
        );
    }
    mysqli_stmt_close($stmt);
    $amount = number_format(
        $subtotal,
        2,
        ".",
        ""
    );
    $taxAmount = "0";
    $productServiceCharge = "0";
    $productDeliveryCharge =
        number_format(
            $shippingCharge,
            2,
            ".",
            ""
        );
    $totalAmountFormatted =
        number_format(
            $totalAmount,
            2,
            ".",
            ""
        );

    $signedFieldNames="total_amount,transaction_uuid,product_code";
    $signatureMessage =
        "total_amount=" .
        $totalAmountFormatted .
        ",transaction_uuid=" .
        $transactionUuid .
        ",product_code=" .
        $esewaProductCode;
    $signature = base64_encode(
        hash_hmac(
            "sha256",
            $signatureMessage,
            $esewaSecretKey,
            true
        )
    );

    $successUrl=$baseUrl ."/payment_success.php";
    $failureUrl =$baseUrl ."/payment_failure.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        Redirecting to eSewa | Inknest
    </title>
    <link rel="stylesheet" href="css/process_payment.css?v=<?php echo time(); ?>">
</head>

<body>
    <div class="loading">
        <h2>
            Redirecting to eSewa...
        </h2>
        <p>
            Please wait while we connect you to eSewa.
        </p>
        <form id="esewaForm" action="<?php echo htmlspecialchars($esewaUrl); ?>" method="POST">
            <input type="hidden" name="amount" value="<?php echo htmlspecialchars($amount); ?>">
            <input type="hidden" name="tax_amount" value="<?php echo htmlspecialchars($taxAmount); ?>">
            <input type="hidden" name="total_amount" value="<?php echo htmlspecialchars($totalAmountFormatted); ?>">
            <input type="hidden" name="transaction_uuid" value="<?php echo htmlspecialchars($transactionUuid); ?>">
            <input type="hidden" name="product_code" value="<?php echo htmlspecialchars($esewaProductCode); ?>">
            <input type="hidden" name="product_service_charge" value="<?php echo htmlspecialchars($productServiceCharge); ?>">
            <input type="hidden" name="product_delivery_charge" value="<?php echo htmlspecialchars($productDeliveryCharge); ?>">
            <input type="hidden" name="success_url" value="<?php echo htmlspecialchars($successUrl); ?>">
            <input type="hidden" name="failure_url" value="<?php echo htmlspecialchars($failureUrl); ?>">
            <input type="hidden" name="signed_field_names" value="<?php echo htmlspecialchars($signedFieldNames); ?>">
            <input type="hidden" name="signature" value="<?php echo htmlspecialchars($signature); ?>">
            <button type="submit">Continue to eSewa</button>
        </form>
    </div>
    <script>
        document.getElementById("esewaForm").submit();
    </script>
</body>
</html>
<?php
    exit();
}
?>