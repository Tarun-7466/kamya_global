<?php
// Start session
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';


// Database connection
$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$dbname = "Kamyaglobal_db"; // Replace with your MySQL database name

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve user input from POST data (with basic sanitization)
    $UserName = isset($_POST['UserName']) ? htmlspecialchars($_POST['UserName']) : '';
    $Email = isset($_POST['Email']) ? filter_var($_POST['Email'], FILTER_SANITIZE_EMAIL) : '';
    $Subject = isset($_POST['Subject']) ? htmlspecialchars($_POST['Subject']) : '';
    $Message = isset($_POST['Message']) ? htmlspecialchars($_POST['Message']) : '';

    // Check if form fields are empty
    if (empty($UserName) || empty($Email) || empty($Subject) || empty($Message)) {
        $_SESSION['error_message'] = "Error: One or more form fields are empty.";
    } else {
        // Prepare and bind SQL statement
        $sql = "INSERT INTO `user_details` (`UserName`, `Email`, `Subject`, `Message`) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $UserName, $Email, $Subject, $Message);

        // Execute the statement
        if ($stmt->execute()) {
            // Send email to owner
            $mail = new PHPMailer;
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Google's SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'your_gmail_account@gmail.com'; // Your Gmail address
            $mail->Password = 'your_gmail_password'; // Your Gmail password
            $mail->SMTPSecure = 'tls'; // Enable TLS encryption
            $mail->Port = 587; // TCP port to connect to
            
            $mail->setFrom($Email, $UserName); // Sender email and name from form input
            $mail->addAddress('Kamyaglobal@gmail.com', 'Kamyaglobal'); // Recipient email and name
            
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = $Subject;
            $mail->Body    = $Message;

            if(!$mail->send()) {
                $_SESSION['error_message'] = 'Error sending email: ' . $mail->ErrorInfo;
            } else {
                $_SESSION['success_message'] = 'Your response has been sent.';
            }
        } else {
            $_SESSION['error_message'] = "Error: " . $stmt->error;
        }

        // Close statement and connection
        $stmt->close();
    }

    $conn->close();

    // Redirect to contact.html
    header("Location: contact.html");
    exit();
}
?>
