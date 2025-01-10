<?php
// Start session (ensure user is logged in and session has 'user_id')
session_start();

// Check if logout is requested
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    // If the user confirmed the logout, proceed with session cleanup
    if (isset($_GET['confirmed']) && $_GET['confirmed'] == 'true') {
        session_unset(); // Clear all session variables
        session_destroy(); // Destroy the session
        header("Location: user_login.php"); // Redirect to login page
        exit();
    }
}

// Assuming user_id is stored in session after login
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

// Variables to store updated data
$updated_email = "";
$updated_phone = "";

// Handle profile picture upload
if (isset($_POST['upload_profile_picture']) && isset($_FILES['profile_picture'])) {
    $uploadDir = "uploads/"; // Directory to store uploaded files
    $fileName = basename($_FILES['profile_picture']['name']);
    $targetFilePath = $uploadDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    // Check file type (allow only images)
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array(strtolower($fileType), $allowedTypes)) {
        // Attempt to upload the file
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
            // Update profile_picture field in database
            $sql_update_picture = "UPDATE customers SET profile_picture = ? WHERE user_id = ?";
            $stmt_picture = $conn->prepare($sql_update_picture);
            $stmt_picture->bind_param("si", $targetFilePath, $user_id);
            $stmt_picture->execute();
        } else {
            echo "Error uploading the file. Please try again.";
        }
    } else {
        echo "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
    }
}

// Handle email and phone number update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update email
    if (isset($_POST['newEmail']) && !empty($_POST['newEmail'])) {
        $updated_email = $_POST['newEmail'];
        $sql_update_email = "UPDATE customers SET email = ? WHERE user_id = ?";
        $stmt_email = $conn->prepare($sql_update_email);
        $stmt_email->bind_param("si", $updated_email, $user_id);
        $stmt_email->execute();
    }

    // Update phone number
    if (isset($_POST['newPhone']) && !empty($_POST['newPhone'])) {
        $updated_phone = $_POST['newPhone'];
        $sql_update_phone = "UPDATE customers SET phone_number = ? WHERE user_id = ?";
        $stmt_phone = $conn->prepare($sql_update_phone);
        $stmt_phone->bind_param("si", $updated_phone, $user_id);
        $stmt_phone->execute();
    }

    // After processing, redirect to avoid resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Query to fetch user data (including address and profile picture)
$sql = "SELECT customer_name, email, gender, date_of_birth, phone_number, profile_picture, address FROM customers WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id); // Bind user_id as integer
$stmt->execute();
$result = $stmt->get_result();

// Fetch the user data
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $customer_name = $row['customer_name'];
    $email = $row['email'];
    $gender = $row['gender'];
    $dob = $row['date_of_birth'];
    $phone_number = $row['phone_number'];
    $profilePicture = $row['profile_picture'] ?: "uploads/default.jpg"; // Default profile picture
    $address = $row['address']; // Fetch address
} else {
    // Handle case if no user data is found
    $customer_name = "Not Found";
    $email = "Not Found";
    $gender = "";
    $dob = "";
    $phone_number = "";
    $profilePicture = "../images/user.png"; // Default profile picture
    $address = "Not Available"; // Default address if not found
}
// Set default profile picture if none is uploaded
if (empty($profile_picture) || !file_exists($profile_picture)) {
    $profile_picture = "../images/user.png";
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            cursor: default;
        }

        .container {
            display: flex;


        }

        .sidebar {
            width: 17%;
            background: linear-gradient(to right, #ff9a9e, #fecfef);
            font-family: 'Georgia', serif;
            text-shadow: 1px 1px 1px rgba(255, 255, 255, 0.384);
            color: white;
            padding: 10px;
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

        li:hover {
            transform: scale(1.2);

        }

        .profile-content {
            width: 80%;
            padding: 20px;
            background-color: white;
            display: flex;
            justify-content: space-between;

        }

        .profile-content h2 {
            color: rgb(223, 55, 83);
            font-size: 40px
        }

        h2 {
            color: white;
        }

        .myacc {
            color: rgb(223, 55, 83);
            margin-left: 40px;
            font-size: 30px
        }

        .profile-content p {
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
            display: flex;

        }

        .form-group2 {
            margin-bottom: 15px;
            display: flex;
            margin-left: 30px;
        }

        .form-group3 {
            margin-bottom: 15px;
            display: flex;
            margin-left: 30px;

        }

        .form-group a {
            font-size: 17px;
            border-radius: 10px;
            margin-top: 7px;
            padding: 8px;
            margin-left: 20px;
            color: rgb(223, 55, 83);
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            margin-top: 9px;
            padding: 5px;
        }

        input[type="text"],
        input[type="email"],
        input[type="date"] {
            width: 50%;
            padding: 10px;
            margin-bottom: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-top: 10px;
        }

        input[type="radio"] {
            margin-right: 5px;
        }

        .save-btn {
            margin-left: 210px;
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

        span {
            margin-top: 17px;
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
            -webkit-user-select: none;
            /* For Safari */
            -ms-user-select: none;
            -webkit-user-drag: none;
        }

        h1 {
            font-size: 18px;
            color: white;
            margin-left: 10px;
            line-height: 1.2;
            -webkit-user-select: none;
            /* For Safari */
            -ms-user-select: none;
            -webkit-user-drag: none;
        }

        span {
            font-size: 16px;
            color: black;
            -webkit-user-select: none;
            /* For Safari */
            -ms-user-select: none;
            -webkit-user-drag: none;
            -webkit-user-drag: none;
        }

        .mid-sec {
            margin-right: 78px;
            display: flex;
            justify-content: center;
            /* Centers items horizontally */
            align-items: center;
            /* Centers items vertically */
            gap: 20px;
            /* Adds space between the items */
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

        .printing-sub-dropdown,
        .imaging-sub-dropdown {
            display: none;
            position: absolute;
            left: 100%;
            /* Adjust based on layout */
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

        .printing-sub-dropdown a,
        .imaging-sub-dropdown a {
            padding: 10px 15px;
            color: rgb(223, 55, 83);
            text-decoration: none;
            display: block;
        }

        .printing-sub-dropdown a:hover,
        .imaging-sub-dropdown a:hover {
            background-color: rgb(223, 55, 83);
            color: white;
        }

        .right-sec {
            display: flex;
            align-items: center;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 10px;
            border: 1px solid #888;
            width: 30%;
            border-radius: 20px;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            margin-left: 440px;
            top: 0;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        div.card-body.text-center {
            color: black;
            padding: 20px;
            height: 400px;
            text-align: center;
            border-radius: 15px;
            width: 350px;
            margin-left: 100px;
        }

        form {
            color: black;
            font-size: 20px;
        }

        button.btn.green-btn {
            margin-bottom: 30px;
            color: white;
            font-size: 16px;
            background-color: rgb(237, 41, 103);
            padding: 10px;
            border: none;
            border-radius: 5px;
        }

        .file-input-container label {
            background-color: rgb(237, 41, 103);
            color: white;
            padding: 5px;
            width: 150px;
            margin-left: 88px;
            border-radius: 5px;
            font-size: 15px;
            margin-bottom: 8px;
        }

        .uploadfile {
            background-color: transparent;
            color: transparent;

        }

        h5 strong {
            color: black;
            font-size: 25px;

        }

        .add {
            color: blue;
        }

        .logout-modal {
            display: none;
            /* Hidden by default */
            position: fixed;
            /* Stay in place */
            z-index: 1;
            /* Sit on top */
            left: 0;
            top: 0;
            width: 100%;
            /* Full width */
            height: 100%;
            /* Full height */
            background-color: rgba(0, 0, 0, 0.4);
            /* Black with transparency */
            overflow: auto;
            /* Enable scroll if needed */
            padding-top: 60px;
            /* Location of the modal */
        }

        .logout-modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 300px;
            /* Modal width */
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
            background-color: #4CAF50;
            /* Green */
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
            background-color: #f44336;
            /* Red */
        }

        .modal-actions a:last-child:hover {
            background-color: #e53935;
        }

        .card-body {
            text-align: center;
            margin-left: 400px;
            margin-top: -20px;
        }

        .map {
            margin-left: 45px;
            font-size: 20px;
        }

        .pris {
            color: rgb(237, 41, 103);
            -webkit-user-select: none;
            /* For Safari */
            -ms-user-select: none;
            -webkit-user-drag: none;
            -webkit-user-drag: none;
        }

        .changeemail {
            font-size: 20px;
            margin-top: -30px;
            margin-left: 160px;
        }

        .pn {
            width: 160px;
            border: none;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="left-sec">
            <img class="logo" src="../images/logo.png" alt="logo">

            <h1>KRISHIEL <span class="pris"><br>PRINTING AND IMAGING SERVICES</span></h1>
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
    <div class="container">
        <div class="sidebar">
            <h2>My Account</h2>
            <ul>
                <li><a href="user_myprof.php">Profile</a></li>
                <li><a href="user_myadd.php">Address</a></li>
                <li><a href="user_changepass.php">Change Password</a></li>
                <li><a href="user_mypur.php">My Purchase</a></li>
                <!-- Update the logout link to point to the current file with the logout query -->
                <li><a href="javascript:void(0);" onclick="showLogoutModal()">Logout</a></li>
            </ul>
        </div>

        <div class="col-md-6 col-xl-4 mb-4">
            <div class="card green-theme">
                <div>
                    <h2 class="myacc">My Profile</h2>
                    <p class="map">Manage and protect your account</p>
                </div>

                <div class="card-body">
                    <!-- Display the profile picture or a default image -->
                    <img src="<?php echo htmlspecialchars($profile_picture); ?>" 
     class="profile-img mb-3" 
     alt="Customer Image" 
     style="width: 200px; height: 200px; object-fit: cover; border-radius: 50%;">

                    <!-- Display customer name -->
                    <h5><strong><?php echo htmlspecialchars(ucwords(strtolower($customer_name))); ?></strong></h5>

                    <!-- Display customer address -->
                    <p><?php echo htmlspecialchars(ucwords(strtolower($address))); ?></p>

                    <!-- Form for uploading profile picture -->
                    <form action="user_myprof.php" method="POST" enctype="multipart/form-data">
                        <div class="file-input-container">
                            <label for="profile_picture" id="file-label" class="btn green-btn">
                                Choose File
                            </label>
                            <input class="uploadfile" type="file" id="profile_picture" name="profile_picture"
                                style="display: none;" required
                                onchange="document.getElementById('file-label').innerText = this.files[0].name;">
                        </div>
                        <button type="submit" class="btn green-btn mt-3" name="upload_profile_picture">
                            Upload Profile Picture
                        </button>
                    </form>

                    <!-- Customer Information -->
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="name">Name:</label>
                            <span id="name">
                                <?php echo htmlspecialchars(ucwords(strtolower($customer_name))); ?>
                            </span>
                        </div>

                        <div class="form-group">
                            <label for="email">Email:</label>
                            <span id="email">
                                <?php echo htmlspecialchars(strtolower($email)); ?>
                            </span>
                            <a href="#" data-toggle="modal" data-target="#changeEmailModal">Change</a>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number:</label>
                            <span id="phone">
                                <?php echo htmlspecialchars(ucwords(strtolower($phone_number))); ?>
                            </span>
                            <a href="#" data-toggle="modal" data-target="#addPhoneModal">Add</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <!-- Modal for Changing Email -->
        <div class="modal" id="changeEmailModal">
            <div class="modal-content">
                <span class="close" data-dismiss="modal">&times;</span>
                <h3 class="changeemail">Change Email</h3>
                <form method="POST" action="">
                    <div class="form-group2">
                        <label for="newEmail">New Email:</label>
                        <input type="email" id="newEmail" name="newEmail" placeholder="Enter new email" required>
                    </div>
                    <button type="submit" class="save-btn">Save</button>
                </form>
            </div>
        </div>

        <!-- Modal for Adding Phone Number -->
        <div class="modal" id="addPhoneModal">
            <div class="modal-content">
                <span class="close" data-dismiss="modal">&times;</span>
                <h3 class="changeemail">Add Phone Number</h3>
                <form method="POST" action="">
                    <div class="form-group3">
                        <label class="pn" for="newPhone">Phone Number:</label>
                        <input type="tel" id="newPhone" name="newPhone" placeholder="Enter phone number" required>
                    </div>
                    <button type="submit" class="save-btn">Save</button>
                </form>
            </div>
        </div>
    </div>
    <div id="logoutModal" class="logout-modal">
        <div class="logout-modal-content">
            <span class="close-btn" onclick="hideLogoutModal()">&times;</span>
            <h2 style="color: black;">Are you sure you want to log out?</h2>

            <div class="modal-actions">
                <a href="javascript:void(0);" onclick="confirmLogout()">Yes, Logout</a>
                <a href="javascript:void(0);" onclick="hideLogoutModal()">Cancel</a>
            </div>
        </div>
    </div>

    <script>
        // Get modals
        var changeEmailModal = document.getElementById("changeEmailModal");
        var addPhoneModal = document.getElementById("addPhoneModal");

        // Get links that open the modals
        var changeEmailLink = document.querySelector('[data-target="#changeEmailModal"]');
        var addPhoneLink = document.querySelector('[data-target="#addPhoneModal"]');

        // Get <span> elements that close the modals
        var closeBtns = document.getElementsByClassName("close");

        // When the user clicks the "Change" link, open the Change Email modal
        changeEmailLink.onclick = function () {
            changeEmailModal.style.display = "block";
        }

        // When the user clicks the "Add" link, open the Add Phone Number modal
        addPhoneLink.onclick = function () {
            addPhoneModal.style.display = "block";
        }

        // When the user clicks on <span> (x), close the modal
        for (let btn of closeBtns) {
            btn.onclick = function () {
                changeEmailModal.style.display = "none";
                addPhoneModal.style.display = "none";
            }
        }

        // When the user clicks anywhere outside the modal, close it
        window.onclick = function (event) {
            if (event.target == changeEmailModal || event.target == addPhoneModal) {
                changeEmailModal.style.display = "none";
                addPhoneModal.style.display = "none";
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