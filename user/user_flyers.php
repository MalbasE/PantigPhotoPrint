<?php
session_start(); // Start the session

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get the user ID from session

// Database connection
$host = 'localhost';
$dbname = 'kpis';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "<script>alert('Database connection failed: " . $e->getMessage() . "');</script>";
    exit();
}

// Fetch the customer's name
$sql = "SELECT customer_name FROM customers WHERE user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$customer_name = $stmt->fetchColumn();

// Fetch the profile picture for the user
$sql = "SELECT profile_picture FROM customers WHERE user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$profile_picture = $stmt->fetchColumn();

// Set default profile picture if none exists or the file doesn't exist
if (empty($profile_picture) || !file_exists($profile_picture)) {
    $profile_picture = "../images/user.png"; // Default profile picture path
}
// Fetch cart items for the logged-in user
$sql = "SELECT order_id,service_type ,layout_size, price, paper_type,thickness, copies, notes, print_type, file_upload, created_at 
        FROM cart_tbl 
        WHERE user_id = :user_id
        ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Handle adding items to the cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['layout'], $_POST['paper-type'], $_POST['copies'], $_POST['printType'], $_POST['price'])) {
    // Capture service type from the dropdown or radio button selection
    // Let's assume you have a 'service' field in your form that captures whether the user is selecting a printing or imaging service
    $service_type = isset($_POST['service_type']) ? $_POST['service_type'] : 'Printing'; // Default to 'Printing' if no service is selected

    // Capture other form data
    $layout_size = $_POST['layout'];
    $paper_type = $_POST['paper-type'];
    $copies = intval($_POST['copies']);
    $notes = $_POST['notes'] ?? '';
    $print_type = $_POST['printType'];
    $price = floatval($_POST['price']);

    $file_path = '';
    if (isset($_FILES['fileUpload']) && $_FILES['fileUpload']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES['fileUpload']['name']);
        if (move_uploaded_file($_FILES['fileUpload']['tmp_name'], $target_file)) {
            $file_path = $target_file;
        } else {
            echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
        }
    }

    // Insert item into cart table including service_type
    $sql = "INSERT INTO cart_tbl (user_id, name, layout_size, paper_type, copies, notes, file_upload, print_type, price, service_type) 
            VALUES (:user_id, :customer_name, :layout_size, :paper_type, :copies, :notes, :file_path, :print_type, :price, :service_type)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':customer_name', $customer_name);
    $stmt->bindParam(':layout_size', $layout_size);
    $stmt->bindParam(':paper_type', $paper_type);
    $stmt->bindParam(':copies', $copies, PDO::PARAM_INT);
    $stmt->bindParam(':notes', $notes);
    $stmt->bindParam(':file_path', $file_path);
    $stmt->bindParam(':print_type', $print_type);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':service_type', $service_type);  // Bind the service_type parameter

    if ($stmt->execute()) {
        header("Location: user_flyers.php?message=added");
        exit();
    } else {
        echo "<script>alert('Error: There was an issue adding the item to the cart.');</script>";
    }
}

// Checkout logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $pdo->beginTransaction();
    try {
        // Loop through the cart items and insert them into orders_tbl
        foreach ($cart_items as $item) {
            // Add service_type to the SQL query
            $sql = "INSERT INTO orders_tbl (user_id, customer_name, layout_size, paper_type, copies, notes, file_upload, print_type, service_type) 
                    VALUES (:user_id, :customer_name, :layout_size, :paper_type, :copies, :notes, :file_upload, :print_type, :service_type)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':customer_name', $customer_name);
            $stmt->bindParam(':layout_size', $item['layout_size']);
            $stmt->bindParam(':paper_type', $item['paper_type']);
            $stmt->bindParam(':copies', $item['copies'], PDO::PARAM_INT);
            $stmt->bindParam(':notes', $item['notes']);
            $stmt->bindParam(':file_upload', $item['file_upload']);
            $stmt->bindParam(':print_type', $item['print_type']);
            $stmt->bindParam(':service_type', $item['service_type']); // Add service_type to the insert
            $stmt->execute();
        }

        // Delete items from cart_tbl after processing
        $sql = "DELETE FROM cart_tbl WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        // Commit transaction
        $pdo->commit();

        // Redirect to success page
        header("Location: user_flyers.php?message=checkout-success");
        exit();
    } catch (PDOException $e) {
        // Rollback transaction in case of error
        $pdo->rollBack();
        echo "<script>alert('Error during checkout: " . $e->getMessage() . "');</script>";
    }
}

// Handle item removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $itemId = intval($_POST['order_id']);

    $sql = "DELETE FROM cart_tbl WHERE order_id = :order_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':order_id', $itemId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
    }
    exit();
}
if (empty($profile_picture) || !file_exists($profile_picture)) {
    $profile_picture = "../images/user.png";
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/logo.png" type="image/png">
    <title>Flyers</title>
    <style>
   body {
        background: linear-gradient(to right, #ffb3c6, #f0e68c);
        font-family: Arial, Helvetica, sans-serif;
        margin: 0;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 80px;
       
        top: 0;
        left: 0;
        right: 0;
        background-color: transparent;
        border-bottom: 2px solid rgb(223, 55, 83);
        z-index: 1000;
        padding: 0 20px;
    }

    .left-sec {
        display: flex;
        align-items: center;
    }

    h1 {
        font-size: 18px;
        color: white;
        margin-left: 10px;
        line-height: 1.2;
    }

    .logo {
        height: 50px;
        width: 50px;
    }

    span {
        font-size: 14px;
        color: rgb(223, 55, 83);
    }

    .right-sec {
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

   .mid-sec {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
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
    font-size: 16px;
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

.dropdown-content a {
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
    left: 100%;
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

    input {
        flex: 1;
        height: 40px;
        outline: none;
        padding: 15px;
        padding-left: 30px;
        font-size: 18px;
        position: relative;
    }

    .search {
        position: absolute;
        right: 400px;
        height: 25px;
    }

    .header3 {
        height: 50px;
        background-color: white;
        left: 0;
        right: 60%;
        position: fixed;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-right: 20px;
        padding-left: 20px;
    }

    li {
        list-style: none;
        transition: transform 0.3s ease;
        padding: 15px;
    }

    .nav-bar {
        text-decoration: none;
        color: rgb(223, 55, 83);
        font-size: 18px;
        font-weight: 600;
    }

    .nav-bar:hover {
        color: white;
    }

    * Add to Cart Button */
        #addToCartBtn {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }

        #addToCartBtn:hover {
            background-color: #218838;
        }

        /* Cart Icon and Basket Styles */
        #cartIcon {
            font-size: 24px;
            cursor: pointer;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border-radius: 50%;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
        }

        #cartBasket {
    display: none; /* Hidden by default */
    position: fixed;
    top: 0;
    right: 0;
    width: 400px;
    background-color: #fff;
    border-left: 2px solid #ccc;
    padding: 20px;
    box-shadow: -2px 0 8px rgba(0, 0, 0, 0.2);
    height: 100vh;
    z-index: 101;
    display: flex;
    flex-direction: column;
}

#cartBasket h3 {
    text-align: center;
}

#cartContent {
    flex-grow: 1; /* Makes the content area take up remaining space */
    overflow-y: auto; /* Enables scrolling for the items */
    max-height: calc(100vh - 140px); /* Ensures there's enough space for buttons */
    margin-bottom: 20px; /* Adds space below the items for the buttons */
}

#cartItemsList {
    list-style-type: none;
    padding: 0;
    margin: 0;
}

#cartItemsList li {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
}

#cartItemsList li button {
    background-color: #e74c3c;
    color: white;
    border: none;
    padding: 5px 10px;
    cursor: pointer;
    border-radius: 5px;
}



/* Clear Cart Button */
#clearCartBtn {
    background-color: #f39c12;
    color: white;
    padding: 10px;
    border: none;
    border-radius: 5px;
    width: 100%;
    cursor: pointer;
    margin-top: 10px;
}



.close-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: transparent;
    border: none;
    font-size: 20px;
    font-weight: bold;
    cursor: pointer;
    color: #000;
}

.close-btn:hover {
    color: red; /* Change the color when hovered */
}

.cart-buttons {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.cart-buttons button {
    width: 100%;
}

/* Container for centering the button */
.product-container {
    display: flex;
    justify-content: center;
    align-items: center;
}


/* Styling for the "Add to Cart" button */
.add-to-cart-btn {
    padding: 10px 20px;
    background-color: #4CAF50;  /* Green background */
    color: white;
    font-size: 16px;
    font-weight: bold;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

/* Hover effect */
.add-to-cart-btn:hover {
    background-color: #45a049;
    transform: scale(1.1);  /* Slightly enlarges the button when hovered */
}

/* Focus effect */
.add-to-cart-btn:focus {
    outline: none;
    box-shadow: 0 0 10px rgba(0, 128, 0, 0.6);  /* Adds a glowing effect when the button is focused */
}

.remove-btn,
.edit-btn {
    padding: 5px 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.remove-btn {
    background-color: red;
    color: white;
}

.edit-btn {
    background-color: green;
    color: white;
    margin-left: 10px;
}

.remove-btn:hover {
    background-color: darkred;
}

.edit-btn:hover {
    background-color: darkgreen;
}

/* Cart Buttons */
.cart-buttons {
    display: flex;
    justify-content: space-between;
}

.cart-buttons button {
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    border: none;
}

.cart-buttons button:first-child {
    background-color: #f44336;
    color: white;
}

.cart-buttons button:first-child:hover {
    background-color: #d32f2f;
}

.cart-buttons button:last-child {
    background-color: #228B22;
    color: white;
}

.cart-buttons button:last-child:hover {
    background-color: #2a9d2b;
}

/* Coupon Section */
#couponSection {
    margin-bottom: 15px;
}

#coupon {
    padding: 8px;
    width: 100%;
    margin-top: 5px;
    border-radius: 4px;
    border: 1px solid #ddd;
}

/* Buttons */
button {
    padding: 10px 20px;
    font-size: 16px;
    cursor: pointer;
    border: none;
    border-radius: 4px;
}

button:hover {
    opacity: 0.9;
}

.close-btn {
    background-color: transparent;
    color: black;
    font-size: 20px;
    border: none;
    cursor: pointer;
    padding: 0;
    position: absolute;
    top: 10px;
    right: 10px;
}

/* Responsive Design */
@media screen and (max-width: 768px) {
    .service-form {
        width: 100%;
        padding: 10px;
    }

    #cartBasket {
        width: 90%;
        padding: 15px;
    }

    .add-to-cart-btn,
    .remove-btn,
    .edit-btn,
    .cart-buttons button {
        width: 100%;
        font-size: 14px;
        padding: 2px;
        background:green;
    }
}

#couponSection {
    position: fixed; /* Fixes the coupon field at the bottom, above the buttons */
    bottom: 80px; /* Position the coupon field above the buttons */
    right: 0;
    background-color: white;
    padding: 15px;
    border-radius: 5px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    width: 640px;
    margin-bottom:60px; /* Adds space between coupon and buttons */
}

.cart-buttons {
    position: fixed; /* Fixes the buttons at the bottom */
    bottom: 2px;
    display: flex;
    flex-direction: column; /* Stacks buttons vertically */
    background-color: white;
    padding: 10px;
    right:0;
    border-radius: 5px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    width: 420px; /* Ensures the buttons are the same width as the coupon field */
}

.cart-buttons button {
    margin: 10px 0; /* Adds space between the buttons */
}





#cartCount {
    position: absolute;
    top: 5px;
    right: 85px;
    background-color: red;  /* Color for the cart count badge */
    color: white;  /* Text color for the count */
    font-size: 12px;  /* Font size for the count */
    font-weight: bold;
    padding: 3px;
    border-radius: 50%;  /* Makes the badge round */
    min-width: 10px;
    text-align: center;
}

/* If the cart count is 0 or empty, you can hide the badge */
#cartCount.hidden {
    display: none;
}
.right-sec {
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
.col {
    display: flex;
    justify-content: center; /* Center items horizontally */
    align-items: center; /* Center items vertically */
    gap: 100px;
}

.idpic {
    width: 370px; /* Adjust the size of the image */
    height: 400px; /* Maintain aspect ratio */
    margin-left: 300px;
    margin-top: 100px;
    border-radius: 20px;

}

.id_type {
    flex-grow: 5; /* Allow the label and select box to take remaining space */
}
.label{
    margin-bottom: 30px;
    color: rgb(223, 55, 83);
    font-size: 20px;
    padding: 10px;
}
.option{
    border-color:  rgb(223, 55, 83);
    color: rgb(223, 55, 83);
    font-size: 20px;
    padding: 8px;
}

.label label {
    margin-right: 10px; /* Adds space between the label and the select box */
}
input{
    font-size: 20px;
    border-color:  rgb(223, 55, 83);
}
h2{
    font-size: 30px;
    color: rgb(223, 55, 83);
    margin-left: 50px;
    margin-bottom: 0;
    margin-top: 30px;
}
button{
    background-color:  rgb(223, 55, 83);
    border-color:  rgb(223, 55, 83);
    color: rgb(248, 248, 248);
    font-size: 15px;
    padding: 8px;
}
.submit-btn {
    background-color:rgb(223, 55, 83);
    color: white;
    padding: 15px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    margin-left: 350px;
}
.notes{
    height: 80px;
    width: 330px;
    border-color: rgb(223, 55, 83);
}


.radio-buttons {
    position: flex; /* Position absolute within the parent container */
    margin-left: 270px;
    top: 50%; /* Center vertically */
    transform: translateY(-50%); /* Adjust for perfect vertical centering */
}
.message {
    color: green;
    font-weight: bold;
    margin-bottom: 20px;
    position: fixed; /* Position the message fixed on the screen */
    top: 20px; /* Adjust to position the message from the top */
    left: 50%; /* Center it horizontally */
    transform: translateX(-50%); /* Adjust the message to center it properly */
    background-color: rgba(0, 0, 0, 0.5); /* Optional: Add a background with transparency */
    color: white; /* White text on the background */
    padding: 10px 20px;
    border-radius: 5px;
    z-index: 9999; /* Ensure the message is on top of other elements */
}      


    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header">
        <div class="left-sec">
            <img class="logo" src="../images/logo.png" alt="Logo">
            <h1>KRISHIEL <span><br>PRINTING AND IMAGING SERVICES</span></h1>
        </div>
        <div class="mid-sec">
            <a class="feat" href="user_dash.php">HOME</a>
            <div class="navbar">
                <div class="dropdown">
                <input type="hidden" name="service" id="service">
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
                <span id="cartCount">(<?php echo count($cart_items); ?>)</span>
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

    <!-- Flyers Section -->
    <form action="user_flyers.php" method="POST" enctype="multipart/form-data">
        <h2>Flyers</h2>
        
    <!-- Success Message -->
        <?php
    if (isset($_GET['message'])) {
        $message = '';
        if ($_GET['message'] == 'added') {
            $message = "Item added to the basket successfully!";
        } elseif ($_GET['message'] == 'checkout-success') {
            $message = "Checkout successful! Your cart has been processed.";
        }
        if ($message) {
            echo "<p class='message' id='success-message'>$message</p>";
        }
    }
    ?>
   <div class="col">
        <img src="../images/fly.png" alt="Flyer" class="idpic">
        <div class="id_type">
            <div class="label">
                <label for="layout">Layout Size:</label>
                <select class="option" name="layout" id="layout" required>
                    <option selected value="DL">DL (99mm x 210mm / 3.9in x 8.3in)</option>
                    <option value="Square">Square (210mm x 210mm / 8.27in x 8.27in)</option>
                    <option value="Half Page">Half Page (5.5in x 8.5in / 140mm x 216mm)</option>
                    <option value="Tabloid">Tabloid (11in x 17in / 279mm x 432mm)</option>
                </select>
            </div>
            <div class="label">
                <label for="paper-type">Paper Type:</label>
                <select class="option" name="paper-type" id="paper-type" required>
                    <option selected value="Glossy">Glossy Paper</option>
                    <option value="Matte">Matte Paper</option>
                    <option value="Textured">Textured Paper</option>
                    <option value="Recycled">Recycled Paper</option>
                    <option value="Uncoated">Uncoated Paper</option>
                    <option value="Cardstock">Cardstock (Heavyweight Paper)</option>
                    <option value="Satin">Satin Paper</option>
                    <option value="Coated">Coated Paper</option>
                </select>
            </div>
            <div class="label">
                <label for="copies">Copies:</label>
                <input type="number" id="copies" name="copies" min="1" required >
            </div>
            <div class="label">
                <label for="notes">Notes:</label>
                <input type="text" id="notes" class="notes" name="notes">
            </div>
            <div class="radio-buttons">
                    <label>
                        <input type="radio" name="printType" value="bw" required> B/W
                    </label>
                    <label>
                        <input type="radio" name="printType" value="colored"> Colored
                    </label>
                </div>
              <!-- Price Display -->
              <div class="label">
                <label for="price">Price: </label>
                <span id="price">₱0.00</span>
                <input type="hidden" id="calculated-price" name="price" value="0">
                </div>
                
            <div class="label">
                <button type="button" onclick="document.getElementById('fileUpload').click()">Choose File</button>
                <input type="file" id="fileUpload" name="fileUpload" style="display: none;" required>
            
                <button type="submit" class="submit-btn">Add to Basket</button>
            </div>
            
          

           
        </div>
    </div>
</form>

    <!-- Cart Basket Popup -->
   <div id="cartBasket" style="display:none;">
    <h3>Your Cart</h3>
    <button id="closeCartBasket" class="close-btn">x</button>
    <div id="cartContent" class="cart-content">
        <ul id="cartItemsList">
            <?php if (!empty($cart_items)): ?>
                <?php foreach ($cart_items as $item): ?>
                    <li id="cart-item-<?php echo $item['order_id']; ?>" style="display: flex; align-items: center; margin-bottom: 10px;">
                        <!-- Image on the left -->
                        <?php if (isset($item['file_upload']) && is_file($item['file_upload'])): ?>
                            <div style="flex-shrink: 0; margin-right: 10px;">
                                <img src="<?php echo htmlspecialchars($item['file_upload']); ?>" alt="Uploaded File" style="max-width: 150px; max-height: 150px;"/>
                            </div>
                        <?php endif; ?>

                        <!-- Product information on the right -->
                        <div style="flex-grow: 1;">
                            <p><?php echo htmlspecialchars($item['layout_size']); ?></p>
                            <p><?php echo htmlspecialchars($item['paper_type']); ?></p>
                            <p><?php echo htmlspecialchars($item['thickness']); ?></p>
                            <p><?php echo htmlspecialchars($item['print_type']); ?></p>
                            <p><?php echo htmlspecialchars($item['copies']); ?></p>

                            <p><strong>Price:</strong> ₱<?php echo number_format($item['price'], 2); ?></p>

                        </div>
                        <button onclick="removeItem(<?php echo $item['order_id']; ?>)" class="remove-btn" style="margin-left: 10px;">x</button>

                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>Your cart is empty.</li>
            <?php endif; ?>
        </ul>

        <div class="cart-buttons" style="margin-top: 20px;">
        <a href="user_cartinfo.php" class="cart-btn">
    <button id="checkoutBtn" class="cart-btn">Checkout</button>
      </a>
        </div>
    </div>
</div>
<script>
      document.addEventListener('DOMContentLoaded', function() {
        const serviceButtons = document.querySelectorAll('.dropdown-content a'); // Get all the service links
        const serviceField = document.getElementById('service'); // Hidden input field

        serviceButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Capture the service name from the clicked link text
                const serviceText = this.textContent;

                // Set the hidden field value
                if (serviceText.includes("Printing")) {
                    serviceField.value = "Printing";
                } else if (serviceText.includes("Imaging")) {
                    serviceField.value = "Imaging";
                }
            });
        });
    });
    // Handle cart popup and interactions
    function viewCart() {
        const cartBasket = document.getElementById("cartBasket");
        cartBasket.style.display = "block";
    }

    document.getElementById("closeCartBasket").onclick = function() {
        const cartBasket = document.getElementById("cartBasket");
        cartBasket.style.display = "none";
    };

    function clearCart() {
        alert("Cart cleared!");
    }

    // Checkout logic using AJAX
    function checkout() {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "checkout.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.status === 'success') {
                    alert(response.message);  
                    document.getElementById('cartBasket').style.display = 'none';
                    document.getElementById('cartCount').textContent = '0';
                } else {
                    alert(response.message);
                }
            }
        };

        xhr.send();
    }
</script>
<script>
        // Make the success message disappear after 5 seconds
        document.addEventListener('DOMContentLoaded', () => {
            const messageElement = document.getElementById('success-message');
            if (messageElement) {
                setTimeout(() => {
                    messageElement.style.display = 'none';
                }, 1000); // 5000ms = 5 seconds
            }
        });
     // Function to calculate price based on the selections
function calculatePrice() {
    const layout = document.getElementById('layout').value;
    const paperType = document.getElementById('paper-type').value;
    const copies = document.getElementById('copies').value;
    const printType = document.querySelector('input[name="printType"]:checked')?.value;

    let price = 0;

    // Base price per layout size
    const layoutPrices = {
        'DL': 10, // example price
        'Square': 12,
        'Half Page': 15,
        'Tabloid': 20
    };

    // Price adjustments based on paper type
    const paperTypePrices = {
        'Glossy': 2,
        'Matte': 1.5,
        'Textured': 2.5,
        'Recycled': 2,
        'Uncoated': 1.2,
        'Cardstock': 3,
        'Satin': 2.8,
        'Coated': 2
    };

    // Price adjustment based on print type (black and white vs colored)
    const printTypeMultiplier = printType === 'colored' ? 1.5 : 1;

    // Calculate total price
    price += (layoutPrices[layout] || 0) * printTypeMultiplier; // layout price adjusted for print type
    price += (paperTypePrices[paperType] || 0); // paper type price
    price *= copies; // multiply by the number of copies

    // Display the calculated price
    document.getElementById('price').textContent = `₱${price.toFixed(2)}`;

    // Set the calculated price in the hidden input field for form submission
    document.getElementById('calculated-price').value = price.toFixed(2);
}

// Event listeners to trigger price calculation when the user changes options
document.getElementById('layout').addEventListener('change', calculatePrice);
document.getElementById('paper-type').addEventListener('change', calculatePrice);
document.getElementById('copies').addEventListener('input', calculatePrice);
document.querySelectorAll('input[name="printType"]').forEach(radio => {
    radio.addEventListener('change', calculatePrice);
});

// Initialize price calculation on page load
window.onload = calculatePrice;


    function showCartOnScroll() {
    const cart = document.getElementById('cartBasket');
    const scrollHeight = document.documentElement.scrollHeight; // Total height of the document
    const scrollPosition = window.innerHeight + window.scrollY; // Current scroll position + window height

    // If the user is at the bottom of the page, show the cart
    if (scrollHeight - scrollPosition <= 1) {
        cart.classList.add('show'); // Show the cart
    }
}

// Event listener for scrolling
window.addEventListener('scroll', showCartOnScroll);

// Optional: Close cart when the close button is clicked
document.getElementById('closeCartBasket').addEventListener('click', function() {
    document.getElementById('cartBasket').classList.remove('show');
});
function removeItem(itemId) {
    if (confirm('Are you sure you want to remove this item from the cart?')) {
        // Send AJAX request to remove the item
        fetch('user_flyers.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ order_id: itemId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the item from the UI
                const itemElement = document.getElementById(`cart-item-${itemId}`);
                if (itemElement) {
                    itemElement.remove();
                }
                alert('Item removed successfully');
            } else {
                alert(data.message || 'Failed to remove item');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while removing the item');
        });
    }
}

    </script>
</body>

</html>
