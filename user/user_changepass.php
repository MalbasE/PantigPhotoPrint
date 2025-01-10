<?php
session_start();

// Handle logout request
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    if (isset($_GET['confirmed']) && $_GET['confirmed'] === 'true') {
        session_unset(); // Clear all session variables
        session_destroy(); // Destroy the session
        header("Location: user_login.php"); // Redirect to login page
        exit();
    }
}

// Check if user_id is available in the session
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Database credentials
$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = "";     // Replace with your MySQL password
$dbname = "kpis";   // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize messages
$successMessage = "";
$errorMessage = "";
$newpassinvalidMessage = "";
$currentnotmatchMessage = "";

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

// Handle form submission for password change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = trim($_POST['current-password']);
    $new_password = trim($_POST['new-password']);
    $confirm_password = trim($_POST['confirm-password']);

    // Fetch the current password from the database (no password_verify now)
    $password_sql = "SELECT password FROM customers WHERE user_id = ?";
    $password_stmt = $conn->prepare($password_sql);
    $password_stmt->bind_param('i', $user_id);
    $password_stmt->execute();
    $password_stmt->store_result();
    $password_stmt->bind_result($db_password);
    $password_stmt->fetch();

    // Verify the current password directly (without hashing)
    if ($current_password === $db_password) {
        // Check if the new password matches the confirmation
        if ($new_password === $confirm_password) {
            // Update the password in the database without hashing
            $update_sql = "UPDATE customers SET password = ? WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param('si', $new_password, $user_id);

            if ($update_stmt->execute()) {
                $_SESSION['successMessage'] = "Password updated successfully!";
            } else {
                $_SESSION['errorMessage'] = "Error updating password!";
            }
        } else {
            $_SESSION['newpassinvalidMessage'] = "New passwords do not match!";
        }
    } else {
        $_SESSION['currentnotmatchMessage'] = "Current password is incorrect!";
    }

    // Redirect to the same page to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Display messages from the session
if (isset($_SESSION['successMessage'])) {
    $successMessage = $_SESSION['successMessage'];
    unset($_SESSION['successMessage']);
}

if (isset($_SESSION['errorMessage'])) {
    $errorMessage = $_SESSION['errorMessage'];
    unset($_SESSION['errorMessage']);
}

if (isset($_SESSION['newpassinvalidMessage'])) {
    $newpassinvalidMessage = $_SESSION['newpassinvalidMessage'];
    unset($_SESSION['newpassinvalidMessage']);
}

if (isset($_SESSION['currentnotmatchMessage'])) {
    $currentnotmatchMessage = $_SESSION['currentnotmatchMessage'];
    unset($_SESSION['currentnotmatchMessage']);
}

// Close the database connection
$conn->close();

if (empty($profile_picture) || !file_exists($profile_picture)) {
    $profile_picture = "../images/user.png";
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <style>
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
            width: 20%;
            background: linear-gradient(to right, #ff9a9e, #fecfef);
            color: white;
            padding: 10px;
            font-family: 'Georgia', serif;
            text-shadow: 1px 1px 1px rgba(255, 255, 255, 0.384);
            height: 100vh;
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
            color: rgb(223, 55, 83);
            text-decoration: none;

        }
        li:hover{
            transform: scale(1.2);
            
        }

        .content {
            width: 100%;
            padding: 20px;
            background-color: white;
            display: flex;
            gap: 20px;
            box-sizing: border-box;
        }

        .change-password-section {
            font-size: 15px;
            width: 100%;
            background-color: white;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 0;
        }

        .change-password-section h3 {
            font-size: 27px;
            margin: 0 0 15px;
            color: rgb(223, 55, 83);
            margin-top: -13px;

        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .form-group input[type="password"] {
            width: 30%;
            padding: 10px;
            margin-bottom: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 18px;
        }

        .save-btn {
            font-size: 18px;
            background-color:rgb(223, 55, 83);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 50px;
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
    color: rgb(241, 56, 87);
    padding: 5px 10px;
    font-family: 'Georgia', serif;
    text-shadow: 1px 1px 1px rgba(255, 255, 255, 0.384);
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
        color:black;
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
        color: rgb(241, 56, 87);
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
         font-family: 'Georgia', serif;
       text-shadow: 1px 1px 1px rgba(255, 255, 255, 0.384);
        display: block;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .dropdown-content a:hover {
        background-color: rgb(223, 55, 83);
        color:black;
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
     /* Styles for the page */
        /* Styles for the modal */
        .message-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 20px;
            border-radius: 10px;
            z-index: 9999;
            text-align: center;
            width: 15%;
        }
        .message-modal.success {
            background-color: #28a745;
        }
        .message-modal.error {
            background-color: #dc3545;
        }
        .message-modal.show {
            display: block;
        }
        .password-container {
        position: relative;
    }

    .eye-icon {
        margin-left: -40px;
        top: 50%;
        right: 10px;
        cursor: pointer;
        transform: translateY(-50%);
        font-size: 18px;
    }

    input[type="password"], input[type="text"] {
        padding-right: 30px; /* Ensure there's space for the eye icon */
    }
    /* Prevent the input field from adjusting size */
    input[type="password"], input[type="text"] {
        width: 236px; /* Ensure the input takes full width */
        padding: 10px; /* Space for the eye icon, but doesn't affect the input's width */
        border-radius: 5px;
        border-color: gray;
        height: 20px;
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
.changepass{
    margin-left: 400px;
    margin-top: 50px;
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
        <!-- Sidebar Content Here -->
        <div class="sidebar">
            <h2>My Account</h2>
            <ul>
                <li><a href="user_myprof.php">Profile</a></li>
                <li><a href="user_myadd.php">Addresses</a></li>
                <li><a href="user_changepass.php">Change Password</a></li>
                <li><a href="user_mypur.php">My Purchase</a></li>
                <!-- Update the logout link to point to the current file with the logout query -->
                <li><a href= "javascript:void(0);" onclick="showLogoutModal()">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content Section -->
        <div class="content">
                <div class="change-password-section">
                    <h3>Change Password</h3>
                    <span style="color: black; font-size: 20px; font-family: Arial, sans-serif;">
    Manage and protect your account
</span>

                    <form class="changepass" method="POST" action="user_changepass.php">
                        <div class="form-group">
                            <label for="current-password">Current Password</label>
                            <div class="password-container">
                                <input type="password" id="current-password" name="current-password" placeholder="Enter current password" required>
                                <span class="eye-icon" id="toggle-current-password" onclick="togglePassword('current-password')">&#128065;</span> <!-- Eye icon -->
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="new-password">New Password</label>
                            <div class="password-container">
                                <input type="password" id="new-password" name="new-password" placeholder="Enter new password" required>
                                <span class="eye-icon" id="toggle-new-password" onclick="togglePassword('new-password')">&#128065;</span> <!-- Eye icon -->
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirm-password">Confirm New Password</label>
                            <div class="password-container">
                                <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm new password" required>
                                <span class="eye-icon" id="toggle-confirm-password" onclick="togglePassword('confirm-password')">&#128065;</span> <!-- Eye icon -->
                            </div>
                        </div>
                        <button type="submit" class="save-btn">Save Changes</button>
                    </form>
                </div>
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
    <!-- Success Message Modal -->
     <!-- Message Modals -->
     <?php if ($successMessage): ?>
        <div id="successModal" class="message-modal success show">
            <?php echo $successMessage; ?>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div id="errorModal" class="message-modal error show">
            <?php echo $errorMessage; ?>
        </div>
    <?php endif; ?>

    <?php if ($newpassinvalidMessage): ?>
        <div id="newpassinvalidModal" class="message-modal error show">
            <?php echo $newpassinvalidMessage; ?>
        </div>
    <?php endif; ?>

    <?php if ($currentnotmatchMessage): ?>
        <div id="currentnotmatchModal" class="message-modal error show">
            <?php echo $currentnotmatchMessage; ?>
        </div>
    <?php endif; ?>

    <script>
        // Automatically hide the modal after 3 seconds
        setTimeout(function() {
            var modals = document.querySelectorAll('.message-modal.show');
            modals.forEach(function(modal) {
                modal.classList.remove('show');
            });
        }, 2000);
    </script>
    <script>
    function togglePassword(fieldId) {
        var field = document.getElementById(fieldId);
        var icon = document.getElementById('toggle-' + fieldId);
        
        if (field.type === "password") {
            field.type = "text";
            icon.innerHTML = "&#128065;";  // Change eye icon to open
        } else {
            field.type = "password";
            icon.innerHTML = "&#128064;";  // Change eye icon to closed
        }
    }
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
</body>
</html>
