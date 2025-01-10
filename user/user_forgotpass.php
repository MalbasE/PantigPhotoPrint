<?php
session_start();
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

// Function to send an email using PHPMailer
function sendEmail($email, $subject, $message) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'lorem.ipsum.sample.email@gmail.com';
        $mail->Password   = 'novtycchbrhfyddx';
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        // Recipients
        $mail->setFrom('lorem.ipsum.sample.email@gmail.com', 'Krishiel Printing and Imaging Services');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = htmlspecialchars(trim($_POST['email']));

    if (empty($email)) {
        $error_message = "Please enter your email address!";
    } else {
        // Check if the email exists in the database
        $email_check_query = "SELECT * FROM customers WHERE email = ?";
        $stmt = $conn->prepare($email_check_query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Generate a random reset code (6 digits, for example)
            $reset_code = rand(100000, 999999);

            // Store the reset code in the database (you would normally set an expiration time for it)
            $sql = "UPDATE customers SET reset_code = ? WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $reset_code, $email);
            $stmt->execute();

            // Send the password reset email
            $subject = "Password Reset Request";
            $message = "
            <html>
            <head>
                <style>
                    body {
                        margin: 0;
                        padding: 0;
                        font-family: Arial, sans-serif;
                        background-image: url('https://example.com/bg-image.png'); /* Replace with actual image URL */
                        background-size: cover;
                        background-position: center;
                    }
                    .container {
                        width: 100%;
                        max-width: 600px;
                        margin: 0 auto;
                        background: rgba(255, 255, 255, 0.9);
                        padding: 30px;
                        border-radius: 15px;
                        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                    }
                    .logo-container {
                        text-align: center;
                        margin-bottom: 30px;
                    }
                    .logo {
                        width: 150px;
                        height: 150px;
                    }
                    .header {
                        font-family: 'Georgia', serif;
                        font-size: 28px;
                        color: #D5006D;
                        text-align: center;
                    }
                    .reset-code {
                        font-size: 20px;
                        color: #D5006D;
                        text-align: center;
                        font-weight: bold;
                    }
                    .message {
                        text-align: center;
                        font-size: 16px;
                        color: #333;
                    }
                    .footer {
                        text-align: center;
                        font-size: 12px;
                        color: #888;
                        margin-top: 20px;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='logo-container'>
                        <img class='logo' src='https://i.pinimg.com/originals/98/62/ec/9862ec725d8b5089f4fd920f496b4322.png' alt='Krishiel Logo'><!-- Replace with actual logo URL -->
                    </div>
                    <div class='header'>
                        KRISHIEL Printing and Imaging Services
                    </div>
                    <p class='message'>
                        You requested a password reset. Please use the following code to reset your password.
                    </p>
                    <p class='reset-code'>
                        Your Reset Code: <strong>$reset_code</strong>
                    </p>
                    <p class='message'>
                        If you did not request this, please ignore this email.
                    </p>
                    <div class='footer'>
                        &copy; 2024 Krishiel Printing and Imaging Services
                    </div>
                </div>
            </body>
            </html>
            ";

            $email_sent = sendEmail($email, $subject, $message);

            if ($email_sent) {
                $_SESSION['success_message'] = "Password reset email sent successfully! Check your inbox.";
                header("Location: user_resetpass.php?email=" . urlencode($email));
                exit(); // Ensure no further code is executed after the redirect
            } else {
                $error_message = "Unable to send password reset email. Please try again.";
            }
        } else {
            $error_message = "No account found with that email address.";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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

        /* Outer Container for Logo and Forgot Password Form */
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
            animation: pulseLogo 2s infinite alternate; /* Pulse animation */
        }

        /* Keyframe animation for pulsing effect */
        @keyframes pulseLogo {
            0% {
                transform: scale(1); /* Original size */
            }
            100% {
                transform: scale(1.1); /* Slightly larger */
            }
        }

        .krishiel-text {
            font-family: 'Georgia', serif; /* Added font-family */
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

        /* Main Container for the Forgot Password Form */
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

        .forgot-password-form {
            width: 100%;
            text-align: center;
        }

        .forgot-password-form h2 {
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

        .already-registered {
            display: block;
            margin-top: 10px;
            font-size: 14px;
            color: #D5006D;
            text-decoration: none;
        }

        .already-registered:hover {
            text-decoration: underline;
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
            <div class="forgot-password-form">
                <h2>Forgot Password</h2>

                <!-- Display success or error messages -->
                <?php if (isset($success_message)): ?>
                    <div class="success-message"><?php echo $success_message; ?></div>
                <?php elseif (isset($error_message)): ?>
                    <div class="error-message"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <!-- Forgot Password Form -->
                <form method="POST">
                    <input type="email" class="input-field" name="email" placeholder="Email Address" required>
                    <button class="submit-btn" type="submit">Send Reset Code</button>
                </form>
                <a href="user_login.php" class="already-registered">Remember your password? Login here</a>
            </div>
        </div>
    </div>

</body>

</html>