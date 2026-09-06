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
$khaltiSecretKey = "Authorization: 405d86faaaca4c3dacc4489b57fbcae7";
$khaltiUrl ="https://dev.khalti.com/api/v2/epayment/initiate/";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: checkout.php");
    exit();
}

$paymentMethod = $_POST["payment_method"] ?? "";
$allowedMethods = ["esewa","khalti","cash"];

if (!in_array($paymentMethod, $allowedMethods, true)) {
    die("Invalid payment method.");
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
$productIds = array_values(array_unique($cleanCart));
$placeholders = implode(
    ",",
    array_fill(
        0,
        count($productIds),
        "?"
    )
);
$sql = "SELECT id, product_name, price FROM products WHERE id IN ($placeholders)";
$stmt = mysqli_prepare(
    $conn,
    $sql
);

if (!$stmt) {
    die("Database error.");
}
$types = str_repeat("i",count($productIds));
mysqli_stmt_bind_param(
    $stmt,
    $types,
    ...$productIds
);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$products = [];

while ($row = mysqli_fetch_assoc($result)) {
    $products[$row["id"]] = $row;
}
mysqli_stmt_close($stmt);

if (empty($products)) {
    die("No valid products found.");
}
$subtotal = 0;

foreach ($products as $productId => $product) {
    $quantity = 0;
    foreach ($cleanCart as $cartId) {
        if ($cartId == $productId) {
            $quantity++;
        }
    }

    if ($quantity > 0) {
        $price = (float) $product["price"];
        $subtotal += $price * $quantity;
    }
}

if ($subtotal <= 0) {
    die("Invalid order amount.");
}
$totalAmount =$subtotal + $shippingCharge;
$sql = "INSERT INTO orders(user_id,total_amount,status,payment_method,payment_status,transaction_id)
    VALUES(?, ?, 'Pending', ?, 'Pending', NULL)";
$stmt = mysqli_prepare(
    $conn,
    $sql
);

if (!$stmt) {
    die("Unable to create order: ". mysqli_error($conn));
}
mysqli_stmt_bind_param(
    $stmt,
    "ids",
    $userId,
    $totalAmount,
    $paymentMethod
);

if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    die("Unable to create order: ". mysqli_error($conn));
}
$orderId =mysqli_insert_id($conn);
mysqli_stmt_close($stmt);
$itemSql = "INSERT INTO order_items(order_id,product_id,quantity,price) VALUES(?, ?, ?, ?)";
$itemStmt = mysqli_prepare(
    $conn,
    $itemSql
);

if (!$itemStmt) {
    die("Unable to create order items.");
}

foreach ($products as $productId => $product) {
    $quantity = 0;
    foreach ($cleanCart as $cartId) {
        if ($cartId == $productId) {
            $quantity++;
        }
    }

    if ($quantity <= 0) {
        continue;
    }
    $productId = (int) $productId;
    $price = (float) $product["price"];
    mysqli_stmt_bind_param(
        $itemStmt,
        "iiid",
        $orderId,
        $productId,
        $quantity,
        $price
    );
    mysqli_stmt_execute($itemStmt);
}
mysqli_stmt_close($itemStmt);
if ($paymentMethod === "cash") {
    header(
        "Location: payment_success.php"
        . "?method=cash"
        . "&order_id="
        . $orderId
    );
    exit();
}

if ($paymentMethod === "esewa") {
    $transactionUuid =
        "INK-" . $orderId . "-" . time();
    $sql = "UPDATE orders SET transaction_id = ? WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare(
        $conn,
        $sql
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sii",
        $transactionUuid,
        $orderId,
        $userId
    );
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $amount =number_format(
            $subtotal,
            2,
            ".",
            ""
        );

    $taxAmount = "0";
    $productServiceCharge = "0";
    $productDeliveryCharge =number_format(
            $shippingCharge,
            2,
            ".",
            ""
        );

    $totalAmountFormatted =number_format(
            $totalAmount,
            2,
            ".",
            ""
        );
    $signedFieldNames ="total_amount,transaction_uuid,product_code";
    $signatureMessage =
        "total_amount="
        . $totalAmountFormatted
        . ",transaction_uuid="
        . $transactionUuid
        . ",product_code="
        . $esewaProductCode;

    $signature =base64_encode(
            hash_hmac(
                "sha256",
                $signatureMessage,
                $esewaSecretKey,
                true
            )
        );
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <title>Redirecting to eSewa | Inknest</title>
    <link rel="stylesheet" href="css/process_payment.css?v=<?php echo time(); ?>">
</head>

<body>
    <div class="loading">
        <h2>Redirecting to eSewa...</h2>
        <p>Please wait while we connect you to eSewa.</p>
        <form id="esewaForm" action="<?php echo htmlspecialchars($esewaUrl); ?>" method="POST">
            <input type="hidden" name="amount" value="<?php echo htmlspecialchars($amount); ?>">
            <input type="hidden" name="tax_amount" value="<?php echo htmlspecialchars($taxAmount); ?>">
            <input type="hidden" name="total_amount" value="<?php echo htmlspecialchars($totalAmountFormatted); ?>">
            <input type="hidden" name="transaction_uuid" value="<?php echo htmlspecialchars($transactionUuid); ?>">
            <input type="hidden" name="product_code" value="<?php echo htmlspecialchars($esewaProductCode); ?>">
            <input type="hidden" name="product_service_charge" value="<?php echo htmlspecialchars($productServiceCharge); ?>">
            <input type="hidden" name="product_delivery_charge" value="<?php echo htmlspecialchars($productDeliveryCharge); ?>">
            <input type="hidden" name="success_url" value="<?php echo htmlspecialchars($baseUrl . "/payment_success.php"); ?>">
            <input type="hidden" name="failure_url" value="<?php echo htmlspecialchars($baseUrl . "/payment_success.php"); ?>">
            <input type="hidden" name="signed_field_names" value="<?php echo htmlspecialchars( $signedFieldNames); ?>">
            <input type="hidden" name="signature" value="<?php echo htmlspecialchars( $signature); ?>">
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

if ($paymentMethod === "khalti") {
    if (
        empty($khaltiSecretKey) ||$khaltiSecretKey ==="Authorization: 405d86faaaca4c3dacc4489b57fbcae7"
    ) {
        die("Khalti test secret key is not configured.");
    }
    $amountPaisa =(int) round($totalAmount * 100);
    $sql = "SELECT first_name, last_name,email,phone_no FROM users WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare(
        $conn,
        $sql
    );
    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $userId
    );
    mysqli_stmt_execute($stmt);
    $result =mysqli_stmt_get_result($stmt);
    $user =mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$user) {
        die("User information not found.");
    }

    $customerName =trim(
            $user["first_name"]
            . " "
            . $user["last_name"]
        );
    $purchaseOrderId ="INK-ORDER-" . $orderId;
    $khaltiData = ["return_url" =>$baseUrl . "/payment_success.php",
        "website_url" =>$baseUrl,
        "amount" =>$amountPaisa,
        "purchase_order_id" =>$purchaseOrderId,
        "purchase_order_name" =>"Inknest Order #" . $orderId,
        "customer_info" => ["name" =>$customerName,
            "email" =>$user["email"],
            "phone" =>$user["phone_no"]
        ]
    ];
    $ch = curl_init( $khaltiUrl);
    curl_setopt(
        $ch,
        CURLOPT_RETURNTRANSFER,
        true
    );

    curl_setopt(
        $ch,
        CURLOPT_POST,
        true
    );

    curl_setopt(
        $ch,
        CURLOPT_POSTFIELDS,
        json_encode($khaltiData)
    );

    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        [ "Authorization: Key ". $khaltiSecretKey,
            "Content-Type: application/json"
        ]
    );

    $response=curl_exec($ch);
    $curlError=curl_error($ch);
    $httpCode=curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );
    curl_close($ch);

    if ($response === false) {
        die("Khalti connection failed: ". $curlError);
    }

    $khaltiResponse =json_decode($response,true);


    if (!is_array($khaltiResponse)) {
        die("Invalid response received from Khalti.");
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        $message =$khaltiResponse["detail"] ?? "Khalti payment initiation failed.";
        die(htmlspecialchars($message));
    }

    $pidx=$khaltiResponse["pidx"]?? "";
    $paymentUrl =$khaltiResponse["payment_url"]?? "";

    if (empty($pidx) ||empty($paymentUrl)) {
        die("Khalti did not return a payment URL.");
    }

    $sql = "UPDATE orders SET transaction_id = ? WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare(
        $conn,
        $sql
    );
    mysqli_stmt_bind_param(
        $stmt,
        "sii",
        $pidx,
        $orderId,
        $userId
    );
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: " . $paymentUrl);
    exit();
}
?>