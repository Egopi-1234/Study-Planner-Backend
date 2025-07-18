<?php
header('Content-Type: application/json');
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name            = trim($_POST['name'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
        echo json_encode([
            'status' => false,
            'message' => 'All fields are required',
            'data' => []
        ]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'status' => false,
            'message' => 'Invalid email address',
            'data' => []
        ]);
        exit;
    }

    if ($password !== $confirmPassword) {
        echo json_encode([
            'status' => false,
            'message' => 'Passwords do not match',
            'data' => []
        ]);
        exit;
    }

    // Check only email uniqueness
    $stmt = $sql->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode([
            'status' => false,
            'message' => 'Email already taken',
            'data' => []
        ]);
        exit;
    }

    // Insert into database (assumes column is named 'name' not 'username')
    $insert = $sql->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
    $insert->bind_param('sss', $name, $email, $password);

    if ($insert->execute()) {
        $id = $insert->insert_id;
        echo json_encode([
            'status' => true,
            'message' => 'Registration successful',
            'data' => [
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'password' => $password
            ]
        ]);
        exit;
    }

    echo json_encode([
        'status' => false,
        'message' => 'Registration failed, please try again',
        'data' => []
    ]);
    exit;
}

echo json_encode([
    'status' => false,
    'message' => 'Invalid request method',
    'data' => []
]);
