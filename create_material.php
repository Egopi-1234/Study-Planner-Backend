<?php
header('Content-Type: application/json');
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $due_date = $_POST['due_date'] ?? '';
    $due_time = $_POST['due_time'] ?? '';
    $user_id = $_POST['user_id'] ?? '';

    // Validate required fields
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

    // Ensure the directory exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true); // Create folder recursively with write permissions
    }

    // Base URL for file access
    $base_url = 'http://localhost/study_planner/';

    // Handle file upload if present
    if (!empty($_FILES['file']['name'])) {
        $file_name = time() . '_' . basename($_FILES["file"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            $file_path = $base_url . $target_file;
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'File upload failed',
                'data' => []
            ]);
            exit;
        }
    }

    // Prepare and execute the database insert
    $stmt = $sql->prepare("INSERT INTO materials (name, subject, due_date, due_time, file_path, user_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $name, $subject, $due_date, $due_time, $file_path, $user_id);

    if ($stmt->execute()) {
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
}
?>
