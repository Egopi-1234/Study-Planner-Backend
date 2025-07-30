<?php
header('Content-Type: application/json');
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $due_date = $_POST['due_date'] ?? '';
    $due_time = $_POST['due_time'] ?? '';
    $user_id = $_POST['user_id'] ?? '';

    if (empty($name) || empty($subject) || empty($due_date) || empty($due_time) || empty($user_id)) {
        echo json_encode([
            'status' => false,
            'message' => 'All fields are required',
            'data' => []
        ]);
        exit;
    }

    $file_path = '';
    $target_dir = "uploads/materials/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (!empty($_FILES['file']['name'])) {
        $file_name = time() . '_' . basename($_FILES["file"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            $file_path = $target_file;
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'File upload failed',
                'data' => []
            ]);
            exit;
        }
    }

    $stmt = $sql->prepare("INSERT INTO materials (name, subject, due_date, due_time, file_path, user_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $name, $subject, $due_date, $due_time, $file_path, $user_id);

    if ($stmt->execute()) {
        // ✅ Fetch FCM token
        $token_stmt = $sql->prepare("SELECT fcm_token FROM users WHERE id = ?");
        $token_stmt->bind_param("i", $user_id);
        $token_stmt->execute();
        $result = $token_stmt->get_result();
        $token_data = $result->fetch_assoc();

        if (!empty($token_data['fcm_token'])) {
            $fcm_token = $token_data['fcm_token'];
            $title = "New Study Material Added";
            $body = "$name - $subject due on $due_date at $due_time.";
            sendFCMNotification($fcm_token, $title, $body);
        }

        echo json_encode([
            'status' => true,
            'message' => 'Material added successfully',
            'data' => [[
                'id' => $stmt->insert_id,
                'name' => $name,
                'subject' => $subject,
                'due_date' => $due_date,
                'due_time' => $due_time,
                'file_path' => $file_path
            ]]
        ]);
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'Failed to add material',
            'data' => []
        ]);
    }

    $stmt->close();
} else {
    echo json_encode([
        'status' => false,
        'message' => 'Invalid request method',
        'data' => []
    ]);
}

// ✅ FCM Notification Function
function sendFCMNotification($token, $title, $body)
{
    $url = "https://fcm.googleapis.com/fcm/send";
    $serverKey = "YOUR_FIREBASE_SERVER_KEY"; // Replace with your actual FCM server key

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
