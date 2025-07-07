<?php
header('Content-Type: application/json');
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $material_id = $_POST['material_id'] ?? '';
    $user_id = $_POST['user_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $due_date = $_POST['due_date'] ?? '';
    $due_time = $_POST['due_time'] ?? '';

    if (empty($material_id) || empty($user_id) || empty($name) || empty($subject) || empty($due_date) || empty($due_time)) {
        echo json_encode([
            'status' => false,
            'message' => 'All fields are required',
            'data' => []
        ]);
        exit;
    }

    $file_path = '';
    if (!empty($_FILES['file']['name'])) {
        $target_dir = "uploads/materials/";
        $file_name = time() . '_' . basename($_FILES["file"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            $file_path = $target_file;
        }
    }

    if ($file_path) {
        $stmt = $sql->prepare("UPDATE materials SET name = ?, subject = ?, due_date = ?, due_time = ?, file_path = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssssssi", $name, $subject, $due_date, $due_time, $file_path, $material_id, $user_id);
    } else {
        $stmt = $sql->prepare("UPDATE materials SET name = ?, subject = ?, due_date = ?, due_time = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssssii", $name, $subject, $due_date, $due_time, $material_id, $user_id);
    }

    if ($stmt->execute()) {
        echo json_encode([
            'status' => true,
            'message' => 'Material updated successfully',
            'data' => [
                'id' => $material_id,
                'name' => $name,
                'subject' => $subject,
                'due_date' => $due_date,
                'due_time' => $due_time,
                'file_path' => $file_path
            ]
        ]);
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'Failed to update material',
            'data' => []
        ]);
    }
}
?>
