<?php
// Retrieve transaction details from the URL
$transaction_id = $_GET['transaction_id'];
$amount_paid = $_GET['amount_paid'];

// Simulate payment confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "kpis";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Update the payment status in payment_tbl
    $sql = "UPDATE payment_tbl SET payment_status = 'Completed' WHERE transaction_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $transaction_id);

    if ($stmt->execute()) {
        echo "<h2>Payment Successful!</h2>";
        echo "<p>Your payment has been confirmed.</p>";
        echo "<p>Transaction ID: " . htmlspecialchars($transaction_id) . "</p>";
        echo "<p>Amount Paid: PHP " . htmlspecialchars(number_format($amount_paid, 2)) . "</p>";
    } else {
        echo "Error updating payment status: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mock GCash</title>
</head>
<body>
    <h2>GCash Payment Confirmation</h2>
    <p>Transaction ID: <?= htmlspecialchars($transaction_id) ?></p>
    <p>Amount to Pay: PHP <?= htmlspecialchars(number_format($amount_paid, 2)) ?></p>
    <form method="POST">
        <button type="submit">Confirm Payment</button>
    </form>
</body>
</html>
