<?php
header('Content-Type: application/json');
include 'db.php';  // Assumes $sql is your MySQLi connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $user_id = $_POST['user_id'] ?? '';

    if (empty($user_id)) {
        echo json_encode([
            'status'  => false,
            'message' => 'User ID is required',
            'data'    => []
        ]);
        exit;
    }

    $today = date('Y-m-d');

    $stmt = $sql->prepare("SELECT * FROM tasks WHERE user_id = ? AND date >= ? AND status = 'Pending' ORDER BY Date ASC, time ASC LIMIT 5");
    $stmt->bind_param('ss', $user_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();

    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = [
            'id'         => $row['id'],
            'task'       => $row['name'],
            'description'=> $row['description'],
            'date'       => $row['Date'],
            'time'       => $row['time'],
            'priority'   => $row['Priority'],
            'status'     => $row['status']
        ];
    }

    $stmt->close();

    if (!empty($tasks)) {
        echo json_encode([
            'status'  => true,
            'message' => 'Upcoming tasks retrieved successfully',
            'data'    => $tasks
        ]);
    } else {
        echo json_encode([
            'status'  => false,
            'message' => 'No pending tasks found',
            'data'    => []
        ]);
    }

} else {
    echo json_encode([
        'status'  => false,
        'message' => 'Request method is invalid',
        'data'    => []
    ]);
}
?>
