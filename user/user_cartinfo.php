<?php
// Start session to get the user_id
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get the user ID from session

// Database credentials
$host = "localhost"; // Replace with your host
$username = "root";  // Replace with your database username
$password = "";      // Replace with your database password
$database = "kpis";  // Your database name

// Connect to the database
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the profile picture for the user
$sql = "SELECT profile_picture FROM customers WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($profile_picture);
$stmt->fetch();

// Set default profile picture if none exists
if (empty($profile_picture) || !file_exists($profile_picture)) {
    $profile_picture = "uploads/default.jpg"; // Default image
}

// Fetch customer details
// Fetch customer details including customer_name
$sql = "SELECT customer_name, phone_number, address FROM customers WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Variables to store customer data
$customer_name = "N/A";
$phone_number = "N/A";
$address = "N/A";

if ($result->num_rows > 0) {
    // Fetch the customer data
    $row = $result->fetch_assoc();
    $customer_name = htmlspecialchars($row['customer_name']);
    $phone_number = htmlspecialchars($row['phone_number']);
    $address = htmlspecialchars($row['address']);
}

// Fetch cart items for the user
$sql = "SELECT order_id, service_type, layout_size, price, paper_type, thickness, copies, notes, print_type, file_upload, created_at 
        FROM cart_tbl 
        WHERE user_id = ? 
        ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Initialize total amount
$total_amount = 0;
$cart_items = [];
$subtotals = []; // To store subtotals of items

while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    // Set the subtotal as the price (no multiplication with copies)
    $subtotal = $row['price']; // Subtotal is just the price, not price * copies
    $subtotals[] = $subtotal;
    $total_amount += $subtotal; // Total amount is the sum of the prices (no multiplication by copies)
}

// Initialize coupon message and discount
$coupon_message = "";
$voucher_discount = 0;

// Check if a coupon code is provided and validate it
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['voucher_code'])) {
    $voucher_code = $_POST['voucher_code'];

    // Query to check the coupon validity and fetch the discount value
    $voucher_sql = "SELECT discount_value, expiry_date FROM promotion_tbl WHERE coupon_code = ?";
    $voucher_stmt = $conn->prepare($voucher_sql);
    $voucher_stmt->bind_param('s', $voucher_code);
    $voucher_stmt->execute();
    $voucher_stmt->store_result();

    // If the coupon exists, check if it's expired
    if ($voucher_stmt->num_rows > 0) {
        $voucher_stmt->bind_result($discount_value, $expiry_date);
        $voucher_stmt->fetch();

        // Check if the coupon is expired
        if (strtotime($expiry_date) >= time()) {
            // Apply the discount
            $voucher_discount = ($total_amount * ($discount_value / 100)); // Apply percentage discount
            $coupon_message = "Coupon applied! Discount: $discount_value%";
            $coupon_message_class = "success"; // Green text for success
        } else {
            $coupon_message = "The coupon has expired.";
            $coupon_message_class = "error"; // Red text for expired coupon
        }
    } else {
        $coupon_message = "Invalid or expired coupon code.";
        $coupon_message_class = "error"; // Red text for invalid coupon
    }

    $voucher_stmt->close();
}

$total_subtotal = 0;

// Loop through each cart item
foreach ($cart_items as $item) {
    // Subtotal is now just the price, not price * copies
    $subtotal = $item['price']; 

    // Add the subtotal to the total subtotal
    $total_subtotal += $subtotal;
}
// Define shipping cost (this can still be dynamic if needed)
$shipping_cost = 30; // Example shipping cost

// Calculate the total payment after applying the shipping cost and voucher discount
$total_payment = $total_amount + $shipping_cost - $voucher_discount;

// Process payment and transfer cart to order_tbl
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Generate unique IDs
    $order_id = uniqid("ORDER_");
    $transaction_id = uniqid("TXN_");

    // Payment details
    $payment_method = 'Credit Card'; // Example method
    $payment_status = 'Completed';

    // Start a database transaction
    $conn->begin_transaction();

    try {
     // Step 1: Insert payment details into payment_tbl
$payment_sql = "INSERT INTO payment_tbl (order_id, payment_method, payment_status, amount_paid, transaction_id, payment_date) 
VALUES (?, ?, ?, ?, ?, NOW())";

// Prepare the statement
$payment_stmt = $conn->prepare($payment_sql);

// Bind parameters
$payment_stmt->bind_param('issds', $order_id, $payment_method, $payment_status, $total_payment, $transaction_id);

// Execute the statement
if (!$payment_stmt->execute()) {
throw new Exception("Failed to record payment: " . $conn->error);
}


        // Step 2: Transfer cart items to order_tbl
        $order_sql = "INSERT INTO order_tbl (order_id2, user_id, customer_name, service_type, layout_size, price, paper_type, thickness, copies, notes, print_type, file_upload, order_date) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $order_stmt = $conn->prepare($order_sql);

        foreach ($cart_items as $item) {
            $order_stmt->bind_param(
                'sissdssdssss',
                $order_id,
                $user_id,
                $customer_name,  // Now using customer_name from the customers table
                $item['service_type'],
                $item['layout_size'],
                $item['price'],
                $item['paper_type'],
                $item['thickness'],
                $item['copies'],
                $item['notes'],
                $item['print_type'],
                $item['file_upload']
            );

            if (!$order_stmt->execute()) {
                throw new Exception("Failed to transfer cart item to order_tbl: " . $conn->error);
            }
        }

        // Step 3: Clear cart_tbl
        $delete_cart_sql = "DELETE FROM cart_tbl WHERE user_id = ?";
        $delete_cart_stmt = $conn->prepare($delete_cart_sql);
        $delete_cart_stmt->bind_param('i', $user_id);

        if (!$delete_cart_stmt->execute()) {
            throw new Exception("Failed to clear cart: " . $conn->error);
        }

        // Commit the transaction
        $conn->commit();

        echo "<script>alert('Order placed successfully!'); window.location.href = 'user_mypur.php';</script>";

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }

    // Close statements
    $payment_stmt->close();
    $order_stmt->close();
    $delete_cart_stmt->close();
}

// Close the database connection
$conn->close();

?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Checkout UI</title>
    <style>
        /* General reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background-color: #fce4ec; /* Light pink background */
            color: #333;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .section-title {
            margin-top: -5px;
            padding: 10px;
            background-color: #f8bbd0; /* Pink theme header */
            font-weight: bold;
        }
        .section {
            padding: 20px;
        }
        /* Delivery Address */
        .delivery-address h3 {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: #d81b60; /* Pink header */
        }
        .delivery-address p {
            font-size: 0.9rem;
            color: #555;
        }
        /* Table of Products */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th, table td {
            border-bottom: 1px solid #f0 ```php
f0f0;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #fce4ec;
            color: #d81b60;
        }
        .price {
            color: #d81b60;
            font-weight: bold;
        }
        /* Shipping and Total */
        .shipping-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 10px 0;
        }
        .shipping-option {
            font-size: 0.9rem;
            color: #777;
        }
        .total-payment {
            font-size: 1.2rem;
            text-align: right;
            font-weight: bold;
            color: #d81b60;
        }
        /* Vouchers */
        .voucher-section {
            margin: 10px 0;
        }
        /* Place Order Button */
        .place-order-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #d81b60;
            color: #fff;
            font-size: 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .place-order-btn:hover {
            background-color: #ad1457;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 80px;
            background: linear-gradient(to right, #ffb3c6, #f0e68c);
            top: 0;
            left: 0;
            right: 0;
            background-color: transparent;
            border-bottom: 1px solid rgb(223, 55, 83);
            z-index: 1000;
            padding: 0 20px;
        }
        .left-sec {
            display: flex;
            align-items: center;
        }
        .logo-btn {
            height: 35px;
            width: 35px;
            margin-right: 20px;
            transition: transform 0.3s ease;
        }
        .logo-btn:hover {
            transform: scale(1.2);
        }
        .logo {
            height: 50px;
            width: 50px;
            -webkit-user-select: none; /* For Safari */
            -ms-user-select: none;
            -webkit-user-drag: none;
        }
        h1 {
            font-size: 18px;
            color: white;
            margin-left: 10px;
            line-height: 1.2;
            -webkit-user-select: none; /* For Safari */
            -ms-user-select: none;
            -webkit-user-drag: none;
        }
        span {
            font-size: 14px;
            color: rgb(223, 55, 83);
            -webkit-user-select: none; /* For Safari */
            -ms-user-select: none;
            -webkit-user-drag: none;
        }
        .mid-sec {
            margin-right: 78px;
            display: flex;
            justify-content: center;  /* Centers items horizontally */
            align-items: center;      /* Centers items vertically */
            gap: 20px;  /* Adds space between the items */
            width: 100%;
        }
        .feat {
            font-size: 18px;
            color: white;
            margin-right: 30px;
            text-decoration: none;
            font-weight: 600;
            position: relative;
        }
        .feat:after {
            content: "";
            position: absolute;
            bottom: -3px;
            left: 0;
            height: 2px;
            width: 0;
            background-color: white;
            transition: width 0.3s ease;
        }
        .feat:hover {
            color: palevioletred;
        }
        .feat:hover:after {
            width: 100%;
        }
        .navbar {
            position: relative;
        }
        .dropdown {
            position: relative;
        }
        .dropdown .dropbtn {
            background-color: transparent;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 18px;
            cursor: pointer;
            font-weight: 600;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 200px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }
        .dropdown:hover .dropdown-content {
            display: block;
        }
        .dropdown ```php
-content a {
            color: rgb(223, 55, 83);
            padding: 10px 15px;
            text-decoration: none;
            display: block;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .dropdown-content a:hover {
            background-color: rgb(223, 55, 83);
            color: white;
        }

        .printing-sub-dropdown, .imaging-sub-dropdown {
            display: none;
            position: absolute;
            left: 100%; /* Adjust based on layout */
            top: 0;
            background-color: white;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 2;
        }

        .dropdown-content .dropdown:hover .printing-sub-dropdown,
        .dropdown-content .dropdown:hover .imaging-sub-dropdown {
            display: block;
        }

        .printing-sub-dropdown a, .imaging-sub-dropdown a {
            padding: 10px 15px;
            color: rgb(223, 55, 83);
            text-decoration: none;
            display: block;
        }

        .printing-sub-dropdown a:hover, .imaging-sub-dropdown a:hover {
            background-color: rgb(223, 55, 83);
            color: white;
        }

        .right-sec {
            display: flex;
            align-items: center;
        }

        .gcash {
            width: 100px;
            height: 50px;
            margin-bottom: 0;
            margin-top: 5px;
        }
        .success {
        color: green; /* Green text for success */
    }

    .error {
        color: red; /* Red text for error */
    }
     .gcash-logo {
            width: 100px;
            height: 50px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="header">
        <div class="left-sec">
            <img class="logo" src="../images/logo.png" alt="logo">
            <h1>KRISHIEL <span><br>PRINTING AND IMAGING SERVICES</span></h1>
        </div>
        <div class="mid-sec">
            <a class="feat" href="user_dash.php">HOME</a>
            <div class="navbar">
                <div class="dropdown">
                    <button class="feat dropbtn">SERVICES</button>
                    <div class="dropdown-content">
                        <div class="dropdown">
                            <a href="#">Printing Services</a>
                            <div class="printing-sub-dropdown">
                                <a href="user_docu.php">Print Document</a>
                                <a href="user_flyers.php">Flyer</a>
                                <a href="user_sticker.php">Stickers</a>
                                <a href="user_invitations.php">Invitation Cards</a>
                            </div>
                        </div>
                        <div class="dropdown">
                            <a href="#">Imaging Services</a>
                            <div class="imaging-sub-dropdown">
                                <a href="user_idpic.php">ID Picture</a>
                                <a href="user_instax.php">Instax Photo</a>
                                <a href="user_sintra.php">Photo Sintra Board</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <a class="feat" href="user_aboutus.php">ABOUT US</a>
        </div>
        <div class="right-sec">
            <a href="#" onclick="viewCart()">
                <img class="logo-btn" src="../images/cart.png" alt="Cart">
            </a>
            <a href="user_myprof.php">
                <img src="<?php echo htmlspecialchars($profile_picture); ?>" 
                     class="profile-img mb-3" 
                     alt="Customer Image" 
                     style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
            </a>
        </div>
    </div>
    <div class="container">
       <!-- Products Ordered -->
<div class="section">
    <div class="section-title">Ordered</div>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Copies</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Loop through each cart item and display it
            foreach ($cart_items as $item) {
                // Subtotal is now just the price (without multiplying by copies)
                $subtotal = $item['price']; // Subtotal is just the price, not price * copies
            ?>
                <tr>
                    <td><?= htmlspecialchars($item['service_type']) ?> - <?= htmlspecialchars($item['layout_size']) ?> - <?= htmlspecialchars($item['paper_type']) ?></td>
                    <td class="price">₱<?= number_format($item['price'], 2) ?></td>
                    <td><?= $item['copies'] ?></td>
                    <td class="price">₱<?= number_format($subtotal, 2) ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Total of Subtotal -->
<div class="section">
    <div class="total-payment">
   Total Amount: ₱<?= number_format($total_subtotal, 2) ?>
    </div>
</div>

<!-- Shipping Info -->
<div class="section">
    <div class="section-title">Shipping</div>
    <div class="shipping-info">
        <span class="shipping-option">Standard Local (₱30)</span>
        <span class="price">₱30</span>
    </div>
</div>

<!-- Payment Method -->
<div class="section">
    <div class="section-title">Payment Method</div>
    <div class="shipping-info">
        <span class="shipping-option">E-wallet / <br>Gcash</span>
        <img class="gcash" src="../images/gcash.png" alt="gcash">
    </div>
</div>

<!-- Voucher Section -->
<div class="section voucher-section">
    <div class="section-title">Voucher</div>
    <form method="POST" action="">
        <input type="text" name="voucher_code" placeholder="Voucher Code" style="height: 30px; width: 200px; margin-top: 10px;">
        <button type="submit" class="price" style="padding: 6px; background-color: #d81b60; color: #fff;">Apply Code</button>
    </form>
    <?php if (!empty($coupon_message)): ?>
        <p class="<?php echo isset($coupon_message_class) ? $coupon_message_class : ''; ?>">
            <?php echo $coupon_message; ?>
        </p>
    <?php endif; ?>
</div>

<div class="section">
    <div class="total-payment">
        Shipping Cost: ₱30
    </div>
</div>

<div class="section">
    <div class="total-payment">
        Voucher Discount: - ₱<?php echo number_format($voucher_discount, 2); ?>
    </div>
</div>

<!-- Total Payment -->
<div class="section">
    <div class="total-payment">
        Total Payment: ₱<?= number_format($total_payment, 2) ?>
    </div>
</div>


<form method="POST" action="">
    <button type="submit" name="place_order" class="place-order-btn">Place Order</button>
</form>

    </div>
</body>
</html>