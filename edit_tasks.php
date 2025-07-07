<?php
header('Content-Type: application/json');
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = $_POST['task_id'] ?? '';
    $user_id = $_POST['user_id'] ?? '';
    $task = $_POST['task'] ?? '';
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $priority = $_POST['priority'] ?? '';

    if (
        empty($task_id) || empty($user_id) || empty($task) || empty($description)
        || empty($date) || empty($time) || empty($priority)
    ) {
        echo json_encode([
            'status' => false,
            'message' => 'All fields are required',
            'data' => []
        ]);
        exit;
    }

    $stmt = $sql->prepare('UPDATE tasks SET name = ?, description = ?, Date = ?, time = ?, Priority = ? WHERE id = ? AND user_id = ?');
    $stmt->bind_param('sssssii', $task, $description, $date, $time, $priority, $task_id, $user_id);

    if ($stmt->execute()) {
        echo json_encode([
            'status' => true,
            'message' => 'Task updated successfully',
            'data' => [
                'id' => $task_id,
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
            'message' => 'Failed to update task',
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
