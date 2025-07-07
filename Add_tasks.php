<?php
header('Content-Type: application/json');
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $task = $_POST['task'] ?? '';
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $priority = $_POST['priority'] ?? '';

    if (empty($user_id) || empty($task) || empty($description) || empty($date) || empty($time) || empty($priority) ) {
        echo json_encode([
            'status' => false,
            'message' => 'All fields are required',
            'data' => []
        ]);
        exit;
    }

    $stmt = $sql->prepare('INSERT INTO tasks (name, description, Date, time, Priority, user_id) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssssi', $task, $description, $date, $time, $priority,  $user_id);

    if ($stmt->execute()) {
        echo json_encode([
            'status' => true,
            'message' => 'Task added successfully',
            'data' => [
                'id' => $stmt->insert_id,
                'task' => $task,
                'description' => $description,
                'date' => $date,
                'time' => $time,
                'priority' => $priority,
                'user_id' => $user_id
            ]
        ]);
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'Failed to add task',
            'data' => []
        ]);
    }
} else {
    echo json_encode([
        'status' => false,
        'message' => 'Invalid request method',
        'data' => []
    ]);
}
