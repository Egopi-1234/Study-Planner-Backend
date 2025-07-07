<?php
header('Content-Type: application/json');
include 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $task_id = $_POST['task_id'] ?? '';

    if (empty($user_id) || empty($task_id) ) {
        echo json_encode([
            'status' => false,
            'message' => 'Invalid input',
            'data' => []
        ]);
        exit;
    }

    $fetch_stmt = $sql->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
    $fetch_stmt->bind_param('ii', $task_id, $user_id);
    $fetch_stmt->execute();
    $result = $fetch_stmt->get_result();
    $task = $result->fetch_assoc();
    $fetch_stmt->close();

    if ($task) {
        echo json_encode([
            'status' => true,
            'message' => 'Task status updated successfully',
            'data' => [
                $task
            ]
        ]);
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'Task not found',
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
?>
