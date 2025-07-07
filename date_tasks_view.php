<?php
header('Content-Type: application/json');
include 'db.php';  // Assumes $sql is your MySQLi connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $user_id = $_POST['user_id'] ?? '';
    $date= $_POST['date'] ?? '';

    if (empty($user_id)) {
        echo json_encode([
            'status'  => false,
            'message' => 'User ID is required',
            'data'    => []
        ]);
        exit;
    }
    if (empty($date)) {
        echo json_encode([
            'status'  => false,
            'message' => 'Date is required',
            'data'    => []
        ]);
        exit;
    }
    if (!isset($date) || !DateTime::createFromFormat('Y-m-d', $date) || DateTime::createFromFormat('Y-m-d', $date)->format('Y-m-d') !== $date) {
    echo json_encode([
        'status'  => false,
        'message' => 'Invalid date format. Expected Y-m-d',
        'data'    => []
    ]);
    exit;
}

    $stmt = $sql->prepare("SELECT * FROM tasks WHERE user_id = ? AND date = ?  ORDER BY Date ASC, time ASC LIMIT 3");
    $stmt->bind_param('ss', $user_id, $date);
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
            'message' => 'tasks retrieved successfully',
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
