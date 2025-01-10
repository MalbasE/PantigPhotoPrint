<?php
session_start(); // Start the session
include_once('../included/config.php'); // Include the database configuration

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message']; // Assign the session message to the variable
    unset($_SESSION['success_message']); // Clear the message after displaying it
} else {
    $success_message = ''; // If no message is set, make it an empty string
}

$error_message = ""; // Initialize error message variable

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the username and password from the form and sanitize the inputs
    $username_input = htmlspecialchars(trim($_POST['username'])); // This is the username (could be email)
    $password_input = htmlspecialchars(trim($_POST['password']));

    // Check if both fields are filled
    if (empty($username_input) || empty($password_input)) {
        $error_message = "Both fields are required!";
    } else {
        // Prepare the query to check the username in the database
        $sql = "SELECT user_id, password, is_verified FROM customers WHERE email = ?"; // Add 'is_verified' field to the query

        // Prepare the statement to avoid SQL injection
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $username_input); // Bind the username parameter (assuming it's a string)

            // Execute the query
            $stmt->execute();
            $stmt->store_result();

            // Check if a record was found (i.e., username exists)
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($db_user_id, $db_password, $db_is_verified); // Get the result fields (user_id, password, is_verified)
                $stmt->fetch();

                // Check if the account is verified
                if ($db_is_verified != 1) {
                    $error_message = "Your email is not yet registered.";
                } elseif ($password_input === $db_password) {
                    // Successful login: Set session variables
                    $_SESSION["user_id"] = $db_user_id; // Set user_id in session

                    // Redirect to the user dashboard with JavaScript
                    echo "
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var loadingBox = document.createElement('div');
                            loadingBox.setAttribute('id', 'loading');
                            loadingBox.style.cssText = 'position: fixed; top: 50px; left: 67%; transform: translateX(-50%); background-color: rgba(255, 255, 255, 0.8); padding: 20px; border-radius: 5px; font-size: 18px; color: #FF69B4; font-weight: bold;';
                            loadingBox.innerHTML = 'Loading...';
                            // Append to the outer container instead of the login form
                            document.querySelector('.outer-container').appendChild(loadingBox);
                            setTimeout(function(){
                                window.location.href = 'user_dash.php'; // Redirect to user dashboard
                            }, 2000);
                        });
                    </script>
                    ";
                } else {
                    // Incorrect password
                    $error_message = "Incorrect username or password.";
                }
            } else {
                // No user found with that username
                $error_message = "Incorrect username or password.";
            }

            // Close the statement
            $stmt->close();
        } else {
            // Database query failure
            $error_message = "Database query failed!";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <style>
          /* General Body Styling */
          body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url('../images/bg-image.png');
            background-size: cover;
            background-position: center;
        }

        /* Outer Container for Logo and Login Form */
        .outer-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        /* Logo and Text Container */
        .logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-right: 300px;
            user-select: none;
        }

        .logo {
            width: 250px;
            height: 250px;
            cursor: default;
            animation: pulseLogo 2s infinite alternate;
        }

        @keyframes pulseLogo {
            0% {
                transform: scale(1);
            }

            100% {
                transform: scale(1.1);
            }
        }

        .krishiel-text {
            font-family: 'Georgia', serif;
            font-size: 50px;
            font-weight: 500;
            color: #D5006D;
            margin-top: 10px;
        }

        .printing-services {
            font-size: 20px;
            color: #D5006D;
            margin-top: 5px;
            cursor: default;
        }

        /* Main Container for the Login Form */
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 400px;
            height: 350px;
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .background-blur {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            -webkit-backdrop-filter: blur(10px);
            backdrop-filter: blur(10px);
            z-index: -1;
        }

        .login-form {
            width: 100%;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .login-form h2 {
            font-size: 24px;
            color: #D5006D;
            margin-bottom: 20px;
        }

        .input-field {
            width: 100%;
            max-width: 300px;
            padding: 12px;
            margin: 5px 0;
            border: 1px solid #FF69B4;
            border-radius: 8px;
            font-size: 16px;
        }

        .login-btn {
            width: 100%;
            max-width: 300px;
            padding: 12px;
            background-color: #D5006D;
            color: #fff;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .forgot-password {
            display: block;
            margin-top: 10px;
            margin-bottom: 10px;
            font-size: 14px;
            color: #D5006D;
            text-decoration: none;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .register-text {
            margin-top: 20px;
            font-size: 14px;
            color: #FF69B4;
        }

        .register-text a {
            text-decoration: none;
            color: #D5006D;
        }

        .register-text a:hover {
            text-decoration: underline;
        }

        /* Error Message Styling */
        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 10px;
            font-weight: bold;
        }

        /* Success Message Overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            visibility: hidden; /* Hidden by default */
        }

        .overlay.show {
            visibility: visible;
        }

        .success-message-box {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            font-size: 18px;
            color: #4CAF50;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 500px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .outer-container {
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }

            .logo-container {
                margin-right: 0;
                margin-bottom: 20px;
            }

            .logo {
                width: 150px;
                height: 150px;
            }

            .krishiel-text {
                font-size: 28px;
            }

            .printing-services {
                font-size: 12px;
            }

            .container {
                width: 90%;
                max-width: 400px;
                height: auto;
                padding: 20px;
                margin-left: 15px;
                margin-right: 15px;
            }

            .input-field,
            .login-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <?php if ($success_message): ?>
        <div class="overlay show" id="success-overlay">
            <div class="success-message-box">
                <p><?php echo $success_message; ?></p>
            </div>
        </div>
        <script>
            setTimeout(function() {
                document.getElementById('success-overlay').classList.remove('show'); // Hide the overlay after 2 seconds
            }, 2000); // 2000 milliseconds (2 seconds)
        </script>
    <?php endif; ?>

    <!-- Outer Container (Logo and Text + Login Form) -->
    <div class="outer-container">
        <!-- Logo Section (On the Left) -->
        <div class="logo-container">
            <img class="logo" src="../images/logo.png" alt="Logo"> <!-- Logo Image -->
            <div class="krishiel-text">KRISHIEL</div> <!-- "KRISHIEL" Text -->
            <div class="printing-services">Printing And Imaging Services</div> <!-- "Printing And Imaging Services" Text -->
        </div>

        <!-- Main Container for the Login Form -->
        <div class="container">
            <!-- Background Blur -->
            <div class="background-blur"></div>

            <!-- Login Form -->
            <div class="login-form">
                <h2>CUSTOMER LOGIN</h2>
                <!-- Display error message if credentials are incorrect -->
                <?php if (!empty($error_message)): ?>
                    <div class="error-message"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <form action="" method="POST">
                    <input type="text" name="username" class="input-field" placeholder="Username" required> <!-- Username input -->
                    <input type="password" name="password" class="input-field" placeholder="Password" required>
                    <a href="user_forgotpass.php" class="forgot-password">Forgot Password?</a>
                    <button class="login-btn" type="submit">LOGIN</button>

                    <!-- Register Text -->
                    <div class="register-text">
                        <a href="user_reg.php">Don't have an account? Sign up here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
