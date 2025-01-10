<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printing Services</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <style>
        /* General Body Styling */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif; /* Default font for the entire page */
            background: linear-gradient(to bottom right, #fff7a8, #fcb2c8); /* Updated gradient colors */
        }

        /* Header Styling */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: transparent;
            border-bottom: 2px solid #ff69b4; /* Pink border line */
        }

        /* Left Section (Logo + Text) */
        .left-sec {
            display: flex;
            align-items: center;
        }

        .logo {
            width: 50px;
            height: 50px;
            margin-right: 10px;
        }

        .header h1 {
            font-size: 24px;
            color: #ff69b4;
            margin: 0;
        }

        .header span {
            font-size: 14px;
            color: #ff69b4;
            display: block;
        }

        /* Navigation Links */
        .nav-links {
            display: flex;
            gap: 20px;
            margin-right: 30px; /* Shift the nav-links a bit to the left */
        }

        .nav-links a {
            text-decoration: none;
            color: #ff69b4;
            font-size: 16px;
            font-weight: bold;
        }

        .nav-links a:hover {
            color: #ff1493; /* Darker pink for hover */
        }

        /* Header Icons (Cart and User) */
        .icons img {
            width: 24px;
            height: 24px;
            margin-left: 20px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .icons img:hover {
            transform: scale(1.2); /* Slightly enlarges on hover */
        }

        /* Page Title Styling */
        h2 {
            text-align: center;
            font-size: 32px;
            font-family: 'Dancing Script', cursive; /* Special font for title */
            color: #ff69b4;
            margin-top: 40px;
        }

        /* Service Section Styling */
        .services {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px; /* Increased gap between service boxes */
            margin-top: 20px;
        }

        .service-box {
            width: 350px; /* Increased the width */
            height: 250px; /* Increased the height */
            background-color: #ffb6c1; /* Light pink for service boxes */
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(255, 255, 255, 0.5); /* White shadow around the border */
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden; /* Prevents image overflow */
        }

        .service-box img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Ensures the image covers the box area */
        }

        /* Dropdown Styling */
        .dropdown {
            position: relative;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #fff;
            min-width: 200px;
            border-radius: 8px; /* Slightly rounded corners */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1;
        }

        .dropdown-content a {
            text-decoration: none;
            display: block;
            padding: 8px 10px;
            color: #ff69b4;
            font-size: 14px; /* Smaller text for dropdown items */
        }

        .dropdown-content a:hover {
            background-color: #ffe4e1;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header">
        <div class="left-sec">
            <!-- Logo -->
            <img class="logo" src="../images/logo.png" alt="logo">
            <!-- Business Name -->
            <h1>KRISHIEL<br><span>PRINTING AND IMAGING SERVICES</span></h1>
        </div>
        <!-- Navigation Links -->
        <div class="nav-links">
            <a href="#">HOME</a>
            <div class="dropdown">
                <a href="#">SERVICES</a>
                <div class="dropdown-content">
                    <a href="#">Printing Services</a>
                    <a href="#">Imaging Services</a>
                </div>
            </div>
            <a href="#">ABOUT US</a>
        </div>
        <!-- Header Icons -->
        <div class="icons">
            <img src="../images/cart.png" alt="Cart">
            <img src="../images/user.png" alt="User">
        </div>
    </div>

    <!-- Page Title -->
    <h2>Printing Services</h2>

    <!-- Service Boxes Section -->
    <div class="services">
        <div class="service-box">
            <img src="../images/placeholder.jpg" alt="Service Image">
        </div>
        <div class="service-box">
            <img src="../images/placeholder.jpg" alt="Service Image">
        </div>
    </div>
</body>
</html>
