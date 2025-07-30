<?php
header('Content-Type: application/json');
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? '';

    if (empty($user_id)) {
        echo json_encode([
            'status' => false,
            'message' => 'User ID is required',
            'data' => []
        ]);
        exit;
    }

    $stmt = $sql->prepare("SELECT id, name, subject, due_date, due_time, file_path, created_at ,status FROM materials WHERE user_id = ? ORDER BY due_date ASC, due_time ASC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $materials = [];
    while ($row = $result->fetch_assoc()) {
        $materials[] = $row;
    }

    echo json_encode([
        'status' => true,
        'message' => 'Materials fetched successfully',
        'data' => $materials
    ]);
} else {
    echo json_encode([
        'status' => false,
        'message' => 'Invalid request method',
        'data' => []
    ]);
}
?>
