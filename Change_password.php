<?php
header('Content-Type: application/json');
include 'db.php';

try {
    $user_id = $_POST['user_id'] ?? '';
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($user_id) || empty($old_password) || empty($new_password) || empty($confirm_password)) {
        echo json_encode([
            'status' => false,
            'message' => 'All fields are required',
            'data' => []
        ]);
        exit;
    }

    if ($new_password !== $confirm_password) {
        echo json_encode([
            'status' => false,
            'message' => 'New passwords do not match',
            'data' => []
        ]);
        exit;
    }

    if (strlen($new_password) < 6) {
        echo json_encode([
            'status' => false,
            'message' => 'New password must be at least 6 characters long',
            'data' => []
        ]);
        exit;
    }

    $stmt = $sql->prepare("SELECT id, name, email, password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo json_encode([
            'status' => false,
            'message' => 'User not found',
            'data' => []
        ]);
        exit;
    }

    if ($old_password !== $user['password']) {
        echo json_encode([
            'status' => false,
            'message' => 'Incorrect old password',
            'data' => []
        ]);
        exit;
    }

    $stmt = $sql->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $new_password, $user_id);
    $stmt->execute();

    echo json_encode([
        'status' => true,
        'message' => 'Password changed successfully',
        'data' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'password' => $new_password
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => 'Server error',
        'data' => []
    ]);
}
?>
