<?php
ini_set('display_errors', 0);
error_reporting(0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

header('Content-Type: application/json');
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $Dept_info = trim($_POST['Dept_info'] ?? '');

    if (!is_numeric($userId)) {
        echo json_encode(['status' => false, 'message' => 'Invalid user ID', 'data' => []]);
        exit;
    }

    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => false, 'message' => 'Invalid email address', 'data' => []]);
        exit;
    }

    // Fetch existing values to support partial update
    $stmt = $sql->prepare('SELECT name, email, phone, Dept_info, profile_image FROM users WHERE id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();

    if (!$existing) {
        echo json_encode(['status' => false, 'message' => 'User not found', 'data' => []]);
        exit;
    }

    $name = $name !== '' ? $name : $existing['name'];
    $email = $email !== '' ? $email : $existing['email'];
    $phone = $phone !== '' ? $phone : $existing['phone'];
    $Dept_info = $Dept_info !== '' ? $Dept_info : $existing['Dept_info'];
    $profileImagePath = $existing['profile_image'];

    // Handle image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['profile_image']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($ext, $allowed)) {
            echo json_encode(['status' => false, 'message' => 'Unsupported image type', 'data' => []]);
            exit;
        }

        $newName = uniqid('img_', true) . '.' . $ext;
        $uploadDir = __DIR__ . '/uploads/profile_images/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $target = $uploadDir . $newName;

        if (!move_uploaded_file($tmp, $target)) {
            echo json_encode(['status' => false, 'message' => 'Failed to save uploaded image', 'data' => []]);
            exit;
        }

        $profileImagePath = 'uploads/profile_images/' . $newName;
    }

    // Update the user
    $stmt = $sql->prepare('UPDATE users SET name = ?, email = ?, phone = ?, Dept_info = ?, profile_image = ? WHERE id = ?');
    $stmt->bind_param('sssssi', $name, $email, $phone, $Dept_info, $profileImagePath, $userId);

    if (!$stmt->execute()) {
        echo json_encode(['status' => false, 'message' => 'Update failed', 'data' => []]);
        exit;
    }

    // Return updated data
    $stmt = $sql->prepare('SELECT id, name, email, phone, Dept_info, profile_image FROM users WHERE id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    echo json_encode([
        'status' => true,
        'message' => 'User updated successfully',
        'data' => [$user]
    ]);
} else {
    echo json_encode(['status' => false, 'message' => 'Invalid request method', 'data' => []]);
}
?>
