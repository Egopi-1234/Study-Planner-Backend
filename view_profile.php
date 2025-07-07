<?php
header('Content-Type: application/json');
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? null;
    if ($userId === null) {
        echo json_encode([
            'status' => false,
            'message'    => 'User ID is required',
            'data'   => []
        ]);
        exit;
    }
    $stmt = $sql->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if ($user) {
        echo json_encode([
            'status' => true,
            'message'    => 'User profile retrieved successfully',
            'data'   => [$user]
        ]);
        exit;
    }
    else {
        echo json_encode([
            'status' => false,
            'message'    => 'User not found',
            'data'   => []
        ]);
        exit;
    }
}

?>