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

    if (empty($user_id) || empty($task) || empty($description) || empty($date) || empty($time) || empty($priority)) {
        echo json_encode([
            'status' => false,
            'message' => 'All fields are required',
            'data' => []
        ]);
        exit;
    }

    $stmt = $sql->prepare('INSERT INTO tasks (name, description, Date, time, Priority, user_id) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssssi', $task, $description, $date, $time, $priority, $user_id);

    if ($stmt->execute()) {
        // ✅ Fetch FCM token
        $token_stmt = $sql->prepare("SELECT fcm_token FROM users WHERE id = ?");
        $token_stmt->bind_param("i", $user_id);
        $token_stmt->execute();
        $result = $token_stmt->get_result();
        $token_data = $result->fetch_assoc();

        if (!empty($token_data['fcm_token'])) {
            $fcm_token = $token_data['fcm_token'];
            $title = "New Task Added";
            $body = "$task is scheduled on $date at $time.";
            sendFCMNotification($fcm_token, $title, $body);
        }

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

// ✅ Function to send FCM Notification
function sendFCMNotification($token, $title, $body)
{
    $url = "https://fcm.googleapis.com/fcm/send";
    $serverKey = "YOUR_FIREBASE_SERVER_KEY"; // Replace this with your actual key

    $notification = [
        'to' => $token,
        'notification' => [
            'title' => $title,
            'body' => $body,
            'sound' => 'default'
        ],
        'priority' => 'high'
    ];

    $headers = [
        'Authorization: key=' . $serverKey,
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notification));

    $response = curl_exec($ch);
    if ($response === FALSE) {
        error_log('FCM Send Error: ' . curl_error($ch));
    }
    curl_close($ch);
}
?>
