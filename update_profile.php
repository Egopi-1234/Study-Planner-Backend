<?php

ini_set('display_errors', 0);
error_reporting(0); 
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

header('Content-Type: application/json');
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $Dept_info = trim($_POST['Dept_info'] ?? '');

    if ($userId === null) {
        echo json_encode([
            'status' => false,
            'message' => 'User ID is required',
            'data' => []
        ]);
        exit;
    }

    if ($name === '' && $email === '' && $phone === '' && $Dept_info === '') {
        echo json_encode([
            'status' => false,
            'message' => 'At least one field is required to update',
            'data' => []
        ]);
        exit;
    }

    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'status' => false,
            'message' => 'Invalid email address',
            'data' => []
        ]);
        exit;
    }

    // Handle image upload if provided
    $profileImagePath = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['profile_image']['tmp_name'];
        $filename = basename($_FILES['profile_image']['name']);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($ext, $allowed)) {
            echo json_encode(['status' => false, 'message' => 'Unsupported image type', 'data' => []]);
            exit;
        }

        $newName = uniqid('img_') . '.' . $ext;
        $uploadDir = __DIR__ . '/uploads/profile_images/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $target = $uploadDir . $newName;

        if (!move_uploaded_file($tmp, $target)) {
            echo json_encode(['status' => false, 'message' => 'Failed to move uploaded file', 'data' => []]);
            exit;
        }

        $profileImagePath = 'uploads/profile_images/' . $newName;
    }

    // Build and execute the UPDATE SQL
    if ($profileImagePath) {
        $stmt = $sql->prepare('UPDATE users SET name = ?, email = ?, phone = ?, Dept_info = ?, profile_image = ? WHERE id = ?');
        $stmt->bind_param('sssssi', $name, $email, $phone, $Dept_info, $profileImagePath, $userId);
    } else {
        $stmt = $sql->prepare('UPDATE users SET name = ?, email = ?, phone = ?, Dept_info = ? WHERE id = ?');
        $stmt->bind_param('ssssi', $name, $email, $phone, $Dept_info, $userId);
    }

    if (!$stmt->execute()) {
        echo json_encode(['status' => false, 'message' => 'Update failed', 'data' => []]);
        exit;
    }

    // Fetch updated user data
    $stmt = $sql->prepare('SELECT id, name, email, phone, Dept_info, profile_image FROM users WHERE id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    echo json_encode([
        'status' => true,
        'message' => 'User updated successfully',
        'data' => [ $user ]
    ]);
} else {
    echo json_encode([
        'status' => false,
        'message' => 'Invalid request method',
        'data' => []
    ]);
    exit;
}
?>
