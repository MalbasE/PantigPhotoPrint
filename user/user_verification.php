<?php
// Database connection details
$host = 'localhost'; // Change if using a different host
$dbname = 'kpis'; // Replace with your actual database name
$username = 'root'; // Replace with your actual database username
$password = ''; // Replace with your actual database password

// Create a MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// Function to send an email
function sendEmail($email, $subject, $message) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'lorem.ipsum.sample.email@gmail.com'; // Change this to your email
        $mail->Password = 'novtycchbrhfyddx'; // Change this to your email app password
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        $mail->setFrom('lorem.ipsum.sample.email@gmail.com', 'Krishiel Printing and Imaging Services');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: " . $e->getMessage());
        return false;
    }
}

// Initialize success overlay and redirection delay variables
$show_success_overlay = false;
$redirect_delay = 5; // Redirection delay in seconds

// Verification Process
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate the verification code
    $verification_code = htmlspecialchars(trim($_POST['verification_code']));

    if (empty($verification_code)) {
        $error_message = "Please enter the verification code.";
    } else {
        // Check the database for the verification code
        $sql = "SELECT * FROM customers WHERE verification_code = ? AND is_verified = 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $verification_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Verification code is valid, update the user's status to verified
            $update_sql = "UPDATE customers SET is_verified = 1, verification_code = NULL WHERE verification_code = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("s", $verification_code);

            if ($update_stmt->execute()) {
                // Set success overlay flag and redirection delay
                $show_success_overlay = true;
                $redirect_delay = 3; // 2 seconds delay for redirect

                // Optionally, you can also set a success message here, e.g.
                // $success_message = "Verification successful! Redirecting to login...";
            } else {
                $error_message = "Something went wrong. Please try again.";
            }

            $update_stmt->close();
        } else {
            // Invalid verification code
            $error_message = "Invalid verification code or already verified.";
        }

        $stmt->close();
    }
}

// Handle the email request for verification code resend
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['email'])) {
    $email = htmlspecialchars(trim($_GET['email']));

    if (empty($email)) {
        die("Email is required!");
    }

    // Check if email exists and is not verified
    $sql = "SELECT customer_name, is_verified FROM customers WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if ($row['is_verified']) {
            die("This account is already verified.");
        }

        $customer_name = $row['customer_name'];

        // Generate a new verification code
        $verification_code = rand(100000, 999999);

        // Update the verification code in the database
        $update_sql = "UPDATE customers SET verification_code = ? WHERE email = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ss", $verification_code, $email);

        if ($update_stmt->execute()) {
            // Send a new verification email
            $subject = "Resend: Email Verification Code";
            $message = "
            <html>
            <head>
                <title>Resend Verification</title>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .verification-code {
                        color: #007bff;
                        font-weight: bold;
                        font-size: 18px;
                    }
                </style>
            </head>
            <body>
                <p>Dear $customer_name,</p>
                <p>Here is your new verification code:</p>
                <p class='verification-code'>$verification_code</p>
                <p>Use this code to complete your registration. If you did not request this, please ignore this email.</p>
                <p>Best regards,<br>Krishiel Team</p>
            </body>
            </html>";

            if (sendEmail($email, $subject, $message)) {
                echo "A new verification code has been sent to your email.";
            } else {
                echo "Failed to send the verification email. Please try again.";
            }
        } else {
            echo "Failed to update the verification code. Please try again.";
        }
        $update_stmt->close();
    } else {
        echo "No account found with this email.";
    }

    $stmt->close();
}

// Close database connection
$conn->close();
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
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

        /* Outer Container for Logo and Verification Form */
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
        }

        /* Main Container for the Verification Form */
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

        .verify-form {
            width: 100%;
            text-align: center;
        }

        .verify-form h2 {
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

        .verify-btn {
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

        .verify-btn:hover {
            background-color: #005f99;
        }

        .resend-link {
            display: block;
            margin-top: 10px;
            font-size: 14px;
            color: #D5006D;
            text-decoration: none;
        }

        .resend-link:hover {
            text-decoration: underline;
        }

        /* Error Message */
        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 5px;
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
            .verify-btn {
                width: 100%;
            }
        }
            /* Success Message Overlay */
            .success-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            text-align: center;
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 1s ease-out, visibility 1s ease-out;
        }

        .success-overlay.visible {
            opacity: 1;
            visibility: visible;
        }


    </style>
</head>

<body>
<!-- Success Message Overlay -->
<?php if (isset($show_success_overlay) && $show_success_overlay): ?>
<div class="success-overlay visible">
    Verification Successful! Redirecting to Login...
</div>
<?php endif; ?>

<div class="outer-container">
    <div class="logo-container">
        <img class="logo" src="../images/logo.png" alt="Logo">
        <div class="krishiel-text">KRISHIEL</div>
        <div class="printing-services">Printing And Imaging Services</div>
    </div>

    <div class="container">
        <div class="verify-form">
            <h2>EMAIL VERIFICATION</h2>

            <!-- Display error messages -->
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Verification Form -->
            <form method="POST">
                <input type="text" class="input-field" name="verification_code" placeholder="Enter Verification Code" required>
                <button class="verify-btn" type="submit">VERIFY</button>
            </form>
            <a href="user_verification.php?email=<?php echo $user_email; ?>" class="resend-link">Resend verification code</a>
        </div>
    </div>
</div>

<script>
    // Redirect the user after a delay (5 seconds) if success overlay is shown
    <?php if (isset($show_success_overlay) && $show_success_overlay): ?>
        setTimeout(function() {
            window.location.href = 'user_login.php'; // Adjust the redirect URL as needed
        }, <?php echo $redirect_delay * 1000; ?>);
    <?php endif; ?>
</script>

</body>
</html>
