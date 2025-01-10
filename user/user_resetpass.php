<?php
session_start();

// Database connection details
$host = 'localhost';
$dbname = 'kpis';
$username = 'root';
$password = '';

// Create a MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$error_message = '';
$success_message = ''; // Used to store success message

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize inputs
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $reset_code = trim($_POST['reset_code'] ?? '');

    // Validate inputs
    if (empty($reset_code)) {
        $error_message = "Reset code is required.";
    } elseif (empty($new_password) || empty($confirm_password)) {
        $error_message = "Both password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } else {
        // Prepare SQL query
        $sql = "UPDATE customers SET password = ?, reset_code = NULL WHERE reset_code = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ss", $new_password, $reset_code);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                // Set success message in session
                $_SESSION['success_message'] = "Password successfully reset!";

                // Redirect to user_login.php
                header('Location: user_login.php');
                exit();
            } else {
                $error_message = "Invalid or expired reset code.";
            }
            $stmt->close();
        } else {
            $error_message = "Database query failed. Please try again.";
        }
    }
}

$conn->close();
?>

<!-- HTML for the Reset Password Page (same as before) -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Password</title>
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

        /* Outer Container for Logo and Form */
        .outer-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        .logo-container {
            padding: 200px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-right: 30px;
        }

        .logo {
            width: 350px;
            height: 350px;
            animation: pulseLogo 2s infinite alternate;
            /* Pulse animation */
        }

        /* Keyframe animation for pulsing effect */
        @keyframes pulseLogo {
            0% {
                transform: scale(1);
                /* Original size */
            }

            100% {
                transform: scale(1.1);
                /* Slightly larger */
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
        }

        /* Main Container for the Form */
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 400px;
            height: auto;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            position: relative;
        }

        .new-password-form {
            width: 100%;
            text-align: center;
        }

        .new-password-form h2 {
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
            margin-bottom: 20px;
        }

        .submit-btn {
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

        .submit-btn:hover {
            background-color: #005f99;
        }

        /* Error Message */
        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }

        /* Success Message */
        .success-message {
            color: green;
            font-size: 16px;
            margin-top: 10px;
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
                padding: 20px;
            }

            .input-field,
            .submit-btn {
                width: 100%;
            }
        }

        .success-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 255, 0, 0.3);
            color: green;
            font-size: 24px;
            text-align: center;
            padding-top: 20%;
            z-index: 1000;
        }

        .success-overlay.visible {
            display: block;
        }

        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="outer-container">
        <div class="logo-container">
            <img class="logo" src="../images/logo.png" alt="Logo">
            <div class="krishiel-text">KRISHIEL</div>
            <div class="printing-services">Printing And Imaging Services</div>
        </div>

        <div class="container">
            <div class="new-password-form">
                <h2>Create New Password</h2>
                <?php if (isset($_SESSION['success_message'])): ?>
    <div class="success-message">
        <?php 
        echo htmlspecialchars($_SESSION['success_message']); 
        unset($_SESSION['success_message']); // Remove the message after displaying it
        ?>
    </div>
<?php elseif (isset($success_message)): ?>
    <div class="success-message">
        <?php echo htmlspecialchars($success_message); ?>
    </div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="error-message">
        <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>
               
                <!-- New Password Form -->
                <form method="POST">
                    <input type="text" class="input-field" name="reset_code" placeholder="Enter Reset Code" required>
                    <input type="password" class="input-field" name="new_password" placeholder="Enter New Password" required>
                    <input type="password" class="input-field" name="confirm_password" placeholder="Confirm New Password" required>
                    <button class="submit-btn" type="submit">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
