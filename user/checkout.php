<?php
// checkout.php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'kpis';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Fetch cart items
$sql = "SELECT * FROM cart_tbl"; // Ensure all relevant columns are selected
$stmt = $pdo->prepare($sql);
$stmt->execute();
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if there are cart items
if (!empty($cart_items)) {
    // Begin transaction
    $pdo->beginTransaction();

    try {
        foreach ($cart_items as $item) {
            // Prepare the insert query for `order_tbl`
            $sql = "INSERT INTO order_tbl (
                        customer_name, service_type, picture_name, picture_path, status, 
                        order_date, quantity, price, type, fileUpload, layout_size, 
                        paper_type, copies, notes, print_type, file_upload, thickness, 
                        photo_size, order_notes, user_id, id_type
                    ) VALUES (
                        :customer_name, :service_type, :picture_name, :picture_path, :status, 
                        :order_date, :quantity, :price, :type, :fileUpload, :layout_size, 
                        :paper_type, :copies, :notes, :print_type, :file_upload, :thickness, 
                        :photo_size, :order_notes, :user_id, :id_type
                    )";

            $stmt = $pdo->prepare($sql);

            // Bind parameters from `cart_tbl` to `order_tbl`
            $stmt->bindValue(':customer_name', $item['name']); // Customer name from cart_tbl
            $stmt->bindValue(':service_type', $item['service_type'] ?? 'Unknown'); // Default value for service_type
            $stmt->bindValue(':picture_name', !empty($item['fileUpload']) ? $item['fileUpload'] : 'default_image.jpg'); // Fallback to default image if empty
            $stmt->bindValue(':picture_path', !empty($item['fileUpload']) ? $item['fileUpload'] : 'default_picture_path'); // Fallback to default picture path if empty
            $stmt->bindValue(':status', 'Pending');  // Default status
            $stmt->bindValue(':order_date', date('Y-m-d H:i:s'));  // Current date and time
            $stmt->bindValue(':quantity', $item['quantity'], PDO::PARAM_INT);
            $stmt->bindValue(':price', $item['price']);
            $stmt->bindValue(':type', $item['type'] ?? null);
            $stmt->bindValue(':fileUpload', $item['fileUpload'] ?? null);
            $stmt->bindValue(':layout_size', $item['layout_size'] ?? null);
            $stmt->bindValue(':paper_type', $item['paper_type'] ?? null);
            $stmt->bindValue(':copies', $item['copies'], PDO::PARAM_INT);
            $stmt->bindValue(':notes', $item['notes'] ?? null);
            $stmt->bindValue(':print_type', $item['print_type'] ?? null);
            $stmt->bindValue(':file_upload', $item['file_upload'] ?? null);
            $stmt->bindValue(':thickness', $item['thickness'] ?? null);
            $stmt->bindValue(':photo_size', $item['photo_size'] ?? null);
            $stmt->bindValue(':order_notes', $item['order_notes'] ?? null);
            $stmt->bindValue(':user_id', $item['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':id_type', $item['id_type'] ?? null);

            // Execute the query
            $stmt->execute();
        }

        // Clear the cart
        $pdo->exec("DELETE FROM cart_tbl");

        // Commit transaction
        $pdo->commit();

        echo json_encode(['status' => 'success', 'message' => 'Checkout successful!']);
    } catch (PDOException $e) {
        // Rollback transaction in case of error
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Error during checkout: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Your cart is empty!']);
}
