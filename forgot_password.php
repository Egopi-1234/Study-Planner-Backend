<?php
header('Content-Type: application/json');
include 'db.php';

date_default_timezone_set('Asia/Kolkata');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'message' => 'Invalid request method']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$otp = trim($_POST['otp'] ?? '');
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (!$email || !$otp || !$new_password || !$confirm_password) {
    echo json_encode(['status' => false, 'message' => 'All fields are required']);
    exit;
}

if ($new_password !== $confirm_password) {
    echo json_encode(['status' => false, 'message' => 'Passwords do not match']);
    exit;
}

// Validate OTP
$stmt = $sql->prepare("SELECT otp, expires_at FROM password_otps WHERE email = ? AND otp = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param('ss', $email, $otp);
$stmt->execute();
$result = $stmt->get_result();
$otp_record = $result->fetch_assoc();
$stmt->close();

if (!$otp_record) {
    echo json_encode([
        'status' => false,
        'message' => 'Invalid OTP',
        'data' => [
            'email' => $email,
            'otp'   => $otp,
            'new_password' => $new_password,
            'otp_record' => null
        ]
    ]);
    exit;
}

// Check expiration
try {
    $expiresAt = new DateTime($otp_record['expires_at'], new DateTimeZone('UTC'));
    $now = new DateTime('now', new DateTimeZone('UTC'));

    if ($expiresAt < $now) {
        echo json_encode(['status' => false, 'message' => 'Expired OTP']);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['status' => false, 'message' => 'Invalid OTP expiration time']);
    exit;
}

// Hash password (you should hash it properly, not just store raw)
// $password_hash = password_hash($new_password, PASSWORD_BCRYPT);

// Update password
$stmt = $sql->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->bind_param('ss', $new_password, $email);

if (!$stmt->execute()) {
    echo json_encode(['status' => false, 'message' => 'Failed to update password: ' . $stmt->error]);
    exit;
}
$stmt->close();

// Get user info
$stmt = $sql->prepare("SELECT id, email FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

// Delete used OTPs
$stmt = $sql->prepare("DELETE FROM password_otps WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->close();

echo json_encode([
    'status' => true,
    'message' => 'Password reset successfully',
    'data' => $user
]);
?>
