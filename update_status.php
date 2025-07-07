<?php
header('Content-Type: application/json');
include 'db.php'; // Ensure this sets up $sql as your mysqli connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $task_id = $_POST['task_id'] ?? '';
    $status = $_POST['status'] ?? '';

    // Validate inputs
    if (empty($user_id) || empty($task_id) || !in_array($status, ['Pending', 'Complete'])) {
        echo json_encode([
            'status' => false,
            'message' => 'Invalid input',
            'data' => []
        ]);
        exit;
    }

    $update_stmt = $sql->prepare('UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?');
    if (!$update_stmt) {
        echo json_encode([
            'status' => false,
            'message' => 'Prepare failed: ' . $sql->error,
            'data' => []
        ]);
        exit;
    }
    $update_stmt->bind_param('sii', $status, $task_id, $user_id);
    $update_stmt->execute();
    $update_stmt->close();

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
