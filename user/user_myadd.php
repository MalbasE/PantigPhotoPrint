<?php
session_start();

// Check if logout is requested
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    if (isset($_GET['confirmed']) && $_GET['confirmed'] === 'true') {
        session_unset(); // Clear all session variables
        session_destroy(); // Destroy the session
        header("Location: user_login.php"); // Redirect to login page
        exit();
    }
}

// Ensure user_id exists in session
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kpis";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Fetch profile picture and other user details
$sql = "SELECT profile_picture FROM customers WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($profile_picture);
$stmt->fetch();

// Set default profile picture if none exists
$profile_picture = $profile_picture ?: "uploads/default.jpg"; // Default image

// Query to fetch customer name, phone number, address, and profile picture
$sql = "SELECT customer_name, phone_number, address, profile_picture FROM customers WHERE user_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

// Assign values or fallback defaults
$customer_name = $data['customer_name'] ?? "Unknown";
$phone_number = $data['phone_number'] ?? "N/A";
$address = $data['address'] ?? "Address not available";
$profilePicture = $data['profile_picture'] ?? "../iamges/user.png";

// Handle form submission for adding new address
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $street = htmlspecialchars(trim($_POST['street']));
    $city = htmlspecialchars(trim($_POST['city']));
    $barangay = htmlspecialchars(trim($_POST['barangay']));
    $zip = htmlspecialchars(trim($_POST['zip']));

    // Combine address into a single string
    $full_address = "$street, $barangay, $city, $zip";

    // Update the address in the database
    $address_sql = "UPDATE customers SET address = ? WHERE user_id = ?";
    $stmt = $conn->prepare($address_sql);

    if ($stmt) {
        $stmt->bind_param("si", $full_address, $user_id);
        if ($stmt->execute()) {
            $_SESSION['address_message'] = 'Address saved successfully!';
        } else {
            $_SESSION['address_message'] = 'Failed to save address!';
        }
    } else {
        $_SESSION['address_message'] = 'Database error: ' . $conn->error;
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Close the connection
$conn->close();
if (empty($profile_picture) || !file_exists($profile_picture)) {
    $profile_picture = "../images/user.png";
}
?>

<!-- Displaying the session message if any -->
<?php if (isset($_SESSION['address_message'])): ?>
    <script>
        alert("<?php echo $_SESSION['address_message']; ?>");
    </script>
    <?php unset($_SESSION['address_message']); ?> <!-- Clear the session message after displaying -->
<?php endif; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Address</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <style>
        *{
            cursor: default;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            
        }

        .main-container {
            display: flex;
        }

        .sidebar {
            width: 17%;
            background: linear-gradient(to right, #ff9a9e, #fecfef);
            color: white;
            padding: 10px;
            height: 100vh;
            font-family: 'Georgia', serif;
            text-shadow: 1px 1px 1px rgba(255, 255, 255, 0.384);
        }

        .sidebar h2 {
            margin-bottom: 20px;
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 10px 0;
            padding: 10px;
            transition: transform 0.3s ease;
        }

        .sidebar ul li a {
            padding: 10px;
            font-size: 20px;
            color: white;
            text-decoration: none;
        }

        .sidebar ul li a:hover {
            text-decoration: underline;
            color:  rgb(223, 55, 83);
            text-decoration: none;

        }
        li:hover{
            transform: scale(1.2);
            
        }

        .content {
            width: 80%;
            padding: 20px;
            background-color: white;
            display: flex;
            gap: 20px;
            box-sizing: border-box;
        }

        .address-section {
            width: 100%;
        }

        .address-section .header2 {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .address-section .header h3 {
            margin: 0;
        }

        .add-button {
            background-color: rgb(223, 55, 83);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
        }

        .add-button:hover {
            background-color: #e03e00;
        }

        .address-card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .address-card h4 {
            margin: 0 0 5px;
            font-size: 25px;
            font-weight: bold;
        }

        .address-card p {
            margin: 0 0 5px;
            color: #666;
            font-size: 20px;
        }

        .default-tag {
            display: inline-block;
            background-color: #ff4500;
            color: white;
            font-size: 12px;
            padding: 3px 6px;
            border-radius: 3px;
            margin-top: 5px;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .actions a {
            text-decoration: none;
            font-size: 14px;
            color: #007bff;
            margin-right: 10px;
        }

        .actions a:hover {
            text-decoration: underline;
        }

        .set-default-btn {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 5px 10px;
            font-size: 14px;
            cursor: pointer;
        }

        .set-default-btn:hover {
            background-color: #f5f5f5;
        }

        .edit-address-form {
            width: 50%;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .edit-address-form h3 {
            margin: 0 0 15px;
            color: #ff4500;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="date"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .save-btn {
            background-color: #ff4500;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .save-btn:hover {
            background-color: #e63e00;
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
    .content {
            padding: 20px;
        }

        .address-section {
            margin-bottom: 20px;
        }

        .header2 {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .add-button {
            background-color: rgb(223, 55, 83);
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }

        .add-button:hover {
            background-color: palevioletred;
        }

        .address-card {

            border: 1px solid #ccc;
            padding: 50px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .default-tag {
            background-color: (223, 55, 83);
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 15px;
        }

        .actions {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .actions a {
            margin-right: 10px;
            color: #007bff;
            text-decoration: none;
        }

        .actions a:hover {
            text-decoration: underline;
        }

        .set-default-btn {
            background-color: #ffc107;
            border: none;
            color: #fff;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 50px;
            border-radius: 5px;
            width: 110%;
            max-width: 300px;
            text-align: center;
        }

        .modal-header {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .modal input {
            width: 80%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .modal button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            margin-top: 10px;
            cursor: pointer;
            border-radius: 5px;
        }

        .modal button.close {
            background-color: #dc3545;
        }

        .modal button:hover {
            opacity: 0.9;
        }
        .logout-modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    background-color: rgba(0, 0, 0, 0.4); /* Black with transparency */
    overflow: auto; /* Enable scroll if needed */
    padding-top: 60px; /* Location of the modal */
}

.logout-modal-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 300px; /* Modal width */
    text-align: center;
    border-radius: 8px;
}

.close-btn {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close-btn:hover,
.close-btn:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

.modal-actions a {
    background-color: #4CAF50; /* Green */
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    margin: 10px;
    display: inline-block;
}

.modal-actions a:hover {
    background-color: #45a049;
}

.modal-actions a:last-child {
    background-color: #f44336; /* Red */
}

.modal-actions a:last-child:hover {
    background-color: #e53935;
}
.myadd{
    color: rgb(223, 55, 83);
    margin-left: 18px;
    font-size: 28px;
    margin-top: 0px;


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
          <a href="user_myprof.php">
          <img src="<?php echo htmlspecialchars($profile_picture); ?>" 
     class="profile-img mb-3" 
     alt="Customer Image" 
     style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">

          </a>
      </div>
    </div>
    <div class="main-container">
        <div class="sidebar">
            <h2>My Account</h2>
            <ul>
                <li><a href="user_myprof.php">Profile</a></li>
                <li><a href="user_myadd.php">Address</a></li>
                <li><a href="user_changepass.php">Change Password</a></li>
                <li><a href="user_mypur.php">My Purchase</a></li>
                <!-- Update the logout link to point to the current file with the logout query -->
                <li><a href= "javascript:void(0);" onclick="showLogoutModal()">Logout</a></li>
            </ul>
        </div>



        <div class="content">
    <div class="address-section">
        <div class="header2">
            <h2 class="myadd">My Address</h2>
            <button class="add-button" id="addAddressBtn">+ Add Address</button>
        </div>

        <!-- Display address cards here -->
        <div class="address-card">
            <h4>
                <?php echo htmlspecialchars($customer_name); ?> | (+<?php echo htmlspecialchars($phone_number); ?>)
            </h4>
            <p>
        <?php echo htmlspecialchars($address); ?> <!-- Display the address fetched from the database -->
    </p>
    <span class="default-tag">Default</span>
        </div>
    </div>

    <!-- Modal for adding new address -->
    <div class="modal" id="addressModal">
        <div class="modal-content">
            <div class="modal-header">Add New Address</div>
            <form id="addressForm" action="user_myadd.php" method="POST">
                <input type="text" name="street" placeholder="Street" required>
                <input type="text" name="city" placeholder="City" required>
                <input type="text" name="barangay" placeholder="Barangay" required>
                <input type="text" name="zip" placeholder="ZIP Code" required>
                <button type="submit">Save Address</button>
                <button type="button" class="close" id="closeModalBtn">Cancel</button>
            </form>
        </div>
    </div>
  <!-- Logout Modal -->
  <div id="logoutModal" class="logout-modal">
    <div class="logout-modal-content">
        <span class="close-btn" onclick="hideLogoutModal()">&times;</span>
        <h2>Are you sure you want to log out?</h2>
        <div class="modal-actions">
            <a href="javascript:void(0);" onclick="confirmLogout()">Yes, Logout</a>
            <a href="javascript:void(0);" onclick="hideLogoutModal()">Cancel</a>
        </div>
    </div>
</div>
    <script>
        // Get modal and buttons
        const modal = document.getElementById('addressModal');
        const addAddressBtn = document.getElementById('addAddressBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');

        // Open modal
        addAddressBtn.addEventListener('click', () => {
            modal.style.display = 'flex';
        });

        // Close modal
        closeModalBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        // Close modal when clicking outside of it
        window.addEventListener('click', (event) => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    </script>
    <script>
function showLogoutModal() {
    document.getElementById("logoutModal").style.display = "block";
}

function hideLogoutModal() {
    document.getElementById("logoutModal").style.display = "none";
}

function confirmLogout() {
    // Redirect to the logout URL with confirmation
    window.location.href = window.location.href.split('?')[0] + "?logout=true&confirmed=true";
}
</script>
</div>

</body>
</html>
