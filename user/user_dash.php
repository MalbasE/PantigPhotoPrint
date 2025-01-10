<?php
session_start(); // Start the session

// Set session timeout duration (in seconds)
$session_timeout_duration = 2592000; // 30 days (in seconds)

// Check if session is inactive for longer than the timeout duration
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout_duration)) {
    // If session expired, destroy the session
    session_unset();
    session_destroy();
    header("Location: user_login.php"); // Redirect to login page
    exit();
}
$_SESSION['last_activity'] = time(); // Update last activity time

// Regenerate session ID to prevent session fixation attacks
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} else if (time() - $_SESSION['CREATED'] > $session_timeout_duration) { // If session is older than 30 days
    session_regenerate_id(true); // Change session ID and delete the old one
    $_SESSION['CREATED'] = time(); // Update creation time
}

// Check if the user is logged in by verifying the session variable
if (!isset($_SESSION["user_id"])) {
    // If not logged in, redirect to the login page
    header("Location: user_login.php");
    exit();
}

// If logged in, proceed with fetching user data
$user_id = $_SESSION["user_id"];

// Include database connection (assuming it's in config.php)
include_once('../included/config.php');

// Fetch the customer name and profile picture based on user_id
$sql = "SELECT customer_name, profile_picture FROM customers WHERE user_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id); // Bind the user_id parameter (assuming user_id is an integer)
    $stmt->execute();
    $stmt->bind_result($customer_name, $profile_picture); // Bind results to variables
    $stmt->fetch(); // Fetch the data

    // Close the statement
    $stmt->close();
} else {
    // Handle query error
    echo "Error fetching user data.";
    exit();
}

// Capitalize the first letter of each word in the customer name
$customer_name = ucwords(strtolower($customer_name));

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
    <title>Dashboard</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
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
    .mid-sec {
    margin-right: 78px;
    display: flex;
    justify-content: center;  /* Centers items horizontally */
    align-items: center;      /* Centers items vertically */
    gap: 20px;  /* Adds space between the items */
    width: 100%;
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
        -webkit-user-select: none; /* For Safari */
        -ms-user-select: none;
        -webkit-user-drag: none;
    }

    .logo {
        height: 50px;
        width: 50px;
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

    /* Service Form styling */
    .service-form {
        margin-top: 20px;
        padding: 20px;
        border: 1px solid #ddd;
        background-color: #f9f9f9;
    }

    .service-form input, .service-form select {
        margin: 10px 0;
        padding: 8px;
        width: 100%;
        box-sizing: border-box;
    }

  .header2 {
    margin-top: 80px;
    display: flex;
    flex-direction: row;
    align-items: center;  /* Centers items vertically */
    justify-content: center;  /* Centers items horizontally */
}


    h2 {
        color: rgb(223, 55, 83);
    }

   /* Left Section */
.left-sec2 {
    margin-left: 10px;
    width: 200px;  /* You may want to adjust this based on the design */
    flex-shrink: 0; /* Prevents shrinking when flex is applied */
}

/* Mid Section */
.mid-sec2 {
    flex-grow: 1;  /* Allows this section to take up available space */
    max-width: 500px;  /* Max width for mid-sec */
    margin-right: 20px;
    display: flex;
    justify-content: center;  /* Center content horizontally */
    align-items: center;      /* Center content vertically */
    width: 100%;  /* Ensure the mid section spans available space */
}

/* Right Section */
.right-sec2 {
    flex-grow: 1;  /* Allows this section to take up available space */
    max-width: 500px;  /* Max width for right-sec */
    margin-right: 20px;
    display: flex;
    justify-content: flex-end;  /* Aligns items to the right */
    align-items: center;       /* Center content vertically */
    width: 100%;  /* Ensure right section spans available space */
}

/* Optional: If you want a container to hold all sections together */
.container {
    display: flex;
    justify-content: space-between; /* Distribute space between sections */
    align-items: center;
    width: 100%;
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

    li:hover {
        transform: scale(1.1);
        background-color: rgb(223, 55, 83);
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
            width: 300px;
            background-color: #fff;
            border-left: 2px solid #ccc;
            padding: 20px;
            box-shadow: -2px 0 8px rgba(0, 0, 0, 0.2);
            height: 100vh;
            overflow-y: auto;
            z-index: 101;
        }

        #cartBasket h3 {
            text-align: center;
        }

        #cartItemsList {
            list-style-type: none;
            padding: 0;
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

        #cartItemsList li button:hover {
            background-color: #c0392b;
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

        #clearCartBtn:hover {
            background-color: #e67e22;
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
    color: #000;  /* You can change this color as per your preference */
}

.close-btn:hover {
    color: red;  /* Change the color when hovered */
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

    .container {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 90vh;
    }

    .content {
    display: flex;
    border: 1px solid black;
    padding-top: 30px;
    padding-bottom: 50px;
    padding-left: 150px;
    padding-right: 150px;
    text-align: center;
    animation: fadeIn 1.5s ease-in-out; /* Apply the fadeIn animation */
    font-size: 2.5rem;
    animation: slideInRight 1.5s ease-in-out; /* Apply the slideInRight animation */
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(100px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@media (max-width: 768px) {
    .about-us {
        flex-direction: column;
    }

    .content h1 {
        font-size: 2rem;
    }
}


    img {
    height: 200px;
    -webkit-user-select: none; /* For Safari */
    -ms-user-select: none;
    -webkit-user-drag: none;
    -ms-user-drag: none;
    
}

    p {
      color: rgb(241, 56, 87);
      font-weight: 500;
     padding-left: 12px;
    }

    .avail {
      color: rgb(238, 231, 233);
      background-color: (241, 56, 87);
      height: 70px;
      border-radius: 30px;
      width: 160px;
    }

    .content-img {
      width: 320px;
      align-items: center;
      display: flex;
      position: relative;
      margin-left: 400px;
    }

    .social {
    margin-top: 10px;
      position: absolute;
      bottom: 0;
      margin-left: 785px;
      top: 550px;
      animation: fadeIn 1.5s ease-in-out; /* Apply the fadeIn animation */
    }
    /* Keyframe animation for fade-in effect */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px); /* Slightly move up during fade-in */
    }
    to {
        opacity: 1;
        transform: translateY(0); /* Original position after fade-in */
    }
}

    .img {
      height: 100%;
      width: 100%;
      margin-left: 80px;
    }

    .logo1 {
      height: 30px;
      margin-right:5px;
      margin-top: 150px;
    }

    .social {
      justify-content: end;
      display: flex;
      margin-right: 250px;
      bottom:0;
    }


    .sec1 {
      background-image: url('../images/image.png');
      background-size: cover;
      background-position: center;
      padding: 100px 0;
      color: black;
      text-align: center;
    }

    .intro1 {
      text-align: center;
      font-size: 32px;
      font-style: italic;
      letter-spacing: 2px;
      color: black;
      line-height: 1.5;
      padding-top: 10px;

    }

    .content3 {
      font-size: 30px;
      font-weight: 1000;
      letter-spacing: 5px;
      color: white;
    }

    .new-section {
      background-color: white;
      padding: 50px 0;
      text-align: center;
      width: 100%;
    }

    .new-section {
      font-size: 28px;
      font-weight: 700;
      color: #333;
      margin-bottom: 20px;
    }

    .new-section p {
      font-size: 18px;
      color: #555;
      margin-bottom: 30px;
    }

    .new-section button {
      height: 40px;
      width: 180px;
      font-size: 16px;
      font-weight: 600;
      background-color: #f5879a;
      color: white;
      border: none;
      border-radius: 20px;
      cursor: pointer;
      transition: transform 0.3s, background-color 0.3s;
    }

    .new-section button:hover {
      transform: scale(1.05);
      background-color: #f13057;
    }

   .imgs {
    margin-top: 80px;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 150px;
    margin-right: 50px;
    animation: fadeIn 1.5s ease-in-out; /* Apply the fadeIn animation */
}


/* Keyframe animation for fade-in effect */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px); /* Slightly move up during fade-in */
    }
    to {
        opacity: 1;
        transform: translateY(0); /* Original position after fade-in */
    }
}


  
    .overlay{
      font-size: 30px;
      position: absolute;
      top: 50%;
      letter-spacing: 2px;
      left:80% ;
      text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.548);
      transform: translate(-50%, -50%);
      color: white;
      font-weight: 600;
     
    }


    .imgs div {
      width: 150px;
      height: auto;
      margin-right: 50px;
    }

    .imgs img {
      width: 250px;
      height: 200px;
    }

    .imgs div:nth-child(odd) {
      order: 1;
    }

    .imgs div:nth-child(even) {
      order: 2;
    }

    .img2 {
      display: flex;
      justify-content: center;
      gap: 150px;
      text-align: center;
      margin-top: 50px;
    }

    .imgs .img3 {
      width: 250px;
      height: 200px;
    }

    .sec1 {
        background-color: rgba(255, 255, 255, 0.6); /* White with more transparency */

}

   .relative{
    position: relative;
   }
  /* Overlay container */
  .welcome-overlay {
    position: fixed; /* Fix it to the screen */
    top: 0;
    left: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black background */
    display: flex; /* Enable flexbox */
    justify-content: center; /* Center horizontally */
    align-items: flex-start; /* Align items to the top */
    padding-top: 200px; /* Add space from the top of the screen */
    z-index: 9999; /* Ensure it appears above other content */
}

/* Text inside the overlay */
.welcome {
    margin-top: 20px; /* Add top margin */
    color: white; /* White text */
    font-size: 40px; /* Adjust font size */
    text-align: center; /* Center the text */
    margin-left:630px; /* Remove any extra margins */
}
.wel {
    margin-top: 20px; /* Add top margin */
}
    </style>
</head>
<body>
    <!-- Header Section -->
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
        <a href="user_myprof.php" onclick="toggleLogoutForm()">
        <img src="<?php echo htmlspecialchars($profile_picture); ?>" 
     class="profile-img mb-3" 
     alt="Customer Image" 
     style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
        </a>
    </div>
</div>
<div class="wel">
    <!-- HTML to display the welcome message (without User ID) -->
    <span class="welcome" style="font-family: 'Georgia', serif; color: white; font-size: 40px; text-align: center; margin-left: 560px; margin-top: 20px;">
    Welcome, <?php echo htmlspecialchars($customer_name); ?>!
</span>

</div>

<!-- Content Section -->
<div class="container">
    <div class="content">
        <div class="logo">
        <img src="../images/logo.png" alt="logo" style="margin-top: 30px;" />
        <h1 style="font-size: 40px; text-align: center; text-transform: uppercase; letter-spacing: 5px; margin-left: -145px; font-family: 'Georgia', 'Times New Roman', serif;">
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;krishiel
</h1>



           
        </div>
        <div class="content-img">
            <img class="img" src="../images/image8.jpg" alt="picturehand" />
        </div>
        
    </div>
    <div class="social" style="margin-left: 1200px; margin-top: -20px;">
    <img class="logo1" src="../images/facebook.png" alt="facebook" />
    <img class="logo1" src="../images/twitter.png" alt="twitter" />
    <img class="logo1" src="../images/instagram.png" alt="insta" />
    <img class="logo1" src="../images/linkedin.png" alt="in" />
</div>

</div>

<!-- Additional Content Section -->
<section class="sec1" id="part2">
    <div class="section-content">
    <h2 class="intro1" style="font-size: 36px; text-align: center; font-family: 'Lobster', cursive; color: black; margin-top: 20px;">
    Your Treasured Memories on Pictures<br />
    Philippines’ first and leading provider of high-quality photo printing
</h2>


    </div>

    <div class="imgs">
        <div class="relative">
            <img class="imgs1" src="../images/image1.jpg" alt="facebook" />
            <div class="overlay">Collection Sets</div>
        </div>
        <div class="relative">
            <img class="imgs1" src="../images/image3.jpg" alt="twitter" />
            <div class="overlay">Custom Portraits</div>
        </div>
        <div class="relative">
            <img class="imgs1" src="../images/image6.jpg" alt="insta" />
            <div class="overlay">Standard Sizes</div>
        </div>
    </div>

    <div class="imgs">
        <div class="relative">
            <img class="imgs1" src="../images/image5.jpg" alt="twitter" />
            <div class="overlay">Around The Home</div>
        </div>
        <div class="relative">
            <img class="imgs1" src="../images/image7.jpg" alt="insta" />
            <div class="overlay">Gift Ideas</div>
        </div>
    </div>
</section>

<!-- Logout Button (Initially hidden) -->
<form id="logout-form" action="logout.php" method="POST" style="display:none;">
    <button type="submit">Logout</button>
</form>

<!-- JavaScript to toggle the logout form -->
<script>
    function toggleLogoutForm() {
        // Get the logout form element
        var logoutForm = document.getElementById('logout-form');
        
        // Toggle the visibility of the logout form
        if (logoutForm.style.display === 'none' || logoutForm.style.display === '') {
            logoutForm.style.display = 'block';
        } else {
            logoutForm.style.display = 'none';
        }
    }
</script>
</body>
</html>
