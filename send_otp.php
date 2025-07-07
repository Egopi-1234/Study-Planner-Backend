<?php
header('Content-Type: application/json');
require 'vendor/autoload.php';
include 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    if (empty($email)) {
        echo json_encode(['status' => false, 'message' => 'Email is required']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => false, 'message' => 'Invalid email format']);
        exit;
    }

    // Check if email exists
    $stmt = $sql->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 0) {
        echo json_encode(['status' => false, 'message' => 'Email not registered']);
        exit;
    }
    $stmt->close();

    // Generate OTP
    $otp = mt_rand(100000, 999999); // More secure than rand()
    $created_at = date("Y-m-d H:i:s");
    $expires_at = date("Y-m-d H:i:s", strtotime("+5 minutes"));

    // Insert OTP into DB
    $stmt = $sql->prepare("INSERT INTO password_otps (email, otp, created_at, expires_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $email, $otp, $created_at, $expires_at);
    if (!$stmt->execute()) {
        echo json_encode(['status' => false, 'message' => 'Failed to store OTP']);
        exit;
    }
    $stmt->close();

    // Send Email using PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = 2; 
        $mail->Debugoutput = function($str, $level) {
            error_log("SMTP Debug: $str");
        };
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'gopie852@gmail.com';
        $mail->Password   = 'kogg nwlt kmul pftf'; // Use app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('gopie852@gmail.com', 'StudyplannerApp');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Password Reset OTP';
        $mail->Body    = "Your OTP is <strong>$otp</strong>. It expires in 5 minutes.";

        $mail->send();
        echo json_encode(['status' => true, 'message' => 'OTP sent to email', 'data' => ['otp' => $otp]]);
    } catch (Exception $e) {
        echo json_encode(['status' => false, 'message' => 'Mail error: ' . $mail->ErrorInfo]);
    }
}
?>