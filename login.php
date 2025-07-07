<?php
header('Content-Type: application/json');
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $stmt = $sql->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();


    if ($password== $user['password']) {
        echo json_encode([
            'status' => true,
            'message'    => 'Login successful',
            'data'   => [
                'id' => $user['Id'],
                'username' => $user['Username'],
                'email' => $user['email'],
                'password' => $user['password']
            ]
        ]);
        exit;
    }
    echo json_encode([
        'status' => false,
        'message'    => 'Invalid email or password',
        'data'   => []
    ]);
    exit;
}
echo json_encode([
    'status' => false,
    'message'    => 'Invalid request method',
    'data'   => []
]);
