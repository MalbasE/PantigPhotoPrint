<?php
session_start(); // Start the session

// Include database connection
include_once('../included/config.php');

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: user_login.php");
    exit();
}

// Fetch user profile picture
$user_id = $_SESSION["user_id"];
$sql = "SELECT profile_picture FROM customers WHERE user_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($profile_picture);
    $stmt->fetch();
    $stmt->close();
} else {
    echo "Error fetching user data.";
    exit();
}

// Use a default profile picture if none is set
if (empty($profile_picture)) {
    $profile_picture = "../images/user.png";

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
</head>
<style>
    body{
        font-family: Arial, Helvetica, sans-serif;
        background: linear-gradient(to right, #ffb3c6, #f0e68c);
    }
     .dropdown-content {
        display: none;
        color: rgb(241, 56, 87);
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
        text-shadow: 1px 1px 1px rgba(255, 255, 255, 0.384);
        padding: 10px 15px;
        text-decoration: none;
        display: block;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .dropdown-content a:hover {
        background-color: rgb(223, 55, 83);
        color: white;
    }
     .dropdown {
        position: relative;
    }

    .dropdown .dropbtn {
        background-color: transparent;
        color: rgb(255, 253, 254);
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
     .navbar {
        position: relative;
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

       .mid-sec {
    margin-right: 78px;
    display: flex;
    justify-content: center;  /* Centers items horizontally */
    align-items: center;      /* Centers items vertically */
    gap: 20px;  /* Adds space between the items */
    width: 100%;
}
      span {
        font-size: 14px;
        color: rgb(223, 55, 83);
        -webkit-user-select: none; /* For Safari */
        -ms-user-select: none;
        -webkit-user-drag: none;
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

    .logo {
        height: 50px;
        width: 50px;
        -webkit-user-select: none; /* For Safari */
        -ms-user-select: none;
        -webkit-user-drag: none;
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
     .left-sec {
        display: flex;
        align-items: center;
    }
    
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 75px;
        top: 0;
        left: 0;
        right: 0;
        bottom:0;
        background-color: transparent;
        border-bottom: 2px solid rgb(223, 55, 83);
        z-index: 1000;
        padding: 0 20px;
    }

.about-us {
    color:  rgb(223, 55, 83);
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    padding: 30px;
    background-color: #fff;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    margin: 100px auto;
    max-width: 1100px;
}
a{
   
    text-shadow: 1px 1px 1px rgba(255, 255, 255, 0.384);
    font-size: 18px;
}

.content {
    flex: 1;
    padding: 20px;
    animation: fadeIn 2s ease-in-out;
}

.content h1 {
    font-size: 2.5rem;
    color: #0096C7;
    margin-bottom: 20px;
}

.content p {
    font-size: 1.2rem;
    color: #333;
    line-height: 1.8;
    margin-bottom: 15px;
    text-align: justify; /* Added for justified text */
}

.image-container {
    flex: 1;
    text-align: center;
    animation: slideInRight 2s ease-in-out;
}

.image-container img {
    height: 500px;
    width: 400px;
    border-radius: 10px;
    
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

</style>
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
            <a class="feat" href="#">ABOUT US</a>
        </div>
    <div class="right-sec">
        <a href="#" onclick="toggleLogoutForm()">
        <img src="<?php echo htmlspecialchars($profile_picture); ?>" 
     class="profile-img mb-3" 
     alt="Customer Image" 
     style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
        </a>
    </div>
</div>
    <section class="about-us">
    <div class="content">
    <h1 style="color: rgb(223, 55, 83);">About Us</h1>
    <p style="color: black">Krishiel printing and imaging services is your trusted partner for high-quality, affordable, and reliable printing services in the Philippines. We offer a wide range of solutions, including flyers, custom stickers, sintra boards, and ID photos, using modern equipment for vibrant and durable results. Committed to customer satisfaction, we provide personalized services, competitive pricing, and on-time delivery to meet your needs. From professional materials to special occasion prints, we’re here to make your ideas come to life with care and precision.</p>
</div>

        <div class="image-container">
            <img src="../images/abt1.png" alt="logo">
        </div>
    </section>

    <script>
    window.addEventListener('scroll', function () {
        let aboutUsSection = document.querySelector('.about-us');
        let sectionPosition = aboutUsSection.getBoundingClientRect().top;
        let screenPosition = window.innerHeight / 1.5;
    
        if (sectionPosition < screenPosition) {
            aboutUsSection.classList.add('active');
        } else {
            aboutUsSection.classList.remove('active');
        }
    });
</script>
</body>
</html>
