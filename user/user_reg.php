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
    // Sanitize and validate form inputs
    $customer_name = htmlspecialchars(trim($_POST['customer_name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));
    $confirm_password = htmlspecialchars(trim($_POST['confirm_password']));

    // Basic validation for required fields
    if (empty($customer_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        // Check if passwords match
        $error_message = "Passwords do not match!";
    } else {
        // Check if the email already exists in the database
        $email_check_query = "SELECT * FROM customers WHERE email = ?";
        $stmt = $conn->prepare($email_check_query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // If email exists, show an error message
            $error_message = "This email is already registered.";
        } else {
            // Generate a random verification code
            $verification_code = rand(100000, 999999);

            // Prepare the SQL query
            $sql = "INSERT INTO customers (customer_name, email, password, verification_code, is_verified) VALUES (?, ?, ?, ?, 0)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $customer_name, $email, $password, $verification_code); // Use plain text password

            if ($stmt->execute()) {
                // Send verification email
                $subject = "Email Verification";
                $message = "
                <html>
                <head>
                    <title>Email Verification</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            background-color: #f4f4f4;
                            margin: 0;
                            padding: 20px;
                        }
                        .email-container {
                            background-color: #ffffff;
                            padding: 20px;
                            border-radius: 8px;
                            max-width: 600px;
                            margin: 0 auto;
                            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                        }
                        h2 {
                            color: #333;
                        }
                        p {
                            color: #555;
                            line-height: 1.6;
                        }
                        .verification-code {
                            display: inline-block;
                            padding: 10px 20px;
                            background-color: #007bff;
                            color: #ffffff;
                            border-radius: 4px;
                            font-size: 20px;
                            font-weight: bold;
                        }
                        .footer {
                            margin-top: 20px;
                            font-size: 12px;
                            color: #888;
                            text-align: center;
                        }
                        .logo-container {
                            text-align: center;
                            margin-bottom: 20px;
                        }
                        .logo {
                            width: 150px;
                            height: 150px;
                        }
                    </style>
                </head>
                <body>
                    <div class='email-container'>
                        <div class='logo-container'>
                            <img class='logo' src='https://i.pinimg.com/originals/98/62/ec/9862ec725d8b5089f4fd920f496b4322.png' alt='Krishiel Logo'>
                        </div>
                        <h2>Email Verification</h2>
                        <p>Dear $customer_name,</p>
                        <p>Thank you for registering with Krishiel Printing and Imaging Services. Please use the verification code below to verify your email address:</p>
                        <p class='verification-code'>$verification_code</p>
                        <p>If you did not register, please ignore this email.</p>
                        <p class='footer'>Best regards,<br>Krishiel Team</p>
                    </div>
                </body>
                </html>
                ";
                

                $email_sent = sendEmail($email, $subject, $message);

                if ($email_sent) {
                    $show_success_overlay = true;
                    $redirect_delay = 2; // 2 seconds delay before redirection
                } else {
                    $error_message = "Registration successful, but we couldn't send the verification email. Please try again.";
                }
            } else {
                $error_message = "Something went wrong. Please try again.";
            }
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
    <title>Customer Registration</title>
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

        /* Outer Container for Logo and Registration Form */
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

        /* Main Container for the Registration Form */
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

        .register-form {
            width: 100%;
            text-align: center;
        }

        .register-form h2 {
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

        .register-btn {
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

        .register-btn:hover {
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
            .register-btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <!-- Success Message Overlay -->
    <?php if (isset($show_success_overlay) && $show_success_overlay): ?>
    <div class="success-overlay visible">
        Registration Successful! Redirecting to Verification...
    </div>
    <?php endif; ?>

    <div class="outer-container">
        <div class="logo-container">
            <img class="logo" src="../images/logo.png" alt="Logo">
            <div class="krishiel-text">KRISHIEL</div>
            <div class="printing-services">Printing And Imaging Services</div>
        </div>

        <div class="container">
            <div class="register-form">
                <h2>CUSTOMER REGISTRATION</h2>

                <!-- Display success or error messages -->
                <?php if (isset($success_message)): ?>
                    <div style="color: green; font-size: 18px;"><?php echo $success_message; ?></div>
                <?php elseif (isset($error_message)): ?>
                    <div style="color: red; font-size: 18px;"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <!-- Registration Form -->
                <form id="registrationForm" method="POST">
                    <input type="text" class="input-field" name="customer_name" id="customer_name" placeholder="Full Name" required>
                    <input type="email" class="input-field" name="email" id="email" placeholder="Email Address" required>
                    <input type="password" class="input-field" name="password" id="password" placeholder="Password" required minlength="8">
                    <input type="password" class="input-field" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required minlength="8">
                    <span id="passwordError" class="error-message" style="display: none;">Passwords do not match!</span>
                    <button class="register-btn" type="submit">REGISTER</button>
                </form>
                <a href="user_login.php" class="already-registered">Already have an account? Login here</a>
            </div>
        </div>
    </div>

    <script>
        // Redirect the user after a delay (5 seconds)
        <?php if (isset($redirect_delay)): ?>
            setTimeout(function() {
                window.location.href = 'user_verification.php';
            }, <?php echo $redirect_delay * 1000; ?>);
        <?php endif; ?>

        const passwordField = document.getElementById('password');
        const confirmPasswordField = document.getElementById('confirm_password');
        const passwordError = document.getElementById('passwordError');

        confirmPasswordField.addEventListener('input', function () {
            if (passwordField.value !== confirmPasswordField.value) {
                confirmPasswordField.style.borderColor = 'red';
                passwordError.style.display = 'block';
            } else {
                confirmPasswordField.style.borderColor = '#FF69B4';
                passwordError.style.display = 'none';
            }
        });

        document.getElementById('registrationForm').addEventListener('submit', function (event) {
            if (passwordField.value !== confirmPasswordField.value) {
                event.preventDefault();
                alert("Passwords do not match!");
            }
        });
    </script>

</body>

</html>
